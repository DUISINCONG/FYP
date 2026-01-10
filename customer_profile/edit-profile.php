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
    "SELECT customer_name, customer_email, customer_phone, customer_address, customer_photo
     FROM customers WHERE customer_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($db_name, $db_email, $db_phone, $db_address, $db_photo);
$stmt->fetch();
$stmt->close();

/* ================= UPDATE PROFILE ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === "" || $email === "" || $phone === "") {
        $error = "Please fill in all required fields.";
    } else {

        /* ---------- UPDATE TEXT INFO ---------- */
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

        /* ---------- SAVE CROPPED IMAGE ---------- */
        if (!empty($_POST['cropped_image'])) {

            $data = explode(',', $_POST['cropped_image']);
            if (count($data) === 2) {

                $imageData = base64_decode($data[1]);

                $folder = "../uploads/profile/";
                if (!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }

                $fileName = time() . "_profile.png";
                file_put_contents($folder . $fileName, $imageData);

                $img = $conn->prepare(
                    "UPDATE customers SET customer_photo=? WHERE customer_id=?"
                );
                $img->bind_param("si", $fileName, $user_id);
                $img->execute();
                $img->close();

                $db_photo = $fileName;
                $success = "Profile updated & photo uploaded successfully!!";
            }

        } else {
            $success = "Profile updated successfully!";
        }
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
    background: linear-gradient(120deg, #0f2027, #203a43, #2c5364);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.profile-card {
    width: 420px;
    background: rgba(0,0,0,0.75);
    padding: 30px;
    border-radius: 15px;
    color: #fff;
}

.profile-card h2 {
    text-align: center;
    color: #ff9800;
}

label {
    margin-top: 10px;
    display: block;
}

input, textarea {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    background: #222;
    color: #fff;
    border-radius: 8px;
    border: none;
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
}

button:hover {
    background: #ffa726;
}

.error {
    background: #ff4d4d;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.success {
    background: #4caf50;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.profile-img {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ff9800;
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

    <div style="display: flex; gap: 10px; margin-top: 15px;">
    <button type="submit" style="flex: 1;">Update Profile</button>
    <button type="button" style="flex: 1; background: #555;" onclick="history.back();">Back</button>
</div>
</form>
</div>

<script>
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
        cropper = new Cropper(preview, {
            aspectRatio: 1,
            viewMode: 1,
            zoomable: true,
            movable: true
        });

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
</script>

</body>
</html>
