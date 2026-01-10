<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Registration | JC Restaurant</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap & FontAwesome -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<style>
body{
    height:100vh;
    margin:0;
    background:
        linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.65)),
        url("image/cover.png") no-repeat center center / cover;
    display:flex;
    align-items:center;
    justify-content:flex-end;
    padding-right:150px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Registration Card */
.register-box{
    width:420px;
    background:rgba(0,0,0,0.75);
    border-radius:15px;
    padding:35px;
    box-shadow:0 10px 30px rgba(0,0,0,0.6);
    color:#fff;
}

/* Title */
.register-box h2{
    text-align:center;
    color:#ff8c00;
    margin-bottom:10px;
    font-weight:700;
}

.subtitle{
    text-align:center;
    color:#ccc;
    margin-bottom:25px;
}

/* Input Group */
.input-group-text{
    background:#222;
    border:none;
    color:#ff8c00;
}

.form-control{
    background:#111;
    border:none;
    color:#fff;
}

.form-control::placeholder{
    color:#aaa;
}

.form-control:focus{
    box-shadow:none;
    background:#111;
    color:#fff;
}

/* Button */
.btn-orange{
    background:#ff8c00;
    border:none;
    color:#fff;
    font-weight:600;
    padding:12px;
    border-radius:30px;
    transition:0.3s;
}

.btn-orange:hover{
    background:#e67600;
    transform:translateY(-2px);
}

/* Login Link */
.login-link{
    text-align:center;
    margin-top:15px;
    color:#ccc;
}

.login-link a{
    color:#ff8c00;
    text-decoration:none;
}

.login-link a:hover{
    text-decoration:underline;
}

@media(max-width:768px){
    body{
        justify-content:center;
        padding-right:0;
    }
}
</style>
</head>

<body>

<div class="register-box">
    <h2>Customer Registration</h2>
    <p class="subtitle">Be Part of the JC Restaurant Family!</p>

    <form method="POST" action="send.php">

        <div class="mb-3 input-group">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" name="name" class="form-control" placeholder="Full Name" required>
        </div>

        <div class="mb-3 input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="Email Address" required>
        </div>

        <div class="mb-3 input-group">
            <span class="input-group-text"><i class="fas fa-phone"></i></span>
            <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
        </div>

        <div class="mb-3 input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>

        <!-- Hidden OTP -->
        <input type="hidden" name="otp" id="otp">
        <input type="hidden" name="subject" value="Received OTP">

        <button type="submit" name="send" class="btn btn-orange w-100">
            Signup <i class="fas fa-arrow-right"></i>
        </button>
    </form>

    <div class="login-link">
        Already have an account?
        <a href="/jc_restaurant/customerLogin.php">Login here</a>
    </div>
</div>

<script>
function generateOTP(){
    return Math.floor(100000 + Math.random() * 900000);
}
document.getElementById("otp").value = generateOTP();
</script>

</body>
</html>
