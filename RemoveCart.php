<?php
    require __DIR__ . "/backend/db_connect.php";
    session_start();
    if (!$conn) {
        die("DB connection failed");
    }

    if (!isset($_SESSION['id'])) {
        header("Location: /jc_restaurant/customerLogin.php");
        exit;
    }

if (isset($_POST['order_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);

    mysqli_query($conn, "DELETE FROM orders WHERE order_id = '$order_id'");
}

header("Location: AddToCart.php");
exit();
