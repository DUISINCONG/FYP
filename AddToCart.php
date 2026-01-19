<?php
    require __DIR__ . "/backend/db_connect.php";
    session_start();
    if (!$conn) {
        die("DB connection failed");
    }

    if (!isset($_SESSION['id'])) {
        header("Location: /jc_restaurant/customerLogin.php");
        exit;
    }
?>

<?php
    if (isset($_POST['addbutton'])) {

        $quantity = $_POST['quantity'];
        $order_id = $_POST['order_id'];
        $product_id = $_POST['product_id'];

        $query = "
        SELECT * FROM main_food WHERE product_id = '$product_id'
        UNION
        SELECT * FROM snackfood WHERE product_id = '$product_id'
        UNION
        SELECT * FROM coffee WHERE product_id = '$product_id'
        UNION
        SELECT * FROM beverages WHERE product_id = '$product_id'
        UNION
        SELECT * FROM pizza WHERE product_id = '$product_id'
    ";

    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);

    $pprice = $data['product_price'];
        
        if($quantity < 20){
            $quantity++;
            $total_amount = $quantity * $pprice;
            mysqli_query($conn, "update orders set quantity = '$quantity', total_amount = '$total_amount' WHERE order_id = $order_id");
            header("Location: AddToCart.php");
            exit();
        }
            
    }
?>

<?php
    if (isset($_POST['reducebutton'])) {

        $quantity = $_POST['quantity'];
        $order_id = $_POST['order_id'];
        $product_id = $_POST['product_id'];

        $query = "
        SELECT * FROM main_food WHERE product_id = '$product_id'
        UNION
        SELECT * FROM snackfood WHERE product_id = '$product_id'
        UNION
        SELECT * FROM coffee WHERE product_id = '$product_id'
        UNION
        SELECT * FROM beverages WHERE product_id = '$product_id'
        UNION
        SELECT * FROM pizza WHERE product_id = '$product_id'
    ";

    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);

    $pprice = $data['product_price'];
        
        if($quantity > 1){
            $quantity--;
            $total_amount = $quantity * $pprice;
            mysqli_query($conn, "update orders set quantity = '$quantity', total_amount = '$total_amount' WHERE order_id = $order_id");
            header("Location: AddToCart.php");
            exit();
        }
            
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
    <link rel="stylesheet" href="AddToCart.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC Restaurant | Fine Dining Experience</title>
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
                    <li><a href="aboutus.php">ABOUT</a></li>
                    <li><a href="menuPage.php">MENU</a></li>
                    <li><a href="contactus.php">CONTACT</a></li>
                    <li>
                        <a href="/jc_restaurant/customer_profile/edit-profile.php">
                            EDIT PROFILE
                        </a>
                    </li>
                    <li><a href="myorder.php">My Order</a></li>
                    <li>
                        <a href="AddToCart.php" class="cart-btn">
                            <i class="fa-solid fa-cart-shopping"></i>My Cart
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="A">

        <div>
            <h2>My Cart</h2>
        </div>

        
                            
        <?php

        $customerid = $_SESSION['id'];
        $ttotal = 0;
        $count = 0;

        $result = mysqli_query($conn, "select * from orders where customer_id = '$customerid' and order_status = 'incart'");
        while($row = mysqli_fetch_assoc($result)){
        
        $count++;
        
        $product_id = $row['product_id']; 

        $query1 = "
        SELECT * FROM main_food WHERE product_id = '$product_id'
        UNION
        SELECT * FROM snackfood WHERE product_id = '$product_id'
        UNION
        SELECT * FROM coffee WHERE product_id = '$product_id'
        UNION
        SELECT * FROM beverages WHERE product_id = '$product_id'
        UNION
        SELECT * FROM pizza WHERE product_id = '$product_id'
        ";

        $result1 = mysqli_query($conn, $query1);
        $data1 = mysqli_fetch_assoc($result1);

        ?>

        <div>	
            <div class="cart-item">

                <div class="cart-img">
                    <img src="<?php echo "backend/" . $data1['product_image']; ?>">
                </div>

                <div class="cart-name">
                    <?php echo $data1['product_name']; ?>
                </div>

                <div class="cart-qty">
                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                        <input type="hidden" name="total_amount" value="<?php echo $row['total_amount']; ?>">
                        <input type="hidden" name="quantity" value="<?php echo $row['quantity']; ?>">
                        <button type="submit" name="reducebutton">-</button>
                    </form>
                    <input type="text" value="<?php echo $row['quantity']; ?>" readonly>
                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                        <input type="hidden" name="quantity" value="<?php echo $row['quantity']; ?>">
                        <input type="hidden" name="total_amount" value="<?php echo $row['total_amount']; ?>">
                        <button type="submit" name="addbutton">+</button>
                    </form>
                </div>

                <div class="cart-price">
                    <?php $ppprice = $data1['product_price'] * $row['quantity']; ?>
                    RM <?php echo number_format($ppprice, 2); ?>
                </div>

                <div class="cart-remove">
                    <form method="POST" action="RemoveCart.php">
                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                        <button type="submit" class="remove-btn">✕</button>
                    </form>
                </div>

            </div>
        </div>

        <?php
        }
        ?>

        <?php

        $result = mysqli_query($conn, "select * from orders where customer_id = '$customerid' and order_status = 'incart'");
        while($row = mysqli_fetch_assoc($result)){

        $product_id = $row['product_id']; 

        $query1 = "
        SELECT * FROM main_food WHERE product_id = '$product_id'
        UNION
        SELECT * FROM snackfood WHERE product_id = '$product_id'
        UNION
        SELECT * FROM coffee WHERE product_id = '$product_id'
        UNION
        SELECT * FROM beverages WHERE product_id = '$product_id'
        UNION
        SELECT * FROM pizza WHERE product_id = '$product_id'
        ";

        $result1 = mysqli_query($conn, $query1);
        $data1 = mysqli_fetch_assoc($result1);

        $ppprice = $data1['product_price'] * $row['quantity'];

        ?>

        <div>	

            <?php $ttotal += $ppprice; ?>

            <?php
            }
            ?>

        </div>

        <?php
        if($count === 0){
            ?>
            <div style="clear: both;"></div>
            <h1 style="text-align: center;">Your cart is empty</h1>
            <div style="text-align: center;">
                <img src="cartempty2.png"><br>
                <a href="menuPage.php">
                    <button style="font-size: 18px; color: white; height: 60px; width: 200px; border-radius: 36px; background-color: orange; border-color: orange;">Order Now !</button>
                </a>
            </div>
        <?php
        }else{
        ?>

        <div class="cart-item">

            <div class="cart-img">
            </div>

            <div class="cart-name">
                <?php echo "Total Price"; ?>
            </div>

            <div class="cart-qty">
            </div>

            <div class="cart-price">
                <p>RM <?php echo number_format($ttotal, 2); ?></p>
            </div>

        </div>

        <button onclick="window.location.href='/jc_restaurant/payment.php'" style="font-size: 18px; color: white; margin-left: 900px; height: 60px; width: 200px; border-radius: 36px; background-color: orange; border-color: orange;">Make a Payment</button>

        <?php
        }
        ?>
        
    </div>

    <br><br><br>
    
</body>
</html>
