<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require __DIR__ . '/PHPMailer/src/Exception.php'; 
require "backend/db_connect.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$step = $_GET['step'] ?? 'email';
$error = "";
$success = "";

// ================================
// STEP 1: SEND OTP
// ================================
if (isset($_POST['send_otp'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $check = mysqli_query($conn, "SELECT * FROM customers WHERE customer_email='$email'");
    if (mysqli_num_rows($check) == 1) {
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        mysqli_query($conn, "UPDATE customers SET reset_otp='$otp', otp_expiry='$expiry' WHERE customer_email='$email'");

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'keishavrao2526@gmail.com';
            $mail->Password   = 'mrfkyycbokmvtvah';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('keishavrao2526@gmail.com', 'JC Restaurant');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'JC Restaurant Password Reset OTP';
            $mail->Body    = "Your OTP is: <b>$otp</b><br>It expires in 5 minutes.";

            $mail->send();

            $_SESSION['reset_email'] = $email;
            header("Location: reset-password.php?step=verify");
            exit;
        } catch (Exception $e) {
            $error = "OTP email could not be sent.";
        }
    } else {
        $error = "Email not found.";
    }
}

// ================================
// STEP 2: VERIFY OTP
// ================================
if (isset($_POST['verify_otp'])) {
    $otp = $_POST['otp'];
    $email = $_SESSION['reset_email'] ?? '';
    $q = mysqli_query($conn, "
        SELECT * FROM customers 
        WHERE customer_email='$email' 
        AND reset_otp='$otp'
        AND otp_expiry >= NOW()
    ");
    if (mysqli_num_rows($q) == 1) {
        header("Location: reset-password.php?step=newpassword");
        exit;
    } else {
        $error = "Invalid or expired OTP.";
    }
}

// ================================
// STEP 3: RESET PASSWORD
// ================================
if (isset($_POST['reset_password'])) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $email = $_SESSION['reset_email'] ?? '';
    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($conn, "
            UPDATE customers 
            SET password_hash='$hashed', reset_otp=NULL, otp_expiry=NULL 
            WHERE customer_email='$email'
        ");
        session_destroy();
        $success = "Password reset successful! You can now login.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password</title>

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background-image: url('images/reset-bg.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Background img*/
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background-image: url('images/reset-bg.png'); /* background image */
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* OLD BLACK BOX (RESTORED) */
.profile-card {
    width: 420px;
    background: rgba(0, 0, 0, 0.75); /* BLACK TRANSPARENT */
    padding: 30px;
    border-radius: 15px;
    color: #fff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.6);
}

.profile-card h2 {
    text-align: center;
    color: #ff9800;
    margin-bottom: 20px;
}

input {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border-radius: 8px;
    border: none;
    background: #222;
    color: #fff;
    font-size: 15px;
}

input::placeholder {
    color: #bbb;
}

button {
    width: 100%;
    padding: 12px;
    margin-top: 18px;
    border-radius: 25px;
    border: none;
    background: #ff9800;
    color: #000;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #ffa726;
}

.error {
    background: #ff4d4d;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
    text-align: center;
    color: #fff;
}

.success {
    background: #4caf50;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
    text-align: center;
    color: #fff;
}

.toggle {
    cursor: pointer;
    font-size: 14px;
    margin-top: 6px;
    display: inline-block;
    color: #ccc;
}
</style>
</head>

<body>

<div class="profile-card">
<h2>Reset Password</h2>

<?php if ($error): ?>
<div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="success"><?= htmlspecialchars($success) ?></div>
<button onclick="window.location.href='customerLogin.php'">Go to Login</button>
<?php endif; ?>

<?php if (!$success): ?>

<?php if ($step == "email"): ?>
<form method="post">
    <input type="email" name="email" placeholder="Enter your email" required>
    <button name="send_otp">Send OTP</button>
</form>
<?php endif; ?>

<?php if ($step == "verify"): ?>
<form method="post">
    <input type="text" name="otp" placeholder="Enter OTP" required>
    <button name="verify_otp">Verify OTP</button>
</form>
<?php endif; ?>

<?php if ($step == "newpassword"): ?>
<form method="post">
    <input type="password" id="pass" name="password" placeholder="New Password" required>
    <input type="password" id="confirm" name="confirm" placeholder="Confirm Password" required>
    <span class="toggle" onclick="toggle()">Show / Hide Password</span>
    <button name="reset_password">Reset Password</button>
</form>
<?php endif; ?>

<?php endif; ?>
</div>

<script>
function toggle() {
    let p1 = document.getElementById("pass");
    let p2 = document.getElementById("confirm");
    p1.type = p1.type === "password" ? "text" : "password";
    p2.type = p2.type === "password" ? "text" : "password";
}
</script>

</body>
</html>

