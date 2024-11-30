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

// Function to handle file upload
function handleFileUpload($file) {
    global $allowed_types, $target_dir, $conn;

    // Check for file upload errors
    if ($file['error'] !== 0) {
        return "Error with file upload.";
    }

    // Check if file size is within limits
    if ($file['size'] > MAX_FILE_SIZE) {
        return "File is too large. Max allowed size is 5MB.";
    }

    // Validate file type
    $file_type = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if (!in_array($file_type, $allowed_types)) {
        return "Invalid file type. Allowed types: pdf, doc, docx, ppt, pptx, jpeg, jpg, png.";
    }

    // Ensure safe file name
    $target_file = $target_dir . basename($file["name"]);
    
    // Attempt file upload
    if (!move_uploaded_file($file["tmp_name"], $target_file)) {
        return "Error uploading the file.";
    }

    // Store file info in the database
    $stmt = $conn->prepare("INSERT INTO files (filename, filesize, filetype) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $file["name"], $file["size"], $file["type"]);
    if ($stmt->execute()) {
        return "File uploaded and information stored.";
    } else {
        return "Error storing file information in the database.";
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
            return "File deleted successfully.";
        } else {
            return "Error deleting file from database.";
        }
    } else {
        return "Error deleting file from server or file not found.";
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
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <h3>Upload a file</h3>
        <form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="file" class="form-label">Select file</label>
                <input type="file" class="form-control" name="file" id="file" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload file</button>
        </form>

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
</body>
</html>

<?php
$conn->close();
?>
