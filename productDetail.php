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

$id = mysqli_real_escape_string($conn, $_GET['id']);

$query = "
    SELECT * FROM main_food WHERE product_id = '$id'
    UNION
    SELECT * FROM snackfood WHERE product_id = '$id'
    UNION
    SELECT * FROM coffee WHERE product_id = '$id'
    UNION
    SELECT * FROM beverages WHERE product_id = '$id'
    UNION
    SELECT * FROM pizza WHERE product_id = '$id'
";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="productdetailcss.css"/>
    <title>JC Restaurant | Fine Dining Experience</title>
    <style>
        div.down {
            position: absolute;
            top: 580px;
            right: 390px;
        }
        form {
            margin-top: 15px;
        }

        button[type="button"] {
            height: 50px;
            width: 50px;
            border: 1px solid orange;
            background-color: #fff;
            color: orange;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 1s ease;
            border-radius: 50%;
        }

        button[type="button"]:hover {
            background-color: orange;
            color: #fff;
        }

        #qty {
            width: 377px;
            height: 50px;
            border: 1px solid orange;
            outline: none;
            font-size: 16px;
            margin: 0 5px;
            border-radius: 36px;
        }

        button[type="submit"] {
            height: 50px;
            width: 500px;
            margin-top: 15px;
            padding: 12px 25px;
            background-color: orange;
            color: #fff;
            border: none;
            border-radius: 36px;
            font-size: 19px;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            transition: background-color 1s ease;
        }

        button[type="submit"]:hover {
            background-color: orange;
        }

        button:focus,
        input:focus {
            outline: none;
        }

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
            position: fixed;
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
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
    </header>

    <div style="margin-left: 20%; margin-right: 20%; margin-top: 100px;">
        <img style="height: 600px; width: 600px; float: left; margin: 20px; border-radius: 36px;" src="<?php echo "backend/" . $data['product_image']; ?>" alt="Food Image">
        <h1 style="padding: 20px 0px 0px 20px; font-size: 30px; font-family: 'Poppins', 'Segoe UI', Arial, sans-serif; font-weight: 600;"><?php echo $data['product_name']; ?></h1>
        <h1 style="font-size: 26px; font-family: 'Montserrat', Arial, sans-serif; font-weight: 700; color: orange;">RM <?php echo $data['product_price']; ?></h1>
        <p style="font-size: 19px; font-family: 'Arial', 'Helvetica Neue', 'Microsoft YaHei', sans-serif; line-height: 1.6; color: #555; text-align: justify;"><?php echo $data['product_description']; ?></p>

        <div class="down">

            <form method="post">

                <button type="button" onclick="subtract()">-</button>

                <input style="text-align:center;" type="text" id="qty" name="quantity"value="1" oninput="validNumber(this)">

                <button type="button" onclick="add()">+</button>

                <input type="hidden" name="product_id" value="<?php echo $data['product_id']; ?>">
                <input type="hidden" name="product_name" value="<?php echo $data['product_name']; ?>">
                <input type="hidden" name="product_image" value="<?php echo $data['product_image']; ?>">
                <input type="hidden" name="status" value="incart">
                <input type="hidden" name="date" value="<?php echo date("Y-m-d h:i:sa") ?>">

                <br><button type="submit" id="addToCartBtn" name="addToCartBtn">add to cart - RM </button>

            </form>

        </div>

    </div>

    <?php

    if(isset($_POST["addToCartBtn"])){

        $customer_id = $_SESSION['id'];
        $product_id = $_POST["product_id"];
        $product_name = $_POST["product_name"];
        $product_image = $_POST["product_image"];
        $status = $_POST["status"];
        $date = $_POST["date"];
        $qty = $_POST["quantity"];
        $pprice = $data['product_price'];
        $totalamount = $pprice * $qty;
        $checkqty = 0;
        $newqty = 0;
        $found = false;

        $result = mysqli_query($conn, "select * from orders where customer_id = '$customer_id' and order_status = 'incart'");
        while($row = mysqli_fetch_assoc($result)){

            if($row['product_id'] == $product_id){
                $found = true;
                if($row['quantity'] <= 20){
                    $checkqty = $row['quantity'] + $qty;
                    while($checkqty > 20){
                        $checkqty--;
                        $newqty++;
                    }
                    $totalamount = $pprice * $checkqty;
                    mysqli_query($conn, "update orders set order_date = '$date', quantity = '$checkqty', total_amount = '$totalamount' where order_id = '{$row['order_id']}'");
                    if($newqty > 0){
                        $totalamount = $pprice * $newqty;
                        mysqli_query($conn,"insert into orders (customer_id, product_id, product_name, product_image, order_status, order_date, quantity, total_amount) values ('$customer_id', '$product_id', '$product_name', '$product_image', '$status', '$date', '$newqty', '$totalamount')");
                    }
                }
                echo "<script>document.addEventListener('DOMContentLoaded', function () {showAlert('{$data['product_name']} has been added to cart');});</script>";?>

                <div id="customAlert" class="alert-box">
                    <p id="alertText"></p>
                </div>
                <?php
                break;
            }

        }

        if(!$found){

            mysqli_query($conn,"insert into orders (customer_id, product_id, product_name, product_image, order_status, order_date, quantity, total_amount) values ('$customer_id', '$product_id', '$product_name', '$product_image', '$status', '$date', '$qty', '$totalamount')");

            ?>
            <?php
                    
            echo "<script>document.addEventListener('DOMContentLoaded', function () {showAlert('{$data['product_name']} has been added to cart');});</script>";?>
                
            <div id="customAlert" class="alert-box">
                <p id="alertText"></p>
            </div>

            <?php

        }
    }

    ?> 

    <script>
        const price = <?php echo $data['product_price']; ?>;
        const qtyInput = document.getElementById("qty");

        function updateTotal() {
            let qty = parseInt(qtyInput.value);
            let total = price * qty;
            addToCartBtn.innerText = "add to cart - RM " + total.toFixed(2);
        }

        function validNumber(input) {  
            input.value = input.value.replace(/[^0-9]/g, '');
            if (input.value === '') input.value = 1;
            if (input.value === '0') input.value = 1;
            if (input.value === '21') input.value = 20;
            updateTotal();
        }

        function subtract() {
            let qty = document.getElementById("qty");
            let value = parseInt(qty.value);
            if (value > 1) qty.value = value - 1;
            updateTotal();
        }

        function add() {
            let qty = document.getElementById("qty");
            let value = parseInt(qty.value);
            if (value < 20) qty.value = value + 1;
            updateTotal();
        }

        function showAlert(message) {
            const alertBox = document.getElementById("customAlert");
            document.getElementById("alertText").innerText = message;
            alertBox.style.display = "block";

            setTimeout(() => {
                alertBox.style.display = "none";
            }, 2000);
        }

        updateTotal();
    </script>
    
</body>
</html>
