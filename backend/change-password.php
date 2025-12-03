<?php
session_start();
include ("db_connect.php");

// Assuming the user is logged in
$user_id = $_SESSION['user_id'] ?? 1; // Replace with actual session variable

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current = mysqli_real_escape_string($conn, $_POST['currentPassword']);
    $new = mysqli_real_escape_string($conn, $_POST['newPassword']);
    $confirm = mysqli_real_escape_string($conn, $_POST['confirmPassword']);

    // Fetch current password from DB
    $sql = "SELECT password FROM users WHERE id='$user_id'";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $hashed_password = $row['password'];

        if (!password_verify($current, $hashed_password)) {
            $error = "Current password is incorrect.";
        } elseif (strlen($new) < 6) {
            $error = "New password must be at least 6 characters.";
        } elseif ($new !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            // Update password
            $new_hashed = password_hash($new, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password='$new_hashed' WHERE id='$user_id'";
            if (mysqli_query($conn, $update_sql)) {
                $success = "Password Updated Successfully!!";
            } else {
                $error = "Database error: " . mysqli_error($conn);
            }
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Change Password | JC Restaurant</title>
<style>
/* Keep your existing CSS here */
* { margin:0; padding:0; box-sizing:border-box; }
body 
{ font-family:'Poppins',sans-serif; 
  background:linear-gradient(135deg,#3e2723,#6d4c41,#8d6e63); 
  height:100vh; display:flex; 
  align-items:center; 
  justify-content:center; 
  overflow:hidden; 
}
#particles-js 
{ position:absolute; 
  width:100%;
  height:100%; 
  top:0; 
  left:0; 
  z-index:0; 
}
.overlay { position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(20,10,0,0.5); z-index:1; }
.form-card { z-index:2; background: rgba(255,255,255,0.95); border-radius:16px; padding:25px 55px; width:400px; text-align:center; box-shadow:0 0 20px rgba(0,0,0,0.25); position:relative; }
.form-card::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; background: linear-gradient(90deg,#6d4c41,#3e2723,#6d4c41); background-size:200% 100%; animation:shimmer 3s linear infinite; }
@keyframes shimmer {0% {background-position:-200% 0;} 100% {background-position:200% 0;}}
h2 {color:#3e2723;}
.subtitle {color:#6d4c41;margin-bottom:20px;}
label {display:block; text-align:left; color:#4e342e; font-weight:600; margin-top:10px;}
input {width:100%; padding:12px; border-radius:8px; border:1px solid #a1887f; margin-bottom:8px; background:rgba(255,255,255,0.8);}
button {width:100%; padding:14px; background:#6d4c41; color:white; border:none; border-radius:8px; cursor:pointer; margin-top:15px; font-weight:bold;}
button:hover { background:#3e2723; }
.success-box { background:#d5f4d3; border-left:6px solid #1b8a00; padding:12px; border-radius:6px; color:#145c00; display:none; margin-bottom:15px; opacity:0; transition:opacity 0.5s ease; font-weight:bold;}
.error-box { background:#ffd3d3; border-left:6px solid #d10000; padding:10px; border-radius:6px; color:#8a0000; display:none; margin-bottom:10px; font-size:14px;}
.home-btn { margin-top:15px; background:#3e2723; padding:12px; color:white; font-size:14px; border-radius:8px; display:inline-block; width:100%; cursor:pointer; text-decoration:none; font-weight:bold;}
.home-btn:hover { background:#6d4c41; }
</style>
</head>
<body>

<div id="particles-js"></div>
<div class="overlay"></div>

<div class="form-card">

  <h2>Change Password</h2>
  <p class="subtitle">Update your password</p>

  <?php if($success): ?>
    <div id="successMessage" class="success-box" style="display:block;opacity:1;"><?= $success ?></div>
  <?php endif; ?>
  <?php if($error): ?>
    <div id="errorMessage" class="error-box" style="display:block;"><?= $error ?></div>
  <?php endif; ?>

  <form id="passwordForm" method="POST">

    <label>Current Password</label>
    <input type="password" id="currentPassword" name="currentPassword">
    <div id="currentError" class="error-box">Current password cannot be empty.</div>

    <label>New Password</label>
    <input type="password" id="newPassword" name="newPassword">
    <div id="newError" class="error-box">New password must be at least 6 characters.</div>

    <label>Confirm New Password</label>
    <input type="password" id="confirmPassword" name="confirmPassword">
    <div id="confirmError" class="error-box">Passwords do not match.</div>

    <button type="submit">Update Password</button>
  </form>

  <a href="home.html" class="home-btn">⟵ Back to Home</a>
</div>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
particlesJS("particles-js", {
  particles: { number:{value:90}, size:{value:3}, move:{speed:2}, opacity:{value:0.3}, color:{value:"#ffffff"}, line_linked:{enable:true, color:"#ffffff", opacity:0.2} }
});

// JS validation
document.getElementById("passwordForm").addEventListener("submit", function(e){
  e.preventDefault();
  let valid = true;

  let current = document.getElementById("currentPassword");
  let newP = document.getElementById("newPassword");
  let confirm = document.getElementById("confirmPassword");

  if(current.value.trim()===""){ currentError.style.display="block"; valid=false; } else currentError.style.display="none";
  if(newP.value.length<6){ newError.style.display="block"; valid=false; } else newError.style.display="none";
  if(newP.value !== confirm.value){ confirmError.style.display="block"; valid=false; } else confirmError.style.display="none";

  if(valid){
    this.submit(); // Submit form to PHP if JS validation passes
  }
});
</script>

</body>
</html>

