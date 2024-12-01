<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
require_once 'DB_Conn.php';

// Maximum file size (5MB)
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB in bytes

// Allowed file types
$allowed_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpeg', 'jpg', 'png'];
$target_dir = "uploads/";

function handleFileUpload($file) {
    global $allowed_types, $target_dir, $conn;

    // Check for file upload errors
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        echo "<script>alert('File is too large. Max allowed size is 5MB');</script>";
        return false; // Stop execution
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        echo "<script>alert('Error with file upload');</script>";
        return false; // Stop execution
    }

    // Check if file size is within limits (for further validation, even if PHP didn't block it)
    if ($file['size'] > MAX_FILE_SIZE) {
        echo "<script>alert('File is too large. Max allowed size is 5MB');</script>";
        return false; // Stop execution
    }

    // Validate file type
    $file_type = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if (!in_array($file_type, $allowed_types)) {
        echo "<script>alert('Invalid file type. Allowed types: " . implode(", ", $allowed_types) . "');</script>";
        return false; // Stop execution
    }

    // Ensure safe file name
    $target_file = $target_dir . basename($file["name"]);

    // Check if file already exists to avoid overwriting
    if (file_exists($target_file)) {
        echo "<script>alert('File already exists. Please rename the file and try again.');</script>";
        return false; // Stop execution
    }

    // Attempt file upload
    if (!move_uploaded_file($file["tmp_name"], $target_file)) {
        echo "<script>alert('Error uploading the file');</script>";
        return false; // Stop execution
    }

    // Store file info in the database
    $stmt = $conn->prepare("INSERT INTO files (filename, filesize, filetype) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $file["name"], $file["size"], $file_type);
    if ($stmt->execute()) {
        echo "<script>alert('File uploaded and information stored');</script>";
        return true;
    } else {
        echo "<script>alert('Error storing file information in the database');</script>";
        return false;
    }
}

// Handle file upload on POST request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    echo handleFileUpload($_FILES["file"]);
}

// Handle file deletion
function deleteFile($file_id, $file_name) {
    global $conn, $target_dir;

    $file_path = $target_dir . basename($file_name);

    if (file_exists($file_path) && unlink($file_path)) {
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
        $stmt->bind_param("i", $file_id);
        if ($stmt->execute()) {
           echo "<script>alert('File Deleted Successfully');</script>";
        } else {
           echo "<script>alert('Error deleting the file from the database');</script>";
        }
    } else {
           echo "<script>alert('Error deleting the file from the server or file not found');</script>";
    }
}

// Handle file deletion on POST request
if (isset($_POST['delete'])) {
    $file_id = $_POST['file_id'];
    $file_name = $_POST['file_name'];
    echo deleteFile($file_id, $file_name);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style_dashboard.css">
    <script src="Upload.js"></script>
</head>

<body>
    <div class="container">
        <nav>
            <h4 class="user">Welcome,<br><?php echo htmlspecialchars($_SESSION['username']); ?>!</h4>
            <a href="Dashboard.php"><img src="logo.png" class="logo"></a>
            <img src="noProfile.jpeg" alt="Profile Picture" class="profilePic" onclick="togglemenu()">

            <div class="sub-menu-wrap" id="sub-menu-wrap">
                <div class="sub-menu">
                    <div class="user-info">
                        <img src="noProfile.jpeg" class=" ">
                        <h5><?php echo htmlspecialchars($_SESSION['username']); ?></h5>
                    </div>
                    <hr>

                    <a href="ProfileInfo.php" class="sub-menu-link">
                        <img src="userInfo.png">
                        <p>Profile Info</p>
                        <span>></span>
                    </a>

                    <a href="AccSettings.php" class="sub-menu-link">
                        <img src="settings.png">
                        <p>Account Settings</p>
                        <span>></span>
                    </a>

                    <a href="#" class="sub-menu-link" onclick="showAlert()">
                        <img src="logout.png">
                        <p>Logout</p>
                        <span>></span>
                    </a>
                </div>
            </div>
        </nav>
    </div>
    <script>
        let submenu = document.getElementById("sub-menu-wrap");

        function togglemenu() {
            submenu.classList.toggle("open-class");
        }
    </script>

    <!-- UPLOAD FILES -->
    <div class="container mt-5">
        <div class="upload" id="upload-area">
            <h3>Upload a file</h3>
            <form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST" enctype="multipart/form-data">
                <input type="file" class="form-control" name="file" id="file" value="5242880" required hidden>
                <label for="file" id="file-label">
                    <h5>Drag or Choose a file</h5>
                </label>
                <button type="submit" class="btn btn-primary mt-3">Upload file</button>
            </form>
        </div>
        <div class="uploaded_files">
            <h3 class="mt-5">Uploaded Files</h3>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>File Size</th>
                        <th>File Type</th>
                        <th>Download</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                // Fetch files from database and display them
                $result = $conn->query("SELECT * FROM files");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $file_path = $target_dir . $row['filename'];
                        echo "<tr>
                            <td>" . htmlspecialchars($row['filename']) . "</td>
                            <td>" . $row['filesize'] . " bytes</td>
                            <td>" . htmlspecialchars($row['filetype']) . "</td>
                            <td><a href='" . htmlspecialchars($file_path) . "' class='btn btn-primary' download>Download</a></td>
                            <td>
                                <form action='Dashboard.php' method='POST' style='display:inline;'>
                                    <input type='hidden' name='file_id' value='{$row['id']}'>
                                    <input type='hidden' name='file_name' value='{$row['filename']}'>
                                    <button type='submit' name='delete' class='btn btn-danger'>Delete</button>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No files uploaded yet.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- LOGOUT -->
    <div class="logoutAlert">
        <div id="alertOverlay"></div>
        <div id="alertBox">
            <p>Are you sure you want to Logout?</p>
            <div class="button-container">
                <button onclick="handleOk()">OK</button>
                <button onclick="handleCancel()">Cancel</button>
            </div>
        </div>

        <script>
            function showAlert() {
                document.getElementById("alertBox").style.display = "block";
                document.getElementById("alertOverlay").style.display = "block";
            }

            function handleOk() {
                window.location.href = "Logout.php";
                closeAlert();
            }

            function handleCancel() {
                console.log("Cancel clicked");
                closeAlert();
            }

            function closeAlert() {
                document.getElementById("alertBox").style.display = "none";
                document.getElementById("alertOverlay").style.display = "none";
            }
        </script>
    </div>
</body>

</html>

<?php
$conn->close();
?>