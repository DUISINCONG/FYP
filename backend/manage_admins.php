<?php
session_start();
require_once 'admin_auth.php'; 
requireSuperAdmin(); 

include 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login/adminlogin.php");
    exit();
}

$current_admin_id = $_SESSION['admin_id'];
$admin_name = "Admin";
$admin_email = "";
$admin_role = "admin";

if (isset($current_admin_id)) {
    $sql = "SELECT admin_name, admin_email, role FROM admins WHERE admin_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $current_admin_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $admin = mysqli_fetch_assoc($result);
            $admin_name = htmlspecialchars($admin['admin_name']);
            $admin_email = htmlspecialchars($admin['admin_email']);
            $admin_role = htmlspecialchars($admin['role']);
        }
        
        mysqli_stmt_close($stmt);
    }
}

function encryptPassword($password) {
    $key = 'JC_Restaurant_Admin_Key_2024!@#';
    $encrypted = openssl_encrypt(
        $password,
        'AES-128-ECB',
        $key,
        OPENSSL_RAW_DATA
    );
    return base64_encode($encrypted);
}

function generateNextAdminId($conn) {
    $sql = "SELECT MAX(admin_id) as max_id FROM admins";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $next_id = $row['max_id'] + 1;
    } else {
        $next_id = 1; 
    }
    
    return $next_id;
}

function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format";
    }
    return true;
}

