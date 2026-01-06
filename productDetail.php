<?php
include("db_connect.php");
session_start();

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
    <link rel="stylesheet" href="productdetailcss.css"/>
    <title>Document</title>
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
                    <li><a href="AddToCart.php" class="btn">Add To Cart</a></li>
                </ul><br>
            </nav>
        </div>
        <hr style="height:2px; background-color:orange; text-align: center; width: 1200px; border-color: orange;">
    </header>

    <div style="margin-left: 20%; margin-right: 20%; margin-top: 100px;">
        <img style="height: 600px; width: 600px; float: left; margin: 20px; border-radius: 36px;" src="<?php echo $data['product_image']; ?>" alt="Food Image">
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
                <input type="hidden" name="status" value="active">
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

        mysqli_query($conn,"insert into orders (customer_id, product_id, product_name, product_image, order_status, order_date, quantity, total_amount) values ('$customer_id', '$product_id', '$product_name', '$product_image', '$status', '$date', '$qty', '$totalamount')");

        ?>
        <?php
            
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    showAlert('{$data['product_name']} has been added to cart');
                });
            </script>";?>
        
        <div id="customAlert" class="alert-box">
            <p id="alertText"></p>
        </div>

        <?php   

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
