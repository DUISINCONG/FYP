<?php
session_start();
include 'db_connect.php';
include 'mail_config.php';

function decryptPassword($encrypted_password) {
    $key = 'JC_Restaurant_Admin_Key_2024!@#';
    $decrypted = openssl_decrypt(
        base64_decode($encrypted_password),
        'AES-128-ECB',
        $key,
        OPENSSL_RAW_DATA
    );
    return $decrypted;
}

// Handle Login
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM admins WHERE admin_name = '$username'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        
        if ($admin['admin_status'] != 'active') {
            $error_message = "Your account is inactive. Please contact superadmin.";
        } else {
            $decrypted_password = decryptPassword($admin['admin_password']);
            
            if ($password === $decrypted_password) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['admin_name'];
                $_SESSION['role'] = $admin['role'];
                $_SESSION['logged_in'] = true;
                
                $login_time = date('Y-m-d H:i:s');
                $log_sql = "INSERT INTO admin_logs (admin_id, action, action_time) 
                           VALUES ('{$admin['admin_id']}', 'Login', '$login_time')";
                mysqli_query($conn, $log_sql);

                if ($admin['role'] === 'superadmin') {
                    $_SESSION['welcome_message'] = "Welcome Super Admin!";
                } else {
                    $_SESSION['welcome_message'] = "Welcome Admin!";
                }

                header("Location: /FYP/FYP/backend/adminhomepage.php");
                exit();
            } else {
                $error_message = "Invalid password!";
            }
        }
    } else {
        $error_message = "Invalid username!";
    }
}

