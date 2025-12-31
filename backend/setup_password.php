<?php
session_start();
include 'db_connect.php';

$error_message = '';
$success_message = '';
$show_form = false;
$admin = null;

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

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    $sql = "SELECT * FROM admins WHERE reset_token = '$token' AND token_expiry > NOW()";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        $show_form = true;
        
        if (isset($_POST['set_password'])) {
            $password = mysqli_real_escape_string($conn, $_POST['password']);
            $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
            
                if (strlen($password) < 8) {
                    $error_message = "Error: Password must be at least 8 characters long";
                } elseif (strlen($password) > 50) {
                    $error_message = "Error: Password cannot exceed 50 characters";
                } elseif (!preg_match('/[a-zA-Z]/', $password)) {
                    $error_message = "Error: Password must contain at least one letter";
                } elseif (!preg_match('/[0-9]/', $password)) {
                    $error_message = "Error: Password must contain at least one number";
                } elseif (!preg_match('/[?!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
                    $error_message = "Error: Password must contain at least one special character (?!@#$%^&*()-_=+{};:,<.>)";
                } elseif (preg_match('/\s/', $password)) {
                    $error_message = "Error: Password cannot contain spaces";
                } elseif ($password !== $confirm_password) {
                    $error_message = "Error: Passwords do not match";
            } else {
                $encrypted_password = encryptPassword($password);
                
                $update_sql = "UPDATE admins SET 
                              admin_password = '$encrypted_password', 
                              admin_status = 'active',
                              reset_token = NULL,
                              token_expiry = NULL
                              WHERE admin_id = {$admin['admin_id']}";
                
                if (mysqli_query($conn, $update_sql)) {
                    $success_message = "Password set successfully! You can now log in with your username and password.";
                    $show_form = false;
                } else {
                    $error_message = "Error setting password: " . mysqli_error($conn);
                }
            }
        }
    } else {
        $error_message = "Invalid or expired token. Please request a new password setup link.";
        $show_form = false;
    }
} else {
    $error_message = "No token provided in the URL.";
    $show_form = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC Restaurant - Set Up Password</title>
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
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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
        
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 30px;
            margin-bottom: 30px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(176, 194, 206, 0.2);
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
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
        
        .admin-info {
            background-color: #f8f9fa;
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .admin-info p {
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .admin-name {
            font-weight: bold;
            color: var(--primary);
            font-size: 18px;
        }
        
        .admin-email {
            color: #7f8c8d;
            font-size: 15px;
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        
        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .icon-large {
            font-size: 48px;
            color: var(--primary);
            margin-bottom: 20px;
            text-align: center;
            display: block;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
                padding: 15px;
            }
            
            .card {
                padding: 20px;
            }
            
            .banner {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="banner">
        <div class="restaurant-name">JC Restaurant</div>
    </div>
    
    <div class="container">
        <header>
            <h1>Set Up Your Admin Password</h1>
            <p class="subtitle">Complete your account setup by creating a secure password</p>
        </header>
        
        <div class="card">
            <?php if (!empty($success_message)): ?>
                <div style="text-align: center;">
                    <i class="fas fa-check-circle icon-large" style="color: var(--success);"></i>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                    <div class="login-link">
                        <a href="login/adminlogin.php">
                            <i class="fas fa-sign-in-alt"></i> Go to Login Page
                        </a>
                    </div>
                </div>
            <?php elseif ($show_form && $admin): ?>
                <i class="fas fa-user-shield icon-large"></i>
                
                <div class="admin-info">
                    <p><strong>Welcome to JC Restaurant Admin System</strong></p>
                    <p class="admin-name"><?php echo htmlspecialchars($admin['admin_name']); ?></p>
                    <p class="admin-email"><?php echo htmlspecialchars($admin['admin_email']); ?></p>
                </div>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Note:</strong> Please create a secure password for your admin account. 
                    This password will be used to access the JC Restaurant admin system.
                </div>
                
                <div class="password-requirements">
                    <h4><i class="fas fa-shield-alt"></i> Password Requirements:</h4>
                    <ul>
                        <li>At least 8 characters long</li>
                        <li>Contains at least one letter (a-z, A-Z)</li>
                        <li>Contains at least one number (0-9)</li>
                        <li>Contains at least one special character (?!@#$%^&*()-_=+{};:,<.>)</li>
                    </ul>
                </div>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-key"></i> New Password
                        </label>
                        <input type="password" id="password" name="password" placeholder="Enter your new password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-key"></i> Confirm Password
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
                    </div>
                    
                    <div style="margin-top: 25px;">
                        <button type="submit" name="set_password" class="btn btn-primary">
                            <i class="fas fa-check-circle"></i> Set Password & Activate Account
                        </button>
                    </div>
                </form>
                
                <div class="login-link">
                    <a href="login/adminlogin.php">
                        <i class="fas fa-arrow-left"></i> Back to Login Page
                    </a>
                </div>
            <?php else: ?>
                <div style="text-align: center;">
                    <i class="fas fa-exclamation-triangle icon-large" style="color: var(--danger);"></i>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Invalid Request:</strong> <?php echo $error_message; ?>
                    </div>
                    <div class="login-link">
                        <a href="login/adminlogin.php">
                            <i class="fas fa-sign-in-alt"></i> Go to Login Page
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('confirm_password');
            
            if (passwordInput && confirmInput) {
                function validatePasswords() {
                    if (passwordInput.value !== confirmInput.value) {
                        confirmInput.setCustomValidity("Passwords do not match");
                    } else {
                        confirmInput.setCustomValidity("");
                    }
                }
                
                passwordInput.addEventListener('change', validatePasswords);
                confirmInput.addEventListener('keyup', validatePasswords);
                
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    const requirements = document.querySelector('.password-requirements');
                    
                    if (requirements) {
                        if (password.length >= 8) {
                            requirements.querySelector('li:nth-child(1)').style.color = 'var(--success)';
                        } else {
                            requirements.querySelector('li:nth-child(1)').style.color = '';
                        }
                        
                        if (/[a-zA-Z]/.test(password)) {
                            requirements.querySelector('li:nth-child(2)').style.color = 'var(--success)';
                        } else {
                            requirements.querySelector('li:nth-child(2)').style.color = '';
                        }
                        
                        if (/[0-9]/.test(password)) {
                            requirements.querySelector('li:nth-child(3)').style.color = 'var(--success)';
                        } else {
                            requirements.querySelector('li:nth-child(3)').style.color = '';
                        }
                        
                        if (/[?!@#$%^&*()\-_=+{};:,<.>]/.test(password)) {
                            requirements.querySelector('li:nth-child(4)').style.color = 'var(--success)';
                        } else {
                            requirements.querySelector('li:nth-child(4)').style.color = '';
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>