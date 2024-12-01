<?php
// Include the database connection
require_once 'DB_Conn.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
    $password = isset($_POST['password']) ? mysqli_real_escape_string($conn, $_POST['password']) : '';

    // Check if both fields are filled
    if (empty($username) || empty($password)) {
        echo "<script>alert('Please fill in both username and password.');</script>";
        exit;
    }

    // Query the database to check if the user exists
    $sql = "SELECT * FROM userlog WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // User found, now check if password matches
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $username;  // Store the username in session variable
;
            // Redirect to Dashboard.php
            header('Location: Dashboard.php');

            exit;
        } else {
            // Password doesn't match
            echo "<script>alert('Incorrect password.');</script>";
        }
    } else {
        // Username doesn't exist
        echo "<script>alert('Invalid username.');</script>";
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
    <title>Login</title>
</head>

<body>
    <div class="wrapper">
        <div class="logo-container">
            <img src="logo.png" class="logo">
        </div>
        <form action="Login.php" method="post">
            <h1>Login</h1>
            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt'></i>
            </div>
            <button type="submit" class="btn">Login</button>
            <div class="register-link">
                <p>Not yet registered? <a href="Register.php">Sign Up</a></p>
            </div>
        </form>
    </div>
</body>

</html>