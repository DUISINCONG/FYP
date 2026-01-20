<?php
require __DIR__ . "/backend/db_connect.php";
session_start();
if (!$conn) {
    die("DB connection failed");
}

$error = 0;

if (isset($_POST['loginbutton'])) {

    $cemail = mysqli_real_escape_string($conn, $_POST['cemail']);
    $cpassword = $_POST['cpassword'];

    $query = "SELECT * FROM customers WHERE customer_email = '$cemail' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {

        $customer = mysqli_fetch_assoc($result);
        $hashFromDB = $customer['password_hash'];

        if (password_verify($cpassword, $hashFromDB)) {

            $_SESSION['id'] = $customer['customer_id'];
            header("Location: homepage.html");
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

    <style>
        footer {
            margin-top: 80px;
            background-color: var(--secondary);
            color: white;
            padding: 60px 0 20px 0;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .footer-column h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
            font-weight: bold;
            font-family: "Times New Roman", Times, serif;
        }
        
        .footer-column h3::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 2px;
            background-color: orange;
            bottom: 0;
            left: 0;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .footer-links i {
            margin-right: 8px;
            width: 16px;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s;
            font-size: 0.9rem;
            font-family: "Times New Roman", Times, serif;
        }
        
        .footer-links a:hover {
            color: orange;
            padding-left: 5px;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            font-family: "Times New Roman", Times, serif;
        }
        
        .social-icons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            transition: all 0.3s;
        }
        
        .social-icons a:hover {
            background-color: orange;
            color: var(--secondary);
            transform: translateY(-3px);
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        :root {
            --secondary: #1a1a1a;
            --light: #f8f9fa;
            --dark: #343a40;
            --text: #555;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Times New Roman", Times, serif;
            background-color: white;
            color: var(--text);
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header Styles - 与旧的完全一致 */
        header {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 0;
            margin-left: 30px;
            margin-right: 50px;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            color: orange;
            font-family: "Times New Roman", Times, serif;
        }
                
        .logo i {
            margin-right: 10px;
            font-size: 32px;
            color: orange;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            font-family: "Times New Roman", Times, serif;
        }
                
        .nav-links li {
            margin-left: 30px;
            position: relative;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--secondary);
            font-weight: bold;
            font-size: 16px;
            transition: color 0.3s;
            padding: 5px 0;
            font-family: "Times New Roman", Times, serif;
        }
                
        .nav-links a:hover {
            color: orange;
        }
                
        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: orange;
            transition: width 0.3s;
        }
                
        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }
        
        /* VIEW MENU 按钮样式 - 旧的样式 */
        .btn-outline {
            display: inline-block;
            background-color: transparent;
            color: orange;
            border: 2px solid orange;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            font-family: "Times New Roman", Times, serif;
            cursor: pointer;
        }
        
        .btn-outline:hover {
            background-color: orange;
            color: white;
            transform: translateY(-2px);
        }
        
        /* Cart button in navbar - 应用旧样式 */
        .cart-btn {
            display: inline-flex;
            align-items: center;
            background-color: transparent;
            color: var(--secondary);
            border: 2px solid orange;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            font-family: "Times New Roman", Times, serif;
        }
        
        .nav-links .cart-btn:hover {
            background-color: orange;
            color: white;
            transform: translateY(-2px);
        }

        .nav-links .cart-btn::after {
            display: none;
        }
        
        .cart-btn i {
            margin-right: 8px;
        }

        .footer-column p {
            text-align: justify;
            font-weight: bold;
            color: white;
        }

        .nav-links li a {
            font-weight: bold;
        }

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

        .logout-btn{
            display:inline-flex;
            align-items:center;
            background-color:transparent;
            color:var(--primary);
            padding:10px 18px;
            border-radius:30px;
            text-decoration:none;
            font-weight:600;
            border:2px solid var(--border);
            transition:all 0.3s ease;
            letter-spacing:1px;
            }

        .logout-btn i{ margin-right:8px; }

        .logout-btn:hover{
            border-color:var(--primary);
            transform:translateY(-3px);
            box-shadow:0 10px 20px rgba(0,0,0,0.08);
        }
    </style>

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  
  <title>Login | JC Restaurant</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="clogincss.css"/>
</head>
<body>

    <header>
        <div class="container">
            <nav class="navbar">
                <a href="homepage.html" class="logo">
                    <i class="fas fa-utensils"></i>
                    JC Restaurant
                </a>
                <ul class="nav-links">
                    <li><a href="homepage.html">HOME</a></li>
                    <li><a href="aboutus.php">ABOUT</a></li>
                    <li><a href="contactus.pbp">CONTACT</a></li>
                    <li><a href="menuPage.php">MENU</a></li>
                    <li>
                        <a href="/jc_restaurant/customer_profile/edit-profile.php">
                            EDIT PROFILE
                        </a>
                    </li>
                    <li><a href="myorder.php">ORDER</a></li>
                    <li>
                        <a href="AddToCart.php" class="cart-btn">
                            <i class="fa-solid fa-cart-shopping"></i>My Cart
                        </a>
                    </li>
                    <li>
                        <a href="/jc_restaurant/logout.php" class="logout-btn">
                        <i class="fa-solid fa-right-from-bracket"></i>Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
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

            <p class="pp">--------------- or ---------------</p>

            <a class="fo" href="/jc_restaurant/reset-password.php">Forgot your password ?</a><br>

            <div class="E">
            <button type="button" onclick="window.location.href='/jc_restaurant/customer_registration/registration.php'">Register</button>
            </div>

        </div>

    </div>

        <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>JC Restaurant</h3>
                    <p>Experience fine dining at its best with our exquisite menu and unparalleled service.</p>
                    <div class="social-icons">
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="homepage.html">Home</a></li>
                        <li><a href="aboutus.php">About Us</a></li>
                        <li><a href="contactus.php">Contact</a></li>
                        <li><a href="menuPage.php">Menu</a></li>
                        <li><a href="/jc_restaurant/customer_profile/edit-profile.php">Edit Profile</a></li>
                        <li><a href="myorder.php">Order</a></li>
                        <li><a href="AddToCart.php">My Cart</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact Info</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt"></i>123 Restaurant St, Food City</a></li>
                        <li><i class="fas fa-phone"></i>+1 234 567 8900</a></li>
                        <li><i class="fas fa-envelope"></i>info@jcrestaurant.com</a></li>
                        <li><i class="fas fa-clock"></i>Mon-Sun: 11AM - 11PM</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 JC Restaurant. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html> 
