<?php 
    include("db_connect.php"); 
    session_start();
    $ccid = $_SESSION['id'] ?? "@";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="historyc.css"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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

    <div class="B">	

    <div>
        <h2>Order History</h2>
    </div>

    <?php

        $customerid = $_SESSION['id'];

        $result = mysqli_query($conn, "select * from orders where customer_id = '$customerid' and order_status = 'unactive'");
        while($row = mysqli_fetch_assoc($result)){

        ?>
        
            <div class="cart-item">

                <div class="cart-img">
                    <img src="<?php echo $row['product_image']; ?>">
                </div>

                <div class="cart-name">
                    <?php echo $row['product_name']; ?>
                </div>

                <div class="cart-qty">
                    x <?php echo $row['quantity']; ?>
                </div>

                <div class="cart-price">
                    RM <?php echo $row['total_amount']; ?>
                </div>

                <div class="cart-price">
                    <?php echo $row['paymenttime']; ?>
                </div>

            </div>
        

    <?php
    }
    ?>

    </div>
    
</body>
</html>
