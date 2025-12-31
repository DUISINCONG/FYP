<?php
include 'db_connect.php';

$adminCount = 0;
$customerCount = 0;
$productCount = 0;
$orderCount = 0;

$mainFoodCount = 0;
$pizzaCount = 0;
$coffeeCount = 0;
$beveragesCount = 0;
$snackfoodCount = 0;

$adminQuery = "SELECT COUNT(*) as count FROM admins WHERE admin_status = 'active'";
$adminResult = mysqli_query($conn, $adminQuery);
if ($adminResult) {
    $adminRow = mysqli_fetch_assoc($adminResult);
    $adminCount = $adminRow['count'];
}

$customerQuery = "SELECT COUNT(*) as count FROM customers";
$customerResult = mysqli_query($conn, $customerQuery);
if ($customerResult) {
    $customerRow = mysqli_fetch_assoc($customerResult);
    $customerCount = $customerRow['count'];
}

$mainFoodQuery = "SELECT COUNT(*) as count FROM main_food";
$mainFoodResult = mysqli_query($conn, $mainFoodQuery);
if ($mainFoodResult) {
    $mainFoodRow = mysqli_fetch_assoc($mainFoodResult);
    $mainFoodCount = $mainFoodRow['count'];
    $productCount += $mainFoodCount;
}

$pizzaQuery = "SELECT COUNT(*) as count FROM pizza";
$pizzaResult = mysqli_query($conn, $pizzaQuery);
if ($pizzaResult) {
    $pizzaRow = mysqli_fetch_assoc($pizzaResult);
    $pizzaCount = $pizzaRow['count'];
    $productCount += $pizzaCount;
}

$coffeeQuery = "SELECT COUNT(*) as count FROM coffee";
$coffeeResult = mysqli_query($conn, $coffeeQuery);
if ($coffeeResult) {
    $coffeeRow = mysqli_fetch_assoc($coffeeResult);
    $coffeeCount = $coffeeRow['count'];
    $productCount += $coffeeCount;
}

$beveragesQuery = "SELECT COUNT(*) as count FROM beverages";
$beveragesResult = mysqli_query($conn, $beveragesQuery);
if ($beveragesResult) {
    $beveragesRow = mysqli_fetch_assoc($beveragesResult);
    $beveragesCount = $beveragesRow['count'];
    $productCount += $beveragesCount;
}

$snackfoodQuery = "SELECT COUNT(*) as count FROM snackfood";
$snackfoodResult = mysqli_query($conn, $snackfoodQuery);
if ($snackfoodResult) {
    $snackfoodRow = mysqli_fetch_assoc($snackfoodResult);
    $snackfoodCount = $snackfoodRow['count'];
    $productCount += $snackfoodCount;
}

$orderQuery = "SELECT COUNT(*) as count FROM orders";
$orderResult = mysqli_query($conn, $orderQuery);
if ($orderResult) {
    $orderRow = mysqli_fetch_assoc($orderResult);
    $orderCount = $orderRow['count'];
}

mysqli_close($conn);
?>