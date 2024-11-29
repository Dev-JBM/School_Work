<?php
session_start();

//Variables for Database
$host = "127.0.0.1";
$port = 3306;
$user = "myUser";
$pass = "JbMagt64";
$database = "My_Database";

//Connects to Database
$conn = mysqli_connect($host, $user, $pass, $database, $port);

?>