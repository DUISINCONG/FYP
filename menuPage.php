<?php 
    include("db_connect.php"); 
    session_start();
    $ccid = $_SESSION['id'] ?? "@";
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
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <link rel="stylesheet" href="menu.css"/>
    <!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">-->
    <?php
    $result = mysqli_query($conn, "SELECT * from beverages");
	$count = mysqli_num_rows($result);
    ?>
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
        <div class="container">
            <nav class="navbar1">   
                <ul class="nav-links">
                    <?php

                    $gettable = mysqli_query($conn, "SELECT * FROM categories");

                    while ($get = mysqli_fetch_assoc($gettable)) {
                        $title = $get['category_name'];
                        $anchor = $get['anchor'];

                        ?>
                      
                        <li><a href="#<?php echo $anchor ?>"><?php echo $title ?></a></li>
                        <li></li> 
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

                    <?php
                    if($ccid == "@"){?>
                    
                    <a href="customerLogin.php">
                        <img src="<?php echo "backend/" . $row['product_image']; ?>" alt="Food Image"><br>
                    </a>

                    <?php
                    }else{?>

                    <a href="productDetail.php?id=<?php echo $row['product_id']; ?>">
                        <img src="<?php echo "backend/" . $row['product_image']; ?>" alt="Food Image"><br>
                    </a>

                    <?php
                    }
                    ?>

                    <?php echo $row['product_name']; ?><br>
                    <p class="p1">RM<?php echo $row['product_price']; ?></p>

                    </div>

                    <?php
                }   
            }
            ?>

        </div>

        <?php

    }

    ?>
        
    <!--<footer>
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
                        <li><a href="#">Services</a></li>
                        <li><a href="#">Menu</a></li>
                        <li><a href="#">Reservation</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact Info</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Restaurant St, Food City</li>
                        <li><i class="fas fa-phone"></i> +1 234 567 8900</li>
                        <li><i class="fas fa-envelope"></i> info@jcrestaurant.com</li>
                        <li><i class="fas fa-clock"></i> Mon-Sun: 11AM - 11PM</li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Newsletter</h3>
                    <p>Subscribe to our newsletter for updates and special offers.</p>
                    <form>
                        <input type="email" placeholder="Your Email" style="width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: none;">
                        <button type="submit" class="btn" style="width: 100%;">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 JC Restaurant. All Rights Reserved.</p>
            </div>
        </div>
    </footer>-->
    
</body>
</html>
