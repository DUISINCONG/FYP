<?php
session_start();
require_once 'db_connect.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        
        $sql = "SELECT admin_id, admin_name FROM admins WHERE admin_email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $admin_id = $row['admin_id'];
            
            $otp = rand(100000, 999999);
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            $update_sql = "UPDATE admins SET otp_code = ?, otp_expiry = ? WHERE admin_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "ssi", $otp, $otp_expiry, $admin_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $subject = "Password Reset OTP";
                $message_body = "Hello " . $row['admin_name'] . ",\n\n";
                $message_body .= "Your OTP for password reset is: " . $otp . "\n";
                $message_body .= "This OTP will expire in 10 minutes.\n\n";
                $message_body .= "If you didn't request this, please ignore this email.";
                
                $headers = "From: no-reply@yourdomain.com\r\n";
                $headers .= "Reply-To: no-reply@yourdomain.com\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                
                if (mail($email, $subject, $message_body, $headers)) {
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['otp_sent'] = true;
                    header("Location: verify_otp.php");
                    exit();
                } else {
                    $message = "Failed to send email. Please try again.";
                }
            } else {
                $message = "Error updating OTP. Please try again.";
            }
        } else {
            $message = "Email not found in our system.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .success {
            background-color: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'not found') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Enter your email address:</label>
                <input type="email" id="email" name="email" required placeholder="admin@example.com">
            </div>
            
            <button type="submit">Send OTP</button>
        </form>
        
        <div class="back-link">
            <a href="adminlogin.php">Back to Login</a>
        </div>
    </div>
</body>
</html>