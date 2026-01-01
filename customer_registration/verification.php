<?php
require __DIR__ . '/../backend/db_connect.php';

$email = $_GET['email'] ?? '';

if ($email === '') {
    die("Invalid request.");
}

if (isset($_POST['verify'])) {

    $otp = trim($_POST['otp']);

    if ($otp === '') {
        $error = "Please enter the OTP.";
    } else {

        $sql = "
            SELECT customer_id 
            FROM customers
            WHERE customer_email = ?
              AND otp_code = ?
              AND otp_expiry > NOW()
              AND is_verified = 0
        ";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $email, $otp);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {

            $update = "
                UPDATE customers
                SET is_verified = 1,
                    otp_code = NULL,
                    otp_expiry = NULL
                WHERE customer_id = ?
            ";

            $stmt2 = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt2, "i", $row['customer_id']);
            mysqli_stmt_execute($stmt2);

            echo "<script>
                alert('Account verified successfully. Please log in.');
                window.location.href='../customerLogin.php';
            </script>";
            exit;

        } else {
            $error = "Invalid or expired OTP.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
</head>
<body>

<h2>Verify Your Account</h2>

<p>An OTP has been sent to your email.</p>

<?php if (!empty($error)) { ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php } ?>

<form method="POST">
    <label>Enter OTP:</label><br>
    <input type="text" name="otp" required><br><br>
    <button type="submit" name="verify">Verify OTP</button>
</form>

</body>
</html>