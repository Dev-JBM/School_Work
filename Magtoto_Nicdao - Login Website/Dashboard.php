<?php
session_start();
echo "<script>alert('Login successful!');</script>";
echo "Welcome, " . $_SESSION['username'] . "!";
?>