<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset ="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <title>Email Verification</title>

  <style>
    body{
        background-color: #f0f2f5;
        background-image: url('image/cover.png');
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
        height: 100vh;
        margin: 0;
    }
    
    .form-container{
        max-width: 450px;
        margin: 60px auto;
        padding: 25px;
        background-color: white;
        border-radius: 15px;
        border: 1px solid #ddd;
        box-shadow: 0 6px 20px rgb(0, 0, 0 / 0.1);
        transition: box-shadow 0.3s ease-in-out;
        margin-top: 150px;
        margin-right: 250px;
    }

.form-container:hover{
    box-shadow: 0 12px 40px rgb(0, 0, 0 / 0.2);
}

 /*h2 border*/
.form-container h2{
    text-align: center;
    margin-bottom: 25px;
    color: #343a40;
    border: 2px solid gray;
    border-radius:10px;
    padding: 5px 10px;
    display: inline-block;
    background-color:#f8f9fa;
    margin-left: 20px;
}

.form-control{
    padding-left:45px;
}

.input-group-text{
    width:40px;
    justify-content: center;
    background-color: #f8f9fa;
    border-right: none;
}

.input-group .form-control{
    
    border-left: none;
}
  </style>

</head>
<body>
  <div class="form-container">
      <h2>Customer Registration</h2>
      <p class="subtitle">Be Part of the JC Restaurant Family!</p>

      <form method="POST" action="send.php">
      <div class="mb-3 input-group">
        <span class="input-group-text"><i class="fas fa-user"></i></span>
        <input type="text" name="name" id="name" class="form-control" placeholder="Enter Your Name" autocomplete="off">
        <span class="error-message"></span>
      </div>

      <div class="mb-3 input-group">
        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
        <input type="text" name="email" id="email" class="form-control" placeholder="Enter Email Address" autocomplete="off">
      </div>

      <div class="mb-3 input-group">
        <span class="input-group-text"><i class="fas fa-phone"></i></span>
        <input type="text" name="phone" id="phone" class="form-control" placeholder="Enter Phone Number" autocomplete="off">
      </div>

      <div class="mb-3 input-group">
        <span class="input-group-text"><i class="fas fa-lock"></i></span>
        <input type="text" name="password" id="password" class="form-control" placeholder="Enter Password" autocomplete="off">
      </div>

      <div class="mb-3 input-group">
        <input type="hidden" name="otp" id="otp" class="form-control">
        <input type="hidden" name="subject" id="subject" class="form-control" value="Received OTP">
      </div>
      <button type="submit" name="send" class="btn btn-primary w-100">Signup <i class="fas fa-aroow-right"></i></button>

      </form>
  </div>
</body>
</html>

<script>
  function generateRandomNumber(){

    let min = 1000;
    let max = 3000;
    let randomNumber = Math.floor(Math.random() * (max - min +1)) +min;

    let lastGeneratedNumber = localStorage.getItem ('lastGeneratedNumber');
    while (randomNumber === parseInt(lastGeneratedNumber)){

      randomNumber = Math.floor(Math.random() * (max - min + 1)) + min;
    }
localStorage.setItem('lastGeneratedNumber' ,randomNumber);
return randomNumber;
  }

  document.getElementById('otp') .value = generateRandomNumber();
</script>
