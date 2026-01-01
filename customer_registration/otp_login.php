<?php
require __DIR__ . '/../backend/db_connect.php';
require __DIR__ . '/vendor/phpmailer/src/Exception.php';
require __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = 'Please enter your email.';
    } else {
        $otp = (string)random_int(100000, 999999);
        $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $stmt = mysqli_prepare($conn, "UPDATE customers SET otp_code=?, otp_expiry=? WHERE email=?");
        mysqli_stmt_bind_param($stmt, "sss", $otp, $expiry, $email);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if ($affected <= 0) {
            $error = 'Email not found. Please register first.';
        } else {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'keishavrao2526@gmail.com';
                $mail->Password = 'REPLACE_WITH_NEW_APP_PASSWORD';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                $mail->setFrom('keishavrao2526@gmail.com', 'JC Restaurant');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Login OTP';
                $mail->Body = "Your login OTP is: <b>{$otp}</b><br><br>Valid for 5 minutes.";

                $mail->send();
                header('Location: otp_login_verify.php?email=' . rawurlencode($email));
                exit;

            } catch (Exception $e) {
                $error = 'Failed to send OTP email. Check SMTP/App Password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Login with OTP</title>
</head>
<body class="bg-light">
  <div class="container mt-5" style="max-width:520px;">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h4 class="mb-3">Login with OTP</h4>
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">Send OTP</button>
        </form>
        <div class="mt-3">
          <a href="../customerLogin.php">Back to password login</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
