<?php
include 'db_connect.php'; // make sure this path is correct

// Remove debugging line
// var_dump($_POST);

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

    // Hash the password before saving
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO customers (name, email, phone, password) 
            VALUES ('$name', '$email', '$phone', '$hashedPassword')";

    if (mysqli_query($conn, $sql)) {
        echo "<h3 style='color:green;'>Registration successful!</h3>";
    } else {
        if (mysqli_errno($conn) == 1062) {
            echo "<h3 style='color:red;'>Email already exists. Please use another email.</h3>";
        } else {
            echo "<h3 style='color:red;'>Error: " . mysqli_error($conn) . "</h3>";
        }
    }
}

mysqli_close($conn);
?>
