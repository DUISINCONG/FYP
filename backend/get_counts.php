<?php
include 'db_connect.php';

$adminCount = 0;
$customerCount = 0;
$productCount = 0;
$orderCount = 0;

$categoryCounts = array();

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

$categoryQuery = "SELECT category_id, category_name, table_name FROM categories ORDER BY category_id";
$categoryResult = mysqli_query($conn, $categoryQuery);

if ($categoryResult && mysqli_num_rows($categoryResult) > 0) {
    while ($categoryRow = mysqli_fetch_assoc($categoryResult)) {
        $categoryName = $categoryRow['category_name'];
        $tableName = $categoryRow['table_name'];
        $categoryId = $categoryRow['category_id'];
        
        $productQuery = "SELECT COUNT(*) as count FROM `$tableName`";
        $productResult = mysqli_query($conn, $productQuery);
        
        if ($productResult) {
            $productRow = mysqli_fetch_assoc($productResult);
            $count = $productRow['count'];
            
            $categoryCounts[$categoryName] = $count;
            
            $productCount += $count;
            
            switch($categoryName) {
                case 'Main Food':
                    $mainFoodCount = $count;
                    break;
                case 'Pizza':
                    $pizzaCount = $count;
                    break;
                case 'Coffee':
                    $coffeeCount = $count;
                    break;
                case 'Beverages':
                    $beveragesCount = $count;
                    break;
                case 'Snack Food':
                    $snackfoodCount = $count;
                    break;
            }
        }
    }
}

$orderQuery = "SELECT COUNT(*) as count FROM orders";
$orderResult = mysqli_query($conn, $orderQuery);
if ($orderResult) {
    $orderRow = mysqli_fetch_assoc($orderResult);
    $orderCount = $orderRow['count'];
}

if (!isset($mainFoodCount)) $mainFoodCount = 0;
if (!isset($pizzaCount)) $pizzaCount = 0;
if (!isset($coffeeCount)) $coffeeCount = 0;
if (!isset($beveragesCount)) $beveragesCount = 0;
if (!isset($snackfoodCount)) $snackfoodCount = 0;

if (empty($categoryCounts)) {
    $mainFoodQuery = "SELECT COUNT(*) as count FROM main_food";
    $mainFoodResult = mysqli_query($conn, $mainFoodQuery);
    if ($mainFoodResult) {
        $mainFoodRow = mysqli_fetch_assoc($mainFoodResult);
        $mainFoodCount = $mainFoodRow['count'];
        $categoryCounts['Main Food'] = $mainFoodCount;
        $productCount += $mainFoodCount;
    }
    
    $pizzaQuery = "SELECT COUNT(*) as count FROM pizza";
    $pizzaResult = mysqli_query($conn, $pizzaQuery);
    if ($pizzaResult) {
        $pizzaRow = mysqli_fetch_assoc($pizzaResult);
        $pizzaCount = $pizzaRow['count'];
        $categoryCounts['Pizza'] = $pizzaCount;
        $productCount += $pizzaCount;
    }
    
    $coffeeQuery = "SELECT COUNT(*) as count FROM coffee";
    $coffeeResult = mysqli_query($conn, $coffeeQuery);
    if ($coffeeResult) {
        $coffeeRow = mysqli_fetch_assoc($coffeeResult);
        $coffeeCount = $coffeeRow['count'];
        $categoryCounts['Coffee'] = $coffeeCount;
        $productCount += $coffeeCount;
    }
    
    $beveragesQuery = "SELECT COUNT(*) as count FROM beverages";
    $beveragesResult = mysqli_query($conn, $beveragesQuery);
    if ($beveragesResult) {
        $beveragesRow = mysqli_fetch_assoc($beveragesResult);
        $beveragesCount = $beveragesRow['count'];
        $categoryCounts['Beverages'] = $beveragesCount;
        $productCount += $beveragesCount;
    }
    
    $snackfoodQuery = "SELECT COUNT(*) as count FROM snackfood";
    $snackfoodResult = mysqli_query($conn, $snackfoodQuery);
    if ($snackfoodResult) {
        $snackfoodRow = mysqli_fetch_assoc($snackfoodResult);
        $snackfoodCount = $snackfoodRow['count'];
        $categoryCounts['Snack Food'] = $snackfoodCount;
        $productCount += $snackfoodCount;
    }
}

mysqli_close($conn);
?>