if (isset($_POST['request_otp'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $sql = "SELECT * FROM admins WHERE admin_email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        
        // Check account status - if pending, cannot reset password
        if ($admin['admin_status'] == 'pending') {
            $error_message = "Your account is pending approval. You cannot reset password at this time. Please contact superadmin.";
        } 
        // If inactive, also cannot reset password
        elseif ($admin['admin_status'] != 'active') {
            $error_message = "Your account is inactive. You cannot reset password. Please contact superadmin.";
        } 
        // Only active accounts can reset password
        else {
            $otp = sprintf("%06d", mt_rand(1, 999999));
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            $update_sql = "UPDATE admins SET 
                          otp_code = '$otp', 
                          otp_expiry = '$otp_expiry'
                          WHERE admin_email = '$email' AND admin_status = 'active'";
            
            if (mysqli_query($conn, $update_sql)) {
                // Check if rows were actually updated (ensure only active accounts are updated)
                if (mysqli_affected_rows($conn) > 0) {
                    $email_sent = MailSender::sendOTPEmail($email, $otp, $admin['admin_name']);
                    
                    if ($email_sent) {
                        $_SESSION['reset_email'] = $email;
                        $_SESSION['otp_requested'] = true;
                        
                        header("Location: verify_otp.php");
                        exit();
                    } else {
                        $error_message = "Failed to send OTP email. Please try again.";
                    }
                } else {
                    $error_message = "Your account is not active. Password reset is not allowed.";
                }
            } else {
                $error_message = "Error generating OTP. Please try again.";
            }
        }
    } else {
        $error_message = "Email not found in our system!";
    }
}

$clean_sql = "UPDATE admins SET otp_code = NULL, otp_expiry = NULL WHERE otp_expiry < NOW()";
mysqli_query($conn, $clean_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JC Restaurant - Admin Login</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
                    url('adminlogin.jpeg') no-repeat center center fixed;
        background-size: cover;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .banner {
        background-color: rgba(22, 24, 27, 0.9);
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        position: relative;
    }

    .restaurant-name {
        font-size: 28px;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .main-container {
        display: flex;
        justify-content: center;
        align-items: center;
        flex: 1;
        padding: 30px;
    }

    .login-wrapper {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }

    .login-box {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        width: 100%;
        padding: 30px;
        text-align: center;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .login-box .user-icon {
        font-size: 60px;
        color: #3a3a3aff;
        margin-bottom: 15px;
    }

    .login-box h2 {
        color: #16181b;
        margin-bottom: 20px;
        font-size: 24px;
    }

    .input-group {
        position: relative;
        margin: 20px 0;
    }

    .input-group i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #ff9800;
    }

    .login-box input {
        width: 100%;
        border: 2px solid #e0e0e0;
        background: white;
        padding: 12px 15px 12px 45px;
        outline: none;
        color: #2c3e50;
        font-size: 15px;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .login-box input:focus {
        border-color: #ff9800;
        box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.2);
    }

    .login-btn {
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        color: white;
        border: none;
        border-radius: 8px;
        width: 100%;
        padding: 14px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        margin: 20px 0;
    }

    .login-btn:hover {
        background: linear-gradient(135deg, #2d26b1ff 0%, #ff9800 100%);
        transform: translateY(-2px);
    }

    .forgot-password {
        color: #070707ff;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 14px;
        margin-top: 10px;
        display: inline-block;
    }

    .forgot-password:hover {
        text-decoration: underline;
    }

    .back-to-login {
        color: #ff9800;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 14px;
        margin-top: 15px;
        display: inline-block;
    }

    .back-to-login:hover {
        text-decoration: underline;
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        color: white;
        font-size: 14px;
        text-align: left;
    }

    .alert-success {
        background: #2ecc71;
        border-left: 4px solid #27ae60;
    }

    .alert-error {
        background: #e74c3c;
        border-left: 4px solid #c0392b;
    }

    .hidden {
        display: none;
    }

    .instructions {
        color: #7f8c8d;
        font-size: 14px;
        margin: 15px 0 20px 0;
        line-height: 1.6;
    }

    .toggle-password {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #ff9800;
        cursor: pointer;
        font-size: 16px;
    }

    .remember-me {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #2c3e50;
        font-size: 14px;
        margin-top: 10px;
    }

    .password-requirements {
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px;
        margin: 15px 0;
        text-align: left;
        font-size: 13px;
    }

    .password-requirements h4 {
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .password-requirements ul {
        list-style-type: none;
        padding-left: 0;
    }

    .password-requirements li {
        margin-bottom: 5px;
        color: #7f8c8d;
    }

    .password-requirements li i {
        color: #27ae60;
        margin-right: 5px;
    }

    @media (max-width: 768px) {
        .main-container {
            padding: 20px;
        }
        
        .banner {
            text-align: center;
        }
        
        .login-box {
            padding: 25px;
        }
    }

    @media (max-width: 480px) {
        .login-box {
            padding: 20px;
        }
    }
</style>
</head>
<body>

<div class="banner">
    <div class="restaurant-name">
        <i class="fa-solid fa-utensils" style="color: #ff9800;"></i>
        JC Restaurant
    </div>
</div>

<div class="main-container">
    <div class="login-wrapper">
        <div class="login-box">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i> 
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-exclamation-circle"></i> 
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <div id="loginForm">
                <i class="fa-solid fa-circle-user user-icon"></i>
                <h2>Admin Login</h2>

                <form method="POST" action="">
                    
                    <div class="input-group">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                    
                    <div class="input-group">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" id="password" placeholder="Password" required>
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    
                    <button type="submit" name="login" class="login-btn">
                        <i class="fa-solid fa-right-to-bracket"></i> LOGIN
                    </button>
                </form>

                <button type="button" class="forgot-password" onclick="showForgotPassword()">
                    <i class="fa-solid fa-key"></i> Forgot Password?
                </button>
            </div>

            <!-- Forgot Password Form -->
            <div id="forgotPasswordForm" class="hidden">
                <div class="forgot-password-form">
                    <i class="fa-solid fa-key user-icon"></i>
                    <h2>Reset Password</h2>
                    <p class="instructions">Enter your registered email address. We will send you a 6-digit OTP code to verify your identity.</p>
                    <p class="instructions" style="color: #e74c3c; font-weight: bold;">
                        <i class="fa-solid fa-exclamation-triangle"></i> Note: Only active accounts can reset password.
                    </p>
                    
                    <form method="POST" action="">
                        <div class="input-group">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" name="email" placeholder="Enter your email" required>
                        </div>
                        
                        <button type="submit" name="request_otp" class="login-btn">
                            <i class="fa-solid fa-paper-plane"></i> SEND OTP
                        </button>
                    </form>
                    
                    <button type="button" class="back-to-login" onclick="showLogin()">
                        <i class="fa-solid fa-arrow-left"></i> Back to Login
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.querySelector('.toggle-password i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleButton.classList.remove('fa-eye');
            toggleButton.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleButton.classList.remove('fa-eye-slash');
            toggleButton.classList.add('fa-eye');
        }
    }
    
    function showForgotPassword() {
        document.getElementById('loginForm').classList.add('hidden');
        document.getElementById('forgotPasswordForm').classList.remove('hidden');
    }

    function showLogin() {
        document.getElementById('forgotPasswordForm').classList.add('hidden');
        document.getElementById('loginForm').classList.remove('hidden');
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($success_message) && isset($_POST['request_otp'])): ?>
            showForgotPassword();
        <?php endif; ?>
        
        <?php if (isset($error_message) && isset($_POST['request_otp'])): ?>
            showForgotPassword();
        <?php endif; ?>
    });
</script>

</body>
</html>
