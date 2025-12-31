<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['reset_token'])) {
    header("Location: adminlogin.php");
    exit();
}

$token = $_SESSION['reset_token'];
$message = '';

$token = preg_replace('/[^a-f0-9]/', '', $token); 

$sql = "SELECT admin_id, admin_email FROM admins 
        WHERE reset_token LIKE ? 
        AND token_expiry > NOW()";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    die("Prepare failed: " . mysqli_error($conn));
}

$token_param = "%" . $token . "%";
mysqli_stmt_bind_param($stmt, "s", $token_param);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    unset($_SESSION['reset_token']);
    $_SESSION['error'] = "Invalid or expired reset link";
    header("Location: adminlogin.php");
    exit();
}

$user = mysqli_fetch_assoc($result);
$user_id = $user['admin_id'];
$email = $user['admin_email'];

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $message = "<div class='alert error'>Passwords do not match!</div>";
    } else {
        if (strlen($new_password) < 8) {
            $message = "<div class='alert error'>Password must be at least 8 characters long!</div>";
        } elseif (!preg_match('/[a-zA-Z]/', $new_password)) {
            $message = "<div class='alert error'>Password must contain at least one letter!</div>";
        } elseif (!preg_match('/[0-9]/', $new_password)) {
            $message = "<div class='alert error'>Password must contain at least one number!</div>";
        } elseif (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $new_password)) {
            $message = "<div class='alert error'>Password must contain at least one special character (!@#$%^&*()-_=+{};:,<.>)!</div>";
        } elseif (preg_match('/\s/', $new_password)) {
            $message = "<div class='alert error'>Password cannot contain spaces!</div>";
        } else {
            $encrypted_password = encryptPassword($new_password);
            
            $update_sql = "UPDATE admins SET 
                          admin_password = ?,
                          reset_token = NULL,
                          token_expiry = NULL,
                          otp_code = NULL,
                          otp_expiry = NULL
                          WHERE admin_id = ?";
            
            $stmt = mysqli_prepare($conn, $update_sql);
            
            if ($stmt === false) {
                $message = "<div class='alert error'>Database error: " . mysqli_error($conn) . "</div>";
            } else {
                mysqli_stmt_bind_param($stmt, "si", $encrypted_password, $user_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    unset($_SESSION['reset_token']);
                    unset($_SESSION['reset_email']);
                    unset($_SESSION['otp_requested']);
                    
                    $_SESSION['success'] = "Password has been reset successfully!";
                    header("Location: adminlogin.php");
                    exit();
                } else {
                    $message = "<div class='alert error'>Error resetting password. Please try again.</div>";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - JC Restaurant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
        
        .reset-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            width: 100%;
            max-width: 450px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .reset-icon {
            font-size: 60px;
            color: #ff9800;
            margin-bottom: 20px;
        }
        
        h2 {
            color: #16181b;
            margin-bottom: 10px;
        }
        
        .user-info {
            color: #7f8c8d;
            margin-bottom: 25px;
            font-size: 14px;
        }
        
        .user-info strong {
            color: #2c3e50;
        }
        
        .requirements {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            text-align: left;
            border-left: 4px solid #ff9800;
        }
        
        .requirements h4 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .requirement-list {
            list-style: none;
            padding-left: 0;
        }
        
        .requirement-list li {
            margin-bottom: 8px;
            font-size: 13px;
            color: #555;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .requirement-list li i {
            font-size: 12px;
            width: 20px;
        }
        
        .requirement-list li.valid {
            color: #27ae60;
        }
        
        .requirement-list li.invalid {
            color: #e74c3c;
        }
        
        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .password-input {
            width: 100%;
            padding: 14px 45px 14px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .password-input:focus {
            border-color: #ff9800;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.2);
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #7f8c8d;
            cursor: pointer;
            font-size: 16px;
        }
        
        .strength-meter {
            margin-top: 10px;
            height: 5px;
            background: #eee;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .strength-text {
            font-size: 12px;
            margin-top: 5px;
            color: #7f8c8d;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 15px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #f57c00 0%, #ff9800 100%);
            transform: translateY(-2px);
        }
        
        .btn-primary:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: white;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .alert.success {
            background: #2ecc71;
        }
        
        .alert.error {
            background: #e74c3c;
        }
        
        .back-link {
            color: #ff9800;
            text-decoration: none;
            font-size: 14px;
            margin-top: 20px;
            display: inline-block;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .password-match {
            font-size: 12px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .match {
            color: #2ecc71;
        }
        
        .no-match {
            color: #e74c3c;
        }
        
        @media (max-width: 768px) {
            .main-container {
                padding: 20px;
            }
            
            .reset-container {
                padding: 30px;
            }
        }
        
        @media (max-width: 480px) {
            .reset-container {
                padding: 25px;
            }
            
            .restaurant-name {
                font-size: 24px;
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
        <div class="reset-container">
            <i class="fa-solid fa-key reset-icon"></i>
            <h2>Reset Your Password</h2>
            
            <div class="user-info">
                <i class="fa-solid fa-user"></i> Account: <strong><?php echo htmlspecialchars($email); ?></strong>
            </div>
            
            <?php echo $message; ?>
            
            <div class="requirements">
                <h4>Password Requirements:</h4>
                <ul class="requirement-list">
                    <li id="req-length"><i class="fa-solid fa-circle"></i> At least 8 characters</li>
                    <li id="req-letter"><i class="fa-solid fa-circle"></i> At least one letter (a-z, A-Z)</li>
                    <li id="req-number"><i class="fa-solid fa-circle"></i> At least one number (0-9)</li>
                    <li id="req-special"><i class="fa-solid fa-circle"></i> At least one special character (!@#$%^&*()-_=+{};:,<.>)</li>
                    <li id="req-nospace"><i class="fa-solid fa-circle"></i> No spaces allowed</li>
                </ul>
            </div>
            
            <form method="POST" action="" id="resetForm">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password" class="password-input" 
                               placeholder="Enter new password (no spaces)" required minlength="8">
                        <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="strength-meter">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <div class="strength-text" id="strengthText">Password strength: None</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" class="password-input" 
                               placeholder="Confirm new password (no spaces)" required minlength="8">
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-match" id="passwordMatch">
                        <i class="fa-solid fa-circle"></i> <span>Passwords must match</span>
                    </div>
                </div>
                
                <button type="submit" name="reset_password" class="btn btn-primary" id="submitBtn" disabled>
                    <i class="fa-solid fa-check"></i> RESET PASSWORD
                </button>
            </form>
            
            <a href="adminlogin.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>

    <script>
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const submitBtn = document.getElementById('submitBtn');
        
        const reqLength = document.getElementById('req-length');
        const reqLetter = document.getElementById('req-letter');
        const reqNumber = document.getElementById('req-number');
        const reqSpecial = document.getElementById('req-special');
        const reqNoSpace = document.getElementById('req-nospace');
        
        const passwordMatch = document.getElementById('passwordMatch');
        
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        function checkPasswordStrength(password) {
            let strength = 0;
            let color = '#e74c3c'; 
            let text = 'Weak';
            
            if (password.length >= 8) strength++;
            
            if (/[a-zA-Z]/.test(password)) strength++;
            
            if (/[0-9]/.test(password)) strength++;
            
            if (/[!@#$%^&*()\-_=+{};:,<.>]/.test(password)) strength++;
            
            if (/\s/.test(password)) {
                strength = 0;
            }
            
            switch(strength) {
                case 0:
                    color = '#e74c3c'; 
                    text = 'Weak';
                    break;
                case 1:
                    color = '#e74c3c'; 
                    text = 'Weak';
                    break;
                case 2:
                    color = '#f39c12'; 
                    text = 'Fair';
                    break;
                case 3:
                    color = '#ff9800'; 
                    text = 'Good';
                    break;
                case 4:
                    color = '#2ecc71'; 
                    text = 'Strong';
                    break;
            }
            
            strengthFill.style.width = (strength * 25) + '%';
            strengthFill.style.backgroundColor = color;
            strengthText.textContent = 'Password strength: ' + text;
            strengthText.style.color = color;
            
            return strength;
        }
        
        function validatePassword(password) {
            const isValidLength = password.length >= 8;
            const hasLetter = /[a-zA-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*()\-_=+{};:,<.>]/.test(password);
            const noSpaces = !/\s/.test(password);
            
            updateRequirementUI(reqLength, isValidLength);
            updateRequirementUI(reqLetter, hasLetter);
            updateRequirementUI(reqNumber, hasNumber);
            updateRequirementUI(reqSpecial, hasSpecial);
            updateRequirementUI(reqNoSpace, noSpaces);
            
            return isValidLength && hasLetter && hasNumber && hasSpecial && noSpaces;
        }
        
        function updateRequirementUI(element, isValid) {
            const icon = element.querySelector('i');
            if (isValid) {
                element.classList.add('valid');
                element.classList.remove('invalid');
                icon.classList.remove('fa-circle');
                icon.classList.add('fa-check-circle');
                icon.style.color = '#2ecc71';
            } else {
                element.classList.add('invalid');
                element.classList.remove('valid');
                icon.classList.remove('fa-check-circle');
                icon.classList.add('fa-circle');
                icon.style.color = '#e74c3c';
            }
        }
        
        function checkPasswordMatch() {
            const password = newPassword.value;
            const confirm = confirmPassword.value;
            const icon = passwordMatch.querySelector('i');
            const text = passwordMatch.querySelector('span');
            
            if (confirm === '') {
                icon.style.color = '#7f8c8d';
                passwordMatch.classList.remove('match', 'no-match');
                text.textContent = 'Passwords must match';
            } else if (password === confirm) {
                icon.style.color = '#2ecc71';
                icon.classList.remove('fa-circle');
                icon.classList.add('fa-check-circle');
                passwordMatch.classList.add('match');
                passwordMatch.classList.remove('no-match');
                text.textContent = 'Passwords match';
            } else {
                icon.style.color = '#e74c3c';
                icon.classList.remove('fa-check-circle');
                icon.classList.add('fa-times-circle');
                passwordMatch.classList.add('no-match');
                passwordMatch.classList.remove('match');
                text.textContent = 'Passwords do not match';
            }
            
            return password === confirm;
        }
        
        function validateForm() {
            const password = newPassword.value;
            const confirm = confirmPassword.value;
            
            const isPasswordValid = validatePassword(password);
            const isPasswordStrong = checkPasswordStrength(password) >= 3;
            const doPasswordsMatch = checkPasswordMatch();
            
            const canSubmit = isPasswordValid && isPasswordStrong && doPasswordsMatch && password !== '';
            
            submitBtn.disabled = !canSubmit;
        }
        
        newPassword.addEventListener('keypress', function(e) {
            if (e.key === ' ') {
                e.preventDefault();
            }
        });
        
        confirmPassword.addEventListener('keypress', function(e) {
            if (e.key === ' ') {
                e.preventDefault();
            }
        });
        
        newPassword.addEventListener('input', validateForm);
        confirmPassword.addEventListener('input', validateForm);
        
        validateForm();
    </script>
</body>
</html>