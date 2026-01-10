<?php
// ===============================
// ERROR REPORTING (for debugging)
// ===============================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===============================
// START SESSION & DB CONNECTION
// ===============================
session_start();
include("../backend/db_connect.php");

// ===============================
// CHECK LOGIN
// ===============================
if (!isset($_SESSION['customer_id'])) {
    header("Location: /jc_restaurant/customer_login/customerLogin.php");
    exit;
}

$user_id = $_SESSION['customer_id'];
$error   = "";
$success = "";

// ===============================
// FETCH CURRENT USER DATA
// ===============================
$stmt = $conn->prepare(
    "SELECT customer_name, customer_email, customer_phone, customer_address
     FROM customers
     WHERE customer_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($db_name, $db_email, $db_phone, $db_address);

if (!$stmt->fetch()) {
    $error = "User not found.";
}

$stmt->close();

// ===============================
// UPDATE PROFILE
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === "" || $email === "" || $phone === "") {
        $error = "Please fill in all required fields.";
    } else {

        $update = $conn->prepare(
            "UPDATE customers
             SET customer_name = ?, customer_email = ?, customer_phone = ?, customer_address = ?
             WHERE customer_id = ?"
        );

        $update->bind_param("ssssi", $name, $email, $phone, $address, $user_id);

        if ($update->execute()) {
            $success = "Profile updated successfully.";

            // Update displayed values
            $db_name    = $name;
            $db_email   = $email;
            $db_phone   = $phone;
            $db_address = $address;
        } else {
            $error = "Failed to update profile.";
        }

        $update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }
        .container {
            width: 400px;
            margin: 50px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 5px;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Profile</h2>

    <?php if ($error !== ""): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Name *</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($db_name); ?>" required>

        <label>Email *</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($db_email); ?>" required>

        <label>Phone *</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($db_phone); ?>" required>

        <label>Address</label>
        <textarea name="address"><?php echo htmlspecialchars($db_address); ?></textarea>

        <button type="submit">Update Profile</button>
    </form>
</div>

</body>
</html>
