<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("../backend/db_connect.php");

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['customer_id'])) {
    header("Location: /jc_restaurant/customerLogin.php");
    exit;
}

$user_id = $_SESSION['customer_id'];
$error = "";
$success = "";

/* ================= FETCH USER ================= */
$stmt = $conn->prepare(
    "SELECT customer_name, customer_email, customer_phone, customer_address, customer_photo, password_hash
     FROM customers WHERE customer_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($db_name, $db_email, $db_phone, $db_address, $db_photo, $db_password);
$stmt->fetch();
$stmt->close();

/* ================= UPDATE PROFILE ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === "" || $email === "" || $phone === "") {
        $error = "Please fill in all required fields.";
    } else {
        $update = $conn->prepare(
            "UPDATE customers
             SET customer_name=?, customer_email=?, customer_phone=?, customer_address=?
             WHERE customer_id=?"
        );
        $update->bind_param("ssssi", $name, $email, $phone, $address, $user_id);
        $update->execute();
        $update->close();

        $db_name = $name;
        $db_email = $email;
        $db_phone = $phone;
        $db_address = $address;

        if (!empty($_POST['cropped_image'])) {
            $data = explode(',', $_POST['cropped_image']);
            if (count($data) === 2) {
                $imageData = base64_decode($data[1]);
                $folder = "../uploads/profile/";
                if (!is_dir($folder)) mkdir($folder, 0777, true);
                $fileName = time() . "_profile.png";
                file_put_contents($folder . $fileName, $imageData);

                $img = $conn->prepare(
                    "UPDATE customers SET customer_photo=? WHERE customer_id=?"
                );
                $img->bind_param("si", $fileName, $user_id);
                $img->execute();
                $img->close();

                $db_photo = $fileName;
                $success = "Profile updated & photo uploaded successfully!";
            }
        } else {
            $success = "Profile updated successfully!";
        }
    }
}

/* ================= CHANGE PASSWORD (MODAL) ================= */
$modalError = "";
$modalSuccess = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$current || !$new || !$confirm) {
        $modalError = "All password fields are required!";
    } elseif ($new !== $confirm) {
        $modalError = "New passwords do not match!";
    } elseif (!password_verify($current, $db_password_hash)) {
        $modalError = "Current password is incorrect!";
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE customers SET customer_password=? WHERE customer_id=?");
        $update->bind_param("si", $newHash, $user_id);
        $update->execute();
        $update->close();
        $modalSuccess = "Password changed successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile</title>

<!-- Cropper.js -->
<link rel="stylesheet" href="https://unpkg.com/cropperjs/dist/cropper.min.css">
<script src="https://unpkg.com/cropperjs/dist/cropper.min.js"></script>

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: url('../assets/bg-profile.png') no-repeat center center;
    background-size: cover;
    position: relative;
}

/* Remove overlay */
body::before {
    display: none;
}

.profile-card {
    width: 420px;
    background: rgba(0,0,0,0.85);
    padding: 30px;
    border-radius: 20px;
    color: #fff;
    box-shadow: 0 10px 25px rgba(0,0,0,0.6);
}

.profile-card h2 {
    text-align: center;
    color: #ff9800;
    margin-bottom: 20px;
}

label {
    margin-top: 10px;
    display: block;
    font-weight: bold;
}

input, textarea {
    width: 100%;
    padding: 12px;
    margin-top: 5px;
    background: #222;
    color: #fff;
    border-radius: 12px;
    border: none;
    font-size: 14px;
}

textarea {
    resize: none;
    height: 80px;
}

button {
    width: 100%;
    margin-top: 15px;
    padding: 12px;
    border-radius: 25px;
    border: none;
    background: #ff9800;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background: #ffa726;
}

.error {
    background: #ff4d4d;
    padding: 12px;
    border-radius: 12px;
    margin-bottom: 12px;
    text-align: center;
}

.success {
    background: #4caf50;
    padding: 12px;
    border-radius: 12px;
    margin-bottom: 12px;
    text-align: center;
}

.profile-img {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ff9800;
}

