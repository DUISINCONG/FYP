<?php
require __DIR__ . '/db_connect.php';
session_start();

if (isset($_POST['loginbutton'])) {

    $cemail = trim($_POST['cemail'] ?? '');
    $cpassword = $_POST['cpassword'] ?? '';

    if ($cemail === '' || $cpassword === '') {
        echo "<script>alert('Please enter email and password');</script>";
    } else {

        $sql = "SELECT * FROM customers WHERE customer_email = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $cemail);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {

            $loginOk = false;

            // 1️⃣ New users (hashed password)
            if (!empty($user['password_hash'])) {
                $loginOk = password_verify($cpassword, $user['password_hash']);
            }
            // 2️⃣ Old users (plaintext fallback – optional)
            else if (!empty($user['customer_password'])) {
                $loginOk = ($cpassword === $user['customer_password']);
            }

            if ($loginOk) {

                // 🔐 Block unverified accounts
                if ((int)$user['is_verified'] === 0) {
                    echo "<script>
                        alert('Please verify your email (OTP) before logging in.');
                        window.location.href='customer_registration/registration.php';
                    </script>";
                    exit;
                }

                // ✅ Login success
                $_SESSION['customer_id'] = $user['customer_id'];
                $_SESSION['customer_email'] = $user['customer_email'];

                header("Location: index.html");
                exit;
            }
        }

        echo "<script>alert('Invalid email or password');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Login</title>
    <link rel="stylesheet" href="clogincss.css">
</head>
<body>

<div class="A">
    <div class="B">
        <img src="JC_Restaurant_Logo.png" alt="JC Restaurant Logo">
    </div>

    <div class="B1">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <h2>Welcome to JC Restaurant</h2>

            <div class="C">
                <div class="D">
                    <label for="cemail">Email :</label><br>
                    <input type="email" id="cemail" name="cemail" required>
                </div>

                <div class="D">
                    <label for="cpassword">Password :</label><br>
                    <input type="password" id="cpassword" name="cpassword" required>
                </div>

                <br>

                <div class="F">
                    <input type="submit" name="loginbutton" value="Login">
                </div>
            </div>
        </form>

        <div class="text-center mt-3">
            <a href="customer_registration/otp_login.php">Login with OTP</a>
        </div>

        <p>--------------- or ---------------</p>

        <div class="E">
            <button type="button"
                onclick="window.location.href='customer_registration/registration.php'">
                Register
            </button>
        </div>
    </div>
</div>

</body>
</html>

