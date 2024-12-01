<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

//Variables for Database
$host = "127.0.0.1";
$port = 3306;
$user = "root";
$pass = "";
$database = "My_Database";

//Connects to Database
$conn = mysqli_connect($host, $user, $pass, $database, $port);

?>