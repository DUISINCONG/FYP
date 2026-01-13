<?php
    require __DIR__ . "/backend/db_connect.php";
    session_start();
    if (!$conn) {
        die("DB connection failed");
    } 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .A {
            margin-left: 20%;
            margin-right: 20%;
            float: left;
        }

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
        
        .cart-btn:hover {
            background-color: orange;
            color: white;
            transform: translateY(-2px);
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

        .menu-anchor {
            padding-top: 170px;
        }

        .p1 p {
            font-weight: bold;
            color: black;
        }

    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC Restaurant | Fine Dining Experience</title>
    <link rel="stylesheet" href="menu.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php
    $result = mysqli_query($conn, "SELECT * from beverages");
	$count = mysqli_num_rows($result);
    ?>
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
                    <li><a href="homepage.html" class="active">HOME</a></li>
                    <li><a href="#">ABOUT</a></li>
                    <li><a href="menuPage.php">MENU</a></li>
                    <li><a href="#">CONTACT</a></li>
                    <li>
                        <a href="/jc_restaurant/customer_profile/edit-profile.php">
                            EDIT PROFILE
                        </a>
                    </li>
                    <li>
                        <a href="AddToCart.php" class="cart-btn">
                            <i class="fa-solid fa-cart-shopping"></i>My Cart
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="container">
            <nav class="navbar1">   
                <ul class="nav-links">
                    <?php

                    $gettable = mysqli_query($conn, "SELECT * FROM categories");

                    while ($get = mysqli_fetch_assoc($gettable)) {
                        $title = $get['category_name'];
                        $anchor = $get['anchor'];

                        ?>
                        <div class="padding">
                      
                        <li><a href="#<?php echo $anchor ?>"><?php echo $title ?></a></li> 
                        <li></li> 
                        </div>
                    <?php
                    }
                    ?>
                </ul><br>
            </nav>
        </div>
    </header>

    <?php

    $gettable = mysqli_query($conn, "SELECT * FROM categories");

    while ($get = mysqli_fetch_assoc($gettable)) {
        $table = $get['table_name'];
        $title = $get['category_name'];
        $anchor = $get['anchor'];

        ?>
                    
            <?php

            $tableResult = mysqli_query( $conn, "SELECT * FROM $table WHERE product_status = 'Available'");
            if($tableResult && mysqli_num_rows($tableResult) > 0){
                ?>

                <div class="A">
                
                <div>
                    <h2 class="menu-anchor" id="<?php echo $anchor; ?>"><?php echo $title; ?></h2><br>
                </div>
                <?php
                while($row = mysqli_fetch_assoc($tableResult)){

                    ?>

                    <div class="menutable">

                    <a href="productDetail.php?id=<?php echo $row['product_id']; ?>">
                        <img src="<?php echo "backend/" . $row['product_image']; ?>" alt="Food Image"><br>
                    </a>

                    <?php echo $row['product_name']; ?><br>
                    <div class="p1">
                    <p>RM<?php echo $row['product_price']; ?></p>
                    </div>

                    </div>

                    <?php
                }   
            }
            ?>

        </div>

        <?php

    }

    ?>

    <div style="clear: both;"></div>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>JC Restaurant</h3>
                    <p>Experience fine dining at its best with our exquisite menu and unparalleled service.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-tripadvisor"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#">Home</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Menu</a></li>
                        <li><a href="#">Edit Profile</a></li>
                        <li><a href="#">My Cart</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact Info</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt"></i><a href="#">123 Restaurant St, Food City</a></li>
                        <li><i class="fas fa-phone"></i><a href="#">+1 234 567 8900</a></li>
                        <li><i class="fas fa-envelope"></i><a href="#">info@jcrestaurant.com</a></li>
                        <li><i class="fas fa-clock"></i><a href="#">Mon-Sun: 11AM - 11PM</a></li>
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
