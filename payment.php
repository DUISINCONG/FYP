<?php
    require __DIR__ . "/backend/db_connect.php";
    session_start();
    if (!$conn) {
        die("DB connection failed");
    }

    $error = 0;
    $paymentSuccess = false;

    if (isset($_POST['paymentbutton'])) {

        $address = $_POST['address'];
        $date = $_POST["date"];
        $customer_id = $_SESSION['id'];
        $cnumber = mysqli_real_escape_string($conn, $_POST['cnumber']);
        $cvv = mysqli_real_escape_string($conn, $_POST['cvv']);

        $stmt = mysqli_prepare($conn,"SELECT 1 FROM dummy_payment_table WHERE bank_number = ? AND cvv = ?");
        mysqli_stmt_bind_param($stmt, "ss", $cnumber, $cvv);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {

                mysqli_query($conn, "update orders set paymenttime = '$date', order_status = 'completed' WHERE customer_id = '$customer_id' and order_status = 'active'");


                mysqli_query($conn, "update customers set customer_address = '$address' WHERE customer_id = $customer_id");

                
                 echo "<script>
                        document.addEventListener('DOMContentLoaded', function () {
                            showAlert('Payment Successful');
                        });
                    </script>";?>
                
                <div id="customAlert" class="alert-box">
                    <p id="alertText"></p>
                    <button onclick="redirectAfterPayment()">OK</button>
                </div>
                
                <?php

            } else {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function () {
                            showAlert('Invalid Card Details.');
                        });
                    </script>";?>
                
                <div id="customAlert" class="alert-box1">
                    <p id="alertText"></p>
                    <button onclick="redirectAfterPayment1()">OK</button>
                </div>
                
                <?php
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
            <style>
        .A {
            margin-top: 150px;
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

        .er {
            color: red;
            text-align: center;
            font-size: 18px;
        }

        .alert-box {
            display: none;
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #14a14f, #14a14f);
            color: #ffffff;
            padding: 20px 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            z-index: 9999;
            min-width: 260px;
            animation: slideDown 0.5s ease;
        }

        .alert-box button {
            margin-top: 14px;
            padding: 8px 26px;
            border: none;
            background: #ffffff;
            color: #4f9f76;
            font-size: 14px;
            font-weight: 600;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .alert-box button:hover {
            background: #f2faf6;
            transform: translateY(-1px);
        }

        .alert-box1 {
            display: none;
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #c01a1a, #c01a1a);
            color: white;
            padding: 20px 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            z-index: 9999;
            min-width: 260px;
            animation: slideDown 0.5s ease;
        }

        .alert-box1 button {
            margin-top: 14px;
            padding: 8px 26px;
            border: none;
            background: #ffffff;
            color: #c94a4a;
            font-size: 14px;
            font-weight: 600;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .alert-box1 button:hover {
            background: #fff5f5;
            transform: translateY(-1px);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translate(-50%, -15px);
            }
            to {
                opacity: 1;
                transform: translate(-50%, 0);
            }
        }

    </style>
    <meta charset="UTF-8">
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

    <div class="A">

        <div>
            <h2>Deliver to</h2>
        </div>

        <?php

        $customerid = $_SESSION['id'];
        $ttotal = 0;

        $result = mysqli_query($conn, "select * from customers where customer_id = '$customerid'");
        while($row = mysqli_fetch_assoc($result)){

        ?>
        <div>	
            <form action="" method="post">

                <div>
                    <div class="address">
                        <input type="text" id="address" name="address" value="<?php echo $row['customer_address']; ?>" required>
                    </div>

                </div>

                <div>
                    <h2>Payment Method(Credit Card)</h2>
                </div>

                <div>
                    <div>
                        <div>
                        <label for="cnumber">Credit Number :</label><br>
                        </div>
                        <input type="text" id="cnumber" name="cnumber" required>
                    </div>

                    <div>
                        <div>
                        <label for="cvv">CVV :</label><br>
                        </div>
                        <input type="password" id="cvv" name="cvv" required>
                    </div>
                    <input type="hidden" name="date" value="<?php echo date("Y-m-d h:i:sa") ?>">
                    <br>

                    <?php

                    $result1 = mysqli_query($conn, "select * from orders where customer_id = '$customerid' and order_status = 'active'");
                    while($row1 = mysqli_fetch_assoc($result1)){

                    ?>

                    <?php $row1['total_amount']; ?>
                    <?php $ttotal += $row1['total_amount']; ?>

                    <?php
                    }
                    ?>

                    <p>TOTAL   RM <?php echo number_format($ttotal, 2); ?></p>

                    <div>

                    <input type="submit" name="paymentbutton" value="Proceed">

                    </div>

                </div>

            </form>

        </div>

        <?php
        }
        ?>

    </div>

    <script>
    function showAlert(message) {
        const alertBox = document.getElementById("customAlert");
        document.getElementById("alertText").innerText = message;
        alertBox.style.display = "block";
    }

    function redirectAfterPayment() {
        window.location.href = "homepage.html"; 
    }

    function redirectAfterPayment1() {
        window.location.href = "payment.php"; 
    }
    </script>
</body>
</html>
