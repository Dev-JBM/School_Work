<?php
session_start();

// Destroy the session
session_unset();
session_destroy();

// Prevent back button navigation to the previous page
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Redirect to the login page
header("Location: Login.php");
exit();
?>