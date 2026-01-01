<?php
require __DIR__ . '/../backend/db_connect.php';
session_start();

$email = trim($_GET['email'] ?? '');

if ($email === '') {
    echo "<script>alert('Missing email.'); window.location.href='otp_login.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');

    $sql = "SELECT customer_id FROM customers
            WHERE email=? AND otp_code=? AND otp_expiry IS NOT NULL AND otp_expiry > NOW()
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $email, $otp);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if ($row) {
        mysqli_query($conn, "UPDATE customers SET otp_code=NULL, otp_expiry=NULL WHERE customer_id=" . (int)$row['customer_id']);
        $_SESSION['customer_id'] = (int)$row['customer_id'];
        $_SESSION['customer_email'] = $email;
        header('Location: ../index.html');
        exit;
    } else {
        $error = "Invalid or expired OTP.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Verify Login OTP</title>
</head>
<body class="bg-light">
  <div class="container mt-5" style="max-width:520px;">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h4 class="mb-3">Enter OTP</h4>
        <p class="text-muted">We sent a login OTP to <b><?php echo htmlspecialchars($email); ?></b>.</p>
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">OTP Code</label>
            <input type="text" name="otp" class="form-control" maxlength="6" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">Login</button>
        </form>
        <div class="mt-3">
          <a href="otp_login.php">Resend OTP</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
