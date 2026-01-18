<?php
session_start();
require_once 'admin_auth.php';
requireAdminLogin();

include 'db_connect.php';

$admin_id = $_SESSION['admin_id'];
$sql = "SELECT admin_name, admin_email, role FROM admins WHERE admin_id = ?";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $admin_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        $admin_name = htmlspecialchars($admin['admin_name']);
        $admin_email = htmlspecialchars($admin['admin_email']);
        $admin_role = htmlspecialchars($admin['role']);
    } else {
        $admin_name = getAdminName();
        $admin_email = "";
        $admin_role = getAdminRole();
    }
    
    mysqli_stmt_close($stmt);
} else {
    $admin_name = getAdminName();
    $admin_email = "";
    $admin_role = getAdminRole();
}

if (!function_exists('getAdminName')) {
    function getAdminName() {
        return isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin';
    }
}

if (!function_exists('getAdminRole')) {
    function getAdminRole() {
        return isset($_SESSION['admin_role']) ? htmlspecialchars($_SESSION['admin_role']) : 'admin';
    }
}

// 处理完成订单的请求
if (isset($_POST['complete_order'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    
    $sql = "UPDATE orders SET order_status='completed' WHERE order_id='$order_id' AND (order_status='pending' OR order_status='incart')";
    if(mysqli_query($conn, $sql)) {
        if(mysqli_affected_rows($conn) > 0) {
            $success_message = "Order #$order_id has been marked as completed!";
        } else {
            $error_message = "Order #$order_id is not in pending/incart status or does not exist.";
        }
    } else {
        $error_message = "Error updating order: " . mysqli_error($conn);
    }
}

// 获取所有订单
$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $sql = "SELECT o.*, c.customer_name, c.customer_phone 
            FROM orders o 
            LEFT JOIN customers c ON o.customer_id = c.customer_id 
            WHERE (o.order_id LIKE '%$search%' OR 
                  o.customer_id LIKE '%$search%' OR 
                  o.product_id LIKE '%$search%' OR
                  c.customer_name LIKE '%$search%' OR 
                  c.customer_phone LIKE '%$search%' OR
                  o.product_name LIKE '%$search%') 
            ORDER BY o.order_date DESC";
} else {
    $sql = "SELECT o.*, c.customer_name, c.customer_phone 
            FROM orders o 
            LEFT JOIN customers c ON o.customer_id = c.customer_id 
            ORDER BY o.order_date DESC";
}

$result = mysqli_query($conn, $sql);

// 统计查询
$stats_sql = "SELECT 
    COUNT(CASE WHEN order_status = 'pending' THEN 1 END) as pending_count,
    COUNT(CASE WHEN order_status = 'completed' THEN 1 END) as completed_count,
    COUNT(CASE WHEN order_status = 'incart' THEN 1 END) as incart_count,
    COUNT(*) as total_orders
    FROM orders";
    
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

if (!$result) {
    $error_message = "Database error: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC Restaurant - All Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #16181bff;
            --light: #ecf0f1;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f39c12;
            --border: #bdc3c7;
            --text: #2c3e50;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
            --active-color: #ff9800;
            --active-color-dark: #f57c00;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--text);
            line-height: 1.6;
        }
        
        .banner {
            background-color: var(--secondary);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }
        
        .restaurant-name {
            font-size: 24px;
            font-weight: bold;
        }
        
        .nav-menu {
            display: flex;
            gap: 20px;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .nav-menu a:hover {
            background-color: rgba(255,255,255,0.1);
            transform: translateY(-1px);
        }
        
        .nav-menu a.active {
            background-color: var(--active-color);
            color: white;
            box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
        }
        
        .nav-menu a.active:hover {
            background-color: var(--active-color-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.4);
        }
        
        .user-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.3s;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .user-info:hover {
            background-color: rgba(36, 28, 28, 0.3);
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #fff;
            min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.3s;
        }
        
        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }
        
        .dropdown-divider {
            height: 1px;
            background-color: #eee;
            margin: 5px 0;
        }
        
        .user-email {
            color: #666;
            font-size: 0.9em;
        }
        
        .user-role {
            background-color: <?php echo $admin_role === 'superadmin' ? '#dc3545' : '#007bff'; ?>;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 8px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        h1 {
            color: var(--secondary);
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        h2 {
            color: var(--secondary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        input[type="number"],
        input[type="search"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .divider {
            height: 1px;
            background-color: var(--border);
            margin: 25px 0;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-secondary {
            background-color: var(--light);
            color: var(--text);
        }
        
        .btn-secondary:hover {
            background-color: #dde4e6;
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        .btn-warning {
            background-color: var(--warning);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e67e22;
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .search-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .search-input {
            flex: 1;
        }
        
        .search-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .search-btn:hover {
            background-color: var(--primary-dark);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
        }
        
        th {
            background-color: var(--light);
            font-weight: 600;
            color: var(--secondary);
            border-bottom: 2px solid #333;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        /* 订单之间的黑色分隔线 */
        .order-row-divider {
            border-bottom: 1px solid #333;
        }
        
        /* 浅色分隔线 */
        .order-row-divider-light {
            border-bottom: 1px solid #ccc;
        }
        
        /* 虚线分隔线 */
        .order-row-divider-dashed {
            border-bottom: 1px dashed #333;
        }
        
        /* 无分隔线 */
        .order-row-no-divider {
            border-bottom: none;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .complete-btn {
            background-color: var(--success);
            color: white;
            margin-top: 20px;
             margin-bottom: 20px;
        }
        
        .complete-btn:hover {
            background-color: #27ae60;
        }
        
        .complete-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .view-btn {
            background-color: var(--primary);
            color: white;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        .view-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: rgba(243, 156, 18, 0.2);
            color: var(--warning);
        }
        
        .status-incart {
            background-color: rgba(155, 89, 182, 0.2);
            color: #9b59b6;
        }
        
        .status-completed {
            background-color: rgba(149, 165, 166, 0.2);
            color: #7f8c8d;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            width: 100%;
            max-width: 800px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success);
            border: 1px solid rgba(46, 204, 113, 0.3);
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger);
            border: 1px solid rgba(231, 76, 60, 0.3);
        }
        
        .order-details {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .order-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .order-detail-item {
            margin-bottom: 10px;
        }
        
        .order-detail-label {
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 5px;
        }
        
        .order-items {
            margin-top: 20px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-item-name {
            font-weight: 500;
        }
        
        .order-item-price {
            color: var(--secondary);
        }
        
        .order-total {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            font-size: 18px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid var(--border);
        }
        
        .filter-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-label {
            font-size: 14px;
            font-weight: 600;
            color: var(--secondary);
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-pending .stat-value {
            color: var(--warning);
        }
        
        .stat-incart .stat-value {
            color: #9b59b6;
        }
        
        .stat-completed .stat-value {
            color: var(--success);
        }
        
        .stat-total .stat-value {
            color: var(--secondary);
        }
        
        .stat-label {
            font-size: 14px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .view-details-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .view-details-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .product-image-cell {
            width: 80px;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid var(--border);
        }
        
        .modal-image {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid var(--border);
        }
        
        .no-image {
            color: #999;
            font-style: italic;
        }
        
        .dash {
            color: #999;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .banner {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .search-container {
                flex-direction: column;
            }
            
            .filter-container {
                flex-direction: column;
            }
            
            .order-details-grid {
                grid-template-columns: 1fr;
            }
            
            .user-dropdown {
                margin-top: 10px;
            }
            
            .dropdown-content {
                right: auto;
                left: 50%;
                transform: translateX(-50%);
            }
        }
    </style>
</head>
<body>
    <div class="banner">
        <div class="restaurant-name">JC Restaurant</div>
        <div class="nav-menu">
            <a href="adminhomepage.php">HOME</a>
            
            <?php if (isSuperAdmin()): ?>
                <a href="manage_admins.php">ADMIN</a>
            <?php endif; ?>
            
            <a href="manage_customers.php">CUSTOMERS</a>
            <a href="manage_products.php">MENU</a>
            <a href="manage_orders.php" class="active">ORDER</a>
            <a href="manage_report.php">REPORTS</a>
        </div>
        
        <div class="user-dropdown">
            <div class="user-info" onclick="toggleDropdown()">
                <i class="fas fa-user-cog"></i>
                HI <?php echo $admin_name; ?>
                <span class="user-role">
                    <?php echo $admin_role === 'superadmin' ? 'Super Admin' : 'Admin'; ?>
                </span>
                <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
            </div>
            <div class="dropdown-content" id="userDropdown">
                <div style="padding: 12px 16px; background-color: #f9f9f9;">
                    <?php if (!empty($admin_email)): ?>
                        <div class="user-email"><?php echo $admin_email; ?></div>
                    <?php endif; ?>
                </div>
                <div class="dropdown-divider"></div>
                <a href="login/adminlogin.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <header>
            <h1>All Orders Management</h1>
            <p class="subtitle">View and manage all orders</p>
        </header>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="stats-container">
            <div class="stat-card stat-pending">
                <div class="stat-value"><?php echo $stats['pending_count']; ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
            <div class="stat-card stat-incart">
                <div class="stat-value"><?php echo $stats['incart_count']; ?></div>
                <div class="stat-label">In Cart</div>
            </div>
            <div class="stat-card stat-completed">
                <div class="stat-value"><?php echo $stats['completed_count']; ?></div>
                <div class="stat-label">Completed Orders</div>
            </div>
            <div class="stat-card stat-total">
                <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        
        <div class="card">
            <h2>All Orders List</h2>
            
            <div class="filter-container">
                <div class="filter-group">
                    <div class="filter-label">Status Filter</div>
                    <select id="statusFilter" onchange="filterOrders()">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="incart">In Cart</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <div class="filter-label">Date From</div>
                    <input type="date" id="dateFrom" onchange="filterOrders()">
                </div>
                
                <div class="filter-group">
                    <div class="filter-label">Date To</div>
                    <input type="date" id="dateTo" onchange="filterOrders()">
                </div>
                
                <div class="filter-group" style="align-self: flex-end;">
                    <button class="btn btn-secondary" onclick="clearFilters()">Clear Filters</button>
                </div>
            </div>
            
            <form method="GET" action="" class="search-container">
                <input type="search" name="search" class="search-input" placeholder="Search by Order ID, Customer Name, Phone, or Product..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="manage_orders.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
            
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <table id="ordersTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Product Image</th>
                        <th>Quantity</th>
                        <th>Order Date</th>
                        <th>Payment Time</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)) { 
                        $status = strtolower($row['order_status']); // 转换为小写以确保匹配
                        $order_date = date('M j, Y g:i A', strtotime($row['order_date']));
                        
                        // Payment Time 处理
                        $payment_time = '----';
                        if (!empty($row['paymenttime']) && $row['paymenttime'] != '0000-00-00 00:00:00') {
                            $payment_time = date('M j, Y g:i A', strtotime($row['paymenttime']));
                        }
                        
                        // Customer Name 处理
                        $customer_name = $row['customer_name'] ?? '----';
                        
                        // Product Name 处理
                        $product_name = $row['product_name'] ?? '----';
                        
                        // Product Image 处理
                        $product_image = $row['product_image'] ?? '';
                        
                        // 显示客户电话
                        $customer_phone = $row['customer_phone'] ?? '----';
                    ?>
                    <tr class="order-row order-row-divider" data-status="<?php echo $status; ?>" data-date="<?php echo date('Y-m-d', strtotime($row['order_date'])); ?>">
                        <td><?php echo $row['order_id']; ?></td>
                        <td>
                            <strong><?php echo $customer_name; ?></strong><br>
                            <small>Phone: <?php echo $customer_phone; ?></small><br>
                            <small>ID: <?php echo $row['customer_id']; ?></small>
                        </td>
                        <td><?php echo $product_name; ?><br>
                            <small>ID: <?php echo $row['product_id']; ?></small>
                        </td>
                        <td class="product-image-cell">
                            <?php if (!empty($product_image)): ?>
                                <img src="<?php echo htmlspecialchars($product_image); ?>" alt="Product Image" class="product-image">
                            <?php else: ?>
                                <span class="no-image">No Image</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td><?php echo $order_date; ?></td>
                        <td class="dash"><?php echo $payment_time; ?></td>
                        <td>RM<?php echo number_format($row['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $status; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </td>
                        <td class="actions">
                            <?php if ($status == 'pending' || $status == 'incart'): ?>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                    <button type="submit" name="complete_order" class="action-btn complete-btn" onclick="return confirm('Are you sure you want to mark order #<?php echo $row['order_id']; ?> as completed?')">
                                        <i class="fas fa-check-circle"></i> Complete
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="action-btn complete-btn" disabled title="Order already completed">
                                    <i class="fas fa-check-circle"></i> Completed
                                </button>
                            <?php endif; ?>
                            <button class="action-btn view-btn" onclick="viewOrderDetails('<?php echo $row['order_id']; ?>', '<?php echo $customer_name; ?>', '<?php echo $customer_phone; ?>', '<?php echo $product_name; ?>', '<?php echo $product_image; ?>', '<?php echo $row['quantity']; ?>', '<?php echo $order_date; ?>', '<?php echo $payment_time; ?>', '<?php echo number_format($row['total_amount'], 2); ?>', '<?php echo $status; ?>')">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php elseif (!$result): ?>
                <div class="alert alert-error">Error loading order list. Please check if the 'orders' table exists.</div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📦</div>
                    <h3>No Orders Found</h3>
                    <p>There are no orders in the system yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details - <span id="viewOrderId"></span></h2>
                <button class="close-btn" onclick="closeViewModal()">&times;</button>
            </div>
            
            <div id="orderDetailsContent">
                <!-- Order details will be populated here -->
            </div>
        </div>
    </div>

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        window.onclick = function(event) {
            if (!event.target.matches('.user-info') && !event.target.closest('.user-info')) {
                const dropdowns = document.getElementsByClassName("dropdown-content");
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.style.display === 'block') {
                        openDropdown.style.display = 'none';
                    }
                }
            }
        }

        function viewOrderDetails(orderId, customerName, customerPhone, productName, productImage, quantity, orderDate, paymentTime, totalAmount, status) {
            document.getElementById('viewOrderId').textContent = orderId;
            
            let imageHtml = '';
            if (productImage && productImage !== '' && productImage !== '----') {
                imageHtml = `<img src="${productImage}" alt="Product Image" class="modal-image">`;
            } else {
                imageHtml = '<p class="no-image">No image available</p>';
            }
            
            const detailsHtml = `
                <div class="order-details">
                    <div class="order-details-grid">
                        <div class="order-detail-item">
                            <div class="order-detail-label">Order ID</div>
                            <div>${orderId}</div>
                        </div>
                        <div class="order-detail-item">
                            <div class="order-detail-label">Customer Name</div>
                            <div>${customerName}</div>
                        </div>
                        <div class="order-detail-item">
                            <div class="order-detail-label">Customer Phone</div>
                            <div>${customerPhone}</div>
                        </div>
                        <div class="order-detail-item">
                            <div class="order-detail-label">Product Name</div>
                            <div>${productName}</div>
                        </div>
                        <div class="order-detail-item">
                            <div class="order-detail-label">Quantity</div>
                            <div>${quantity}</div>
                        </div>
                        <div class="order-detail-item">
                            <div class="order-detail-label">Order Date</div>
                            <div>${orderDate}</div>
                        </div>
                        <div class="order-detail-item">
                            <div class="order-detail-label">Payment Time</div>
                            <div class="dash">${paymentTime}</div>
                        </div>
                        <div class="order-detail-item">
                            <div class="order-detail-label">Total Amount</div>
                            <div>RM${totalAmount}</div>
                        </div>
                        <div class="order-detail-item">
                            <div class="order-detail-label">Status</div>
                            <span class="status-badge status-${status}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <div class="order-detail-label">Product Image</div>
                        ${imageHtml}
                    </div>
                </div>
            `;
            
            document.getElementById('orderDetailsContent').innerHTML = detailsHtml;
            document.getElementById('viewModal').style.display = 'flex';
        }

        function closeViewModal() {
            document.getElementById('viewModal').style.display = 'none';
        }

        function filterOrders() {
            const statusFilter = document.getElementById('statusFilter').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            
            const rows = document.querySelectorAll('.order-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                const rowDate = row.getAttribute('data-date');
                
                let statusMatch = !statusFilter || rowStatus === statusFilter;
                let dateMatch = true;
                
                if (dateFrom && rowDate < dateFrom) {
                    dateMatch = false;
                }
                
                if (dateTo && rowDate > dateTo) {
                    dateMatch = false;
                }
                
                if (statusMatch && dateMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            const tableBody = document.querySelector('#ordersTable tbody');
            if (visibleCount === 0) {
                if (!document.querySelector('.no-orders-message')) {
                    const messageRow = document.createElement('tr');
                    messageRow.className = 'no-orders-message';
                    messageRow.innerHTML = `<td colspan="10" style="text-align: center; padding: 40px; color: #7f8c8d;">No orders match the selected filters</td>`;
                    tableBody.appendChild(messageRow);
                }
            } else {
                const messageRow = document.querySelector('.no-orders-message');
                if (messageRow) {
                    messageRow.remove();
                }
            }
        }

        function clearFilters() {
            document.getElementById('statusFilter').value = '';
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';
            filterOrders();
        }

        window.onclick = function(event) {
            const viewModal = document.getElementById('viewModal');
            if (event.target === viewModal) {
                closeViewModal();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const oneMonthAgo = new Date();
            oneMonthAgo.setMonth(today.getMonth() - 1);
            
            document.getElementById('dateFrom').valueAsDate = oneMonthAgo;
            document.getElementById('dateTo').valueAsDate = today;
            
            filterOrders();
        });
    </script>
</body>
</html>
