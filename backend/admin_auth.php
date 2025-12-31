<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function isAdminLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin';
}

function isRegularAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function getAdminRole() {
    return $_SESSION['role'] ?? 'guest';
}

function getAdminName() {
    return $_SESSION['admin_name'] ?? 'Guest';
}

function getAdminId() {
    return $_SESSION['admin_id'] ?? 0;
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: adminlogin.php');
        exit;
    }
}

function requireSuperAdmin() {
    requireAdminLogin();
    
    if (!isSuperAdmin()) {
        $_SESSION['error'] = "Access Denied! Superadmin privileges required.";
        
        logAccessAttempt($_SERVER['REQUEST_URI'], 'SUPERADMIN_ACCESS_DENIED');
        
        header('Location: adminhomepage.php');
        exit;
    }
}

function requireRegularAdmin() {
    requireAdminLogin();
    
    if (!isRegularAdmin()) {
        $_SESSION['error'] = "This page is for regular administrators only.";
        header('Location: adminhomepage.php');
        exit;
    }
}

function canAccessPage($page) {
    $permissions = getAdminPermissions();
    return in_array($page, $permissions);
}

function getAdminPermissions() {
    $permissions = [];
    
    $common_permissions = [
        'adminhomepage.php',
        'manage_products.php',
        'manage_customers.php', 
        'manage_orders.php',
        'manage_report.php',
        'logout.php'
    ];
    
    $permissions = array_merge($permissions, $common_permissions);
    
    if (isSuperAdmin()) {
        $superadmin_permissions = [
            'manage_admins.php',
            'admin_settings.php'
        ];
        $permissions = array_merge($permissions, $superadmin_permissions);
    }
    
    return $permissions;
}

function logAccessAttempt($page, $action) {
    global $conn;
    
    if (isset($conn)) {
        $admin_id = getAdminId();
        $admin_name = getAdminName();
        $role = getAdminRole();
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $access_time = date('Y-m-d H:i:s');
        
        $log_sql = "INSERT INTO admin_access_logs 
                    (admin_id, admin_name, role, page_accessed, action, ip_address, user_agent, access_time) 
                    VALUES ('$admin_id', '$admin_name', '$role', '$page', '$action', '$ip_address', '$user_agent', '$access_time')";
        
        mysqli_query($conn, $log_sql);
    }
}

function checkCurrentPageAccess() {
    $current_page = basename($_SERVER['PHP_SELF']);
    
    if (!canAccessPage($current_page)) {
        $_SESSION['error'] = "You do not have permission to access this page.";
        header('Location: adminhomepage.php');
        exit;
    }
}

function displayPermissionError() {
    if (isset($_SESSION['error'])) {
        $error = $_SESSION['error'];
        unset($_SESSION['error']);
        return '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
    }
    return '';
}

function generateNavigation() {
    $html = '';
    
    $html .= '<a href="adminhomepage.php"><i class="fa-solid fa-home"></i> Home</a>';
    $html .= '<a href="manage_products.php"><i class="fa-solid fa-box"></i> Products</a>';
    $html .= '<a href="manage_customers.php"><i class="fa-solid fa-users"></i> Customers</a>';
    $html .= '<a href="manage_orders.php"><i class="fa-solid fa-shopping-cart"></i> Orders</a>';
    $html .= '<a href="manage_report.php"><i class="fa-solid fa-chart-bar"></i> Reports</a>';
    
    if (isSuperAdmin()) {
        $html .= '<a href="manage_admins.php"><i class="fa-solid fa-user-shield"></i> Admin Management</a>';
    }
    
    return $html;
}

function generateUserInfo() {
    $role_display = isSuperAdmin() ? 'Super Admin' : 'Admin';
    $role_class = isSuperAdmin() ? 'badge-danger' : 'badge-primary';
    
    $html = '<div class="user-info-dropdown">';
    $html .= '<span class="user-name"><i class="fa-solid fa-user"></i> ' . getAdminName() . '</span>';
    $html .= '<span class="role-badge ' . $role_class . '">' . $role_display . '</span>';
    $html .= '<a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>';
    $html .= '</div>';
    
    return $html;
}

function checkAdminStatus() {
    global $conn;
    
    if (isset($conn) && getAdminId() > 0) {
        $admin_id = getAdminId();
        $sql = "SELECT admin_status FROM admins WHERE admin_id = '$admin_id'";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $admin = mysqli_fetch_assoc($result);
            if ($admin['admin_status'] != 'active') {
                session_destroy();
                header('Location: adminlogin.php?error=Account deactivated');
                exit;
            }
        }
    }
}

function secureHeaders() {
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

function initSecurityChecks() {
    if (isAdminLoggedIn()) {
        checkAdminStatus();
        
        checkCurrentPageAccess();
    }
    
    secureHeaders();
}

initSecurityChecks();
?>