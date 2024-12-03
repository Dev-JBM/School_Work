<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'DB_Conn.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "You must log in first.";
    exit();
}

$username = $_SESSION['username']; // Get the logged-in username

// Prepare the query to fetch the user's data
$sql = "SELECT first_name, middle_name, last_name, username, email, date_of_birth, image FROM userlog WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// --- FOR FETCHING PROFILE PIC --- //
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $imagePath = "user-Img/" . $row['image'];  // Set the image path based on the database value
} else {
    echo "No data found for the logged-in user.";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Info</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">;
    <link rel="stylesheet" href="style_dashboard.css">
</head>

<body>
    <div class="container">
        <nav>
            <h4 class="user">Welcome,<br><?php echo htmlspecialchars($_SESSION['username']); ?>!</h4>
            <a href="Dashboard.php"><img src="logo.png" class="logo"></a>
            <img src="<?php echo $imagePath; ?>" alt="Profile Picture" class="profilePic" onclick="togglemenu()">

            <div class="sub-menu-wrap" id="sub-menu-wrap">
                <div class="sub-menu">
                    <div class="user-info">
                        <img src="<?php echo $imagePath; ?>" class=" ">
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

    <!-- Profile Info -->
    <div class="info-table">
        <h1>Profile Information</h1>
        <table>
            <tr>
                <td class="data-label">First Name:</td>
                <td class="fetched-data"><?php echo htmlspecialchars($row['first_name']); ?></td>
            </tr>

            <tr>
                <td class="data-label">Middle Name:</td>
                <td class="fetched-data"><?php echo htmlspecialchars($row['middle_name']); ?></td>
            </tr>

            <tr>
                <td class="data-label">Last Name:</td>
                <td class="fetched-data"><?php echo htmlspecialchars($row['last_name']); ?></td>
            </tr>

            <tr>
                <td class="data-label">Username:</td>
                <td class="fetched-data"><?php echo htmlspecialchars($_SESSION['username']); ?></td>
            </tr>

            <tr>
                <td class="data-label">Email:</td>
                <td class="fetched-data"><?php echo htmlspecialchars($row['email']); ?></td>
            </tr>

            <tr>
                <td class="data-label">Date of Birth:</td>
                <td class="fetched-data"><?php echo htmlspecialchars($row['date_of_birth']); ?></td>
            </tr>
        </table>
            <button><a href="AccSettings.php">Edit</a></button>
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
$stmt->close();
$conn->close();
?>