function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function sendPasswordSetupEmail($recipient_email, $admin_name, $token) {
    require_once 'login/vendor/autoload.php';
    
    $setup_link = "http://" . $_SERVER['HTTP_HOST'] . "/FYP/FYP/backend/setup_password.php?token=" . $token;
    
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'duisincong1121@gmail.com'; 
        $mail->Password = 'jpmk bqrz dflt ovqv'; 
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom('duisincong1121@gmail.com', 'JC Restaurant');
        $mail->addAddress($recipient_email, $admin_name);
        
        $mail->isHTML(true);
        $mail->Subject = 'JC Restaurant - Admin Account Setup';
        
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 30px; background-color: #fff; }
                .button { display: inline-block; padding: 12px 24px; background-color: orange; 
                         color: white; text-decoration: none; border-radius: 4px; font-weight: bold; }
                .footer { margin-top: 30px; padding: 20px; background-color: #f8f9fa; 
                         text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>JC Restaurant Admin Account Setup</h2>
                </div>
                <div class='content'>
                    <p>Dear $admin_name,</p>
                    <p>You have been added as an administrator to the JC Restaurant system.</p>
                    <p>Please click the button below to set up your password and activate your account:</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='$setup_link' class='button'>Set Up Your Password</a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; background-color: #f8f9fa; padding: 10px; 
                       border-radius: 4px;'>$setup_link</p>
                    <p><strong>This link will expire in 24 hours.</strong></p>
                    <p>If you did not request this account, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " JC Restaurant. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
        
        $mail->AltBody = "Dear $admin_name,\n\nYou have been added as an administrator to the JC Restaurant system.\n\nPlease visit the following link to set up your password: $setup_link\n\nThis link will expire in 24 hours.\n\nIf you did not request this account, please ignore this email.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

if (isset($_POST['add_admin'])) {
    $new_admin_id = generateNextAdminId($conn);
    $new_admin_name = mysqli_real_escape_string($conn, $_POST['admin_name']);
    $new_admin_email = mysqli_real_escape_string($conn, $_POST['admin_email']);
    
    $new_role = 'admin';
    $new_admin_status = 'pending';
    
    $reset_token = generateSecureToken();
    $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours')); 

    $emailValidation = validateEmail($new_admin_email);
    if ($emailValidation !== true) {
        $error_message = "Error: " . $emailValidation;
    } else {
        $check_name_sql = "SELECT * FROM admins WHERE admin_name='$new_admin_name'";
        $check_name_result = mysqli_query($conn, $check_name_sql);
        
        if($check_name_result && mysqli_num_rows($check_name_result) > 0) {
            $error_message = "Error: Admin name already exists!";
        } else {
            $check_email_sql = "SELECT * FROM admins WHERE admin_email='$new_admin_email'";
            $check_email_result = mysqli_query($conn, $check_email_sql);
            
            if($check_email_result && mysqli_num_rows($check_email_result) > 0) {
                $error_message = "Error: Admin email already exists!";
            } else {
                $default_password = "Temp123!"; 
                $encrypted_password = encryptPassword($default_password);
                
                $sql = "INSERT INTO admins (admin_id, admin_name, admin_email, admin_password, role, admin_status, reset_token, token_expiry) 
                        VALUES ('$new_admin_id', '$new_admin_name', '$new_admin_email', '$encrypted_password', '$new_role', '$new_admin_status', '$reset_token', '$token_expiry')";
                
                if(mysqli_query($conn, $sql)) {
                    $emailSent = sendPasswordSetupEmail($new_admin_email, $new_admin_name, $reset_token);
                    
                    if ($emailSent) {
                        $success_message = "Admin added successfully! A password setup link has been sent to $new_admin_email";
                    } else {
                        $success_message = "Admin added successfully with ID: $new_admin_id, but email notification failed to send.";
                        $error_message = "Warning: Could not send email notification. Please notify the admin manually.";
                    }
                } else {
                    $error_message = "Error adding admin: " . mysqli_error($conn);
                }
            }
        }
    }
}

if (isset($_POST['update_admin'])) {
    $edit_admin_id = mysqli_real_escape_string($conn, $_POST['admin_id']);
    $edit_admin_email = mysqli_real_escape_string($conn, $_POST['admin_email']);
    $edit_role = mysqli_real_escape_string($conn, $_POST['role']);
    $edit_admin_status = mysqli_real_escape_string($conn, $_POST['admin_status']);

    $emailValidation = validateEmail($edit_admin_email);
    if ($emailValidation !== true) {
        $error_message = "Error: " . $emailValidation;
    } else {
        $check_email_sql = "SELECT * FROM admins WHERE admin_email='$edit_admin_email' AND admin_id != $edit_admin_id";
        $check_email_result = mysqli_query($conn, $check_email_sql);
        
        if($check_email_result && mysqli_num_rows($check_email_result) > 0) {
            $error_message = "Error: Admin email already exists!";
        } else {
            $sql = "UPDATE admins SET admin_email='$edit_admin_email', role='$edit_role', admin_status='$edit_admin_status' WHERE admin_id=$edit_admin_id";
            if(mysqli_query($conn, $sql)) {
                $success_message = "Admin updated successfully!";
                
                if ($edit_admin_id == $current_admin_id) {
                    $admin_role = $edit_role;
                }
            } else {
                $error_message = "Error updating admin: " . mysqli_error($conn);
            }
        }
    }
}

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            font-weight: 500;
        }
        
        .nav-menu a:hover {
            background-color: rgba(255,255,255,0.1);
            transform: translateY(-1px);
        }
        
        .nav-menu a.active {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
        }
        
        .nav-menu a.active:hover {
            background-color: #f57c00;
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
        
        input[readonly],
        select[readonly] {
            background-color: #f8f9fa;
            border-color: #ddd;
            cursor: not-allowed;
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
        
        .status-pending {
            background-color: rgba(243, 156, 18, 0.2);
            color: var(--warning);
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
        
        td:last-child {
            text-align: center;
        }
        
        th:last-child {
            text-align: center;
        }
        
        .email-cell {
            word-break: break-all;
        }
        
        .id-info {
            font-size: 14px;
            color: var(--primary);
            margin-top: 5px;
            font-weight: 500;
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
            <a href="manage_admins.php" class="active">ADMIN</a>
            <a href="manage_customers.php">CUSTOMERS</a>
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
            
            <div class="id-info">
                Next available Admin ID will be auto-generated. Current highest ID: <?php 
                    $next_id = generateNextAdminId($conn);
                    echo $next_id - 1; 
                ?>, Next ID: <?php echo $next_id; ?>
            </div>
            
            <div class="alert alert-warning">
                <strong>Note:</strong> New admins will receive an email to set up their own password. 
                The account will be in 'pending' status until password is set.
            </div>
            
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="admin_name">Admin Name</label>
                        <input type="text" id="admin_name" name="admin_name" placeholder="Enter admin name" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_email">Admin Email</label>
                        <input type="email" id="admin_email" name="admin_email" placeholder="Enter admin email" required>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <div class="btn-group">
                    <button type="submit" name="add_admin" class="btn btn-primary">Add Admin & Send Setup Email</button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2>Admin List</h2>
            
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
                        $password_display = ($status == 'pending') ? 'Not set yet' : '••••••••';
                    ?>
                    <tr class="<?php echo $isSuperadmin ? 'superadmin-row' : ''; ?>">
                        <td><?php echo $row['admin_id']; ?></td>
                        <td><?php echo $row['admin_name']; ?></td>
                        <td class="email-cell"><?php echo $email; ?></td>
                        <td><?php echo $password_display; ?></td>
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
                                    '<?php echo addslashes($row['role']); ?>',
                                    '<?php echo addslashes($status); ?>'
                                )">Edit</button>
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
    
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Admin</h2>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            
            <div class="alert alert-warning">
                <strong>Note:</strong> Admin ID, Admin Name, and Password cannot be modified for security reasons.
            </div>
            
            <form method="POST" action="">
                <input type="hidden" id="editId" name="admin_id">
                <div class="form-group">
                    <label for="editAdminId">Admin ID</label>
                    <input type="number" id="editAdminId" name="new_admin_id" placeholder="Admin ID" readonly>
                </div>
                <div class="form-group">
                    <label for="editAdminName">Admin Name</label>
                    <input type="text" id="editAdminName" name="admin_name" placeholder="Admin Name" readonly>
                </div>
                <div class="form-group">
                    <label for="editAdminEmail">Admin Email</label>
                    <input type="email" id="editAdminEmail" name="admin_email" placeholder="Enter admin email" required>
                </div>
                <div class="form-group">
                    <label for="editAdminPassword">Password</label>
                    <input type="text" id="editAdminPassword" name="admin_password" value="••••••••" readonly>
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
                            <option value="pending">Pending</option>
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

        function openEditModal(id, admin_name, admin_email, role, status) {
            document.getElementById('editId').value = id;
            document.getElementById('editAdminId').value = id;
            document.getElementById('editAdminName').value = admin_name;
            document.getElementById('editAdminEmail').value = admin_email;
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

        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>