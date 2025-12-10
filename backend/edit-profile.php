<?php
session_start();
include "db_connect.php";

// Get logged in user's ID
$user_id = $_SESSION['customer_id'] ?? 1;

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirmPassword']);

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!is_numeric($phone) || strlen($phone) < 10) {
        $error = "Phone must be at least 10 digits.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // CORRECT TABLE + COLUMN NAMES
        $sql = "UPDATE customers 
                SET customer_name='$name',
                    customer_email='$email',
                    customer_phone='$phone',
                    customer_password='$hashed_password'
                WHERE customer_id='$user_id'";

        if (mysqli_query($conn, $sql)) {
            echo "success";
            exit;
        } else {
            echo "Database error: " . mysqli_error($conn);
            exit;
        }
    }

    echo $error;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Profile | JC Restaurant</title>

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #3e2723, #6d4c41, #8d6e63);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    /* SAME MOVING BACKGROUND AS REGISTER.HTML */
    #particles-js {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      z-index: 0;
    }

    .overlay {
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(20, 10, 0, 0.5);
      z-index: 1;
    }

    /* FORM CARD */
    .form-card {
      z-index: 2;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 16px;
      padding: 25px 55px;
      width: 400px;
      text-align: center;
      box-shadow: 0 0 20px rgba(0,0,0,0.25);
      position: relative;
    }

    .form-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 4px;
      background: linear-gradient(90deg, #6d4c41, #3e2723, #6d4c41);
      background-size: 200% 100%;
      animation: shimmer 3s linear infinite;
    }

    @keyframes shimmer {
      0% { background-position: -200% 0; }
      100% { background-position: 200% 0; }
    }

    h2 { color: #3e2723; }
    .subtitle { color: #6d4c41; margin-bottom: 20px; }

    label {
      display: block;
      text-align: left;
      color: #4e342e;
      font-weight: 600;
      margin-top: 10px;
    }

    input {
      width: 100%;
      padding: 12px;
      border-radius: 8px;
      border: 1px solid #a1887f;
      margin-bottom: 8px;
      background: rgba(255,255,255,0.8);
    }

    button {
      width: 100%;
      padding: 14px;
      background: #6d4c41;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      margin-top: 15px;
      font-weight: bold;
    }
    button:hover { background: #3e2723; }

    .success-box {
      background: #d5f4d3;
      border-left: 6px solid #1b8a00;
      padding: 12px;
      border-radius: 6px;
      color: #145c00;
      display: none;
      margin-bottom: 15px;
      opacity: 0;
      transition: opacity 0.5s ease;
      font-weight: bold;
    }

    .error-box {
      background: #ffd3d3;
      border-left: 6px solid #d10000;
      padding: 10px;
      border-radius: 6px;
      color: #8a0000;
      display: none;
      margin-bottom: 10px;
      font-size: 14px;
    }

    /* BACK TO HOME BUTTON */
    .home-btn {
      margin-top: 15px;
      background: #3e2723;
      padding: 12px;
      color: white;
      font-size: 14px;
      border-radius: 8px;
      display: inline-block;
      width: 100%;
      cursor: pointer;
      text-decoration: none;
      font-weight: bold;
    }

    .home-btn:hover {
      background: #6d4c41;
    }
  </style>
</head>
<body>

<div id="particles-js"></div>
<div class="overlay"></div>

<div class="form-card">
  <h2>Edit Profile</h2>
  <p class="subtitle">Update your information</p>

  <div id="successMessage" class="success-box">
     Information Updated Successfully!!
  </div>

  <form id="editForm" method="POST">

<label>Full Name</label>
<input type="text" id="name" name="name">
<div id="nameError" class="error-box"></div>

<label>Email</label>
<input type="email" id="email" name="email">
<div id="emailError" class="error-box"></div>

<label>Phone Number</label>
<input type="text" id="phone" name="phone">
<div id="phoneError" class="error-box"></div>

<label>Password</label>
<input type="password" id="password" name="password">
<div id="passwordError" class="error-box"></div>

<label>Confirm Password</label>
<input type="password" id="confirmPassword" name="confirmPassword">
<div id="confirmError" class="error-box"></div>

<button type="submit">Update Profile</button>
</form>

  <a href="home.html" class="home-btn">⟵ Back to Home</a>

</div>

<!-- PARTICLES JS (LOADS ANIMATION LIBRARY) -->
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>

<script>
document.getElementById("editForm").addEventListener("submit", function (e) {
    e.preventDefault();

    let valid = true;

    nameError.style.display = name.value.trim() === "" ? "block" : "none";
    emailError.style.display = (!email.value.includes("@") || !email.value.includes(".")) ? "block" : "none";
    phoneError.style.display = (phone.value.length < 10 || isNaN(phone.value)) ? "block" : "none";
    passwordError.style.display = (password.value.length < 6) ? "block" : "none";
    confirmError.style.display = (password.value !== confirmPassword.value) ? "block" : "none";

    if (nameError.style.display === "block" || 
        emailError.style.display === "block" ||
        phoneError.style.display === "block" ||
        passwordError.style.display === "block" ||
        confirmError.style.display === "block") {
        return;
    }

    let formData = new FormData(this);

    fetch("edit-profile.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === "success") {
            let box = document.getElementById("successMessage");
            box.style.display = "block";
            box.style.opacity = "1";

            setTimeout(() => {
                box.style.opacity = "0";
                setTimeout(() => { box.style.display = "none"; }, 500);
            }, 4000);
        } else {
            alert("Error: " + data);
        }
    });
});
</script>

</body>
</html>
