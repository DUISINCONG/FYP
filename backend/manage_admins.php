<?php
include 'db_connect.php';

// Password validation function
function validatePassword($password) {
    // Check password length
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long";
    }
    
    // Check if contains letters
    if (!preg_match('/[a-zA-Z]/', $password)) {
        return "Password must contain at least one letter";
    }
    
    // Check if contains numbers
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number";
    }
    
    // Check if contains special characters
    if (!preg_match('/[?!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
        return "Password must contain at least one special character (?!@#$%^&*()-_=+{};:,<.>)";
    }
    
    return true; // Password is valid
}

// Email validation function
function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format";
    }
    return true;
}

// Handle Add Admin
if (isset($_POST['add_admin'])) {
    $admin_id = mysqli_real_escape_string($conn, $_POST['admin_id']);
    $admin_name = mysqli_real_escape_string($conn, $_POST['admin_name']);
    $admin_email = mysqli_real_escape_string($conn, $_POST['admin_email']);
    $admin_password = mysqli_real_escape_string($conn, $_POST['admin_password']);
    // Auto set role to 'admin' and status to 'active'
    $role = 'admin';
    $admin_status = 'active';

    // Validate email
    $emailValidation = validateEmail($admin_email);
    if ($emailValidation !== true) {
        $error_message = "Error: " . $emailValidation;
    } else {
        // Validate password
        $passwordValidation = validatePassword($admin_password);
        if ($passwordValidation !== true) {
            $error_message = "Error: " . $passwordValidation;
        } else {
            // Check if admin ID already exists
            $check_sql = "SELECT * FROM admins WHERE admin_id='$admin_id'";
            $check_result = mysqli_query($conn, $check_sql);
            
            if($check_result && mysqli_num_rows($check_result) > 0) {
                $error_message = "Error: Admin ID already exists!";
            } else {
                // Check if admin name already exists
                $check_name_sql = "SELECT * FROM admins WHERE admin_name='$admin_name'";
                $check_name_result = mysqli_query($conn, $check_name_sql);
                
                if($check_name_result && mysqli_num_rows($check_name_result) > 0) {
                    $error_message = "Error: Admin name already exists!";
                } else {
                    // Check if admin email already exists
                    $check_email_sql = "SELECT * FROM admins WHERE admin_email='$admin_email'";
                    $check_email_result = mysqli_query($conn, $check_email_sql);
                    
                    if($check_email_result && mysqli_num_rows($check_email_result) > 0) {
                        $error_message = "Error: Admin email already exists!";
                    } else {
                        $sql = "INSERT INTO admins (admin_id, admin_name, admin_email, admin_password, role, admin_status) VALUES ('$admin_id', '$admin_name', '$admin_email', '$admin_password', '$role', '$admin_status')";
                        if(mysqli_query($conn, $sql)) {
                            $success_message = "Admin added successfully!";
                        } else {
                            $error_message = "Error adding admin: " . mysqli_error($conn);
                        }
                    }
                }
            }
        }
    }
}

