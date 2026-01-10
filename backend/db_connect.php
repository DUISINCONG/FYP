<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "fyp";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

date_default_timezone_set('Asia/Kuala_Lumpur');

if ($conn) {
    mysqli_query($conn, "SET time_zone = '+08:00'");
}

?>
