<?php
// Include the database connection
require_once 'DB_Conn.php';

$message = ''; // Initialize an empty message variable

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
    $newPassword = isset($_POST['newPassword']) ? mysqli_real_escape_string($conn, $_POST['newPassword']) : '';
    $newPasswordconfirm = isset($_POST['newPasswordconfirm']) ? mysqli_real_escape_string($conn, $_POST['newPasswordconfirm']) : '';

    // Check if all fields are filled
    if (empty($email) || empty($newPassword) || empty($newPasswordconfirm)) {
        $message = 'Please fill in all fields.';
    } else {
        // Check if the email exists in the database
        $sql = "SELECT * FROM userlog WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) === 0) {
            // Email doesn't exist
            $message = 'Email not found.';
        } else {
            // Validate password strength
            $passwordPattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/';
            if (!preg_match($passwordPattern, $newPassword)) {
                $message = 'Password must be at least 8 characters long and should contain at least one of all the following: UPPERCASE letter, LOWERCASE letter, NUMBER, and one SPECIAL character.';
            } elseif ($newPassword !== $newPasswordconfirm) {
                $message = 'Both passwords must match.';
            } else {
                // Email exists, proceed to update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT); // Hash the new password
                $updateQuery = "UPDATE userlog SET password = '$hashedPassword' WHERE email = '$email'";

                if (mysqli_query($conn, $updateQuery)) {
                    $message = 'Password updated successfully.';
                } else {
                    $message = 'Error updating password. Please try again later.';
                }
            }
        }
    }

    // Close the database connection
    mysqli_close($conn);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" id="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>Change Password</title>
    <script>
        // Display alert message if PHP sets it
        window.onload = function() {
            const message = "<?php echo isset($message) ? addslashes($message) : ''; ?>";
            if (message) {
                alert(message);
            }
        };
    </script>
</head>

<body>
    <div class="wrapper">
        <div class="logo-container">
            <img src="logo.png" class="enterlogo">
        </div>
        <form action="ForgotPass.php" method="post">
            <h1>Change Password</h1>
            <div class="input-box">
                <input type="email" name="email" placeholder="Enter your Email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="newPassword" placeholder="Enter New Password" required>
                <i class='bx bxs-lock-alt'></i>
            </div>
            <div class="input-box">
                <input type="password" name="newPasswordconfirm" placeholder="Confirm New Password" required>
                <i class='bx bxs-lock-alt'></i>
            </div>

            <h5 onclick="window.location.href='Login.php'">Return to Login</h5><br>

            <button type="submit" class="btn">Change</button>
        </form>
    </div>
</body>

</html>