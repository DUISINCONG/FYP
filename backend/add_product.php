<?php
include 'db_connect.php';

$category = $_POST['category'];
$product = $_POST['product'];
$price = $_POST['price'];

$sql = "INSERT INTO products (category, product, price) VALUES ('$category','$product','$price')";
if ($conn->query($sql) === TRUE) {
    echo "New product added successfully.";
} else {
    echo "Error: " . $conn->error;
}
?>
