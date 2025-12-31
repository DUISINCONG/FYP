<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => '未登录']);
    exit();
}

$sql = "SELECT MAX(order_id) as max_id FROM orders";
$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $current_max_id = intval($row['max_id']);
    
    $_SESSION['last_order_id'] = $current_max_id;
    
    echo json_encode([
        'success' => true,
        'message' => '订单已标记为已读',
        'new_last_order_id' => $current_max_id
    ]);
} else {
    echo json_encode(['error' => '无法获取订单信息']);
}
?>