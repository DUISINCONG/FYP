<?php
// CONNECT TO DATABASE
include 'db_connect.php';

$success = "";
$error = "";

// WHEN FORM IS SUBMITTED
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $newPass = trim($_POST["new_password"]);
    $confirmPass = trim($_POST["confirm_password"]);

    // BASIC VALIDATION
    if (empty($newPass) || empty($confirmPass)) {
        $error = "Fill up this section first";
    } elseif (strlen($newPass) < 6) {
        $error = "Password must be at least 6 characters";
    } elseif ($newPass !== $confirmPass) {
        $error = "Passwords do not match";
    } else {
        // HASH PASSWORD
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);

        // UPDATE DATABASE (CHANGE `customers` to your table)
        $sql = "UPDATE customers SET password='$hashed' WHERE id=1"; 
        // NOTE: Replace id=1 with SESSION user ID later

        if (mysqli_query($conn, $sql)) {
            $success = "Password reset successful!!";
        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset Password | JC Restaurant</title>

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

    /* BACKGROUND PARTICLES */
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
      inset: 0;
      background: rgba(20, 10, 0, 0.5);
      z-index: 1;
    }

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

    /* SUCCESS BOX */
    .success-box {
      background: #d5f4d3;
      border-left: 6px solid #1b8a00;
      padding: 12px;
      border-radius: 6px;
      color: #145c00;
      display: <?php echo $success ? 'block' : 'none'; ?>;
      margin-bottom: 15px;
      font-weight: bold;
    }

    /* ERROR BOX */
    .error-box {
      background: #ffd3d3;
      border-left: 6px solid #d10000;
      padding: 10px;
      border-radius: 6px;
      color: #8a0000;
      display: <?php echo $error ? 'block' : 'none'; ?>;
      margin-bottom: 10px;
      font-size: 14px;
    }
  </style>
</head>
<body>

<div id="particles-js"></div>
<div class="overlay"></div>

<div class="form-card">
  <h2>Reset Password</h2>
  <p class="subtitle">Enter a new password</p>

  <!-- SUCCESS MESSAGE -->
  <div class="success-box">
      <?php echo $success; ?>
  </div>

  <!-- ERROR MESSAGE -->
  <div class="error-box">
      <?php echo $error; ?>
  </div>

  <form method="POST">

    <label>New Password</label>
    <input type="password" name="new_password" placeholder="Enter new password">

    <label>Confirm Password</label>
    <input type="password" name="confirm_password" placeholder="Confirm password">

    <button type="submit">Reset Password</button>
  </form>
</div>

<!-- PARTICLES JS -->
<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>

<script>
particlesJS("particles-js", {
  particles: {
    number: { value: 90 },
    size: { value: 3 },
    move: { speed: 2 },
    opacity: { value: 0.3 },
    color: { value: "#ffffff" },
    line_linked: {
      enable: true,
      color: "#ffffff",
      opacity: 0.2
    }
  }
});
</script>

</body>
</html>