// Handle Update Admin
if (isset($_POST['update_admin'])) {
    $id = mysqli_real_escape_string($conn, $_POST['admin_id']);
    $new_id = mysqli_real_escape_string($conn, $_POST['new_admin_id']);
    $admin_name = mysqli_real_escape_string($conn, $_POST['admin_name']);
    $admin_email = mysqli_real_escape_string($conn, $_POST['admin_email']);
    $admin_password = mysqli_real_escape_string($conn, $_POST['admin_password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $admin_status = mysqli_real_escape_string($conn, $_POST['admin_status']);

    // Validate email
    $emailValidation = validateEmail($admin_email);
    if ($emailValidation !== true) {
        $error_message = "Error: " . $emailValidation;
    } else {
        // Validate password
        $passwordValidation = validatePassword($admin_password);
        if ($passwordValidation !== true) {
            $error_message = "Error: " . $passwordValidation;
        } else {
            // Check if new admin ID already exists (if changed)
            if ($id != $new_id) {
                $check_sql = "SELECT * FROM admins WHERE admin_id='$new_id'";
                $check_result = mysqli_query($conn, $check_sql);
                
                if($check_result && mysqli_num_rows($check_result) > 0) {
                    $error_message = "Error: Admin ID already exists!";
                } else {
                    // Check if admin name already exists (excluding current admin)
                    $check_name_sql = "SELECT * FROM admins WHERE admin_name='$admin_name' AND admin_id != $id";
                    $check_name_result = mysqli_query($conn, $check_name_sql);
                    
                    if($check_name_result && mysqli_num_rows($check_name_result) > 0) {
                        $error_message = "Error: Admin name already exists!";
                    } else {
                        // Check if admin email already exists (excluding current admin)
                        $check_email_sql = "SELECT * FROM admins WHERE admin_email='$admin_email' AND admin_id != $id";
                        $check_email_result = mysqli_query($conn, $check_email_sql);
                        
                        if($check_email_result && mysqli_num_rows($check_email_result) > 0) {
                            $error_message = "Error: Admin email already exists!";
                        } else {
                            $sql = "UPDATE admins SET admin_id='$new_id', admin_name='$admin_name', admin_email='$admin_email', admin_password='$admin_password', role='$role', admin_status='$admin_status' WHERE admin_id=$id";
                            if(mysqli_query($conn, $sql)) {
                                $success_message = "Admin updated successfully!";
                            } else {
                                $error_message = "Error updating admin: " . mysqli_error($conn);
                            }
                        }
                    }
                }
            } else {
                // ID didn't change, check admin name, email and update other fields
                $check_name_sql = "SELECT * FROM admins WHERE admin_name='$admin_name' AND admin_id != $id";
                $check_name_result = mysqli_query($conn, $check_name_sql);
                
                if($check_name_result && mysqli_num_rows($check_name_result) > 0) {
                    $error_message = "Error: Admin name already exists!";
                } else {
                    // Check if admin email already exists (excluding current admin)
                    $check_email_sql = "SELECT * FROM admins WHERE admin_email='$admin_email' AND admin_id != $id";
                    $check_email_result = mysqli_query($conn, $check_email_sql);
                    
                    if($check_email_result && mysqli_num_rows($check_email_result) > 0) {
                        $error_message = "Error: Admin email already exists!";
                    } else {
                        $sql = "UPDATE admins SET admin_name='$admin_name', admin_email='$admin_email', admin_password='$admin_password', role='$role', admin_status='$admin_status' WHERE admin_id=$id";
                        if(mysqli_query($conn, $sql)) {
                            $success_message = "Admin updated successfully!";
                        } else {
                            $error_message = "Error updating admin: " . mysqli_error($conn);
                        }
                    }
                }
            }
        }
    }
}

// Handle Search and Sorting
$search_query = "";
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'admin_id';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
    $sort_by = mysqli_real_escape_string($conn, $_GET['sort_by']);
    
    $sql = "SELECT * FROM admins WHERE 
            admin_id LIKE '%$search_query%' OR 
            admin_name LIKE '%$search_query%' OR 
            admin_email LIKE '%$search_query%' OR 
            role LIKE '%$search_query%' OR 
            admin_status LIKE '%$search_query%'
            ORDER BY $sort_by $sort_order";
} else {
    $sql = "SELECT * FROM admins ORDER BY $sort_by $sort_order";
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
    <title>JC Restaurant - Admin Management</title>
    <style>
        :root {
            --primary: orange;
            --primary-dark: #2980b9;
            --secondary: #16181bff;
            --light: #ecf0f1;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f39c12;
            --border: #bdc3c7;
            --text: #2c3e50;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .nav-menu a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .nav-menu a.active {
            background-color: var(--primary);
        }
        
        .admin-info {
            font-size: 14px;
            opacity: 0.9;
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
        input[type="password"],
        input[type="number"],
        input[type="email"],
        select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input:focus,
        select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(176, 194, 206, 0.2);
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
            cursor: pointer;
            position: relative;
            user-select: none;
        }
        
        th:hover {
            background-color: #dfe6e9;
        }
        
        .sortable:after {
            content: '↕';
            margin-left: 5px;
            color: var(--primary);
            font-size: 12px;
        }
        
        .sort-asc:after {
            content: '↑';
            color: var(--primary);
        }
        
        .sort-desc:after {
            content: '↓';
            color: var(--primary);
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .superadmin-row {
            background-color: rgba(243, 156, 18, 0.1);
        }
        
        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
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
        
        .edit-btn:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }
        
        .role-badge, .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .role-superadmin {
            background-color: rgba(243, 156, 18, 0.2);
            color: #e67e22;
        }
        
        .role-admin {
            background-color: rgba(52, 152, 219, 0.2);
            color: var(--primary-dark);
        }
        
        .status-active {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success);
        }
        
        .status-inactive {
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
            width: 90%;
            max-width: 500px;
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
        
        .alert-warning {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning);
            border: 1px solid rgba(243, 156, 18, 0.3);
        }
        
        .password-requirements {
            background-color: #f8f9fa;
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .password-requirements h4 {
            margin-bottom: 10px;
            color: var(--secondary);
        }
        
        .password-requirements ul {
            margin-left: 20px;
        }
        
        .password-requirements li {
            margin-bottom: 5px;
        }
        
        .search-sort-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .search-container {
            display: flex;
            gap: 15px;
            flex: 1;
            min-width: 300px;
        }
        
        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 16px;
        }
        
        .search-button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .search-button:hover {
            background-color: var(--primary-dark);
        }
        
        .sort-container {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .sort-select {
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 16px;
            background-color: white;
        }
        
        .sort-order-btn {
            background-color: var(--light);
            color: var(--text);
            border: 1px solid var(--border);
            padding: 12px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .sort-order-btn:hover {
            background-color: #dde4e6;
        }
        
        /* Center the actions column */
        td:last-child {
            text-align: center;
        }
        
        /* Ensure the actions column header is also centered */
        th:last-child {
            text-align: center;
        }
        
        .email-cell {
            word-break: break-all;
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
            
            .search-sort-container {
                flex-direction: column;
            }
            
            .search-container {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="banner">
        <div class="restaurant-name">JC Restaurant</div>
        <div class="nav-menu">
            <a href="#">HOME</a>
            <a href="manage_admins.php" class="active">ADMIN</a>
            <a href="customer_management.php">CUSTOMERS</a>
            <a href="#">MENU</a>
            <a href="#">ORDER HISTORY</a>
            <a href="#">REPORTS</a>
        </div>
        <div class="admin-info">Superadmin</div>
    </div>
    
    <div class="container">
        <header>
            <h1>Admin Management</h1>
            <p class="subtitle">Manage administrator accounts and permissions</p>
        </header>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Add New Admin</h2>
            
            <div class="password-requirements">
                <h4>Password Requirements:</h4>
                <ul>
                    <li>At least 8 characters long</li>
                    <li>Contains at least one letter (a-z, A-Z)</li>
                    <li>Contains at least one number (0-9)</li>
                    <li>Contains at least one special character (!@#$%^&*()-_=+{};:,<.>)</li>
                </ul>
            </div>
            
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="admin_id">Admin ID</label>
                        <input type="number" id="admin_id" name="admin_id" placeholder="Enter admin ID" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_name">Admin Name</label>
                        <input type="text" id="admin_name" name="admin_name" placeholder="Enter admin name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="admin_email">Admin Email</label>
                        <input type="email" id="admin_email" name="admin_email" placeholder="Enter admin email" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_password">Password</label>
                        <input type="password" id="admin_password" name="admin_password" placeholder="Enter password" required>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <div class="btn-group">
                    <button type="submit" name="add_admin" class="btn btn-primary">Save Admin</button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2>Admin List</h2>
            
            <!-- Search and Sort Form -->
            <form method="GET" action="">
                <div class="search-sort-container">
                    <div class="search-container">
                        <input type="text" class="search-input" name="search" placeholder="Search admins by ID, name, email, role, or status..." value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" class="search-button">Search</button>
                    </div>
                    <div class="sort-container">
                        <select name="sort_by" class="sort-select" onchange="this.form.submit()">
                            <option value="admin_id" <?php echo $sort_by == 'admin_id' ? 'selected' : ''; ?>>Sort by ID</option>
                            <option value="admin_name" <?php echo $sort_by == 'admin_name' ? 'selected' : ''; ?>>Name</option>
                            <option value="admin_email" <?php echo $sort_by == 'admin_email' ? 'selected' : ''; ?>>Email</option>
                            <option value="role" <?php echo $sort_by == 'role' ? 'selected' : ''; ?>>Role</option>
                            <option value="admin_status" <?php echo $sort_by == 'admin_status' ? 'selected' : ''; ?>>Status</option>
                        </select>
                        <button type="submit" name="sort_order" value="<?php echo $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-order-btn">
                            <?php echo $sort_order == 'ASC' ? '↑ Asc' : '↓ Desc'; ?>
                        </button>
                    </div>
                </div>
            </form>
            
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th class="sortable <?php echo $sort_by == 'admin_id' ? ($sort_order == 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" onclick="sortTable('admin_id')">ID</th>
                        <th class="sortable <?php echo $sort_by == 'admin_name' ? ($sort_order == 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" onclick="sortTable('admin_name')">Admin Name</th>
                        <th class="sortable <?php echo $sort_by == 'admin_email' ? ($sort_order == 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" onclick="sortTable('admin_email')">Email</th>
                        <th>Password</th>
                        <th class="sortable <?php echo $sort_by == 'role' ? ($sort_order == 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" onclick="sortTable('role')">Role</th>
                        <th class="sortable <?php echo $sort_by == 'admin_status' ? ($sort_order == 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" onclick="sortTable('admin_status')">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)) { 
                        $isSuperadmin = $row['admin_id'] == 1;
                        $status = !empty($row['admin_status']) ? $row['admin_status'] : 'active';
                        $email = !empty($row['admin_email']) ? $row['admin_email'] : 'Not set';
                    ?>
                    <tr class="<?php echo $isSuperadmin ? 'superadmin-row' : ''; ?>">
                        <td><?php echo $row['admin_id']; ?></td>
                        <td><?php echo $row['admin_name']; ?></td>
                        <td class="email-cell"><?php echo $email; ?></td>
                        <td>••••••••</td>
                        <td>
                            <span class="role-badge role-<?php echo $row['role']; ?>">
                                <?php echo $row['role']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $status; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="action-btn edit-btn" onclick="openEditModal(
                                    '<?php echo $row['admin_id']; ?>',
                                    '<?php echo addslashes($row['admin_name']); ?>',
                                    '<?php echo addslashes($row['admin_email']); ?>',
                                    '<?php echo addslashes($row['admin_password']); ?>',
                                    '<?php echo addslashes($row['role']); ?>',
                                    '<?php echo addslashes($status); ?>'
                                )" <?php echo $isSuperadmin ? 'disabled' : ''; ?>>Edit</button>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php elseif (!$result): ?>
                <div class="alert alert-error">Error loading admin list. Please check if the 'admins' table exists.</div>
            <?php else: ?>
                <p>No admin accounts found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Edit Admin Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Admin</h2>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            
            <div class="password-requirements">
                <h4>Password Requirements:</h4>
                <ul>
                    <li>At least 8 characters long</li>
                    <li>Contains at least one letter (a-z, A-Z)</li>
                    <li>Contains at least one number (0-9)</li>
                    <li>Contains at least one special character (!@#$%^&*()-_=+{};:,<.>)</li>
                </ul>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" id="editId" name="admin_id">
                <div class="form-group">
                    <label for="editAdminId">Admin ID</label>
                    <input type="number" id="editAdminId" name="new_admin_id" placeholder="Enter admin ID" required>
                </div>
                <div class="form-group">
                    <label for="editAdminName">Admin Name</label>
                    <input type="text" id="editAdminName" name="admin_name" placeholder="Enter admin name" required>
                </div>
                <div class="form-group">
                    <label for="editAdminEmail">Admin Email</label>
                    <input type="email" id="editAdminEmail" name="admin_email" placeholder="Enter admin email" required>
                </div>
                <div class="form-group">
                    <label for="editAdminPassword">Password</label>
                    <input type="password" id="editAdminPassword" name="admin_password" placeholder="Enter password" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editRole">Role</label>
                        <select id="editRole" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editStatus">Status</label>
                        <select id="editStatus" name="admin_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" name="update_admin" class="btn btn-primary">Update Admin</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, admin_name, admin_email, admin_password, role, status) {
            document.getElementById('editId').value = id;
            document.getElementById('editAdminId').value = id;
            document.getElementById('editAdminName').value = admin_name;
            document.getElementById('editAdminEmail').value = admin_email;
            document.getElementById('editAdminPassword').value = admin_password;
            document.getElementById('editRole').value = role;
            document.getElementById('editStatus').value = status;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        function sortTable(column) {
            const currentSortBy = '<?php echo $sort_by; ?>';
            const currentSortOrder = '<?php echo $sort_order; ?>';
            
            let newSortOrder = 'ASC';
            if (currentSortBy === column) {
                newSortOrder = currentSortOrder === 'ASC' ? 'DESC' : 'ASC';
            }
            
            window.location.href = '?search=<?php echo urlencode($search_query); ?>&sort_by=' + column + '&sort_order=' + newSortOrder;
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }

        // Show warning for superadmin account
        document.addEventListener('DOMContentLoaded', function() {
            const superadminEditButtons = document.querySelectorAll('.superadmin-row .edit-btn');
            superadminEditButtons.forEach(button => {
                if (button.disabled) {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        alert('Superadmin account cannot be modified!');
                    });
                }
            });
        });
    </script>
</body>
</html>
