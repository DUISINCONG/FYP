<?php
include("db_connect.php");

session_start();
$ccid = $_SESSION['id'] ?? "@";
$error = 0;

if(isset($_POST['loginbutton'])) {
    $cemail = mysqli_real_escape_string($conn, $_POST['cemail']);
    $cpassword = $_POST['cpassword'];

    $query = "SELECT * FROM customers WHERE customer_email = '$cemail'";
    $result = mysqli_query($conn, $query);

    $query1 = "SELECT * FROM customers WHERE customer_password = '$cpassword'";   
    $result1 = mysqli_query($conn, $query1);

    if(mysqli_num_rows($result) == true) {
        
        if(mysqli_num_rows($result1) == true) {

            $customer = mysqli_fetch_assoc($result1);
            $_SESSION['id'] = $customer['customer_id'];

            header("Location: menuPage.php");
            exit();

        } else {
            $error = 2;
        }
    } else {
        $error = 1;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  
  <title>Login</title>
  <link rel="stylesheet" href="clogincss.css"/>
  <style>

    .fo {
        display: block;
        text-align: center;
        color: black;
        font-size: 16px;
        text-decoration: none;
        font-family: "Inter", sans-serif;
    }

    .fo:hover {
        text-decoration: underline;
    }

    .er {
        color: red;
        text-align: center;
        font-size: 18px;
    }

  </style>
</head>
<body>

    <header>
        <div class="container">
            <nav class="navbar">
                <a href="#" class="logo">
                    <i class="fas fa-utensils"></i>
                    JC Restaurant
                </a>
                <ul class="nav-links">
                    <li><a href="index.html">HOME</a></li>
                    <li><a href="#">ABOUT</a></li>
                    <li><a href="#">SERVICE</a></li>
                    <li><a href="menuPage.php">Menu</a></li>
                    <li><a href="#">PAGES</a></li>
                    <li><a href="#">CONTACT</a></li>
                    <?php
                    if($ccid == "@"){?>
                    <li><a href="customerLogin.php" class="btn">Add To Cart</a></li>
                    <?php
                    }else{
                    ?>
                    <li><a href="AddToCart.php" class="btn">Add To Cart</a></li>
                    <?php
                    }
                    ?>
                </ul><br>
            </nav>
        </div>
        <hr style="height:2px; background-color:orange; text-align: center; width: 1200px; border-color: orange;">
    </header>

    <div class="A">

        <div class="B1">

            <h2>Welcome to JC Restaurant</h2>

            <img src="JC_Restaurant_Logo5.png" class="center">

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">

                <div class="C">
                    <div class="D">
                        <div class="D1">
                        <label for="cemail">Email :</label><br>
                        </div>
                        <input type="text" id="cemail" name="cemail" required>
                    </div>

                    <div class="D">
                        <div class="D2">
                        <label for="cpassword">Password :</label><br>
                        </div>
                        <input type="password" id="cpassword" name="cpassword" required>
                    </div>
                    <br>

                    <div class="F">

                    <input type="submit" name="loginbutton" value="Login">

                    </div>

                </div>

            </form>

            <div class="er">

            <?php
            
                    if($error == 1 ){
                        echo "Sorry, the email is incorrect. Please check your email.";
                    }else if($error == 2){
                        echo "Sorry, the password is incorrect. Please check your password.";
                    }

            ?>

            </div>

            <p>--------------- or ---------------</p>

            <a class="fo" href="">Forgot your password ?</a><br>

            <div class="E">
            <button type="button">Register</button>
            </div>

        </div>

    </div>

</body>
</html> 
