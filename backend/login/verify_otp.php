<?php
session_start();
require_once 'db_connect.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_SESSION['reset_email'])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $sql = "SELECT * FROM admins WHERE admin_email = '$email'";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $_SESSION['reset_email'] = $email;
            $_SESSION['otp_requested'] = true;
        } else {
            header("Location: adminlogin.php");
            exit();
        }
    } else {
        header("Location: adminlogin.php");
        exit();
    }
}

$email = $_SESSION['reset_email'];
$message = '';
$show_form = true;

$sql = "SELECT admin_name FROM admins WHERE admin_email = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    unset($_SESSION['reset_email']);
    unset($_SESSION['otp_requested']);
    header("Location: adminlogin.php");
    exit();
}

$admin = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify_otp'])) {
        $otp = mysqli_real_escape_string($conn, $_POST['otp']);
        
        $sql = "SELECT admin_id FROM admins WHERE admin_email = ? AND otp_code = ? AND otp_expiry > NOW()";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $email, $otp);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            $reset_token = bin2hex(random_bytes(32));
            $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $update_sql = "UPDATE admins SET 
                          otp_code = NULL, 
                          otp_expiry = NULL,
                          reset_token = '$reset_token',
                          token_expiry = '$token_expiry'
                          WHERE admin_email = '$email'";
            
            if (mysqli_query($conn, $update_sql)) {
                $_SESSION['reset_token'] = $reset_token;
                header("Location: reset_password.php");
                exit();
            } else {
                $message = "<div class='alert error'>Error verifying OTP. Please try again.</div>";
            }
        } else {
            $message = "<div class='alert error'>Invalid or expired OTP. Please try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - JC Restaurant</title>
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
        
        .otp-container {
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
        
        .otp-icon {
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
        
        .instructions {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .otp-input-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .otp-input {
            width: 50px;
            height: 60px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            transition: all 0.3s;
        }
        
        .otp-input:focus {
            border-color: #ff9800;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.2);
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
        
        .timer {
            color: #e74c3c;
            font-weight: bold;
            margin-bottom: 20px;
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
        
        @media (max-width: 768px) {
            .main-container {
                padding: 20px;
            }
            
            .otp-container {
                padding: 30px;
            }
            
            .otp-input-group {
                gap: 8px;
            }
            
            .otp-input {
                width: 45px;
                height: 55px;
                font-size: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .otp-container {
                padding: 25px;
            }
            
            .otp-input-group {
                gap: 5px;
            }
            
            .otp-input {
                width: 40px;
                height: 50px;
                font-size: 18px;
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
        <div class="otp-container">
            <i class="fa-solid fa-shield-alt otp-icon"></i>
            <h2>Verify OTP</h2>
            
            <div class="user-info">
                <i class="fa-solid fa-envelope"></i> Email: <strong><?php echo htmlspecialchars($email); ?></strong>
            </div>
            
            <?php echo $message; ?>
            
            <p class="instructions">
                Enter the 6-digit OTP that was sent to your email address. 
                This OTP will expire in 10 minutes.
            </p>
            
            <form method="POST" action="">
                <div class="otp-input-group">
                    <input type="text" class="otp-input" name="otp1" maxlength="1" required autofocus onkeyup="moveFocus(1)">
                    <input type="text" class="otp-input" name="otp2" maxlength="1" required onkeyup="moveFocus(2)">
                    <input type="text" class="otp-input" name="otp3" maxlength="1" required onkeyup="moveFocus(3)">
                    <input type="text" class="otp-input" name="otp4" maxlength="1" required onkeyup="moveFocus(4)">
                    <input type="text" class="otp-input" name="otp5" maxlength="1" required onkeyup="moveFocus(5)">
                    <input type="text" class="otp-input" name="otp6" maxlength="1" required onkeyup="moveFocus(6)">
                    <input type="hidden" name="otp" id="full-otp">
                </div>
                
                <button type="submit" name="verify_otp" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i> VERIFY OTP
                </button>
            </form>
            
            <a href="adminlogin.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>

    <script>
        const otpInputs = document.querySelectorAll('.otp-input');
        const fullOtpInput = document.getElementById('full-otp');
        
        function moveFocus(currentIndex) {
            const currentInput = otpInputs[currentIndex - 1];
            const nextInput = otpInputs[currentIndex];
            
            if (currentInput.value.length === 1 && nextInput) {
                nextInput.focus();
            }
            
            updateFullOTP();
        }
        
        function updateFullOTP() {
            let otp = '';
            otpInputs.forEach(input => {
                otp += input.value;
            });
            fullOtpInput.value = otp;
        }
        
        otpInputs.forEach((input, index) => {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
        });
        
        document.addEventListener('paste', (e) => {
            const pastedData = e.clipboardData.getData('text').trim();
            if (pastedData.length === 6 && /^\d+$/.test(pastedData)) {
                for (let i = 0; i < 6; i++) {
                    otpInputs[i].value = pastedData[i];
                }
                updateFullOTP();
                otpInputs[5].focus();
                e.preventDefault();
            }
        });
    </script>
</body>
</html>