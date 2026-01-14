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

if (isset($_POST['update_customer'])) {
    $id = mysqli_real_escape_string($conn, $_POST['customer_id']);
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $customer_email = mysqli_real_escape_string($conn, $_POST['customer_email']);
    $customer_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $customer_address = mysqli_real_escape_string($conn, $_POST['customer_address']);
    $customer_status = mysqli_real_escape_string($conn, $_POST['customer_status']);

    // 检查邮箱是否已存在（排除当前用户）
    $check_email_sql = "SELECT * FROM customers WHERE customer_email='$customer_email' AND customer_id != $id";
    $check_email_result = mysqli_query($conn, $check_email_sql);
    
    if($check_email_result && mysqli_num_rows($check_email_result) > 0) {
        $error_message = "Error: Email already exists!";
    } else {
        $sql = "UPDATE customers SET customer_name='$customer_name', customer_email='$customer_email', customer_phone='$customer_phone', customer_address='$customer_address', customer_status='$customer_status' WHERE customer_id=$id";
        if(mysqli_query($conn, $sql)) {
            $success_message = "Customer updated successfully!";
        } else {
            $error_message = "Error updating customer: " . mysqli_error($conn);
        }
    }
}

$search = '';
$sort_by = 'customer_id';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

if (isset($_GET['sort_by']) && !empty($_GET['sort_by'])) {
    $sort_by = mysqli_real_escape_string($conn, $_GET['sort_by']);
}

if (!empty($search)) {
    $sql = "SELECT * FROM customers WHERE 
            customer_id LIKE '%$search%' OR 
            customer_name LIKE '%$search%' OR 
            customer_email LIKE '%$search%' OR 
            customer_phone LIKE '%$search%' OR
            customer_address LIKE '%$search%'
            ORDER BY $sort_by";
} else {
    $sql = "SELECT * FROM customers ORDER BY $sort_by";
}

$result = mysqli_query($conn, $sql);

