<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* =========================
   PHPMailer includes
========================= */
require __DIR__ . '/vendor/phpmailer/src/Exception.php';
require __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/src/SMTP.php';

/* =========================
   Database connection
========================= */
require __DIR__ . '/../backend/db_connect.php';

if (isset($_POST['send'])) {

    /* =========================
       Get & validate input
    ========================= */
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $rawPassword = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $rawPassword === '') {
        echo "<script>
            alert('Please fill in all required fields.');
            window.location.href='registration.php';
        </script>";
        exit;
    }

    /* =========================
       Generate OTP
    ========================= */
    $otp = (string) random_int(100000, 999999);
    $otpExpiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    /* =========================
       Hash password
    ========================= */
    $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);

    /* =========================
       Insert / Update customer
       (uses REAL column names)
    ========================= */
    $sql = "
        INSERT INTO customers
        (customer_name, customer_email, customer_phone, password_hash, otp_code, otp_expiry, is_verified, customer_status)
        VALUES (?, ?, ?, ?, ?, ?, 0, 'active')
        ON DUPLICATE KEY UPDATE
            customer_name = VALUES(customer_name),
            customer_phone = VALUES(customer_phone),
            password_hash = VALUES(password_hash),
            otp_code = VALUES(otp_code),
            otp_expiry = VALUES(otp_expiry),
            is_verified = 0
    ";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo "<script>
            alert('Database prepare failed.');
            window.location.href='registration.php';
        </script>";
        exit;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ssssss",
        $name,
        $email,
        $phone,
        $passwordHash,
        $otp,
        $otpExpiry
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo "<script>
            alert('Database insert failed.');
            window.location.href='registration.php';
        </script>";
        exit;
    }

    mysqli_stmt_close($stmt);

    /* =========================
       Send OTP email
    ========================= */
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // 🔐 PUT YOUR NEW 16-CHAR APP PASSWORD HERE
        $mail->Username = 'keishavrao2526@gmail.com';
        $mail->Password = 'jeljpxxmvtbiqbda';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('keishavrao2526@gmail.com', 'JC Restaurant');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'OTP Verification';
        $mail->Body = "
            <h3>Your OTP Code</h3>
            <p><b>{$otp}</b></p>
            <p>This OTP is valid for 5 minutes.</p>
        ";

        $mail->send();

        echo "<script>
            alert('Verification code has been sent to your email.');
            window.location.href='verification.php?email=" . rawurlencode($email) . "';
        </script>";
        exit;

    } catch (Exception $e) {
        echo "<script>
            alert('Email failed to send. Check Gmail App Password.');
            window.location.href='registration.php';
        </script>";
        exit;
    }
}
?>