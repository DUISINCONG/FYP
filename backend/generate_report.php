<?php
include("db_connect.php");

// Total orders
$orderQuery = "SELECT COUNT(*) AS total_orders FROM orders";
$orderResult = mysqli_query($conn, $orderQuery);
$orderData = mysqli_fetch_assoc($orderResult);
$totalOrders = $orderData['total_orders'];

// Total revenue (completed orders only)
$revenueQuery = "
    SELECT SUM(oi.quantity * p.price) AS total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.status = 'Completed'
";
$revenueResult = mysqli_query($conn, $revenueQuery);
$revenueData = mysqli_fetch_assoc($revenueResult);
$totalRevenue = $revenueData['total_revenue'] ?? 0;

// Top 5 selling products
$topProductsQuery = "
    SELECT p.name, SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    GROUP BY p.product_id
    ORDER BY total_sold DESC
    LIMIT 5
";
$topProductsResult = mysqli_query($conn, $topProductsQuery);

// Display
echo "<h2>Sales Report</h2>";
echo "Total Orders: " . $totalOrders . "<br>";
echo "Total Revenue (Completed Orders): RM " . number_format($totalRevenue, 2) . "<br><br>";

echo "<h3>Top 5 Selling Products</h3>";
if (mysqli_num_rows($topProductsResult) > 0) {
    while ($row = mysqli_fetch_assoc($topProductsResult)) {
        echo $row['name'] . " - " . $row['total_sold'] . " sold<br>";
    }
} else {
    echo "No sales data available.";
}
?>
