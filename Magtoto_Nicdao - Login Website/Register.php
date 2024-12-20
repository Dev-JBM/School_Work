<?php
// Include the database connection
require_once 'DB_Conn.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $firstName = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middleName = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $lastName = mysqli_real_escape_string($conn, $_POST['last_name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $dob = mysqli_real_escape_string($conn, $_POST['date_of_birth']);

    // Validate password strength
    $passwordPattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/';

    if (!preg_match($passwordPattern, $password)) {
        echo "<script>alert('Password must be at least 8 characters long and should contain at least one of all the following: UPPERCASE letter, LOWERCASE letter, NUMBER, and one SPECIAL character.');</script>";
    } elseif ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match.');</script>";
    } else {
        // Check for duplicate username
        $checkUsernameQuery = "SELECT * FROM userlog WHERE username = '$username'";
        $checkUsernameResult = mysqli_query($conn, $checkUsernameQuery);

        if (mysqli_num_rows($checkUsernameResult) > 0) {
            echo "<script>alert('Username is already taken.');</script>";
        } else {
            // Check for duplicate email
            $checkEmailQuery = "SELECT * FROM userlog WHERE email = '$email'";
            $checkEmailResult = mysqli_query($conn, $checkEmailQuery);

            if (mysqli_num_rows($checkEmailResult) > 0) {
                echo "<script>alert('Email is already taken.');</script>";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // Insert data into the database
                $sql = "INSERT INTO userlog (first_name, middle_name, last_name, username, password, email, date_of_birth) 
                        VALUES ('$firstName', '$middleName', '$lastName', '$username', '$hashedPassword', '$email', '$dob')";

                if (mysqli_query($conn, $sql)) {
                    echo "<script>alert('Registration Successful');</script>";
                } else {
                    echo "<script>alert('Error: Unable to register. Please try again later.');</script>";
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
    <title>Register</title>
</head>

<body>
    <div class="wrapper">
        <div class="logo-container">
            <img src="logo.png" class="enterlogo">
        </div>
        <form action="Register.php" method="post">
            <h1>Sign Up</h1>
            <div class="input-box">
                <input type="text" name="first_name" placeholder="First Name" required>
            </div>
            <div class="input-box">
                <input type="text" name="middle_name" placeholder="Middle Name" required>
            </div>
            <div class="input-box">
                <input type="text" name="last_name" placeholder="Last Name" required>
            </div>
            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-box">
                <input type="password" min="8" name="password" placeholder="Password" required>
            </div>
            <div class="input-box">
                <input type="password" name="confirm_password" placeholder="Confirm password" required>
            </div>
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="input-box">
                <label for="birthday">Date of Birth <input type="date" id="date_of_birth" name="date_of_birth" required></label>
            </div><br>
            <button type="submit" class="btn">Register</button>
            <div class="register-link">
                <p>Already have an account? <a href="Login.php">Login</a></p>
            </div>
        </form>
    </div>
</body>

</html>