.flex-buttons {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.75);
    justify-content: center;
    align-items: center;
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.modal-content {
    background: rgba(0,0,0,0.95);
    padding: 30px;
    border-radius: 20px;
    width: 400px;
    color: #fff;
    position: relative;
    box-shadow: 0 10px 25px rgba(0,0,0,0.7);
    transform: scale(0.8);
    animation: scaleUp 0.3s forwards;
}

@keyframes scaleUp { to { transform: scale(1); } }

.close {
    position: absolute;
    right: 20px; top: 15px;
    font-size: 26px;
    cursor: pointer;
    color: #ff9800;
}

.modal input {
    border-radius: 12px;
    background: #222;
    padding: 12px;
    margin-bottom: 12px;
    font-size: 14px;
}

.modal button {
    border-radius: 25px;
}

/* Inline messages inside modal */
.modal .error, .modal .success {
    margin-bottom: 15px;
}
</style>
</head>

<body>

<div class="profile-card">
<h2>Edit Profile</h2>

<?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<form method="POST">
    <label>Profile Picture</label>
    <div style="text-align:center;margin-bottom:10px;">
        <img id="preview"
             src="<?= !empty($db_photo) ? '../uploads/profile/'.htmlspecialchars($db_photo) : '../assets/default-user.png' ?>"
             class="profile-img">
    </div>

    <input type="file" id="photoInput" accept="image/*">
    <button type="button" id="confirmCrop" style="display:none;">Confirm Photo</button>
    <input type="hidden" name="cropped_image" id="cropped_image">

    <label>Name *</label>
    <input type="text" name="name" value="<?= htmlspecialchars($db_name ?? '') ?>" required>

    <label>Email *</label>
    <input type="email" name="email" value="<?= htmlspecialchars($db_email ?? '') ?>" required>

    <label>Phone *</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($db_phone ?? '') ?>" required>

    <label>Address</label>
    <textarea name="address"><?= htmlspecialchars($db_address ?? '') ?></textarea>

    <div class="flex-buttons">
    <button type="submit" name="update_profile">Update Profile</button>
    <button type="button" style="background: #555;" onclick="history.back();">Back</button>
    <a href="change-password.php" style="flex:1; text-align:center; text-decoration:none;">
        <button type="button" style="background:#ff9800; color:#fff; font-weight:bold;">Change Password</button>
    </a>
</div>

</form>
</div>

<!-- Modal -->
<div class="modal" id="passwordModal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2 style="text-align:center; margin-bottom:20px;">Change Password</h2>

        <?php if($modalError): ?><div class="error"><?= htmlspecialchars($modalError) ?></div><?php endif; ?>
        <?php if($modalSuccess): ?><div class="success"><?= htmlspecialchars($modalSuccess) ?></div><?php endif; ?>

        <form method="POST">
            <input type="password" name="current_password" placeholder="Current Password" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            <button type="submit" name="change_password">Change Password</button>
        </form>
    </div>
</div>

<script>
// Cropper.js
let cropper;
const input = document.getElementById('photoInput');
const preview = document.getElementById('preview');
const confirmBtn = document.getElementById('confirmCrop');
const croppedInput = document.getElementById('cropped_image');

input.addEventListener('change', e => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
        preview.src = reader.result;
        if (cropper) cropper.destroy();
        cropper = new Cropper(preview, { aspectRatio: 1, viewMode: 1, zoomable: true, movable: true });
        confirmBtn.style.display = 'block';
        confirmBtn.innerText = 'Confirm Photo';
    };
    reader.readAsDataURL(file);
});

confirmBtn.addEventListener('click', () => {
    const canvas = cropper.getCroppedCanvas({ width: 300, height: 300 });
    croppedInput.value = canvas.toDataURL('image/png');
    confirmBtn.innerText = 'Photo Ready ✔';
});

// Modal JS
const modal = document.getElementById('passwordModal');
const openBtn = document.getElementById('openModal');
const closeBtn = document.getElementById('closeModal');

openBtn.onclick = () => modal.style.display = 'flex';
closeBtn.onclick = () => modal.style.display = 'none';
window.onclick = (e) => { if (e.target == modal) modal.style.display = 'none'; };

// Auto-open modal if message exists
<?php if($modalError || $modalSuccess): ?>
modal.style.display = 'flex';
<?php endif; ?>
</script>

</body>
</html>
