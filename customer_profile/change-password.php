<?php
session_start();
include("../backend/db_connect.php");

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['id'])) {
    header("Location: /jc_restaurant/customerLogin.php");
    exit;
}

$user_id = $_SESSION['id'];
$error = "";
$success = "";

/* ================= FETCH USER ================= */
$stmt = $conn->prepare("SELECT customer_name, password_hash FROM customers WHERE customer_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($db_name, $db_password_hash);
$stmt->fetch();
$stmt->close();

/* ================= CHANGE PASSWORD ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$current || !$new || !$confirm) {
        $error = "All password fields are required!";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match!";
    } elseif (!password_verify($current, $db_password_hash)) {
        $error = "Current password is incorrect!";
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE customers SET password_hash=? WHERE customer_id=?");
        $update->bind_param("si", $newHash, $user_id);
        $update->execute();
        $update->close();
        $success = "Password changed successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Change Password</title>

<style>
/* ===== GLOBAL FIX ===== */
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: url('../assets/bg-password.png') no-repeat center center / cover;
}

/* ===== CARD ===== */
.card {
    width: 380px;
    background: rgba(0,0,0,0.85);
    padding: 30px;
    border-radius: 20px;
    color: #fff;
    box-shadow: 0 10px 25px rgba(0,0,0,0.6);
    overflow: hidden;
}

/* ===== TITLE ===== */
h2 {
    text-align: center;
    margin-bottom: 25px;
}

h2 span {
    background: #ff9800;
    color: #000;
    padding: 8px 22px;
    border-radius: 8px;
    display: inline-block;
}

/* ===== PASSWORD FIELD ===== */
.password-wrapper {
    position: relative;
    width: 100%;
    margin-bottom: 15px;
}

.password-wrapper input {
    width: 100%;
    padding: 12px 65px 12px 15px;
    border-radius: 12px;
    border: none;
    background: #222;
    color: #fff;
    font-size: 14px;
    outline: none;
}

/* ===== SHOW BUTTON ===== */
.show-hide-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: #444;
    color: #fff;
    border: none;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 12px;
    cursor: pointer;
}

.show-hide-btn:hover {
    background: #666;
}

/* ===== SUBMIT ===== */
.submit-btn {
    width: 100%;
    padding: 12px;
    border-radius: 25px;
    border: none;
    background: #ff9800;
    font-weight: bold;
    cursor: pointer;
    margin-top: 10px;
}

.submit-btn:hover {
    background: #ffa726;
}

/* ===== ALERTS ===== */
.error, .success {
    padding: 12px;
    border-radius: 12px;
    margin-bottom: 15px;
    text-align: center;
}

.error {
    background: #ff4d4d;
}

.success {
    background: #4caf50;
}

/* ===== BACK LINK ===== */
.back-link {
    display: block;
    margin-top: 15px;
    text-align: center;
    color: #ff9800;
    text-decoration: none;
}
</style>
</head>

<body>

<div class="card">
    <h2><span>Change Password</span></h2>

    <?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <form method="POST">
        <div class="password-wrapper">
            <input type="password" name="current_password" placeholder="Current Password" required>
            <button type="button" class="show-hide-btn" onclick="togglePassword(this)">Show</button>
        </div>

        <div class="password-wrapper">
            <input type="password" name="new_password" placeholder="New Password" required>
            <button type="button" class="show-hide-btn" onclick="togglePassword(this)">Show</button>
        </div>

        <div class="password-wrapper">
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            <button type="button" class="show-hide-btn" onclick="togglePassword(this)">Show</button>
        </div>

        <button type="submit" class="submit-btn">Change Password</button>
    </form>

    <a href="edit-profile.php" class="back-link">← Back to Profile</a>
</div>

<script>
function togglePassword(btn) {
    const input = btn.previousElementSibling;
    if (input.type === "password") {
        input.type = "text";
        btn.textContent = "Hide";
    } else {
        input.type = "password";
        btn.textContent = "Show";
    }
}
</script>

</body>
</html>

