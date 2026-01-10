<?php
session_start();
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

            session_start(); // ADD THIS at TOP if not present

            $_SESSION['customer_id'] = $row['customer_id'];

            echo "<script>
            alert('Account verified successfully!');
            window.location.href='../homepage.html';
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

    <style>
    body {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

    background:
        linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.65)),
        url("image/verify.png") no-repeat center center / cover;

    display: flex;
    justify-content: center;
    align-items: center;
}

/* Main wrapper using body itself */
body {
    display: flex;
    gap: 80px;
}

/* LEFT TEXT */
h2 {
    color: #ff7a00;
    font-size: 36px;
    font-weight: 700;
    margin: 0 0 10px 0;
}

p {
    color: #e0e0e0;
    font-size: 16px;
    line-height: 1.6;
    max-width: 420px;
    margin: 0;
}

/* Wrap text automatically */
h2, p {
    max-width: 450px;
}

/* OTP CARD */
form {
    background: rgba(0, 0, 0, 0.85);
    padding: 35px 30px;
    width: 380px;
    border-radius: 14px;
    box-shadow: 0 20px 45px rgba(0,0,0,0.7);
}

/* Label */
label {
    color: #fff;
    font-size: 15px;
    font-weight: 500;
}

/* OTP input */
input[type="text"] {
    width: 92%;
    padding: 14px;
    margin-top: 10px;
    border-radius: 8px;
    border: none;
    outline: none;
    font-size: 18px;
    letter-spacing: 6px;
    text-align: center;
}

/* Button */
button {
    width: 100%;
    margin-top: 25px;
    padding: 14px;
    background: #ff7a00;
    border: none;
    border-radius: 8px;
    color: #fff;
    font-size: 17px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
}

button:hover {
    background: #e86c00;
    transform: translateY(-1px);
}

/* Error message */
p[style*="color:red"] {
    color: #ff4d4d;
    margin-top: 15px;
}
body {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h2 {
            margin-bottom: 5px;
        }

        p {
            margin-top: 0;
        }

</style>

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
