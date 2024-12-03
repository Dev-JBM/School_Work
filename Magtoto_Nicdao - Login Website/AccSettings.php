<?php
// Start the session
ob_start();
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include 'DB_Conn.php';

// Check if the username is set in the session
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Query to fetch the image name
    $query = "SELECT image FROM userlog WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $image = $row['image'];

        // Construct the file path (assuming images are stored in the 'images/' folder)
        $imagePath = "user-Img/" . $image;

        // Check if the file exists
        if (file_exists($imagePath)) {
            //do nothing
        } else {
            echo "Image file not found.";
        }
    } else {
        echo "No image found for this user.";
    }

    // Close the statement
    mysqli_stmt_close($stmt);
} else {
    echo "User is not logged in.";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">;
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style_dashboard.css">
</head>

<body>
    <div class="container">
        <nav>
            <h4 class="user">Welcome,<br><?php echo htmlspecialchars($_SESSION['username']); ?>!</h4>
            <a href="Dashboard.php"><img src="logo.png" class="logo"></a>
            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Profile Picture" class="profilePic" onclick="togglemenu()">

            <div class="sub-menu-wrap" id="sub-menu-wrap">
                <div class="sub-menu">
                    <div class="user-info">
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" class=" ">
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



    <!-- ---Account Settings--- -->
    <!-- Update Profile Pic -->
    <div class="settings">

        <div class="userImg">
            <form class="" action="" enctype="multipart/form-data" method="post">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>">

                <div class="userImg-pic">
                    <img src="<?php echo htmlspecialchars($imagePath); ?>" id="image">

                    <div class="rightRound" id="upload">
                        <input type="file" name="fileImg" id="fileImg" accept=".jpg, .jpeg, .png">
                        <i class="fa fa-camera"></i>
                    </div>
                    <div class="leftRound" id="cancel" style="display: none;">
                        <i class="fa fa-times"></i>
                    </div>
                    <div class="rightRound" id="confirm" style="display: none;">
                        <input type="submit" name="" id="check" value="">
                        <i class="fa fa-check"></i>
                    </div>

                </div>

                <script type="text/javascript">
                    // Store the previous image source when the file is selected
                    var previousImageSrc = document.getElementById("image").src;

                    var userImage = document.getElementById("fileImg").onchange = function() {
                        const file = this.files[0]; // Get the selected file
                        if (file) {
                            // Preview the selected image
                            document.getElementById("image").src = URL.createObjectURL(file);

                            // Show Cancel and Confirm buttons
                            document.getElementById("cancel").style.display = "block";
                            document.getElementById("confirm").style.display = "block";
                            document.getElementById("upload").style.display = "none";
                        }
                    };

                    document.getElementById("cancel").onclick = function() {
                        // Revert to the previous image source
                        document.getElementById("image").src = previousImageSrc;

                        // Hide Cancel and Confirm buttons
                        document.getElementById("cancel").style.display = "none";
                        document.getElementById("confirm").style.display = "none";
                        document.getElementById("upload").style.display = "block";

                        // Reset the file input to allow re-uploading a new image
                        document.getElementById("fileImg").value = "";
                    };

                    document.getElementById("confirm").onclick = function() {
                        // You can handle the upload logic here if needed
                        // After confirming, hide the buttons again
                        document.getElementById("cancel").style.display = "none";
                        document.getElementById("confirm").style.display = "none";
                        document.getElementById("upload").style.display = "block";
                    };
                </script>

<?php
include 'DB_Conn.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["fileImg"]["name"])) {
    if (isset($_POST['username'])) {
        $username = $_POST['username'];
    } else {
        echo "Username is missing.";
        exit;
    }

    // File upload logic
    $src = $_FILES["fileImg"]["tmp_name"];
    $imageName = uniqid() . "_" . basename($_FILES["fileImg"]["name"]);
    $target = "user-Img/" . $imageName;

    if (is_uploaded_file($src)) {
        if (move_uploaded_file($src, $target)) {
            $query = "UPDATE userlog SET image = ? WHERE username = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ss", $imageName, $username);

            if (mysqli_stmt_execute($stmt)) {
                // Redirect to avoid header issues
                header("Location: AccSettings.php");
                exit;
            } else {
                echo "Failed to update the database.";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Failed to move the uploaded file.";
        }
    } else {
        echo "File upload failed.";
    }
}
?>

            </form>
            <br>
            <p>Edit Profile Picture - Click Camera Icon<br> Allowed format: .jpg, .jpeg, .png</p>
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