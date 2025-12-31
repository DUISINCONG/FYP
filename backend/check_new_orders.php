<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => '未登录']);
    exit();
}

$last_order_id = isset($_SESSION['last_order_id']) ? intval($_SESSION['last_order_id']) : 0;

$sql = "SELECT MAX(order_id) as max_id FROM orders";
$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $current_max_id = intval($row['max_id']);
    
    $new_order_count = 0;
    
    if ($last_order_id > 0) {
        $count_sql = "SELECT COUNT(*) as new_count FROM orders WHERE order_id > ?";
        $stmt = mysqli_prepare($conn, $count_sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $last_order_id);
            mysqli_stmt_execute($stmt);
            $count_result = mysqli_stmt_get_result($stmt);
            
            if ($count_result) {
                $count_row = mysqli_fetch_assoc($count_result);
                $new_order_count = intval($count_row['new_count']);
            }
            
            mysqli_stmt_close($stmt);
        }
    } else {
        $count_sql = "SELECT COUNT(*) as total_count FROM orders";
        $count_result = mysqli_query($conn, $count_sql);
        
        if ($count_result) {
            $count_row = mysqli_fetch_assoc($count_result);
            $new_order_count = intval($count_row['total_count']);
        }
        
        $_SESSION['last_order_id'] = $current_max_id;
    }
    
    echo json_encode([
        'new_order_count' => $new_order_count,
        'current_max_id' => $current_max_id,
        'last_order_id' => $last_order_id
    ]);
    
} else {
    echo json_encode(['error' => '查询失败']);
}
?>