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

if (isset($_POST['update_order'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $order_status = mysqli_real_escape_string($conn, $_POST['order_status']);
    
    $sql = "UPDATE orders SET order_status='$order_status' WHERE order_id='$order_id'";
    if(mysqli_query($conn, $sql)) {
        $success_message = "Order status updated successfully!";
    } else {
        $error_message = "Error updating order: " . mysqli_error($conn);
    }
}

$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $sql = "SELECT o.*, c.customer_name, c.customer_phone 
            FROM orders o 
            LEFT JOIN customers c ON o.customer_id = c.customer_id 
            WHERE (o.order_id LIKE '%$search%' OR 
                  c.customer_name LIKE '%$search%' OR 
                  c.customer_phone LIKE '%$search%') 
            AND o.order_status != 'completed' 
            ORDER BY o.order_date DESC";
} else {
    $sql = "SELECT o.*, c.customer_name, c.customer_phone 
            FROM orders o 
            LEFT JOIN customers c ON o.customer_id = c.customer_id 
            WHERE o.order_status != 'completed' 
            ORDER BY o.order_date DESC";
}

$result = mysqli_query($conn, $sql);

$stats_sql = "SELECT 
    COUNT(CASE WHEN order_status = 'pending' THEN 1 END) as pending_count,
    COUNT(CASE WHEN order_status = 'confirmed' THEN 1 END) as confirmed_count,
    COUNT(CASE WHEN order_status = 'preparing' THEN 1 END) as preparing_count,
    COUNT(CASE WHEN order_status = 'ready' THEN 1 END) as ready_count,
    COUNT(*) as total_active
    FROM orders 
    WHERE order_status != 'completed'";
    
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
    <title>JC Restaurant - Active Orders</title>
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
            max-width: 1200px;
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
            border-bottom: 1px solid var(--border);
        }
        
        th {
            background-color: var(--light);
            font-weight: 600;
            color: var(--secondary);
        }
        
        tr:hover {
            background-color: #f8f9fa;
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
        
        .edit-btn {
            background-color: var(--primary);
            color: white;
        }
        
        .edit-btn:hover {
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
        
        .status-confirmed {
            background-color: rgba(52, 152, 219, 0.2);
            color: var(--primary);
        }
        
        .status-preparing {
            background-color: rgba(155, 89, 182, 0.2);
            color: #9b59b6;
        }
        
        .status-ready {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success);
        }
        
        .status-completed {
            background-color: rgba(149, 165, 166, 0.2);
            color: #7f8c8d;
        }
        
        .status-cancelled {
            background-color: rgba(231, 76, 60, 0.2);
            color: var(--danger);
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
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 20px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .priority-high {
            color: var(--danger);
            font-weight: 600;
        }
        
        .priority-medium {
            color: var(--warning);
            font-weight: 600;
        }
        
        .priority-low {
            color: var(--success);
            font-weight: 600;
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
            <h1>Active Orders Management</h1>
            <p class="subtitle">Manage orders that haven't been completed yet</p>
        </header>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['pending_count']; ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['confirmed_count']; ?></div>
                <div class="stat-label">Confirmed</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['preparing_count']; ?></div>
                <div class="stat-label">Preparing</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['ready_count']; ?></div>
                <div class="stat-label">Ready</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total_active']; ?></div>
                <div class="stat-label">Total Active</div>
            </div>
        </div>
        
        <div class="card">
            <h2>Active Orders List</h2>
            
            <div class="filter-container">
                <div class="filter-group">
                    <div class="filter-label">Status Filter</div>
                    <select id="statusFilter" onchange="filterOrders()">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="preparing">Preparing</option>
                        <option value="ready">Ready</option>
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
                <input type="search" name="search" class="search-input" placeholder="Search by Order ID, Customer Name, or Phone..." value="<?php echo htmlspecialchars($search); ?>">
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
                        <th>Phone</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)) { 
                        $status = $row['order_status'];
                        $order_date = date('M j, Y g:i A', strtotime($row['order_date']));
                    ?>
                    <tr class="order-row" data-status="<?php echo $status; ?>" data-date="<?php echo date('Y-m-d', strtotime($row['order_date'])); ?>">
                        <td><?php echo $row['order_id']; ?></td>
                        <td><?php echo $row['customer_name'] ?? 'N/A'; ?></td>
                        <td><?php echo $row['customer_phone'] ?? 'N/A'; ?></td>
                        <td><?php echo $order_date; ?></td>
                        <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $status; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </td>
                        <td class="actions">
                            <button class="action-btn edit-btn" onclick="openEditModal('<?php echo $row['order_id']; ?>', '<?php echo $status; ?>')">Update Status</button>
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
                    <h3>No Active Orders</h3>
                    <p>All orders have been completed or there are no orders in the system.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Order Status - <span id="editOrderId"></span></h2>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" id="editId" name="order_id">
                
                <div class="form-group">
                    <label for="editOrderStatus">Order Status</label>
                    <select id="editOrderStatus" name="order_status" required>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="preparing">Preparing</option>
                        <option value="ready">Ready</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="btn-group">
                    <button type="submit" name="update_order" class="btn btn-primary">Update Order</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
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

        function openEditModal(orderId, status) {
            document.getElementById('editOrderId').textContent = orderId;
            document.getElementById('editId').value = orderId;
            document.getElementById('editOrderStatus').value = status;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
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
                    messageRow.innerHTML = `<td colspan="7" style="text-align: center; padding: 40px; color: #7f8c8d;">No orders match the selected filters</td>`;
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
            const editModal = document.getElementById('editModal');
            if (event.target === editModal) {
                closeEditModal();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const oneWeekAgo = new Date();
            oneWeekAgo.setDate(today.getDate() - 7);
            
            document.getElementById('dateFrom').valueAsDate = oneWeekAgo;
            document.getElementById('dateTo').valueAsDate = today;
            
            filterOrders();
        });
    </script>
</body>
</html>