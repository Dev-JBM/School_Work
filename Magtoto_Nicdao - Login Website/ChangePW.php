<?php
// Start the session
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include 'DB_Conn.php';

$successMessage = $errorMessage = '';

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Fetch user data including image and name fields
    $query = "SELECT password, image, first_name, middle_name, last_name FROM userlog WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        $currentPassword = $row['password'];
        $imagePath = !empty($row['image']) ? "user-Img/" . $row['image'] : "user-Img/default.jpg"; // Handle missing images
        $firstName = $row['first_name'];
        $middleName = $row['middle_name'];
        $lastName = $row['last_name'];
    } else {
        echo "No user data found.";
        exit();
    }

    // Handle password update
// Password validation pattern
$passwordPattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/';

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate old password
    if (password_verify($oldPassword, $currentPassword)) {
        if (preg_match($passwordPattern, $newPassword)) {
            if ($newPassword === $confirmPassword) {
                // Hash new password and update in the database
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateQuery = "UPDATE userlog SET password = ? WHERE username = ?";
                $updateStmt = mysqli_prepare($conn, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, "ss", $hashedPassword, $username);
                if (mysqli_stmt_execute($updateStmt)) {
                    $successMessage = "Password updated successfully!";
                } else {
                    $errorMessage = "Error updating password. Please try again.";
                }
                mysqli_stmt_close($updateStmt);
            } else {
                $errorMessage = "New password and confirm password do not match.";
            }
        } else {
            echo "<script>alert('Password must be at least 8 characters long and should contain at least one of all the following: UPPERCASE letter, LOWERCASE letter, NUMBER, and one SPECIAL character.');</script>";
        }
    } else {
        $errorMessage = "Old password is incorrect.";
    }
}

    mysqli_stmt_close($stmt);
} else {
    echo "User is not logged in.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style_dashboard.css">
</head>

<body>
    <div class="container">
        <nav>
            <h4 class="user">Welcome,<br><?php echo htmlspecialchars($_SESSION['username']); ?>!</h4>
            <a href="Dashboard.php"><img src="logo.png" class="logo"></a>

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

    <!-- Change Password Form -->
    <div class="settings">
        <h1>Change Password</h1>

        <!-- Success and Error Messages -->
        <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="wrapper">
                <div class="input-box">
                    <label for="old_password">Enter Old Password:</label>
                    <input type="password" id="old_password" name="old_password" disabled required>
                </div>

                <div class="input-box">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" disabled required>
                </div>

                <div class="input-box">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" disabled required>
                </div><br>

                <h5 onclick="window.location.href='AccSettings.php'">>Return<</h5><br>

                        <button type="button" class="btn" id="btn-update">Edit Info</button>

                        <div class="save-cancel" id="save-cancel" style="display: none;">
                            <button type="submit" id="btn-save">Save Changes</button>
                            <button type="button" id="btn-cancel">Cancel</button>
                        </div>
            </div>
        </form>


        <script>
            // Toggle password editing
            document.getElementById('btn-update').addEventListener('click', function() {
                document.getElementById('btn-update').style.display = 'none';
                document.getElementById('save-cancel').style.display = 'block';
                var inputs = document.querySelectorAll('input[type="password"]');
                inputs.forEach(function(input) {
                    input.disabled = false;
                });
            });

            document.getElementById('btn-cancel').addEventListener('click', function() {
                document.getElementById('save-cancel').style.display = 'none';
                document.getElementById('btn-update').style.display = 'block';
                var inputs = document.querySelectorAll('input[type="password"]');
                inputs.forEach(function(input) {
                    input.disabled = true;
                    input.value = ''; // Reset fields
                });
            });
        </script>
    </div>

    <script>
        // Toggle sub-menu
        let submenu = document.getElementById("sub-menu-wrap");

        function togglemenu() {
            submenu.classList.toggle("open-class");
        }
    </script>


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