if (!$result) {
    $error_message = "Database error: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC Restaurant - Customer Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #16181bff;
            --light: #ecf0f1;
            --danger: #e74c3c;
            --success: #2ecc71;
            --border: #bdc3c7;
            --text: #2c3e50;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
            --active-color: #ff9800;
            --active-color-dark: #f57c00;
            --disabled: #f5f5f5;
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
            overflow-x: auto;
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
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        input:disabled,
        select:disabled,
        textarea:disabled {
            background-color: var(--disabled);
            cursor: not-allowed;
            color: #666;
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
        
        .sort-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .sort-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .sort-label {
            font-weight: 600;
            color: var(--secondary);
            white-space: nowrap;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            min-width: 1000px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        th {
            background-color: var(--light);
            font-weight: 600;
            color: var(--secondary);
            cursor: pointer;
            position: relative;
            transition: background-color 0.3s;
            vertical-align: middle;
            text-align: center;
            height: 60px;
            line-height: 1.4;
        }
        
        th:hover {
            background-color: #dfe6e9;
        }
        
        /* 确保Actions表头不可点击 */
        th:last-child {
            cursor: default;
        }
        
        th:last-child:hover {
            background-color: var(--light);
        }
        
        .sort-indicator {
            margin-left: 5px;
            font-size: 12px;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            white-space: nowrap;
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
            margin-left:20px;
            background-color: var(--primary);
            color: white;
            padding: 22px 25px;
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
            white-space: nowrap;
        }
        
        .status-active {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success);
        }
        
        .status-inactive {
            background-color: rgba(149, 165, 166, 0.2);
            color: #7f8c8d;
        }
        
        .customer-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border);
        }
        
        .no-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--border);
            color: #7f8c8d;
            font-size: 12px;
            font-weight: 600;
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
        
        .input-hint {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
            font-style: italic;
        }
        
        .disabled-field {
            position: relative;
        }
        
        .disabled-field::after {
            content: "Locked";
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background-color: var(--primary);
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
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
                min-width: unset;
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
            
            .sort-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-dropdown {
                margin-top: 10px;
            }
            
            .dropdown-content {
                right: auto;
                left: 50%;
                transform: translateX(-50%);
            }
            
            .customer-photo,
            .no-photo {
                width: 50px;
                height: 50px;
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
            
            <a href="manage_customers.php" class="active">CUSTOMERS</a>
            <a href="manage_products.php">MENU</a>
            <a href="manage_orders.php">ORDER</a>
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
            <h1>Customer Management</h1>
            <p class="subtitle">Manage customer information and accounts (Customer ID and Password cannot be changed)</p>
        </header>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Customer List</h2>
            
            <form method="GET" action="" class="search-container">
                <input type="search" name="search" class="search-input" placeholder="Search by ID, name, email, phone, or address..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="manage_customers.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
            
            <div class="sort-container">
                <div class="sort-group">
                    <span class="sort-label">Sort by:</span>
                    <select name="sort_by" id="sort_by" onchange="updateSort()">
                        <option value="customer_id" <?php echo $sort_by == 'customer_id' ? 'selected' : ''; ?>>ID</option>
                        <option value="customer_name" <?php echo $sort_by == 'customer_name' ? 'selected' : ''; ?>>Name</option>
                        <option value="customer_status" <?php echo $sort_by == 'customer_status' ? 'selected' : ''; ?>>Status</option>
                        <option value="created_at" <?php echo $sort_by == 'created_at' ? 'selected' : ''; ?>>Date Created</option>
                    </select>
                </div>
                
                <?php if (!empty($search) || $sort_by != 'customer_id'): ?>
                    <a href="manage_customers.php" class="btn btn-secondary">Reset Filters</a>
                <?php endif; ?>
            </div>
            
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th onclick="sortTable('customer_id')">
                            ID 
                            <span class="sort-indicator">
                                <?php if ($sort_by == 'customer_id'): ?>↓<?php endif; ?>
                            </span>
                        </th>
                        <th>Photo</th>
                        <th onclick="sortTable('customer_name')">
                            Name
                            <span class="sort-indicator">
                                <?php if ($sort_by == 'customer_name'): ?>↓<?php endif; ?>
                            </span>
                        </th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th onclick="sortTable('customer_status')">
                            Status
                            <span class="sort-indicator">
                                <?php if ($sort_by == 'customer_status'): ?>↓<?php endif; ?>
                            </span>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)) { 
                        $status = !empty($row['customer_status']) ? $row['customer_status'] : 'active';
                        $photo_path = !empty($row['customer_photo']) ? $row['customer_photo'] : null;
                        $address = !empty($row['customer_address']) ? $row['customer_address'] : 'No address provided';
                    ?>
                    <tr>
                        <td><?php echo $row['customer_id']; ?></td>
                        <td>
                            <?php if ($photo_path && file_exists($photo_path)): ?>
                                <img src="<?php echo $photo_path; ?>" alt="Customer Photo" class="customer-photo">
                            <?php else: ?>
                                <div class="no-photo">No Photo</div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_email']); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_phone']); ?></td>
                        <td><?php echo htmlspecialchars(substr($address, 0, 50)); ?><?php echo strlen($address) > 50 ? '...' : ''; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $status; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </td>
                        <td class="actions">
                            <button class="action-btn edit-btn" onclick="openEditModal(
                                '<?php echo $row['customer_id']; ?>',
                                '<?php echo addslashes($row['customer_name']); ?>',
                                '<?php echo addslashes($row['customer_email']); ?>',
                                '<?php echo addslashes($row['customer_phone']); ?>',
                                '<?php echo addslashes($address); ?>',
                                '<?php echo addslashes($status); ?>'
                            )">Edit</button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php elseif (!$result): ?>
                <div class="alert alert-error">Error loading customer list. Please check if the 'customers' table exists.</div>
            <?php else: ?>
                <p>No customer accounts found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Customer</h2>
                <p style="font-size: 14px; color: #666; margin-top: 5px;">Note: Customer ID and Password cannot be changed</p>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" id="editId" name="customer_id">
                <input type="hidden" id="editCustomerId" name="new_customer_id">
                
                <div class="form-group">
                    <label for="displayCustomerId">Customer ID</label>
                    <input type="number" id="displayCustomerId" disabled readonly>
                    <p class="input-hint">Customer ID cannot be changed</p>
                </div>
                
                <div class="form-group">
                    <label for="editCustomerName">Full Name</label>
                    <input type="text" id="editCustomerName" name="customer_name" placeholder="Enter customer name" required>
                </div>
                
                <div class="form-group">
                    <label for="editCustomerPhone">Phone Number</label>
                    <input type="tel" id="editCustomerPhone" name="customer_phone" placeholder="e.g., 0123456789" required>
                </div>
                
                <div class="form-group">
                    <label for="editCustomerEmail">Email Address</label>
                    <input type="email" id="editCustomerEmail" name="customer_email" placeholder="e.g., example@gmail.com" required>
                </div>
                
                <div class="form-group">
                    <label for="editCustomerAddress">Address</label>
                    <textarea id="editCustomerAddress" name="customer_address" placeholder="Enter customer address" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="displayCustomerPassword">Password</label>
                    <input type="text" id="displayCustomerPassword" value="********" disabled readonly>
                    <p class="input-hint">Password cannot be changed for security reasons</p>
                </div>
                
                <div class="form-group">
                    <label for="editCustomerStatus">Status</label>
                    <select id="editCustomerStatus" name="customer_status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="btn-group">
                    <button type="submit" name="update_customer" class="btn btn-primary">Update Customer</button>
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

        function openEditModal(id, customer_name, customer_email, customer_phone, customer_address, customer_status) {
            document.getElementById('editId').value = id;
            document.getElementById('editCustomerId').value = id;
            document.getElementById('displayCustomerId').value = id;
            document.getElementById('editCustomerName').value = customer_name;
            document.getElementById('editCustomerPhone').value = customer_phone;
            document.getElementById('editCustomerEmail').value = customer_email;
            document.getElementById('editCustomerAddress').value = customer_address;
            document.getElementById('editCustomerStatus').value = customer_status;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
            
            // 关闭下拉菜单
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

        function updateSort() {
            const sortBy = document.getElementById('sort_by').value;
            const search = '<?php echo $search; ?>';
            
            let url = 'manage_customers.php?';
            
            if (search) {
                url += 'search=' + encodeURIComponent(search) + '&';
            }
            
            url += 'sort_by=' + sortBy;
            
            window.location.href = url;
        }

        function sortTable(column) {
            const search = '<?php echo $search; ?>';
            
            let url = 'manage_customers.php?';
            
            if (search) {
                url += 'search=' + encodeURIComponent(search) + '&';
            }
            
            url += 'sort_by=' + column;
            
            window.location.href = url;
        }
    </script>
</body>
</html>
