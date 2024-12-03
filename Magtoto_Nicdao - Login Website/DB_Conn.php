<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

//Variables for Database
$host = "127.0.0.1";
$port = 4306;
$user = "root";
$pass = "";
$database = "My_Database";
$column = "image";

//Connects to Database
$conn = mysqli_connect($host, $user, $pass, $database, $port, $column);

?>