<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirmPassword']);

    if ($password !== $confirmPassword) {
        echo "<h3 style='color:red;'>Passwords do not match!</h3>";
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // FIXED SQL: matching your table column names
    $sql = "INSERT INTO customers 
            (customer_name, customer_email, customer_phone, customer_password, customer_status)
            VALUES 
            ('$name', '$email', '$phone', '$hashedPassword', 'active')";

    if (mysqli_query($conn, $sql)) {
        echo "<h3 style='color:green;'>Registration successful!</h3>";
    } else {
        echo "<h3 style='color:red;'>Error: " . mysqli_error($conn) . "</h3>";
    }
}

mysqli_close($conn);
?>


