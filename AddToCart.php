<?php 
    include("db_connect.php"); 
    session_start();
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
    <meta charset="UTF-8">
    <link rel="stylesheet" href="AddToCartCss.css"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add to cart</title>
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
        <hr style="height:2px; background-color:red; text-align: center; width: 1200px;">
    </header>

    <div class="A">

        <div>
            <h2>My Cart</h2>
        </div>
                            
        <?php

        $customer_id = $_SESSION['id'];
        $ttotal = 0;

        $result = mysqli_query($conn, "select * from orders where customer_id = '$customer_id'");
        while($row = mysqli_fetch_assoc($result)){

        ?>
        <div class="B">	
            <div class="cart-item">

                <div class="cart-img">
                    <img src="<?php echo $row['product_image']; ?>">
                </div>

                <div class="cart-name">
                    <?php echo $row['product_name']; ?>
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
                    RM <?php echo $row['total_amount']; ?>
                </div>

                <div class="cart-remove">
                    <form method="POST" action="RemoveCart.php" onsubmit="return confirmDelete();">
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

        $result = mysqli_query($conn, "select * from orders where customer_id = '$customer_id'");
        while($row = mysqli_fetch_assoc($result)){

        ?>

        <div>	

            <?php $row['total_amount']; ?>
            <?php $ttotal += $row['total_amount']; ?>

            <?php
            }
            ?>

        </div>

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

        <button style="font-size: 18px; color: white; margin-left: 900px; height: 60px; width: 200px; border-radius: 36px; background-color: red; border-color: red;">Make a Payment</button>
        
    </div>

    

    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to remove this item from your cart?");
        }
    </script>
    
</body>
</html>
