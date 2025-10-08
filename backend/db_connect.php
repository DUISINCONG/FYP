<?php
$host = "localhost";     // your server, usually localhost
$user = "root";          // default XAMPP username
$pass = "";              // default XAMPP password (empty if not changed)
$dbname = "fyp"; // your database name

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
