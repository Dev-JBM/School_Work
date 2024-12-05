<?php
// Start the session
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include 'DB_Conn.php';

// Initialize alert message
$alertMessage = "";

// Fetch user data if logged in
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Fetch user image
    $query = "SELECT image FROM userlog WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $image = $row['image'];
        $imagePath = "user-Img/" . $image;
        if (!file_exists($imagePath)) {
            $_SESSION['error'] = "Image file not found.";
        }
    } else {
        $_SESSION['error'] = "No image found for this user.";
    }
    mysqli_stmt_close($stmt);

    // Fetch user name details
    $sqlName = "SELECT first_name, middle_name, last_name FROM userlog WHERE username = ?";
    $stmtName = $conn->prepare($sqlName);
    $stmtName->bind_param("s", $username);
    $stmtName->execute();
    $resultName = $stmtName->get_result();

    if ($resultName->num_rows > 0) {
        $rowName = $resultName->fetch_assoc();
        $firstName = $rowName['first_name'];
        $middleName = $rowName['middle_name'];
        $lastName = $rowName['last_name'];
    } else {
        $_SESSION['error'] = "No name data found for the logged-in user.";
    }
    $stmtName->close();
} else {
    $_SESSION['error'] = "User is not logged in.";
}

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["fileImg"]["name"])) {
    $src = $_FILES["fileImg"]["tmp_name"];
    $imageName = uniqid() . "_" . basename($_FILES["fileImg"]["name"]);
    $target = "user-Img/" . $imageName;

    if (is_uploaded_file($src)) {
        if (move_uploaded_file($src, $target)) {
            $query = "UPDATE userlog SET image = ? WHERE username = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ss", $imageName, $username);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "Profile picture updated successfully.";
                header("Location: AccSettings.php");
                exit;
            } else {
                $_SESSION['error'] = "Failed to update the database.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['error'] = "Failed to move the uploaded file.";
        }
    } else {
        $_SESSION['error'] = "File upload failed.";
    }
}

// Set alert message
if (isset($_SESSION['success'])) {
    $alertMessage = "<div class='alert alert-success'>{$_SESSION['success']}</div>";
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    $alertMessage = "<div class='alert alert-danger'>{$_SESSION['error']}</div>";
    unset($_SESSION['error']);
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

            <!-- New container for fname and profilePic -->
            <div class="right-section">
                <h5 class="fname"><?php echo htmlspecialchars($firstName); ?></h5>
                <img src="<?php echo $imagePath; ?>" alt="Profile Picture" class="profilePic" onclick="togglemenu()">
            </div>

            <div class="sub-menu-wrap" id="sub-menu-wrap">
                <div class="sub-menu">
                    <div class="user-info">
                        <img src="<?php echo $imagePath; ?>" class=" ">
                        <h5 class="fullName"><?php echo htmlspecialchars(trim(($firstName ?? '') . ' ' . ($middleName ?? '') . ' ' . ($lastName ?? ''))); ?></h5>
                    </div>
                    <hr>

                    <a href="Dashboard.php" class="sub-menu-link">
                        <img src="home.png">
                        <p>Home</p>
                        <span>></span>
                    </a>

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
    <div class="settings"><br>
        <div class="wrapper">
            <div class="wrapper-header">
                <a href="ProfileInfo.php" class="sub-menu-link" style="margin-left: 5px;">
                    <img src="back.png">
                </a>
                <h1>Edit Information</h1>
                <a href="Dashboard.php" class="sub-menu-link">
                    <img src="home.png">
                </a>
            </div>

            <?php echo $alertMessage; ?>

            <!-- Update Profile Pic -->
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

                <!-- Edit Information -->
                <?php
if (!isset($_SESSION['username'])) {
    echo "User is not logged in.";
    exit;
}

$currentUsername = $_SESSION['username'];

// Fetch existing user information
$query = "SELECT first_name, middle_name, last_name, email, date_of_birth, username FROM userlog WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $currentUsername);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $firstName = $row['first_name'];
    $middleName = $row['middle_name'];
    $lastName = $row['last_name'];
    $email = $row['email'];
    $dob = $row['date_of_birth'];
    $username = $row['username'];
} else {
    echo "User data not found.";
    exit;
}

mysqli_stmt_close($stmt);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = mysqli_real_escape_string($conn, $_POST['username']);
    $firstName = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middleName = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $lastName = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $dob = mysqli_real_escape_string($conn, $_POST['date_of_birth']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate new username
    if ($newUsername !== $currentUsername) {
        $checkUsernameQuery = "SELECT * FROM userlog WHERE username = ?";
        $checkStmt = mysqli_prepare($conn, $checkUsernameQuery);
        mysqli_stmt_bind_param($checkStmt, "s", $newUsername);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        if (mysqli_num_rows($checkResult) > 0) {
            echo "<script>alert('Username is already taken.');</script>";
            exit;
        }

        mysqli_stmt_close($checkStmt);
    }

    // Build the update query
    $query = "UPDATE userlog SET username = ?, first_name = ?, middle_name = ?, last_name = ?, email = ?, date_of_birth = ?";
    $params = [$newUsername, $firstName, $middleName, $lastName, $email, $dob];

    $query .= " WHERE username = ?";
    $params[] = $currentUsername;

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, str_repeat("s", count($params)), ...$params);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['username'] = $newUsername; // Update the session with the new username
    $_SESSION['success'] = "Profile Info updated successfully.";
    // Allow redirection, but the alert should persist
    header("Location: AccSettings.php");
    exit;

    } else {
        $_SESSION['error'] = "Error updating profile" . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}
?>

                <form action="" method="post">

                    <div class="input-box">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required disabled>
                    </div>

                    <div class="input-box">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>" required disabled>
                    </div>

                    <div class="input-box">
                        <label for="middle_name">Middle Name:</label>
                        <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($middleName); ?>" disabled>
                    </div>

                    <div class="input-box">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>" required disabled>
                    </div>

                    <div class="input-box">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required disabled>
                    </div>

                    <div class="input-box">
                        <label for="dob">Date of Birth:</label>
                        <input type="date" id="dob" name="date_of_birth" value="<?php echo htmlspecialchars($dob); ?>" required disabled>
                    </div><br>

                    <h5 onclick="window.location.href='changePW.php'">>Change Password here<</h5><br>

                            <button type="button" class="btn" id="btn-update">Edit Info</button>

                            <div class="save-cancel" id="save-cancel" style="display: none;">
                                <button type="submit" id="btn-save">Save Changes</button>
                                <button type="button" id="btn-cancel">Cancel</button>
                            </div>

                </form>


                <script>
                    document.getElementById('btn-update').addEventListener('click', function() {
                        // Store original values
                        var inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="date"]');
                        inputs.forEach(function(input) {
                            input.dataset.originalValue = input.value; // Save original value in data attribute
                            input.disabled = false; // Enable input fields
                        });

                        // Show save/cancel buttons
                        document.getElementById('btn-update').style.display = 'none';
                        document.getElementById('save-cancel').style.display = 'block';
                    });

                    document.getElementById('btn-cancel').addEventListener('click', function() {
                        // Restore original values
                        var inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="date"]');
                        inputs.forEach(function(input) {
                            input.value = input.dataset.originalValue; // Restore original value
                            input.disabled = true; // Disable input fields
                        });

                        // Show update button
                        document.getElementById('save-cancel').style.display = 'none';
                        document.getElementById('btn-update').style.display = 'block';
                    });
                </script>
            </div> <!-- Closing for .wrapper -->

        </div> <!-- Closing for .settings -->

    </div> <!-- Closing for .container -->


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