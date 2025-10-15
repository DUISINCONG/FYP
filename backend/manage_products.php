<?php
include 'db_connect.php';

// Handle Add Product
if (isset($_POST['add_product'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $sql = "INSERT INTO products (product_id, name, price, status) 
            VALUES ('$product_id', '$name', '$price', '$status')";
    mysqli_query($conn, $sql);
    header("Location: manage_products.php");
    exit;
}

// Handle Delete Product
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM products WHERE product_id = '$id'";
    mysqli_query($conn, $sql);
    header("Location: manage_products.php");
    exit;
}

// Handle Update Product
if (isset($_POST['update_product'])) {
    $id = $_POST['product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $sql = "UPDATE products 
            SET name='$name', price='$price', status='$status' 
            WHERE product_id='$id'";
    mysqli_query($conn, $sql);
    header("Location: manage_products.php");
    exit;
}

// Fetch Products
$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Products</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        h2 { color: #333; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background: #f2f2f2; }
        form { margin-bottom: 20px; }
        input, button, select { padding: 8px; margin-right: 10px; }
        a { text-decoration: none; color: blue; }
    </style>
</head>
<body>
    <h2>Products</h2>

    <!-- Add Product Form -->
    <form method="POST">
        <input type="text" name="product_id" placeholder="Product ID" required>
        <input type="text" name="name" placeholder="Product Name" required>
        <input type="number" step="0.01" name="price" placeholder="Price (RM)" required>
        <select name="status" required>
            <option value="Available">Still Available</option>
            <option value="Sold Out">Sold Out</option>
        </select>
        <button type="submit" name="add_product">Add Product</button>
    </form>

    <!-- Product List -->
    <table>
        <tr>
            <th>Product ID</th>
            <th>Name</th>
            <th>Price (RM)</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['product_id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo number_format($row['price'], 2); ?></td>
            <td>
                <?php 
                    if ($row['status'] == "Available") {
                        echo "<span style='color:green;'>Still Available</span>";
                    } else {
                        echo "<span style='color:red;'>Sold Out</span>";
                    }
                ?>
            </td>
            <td>
                <a href="manage_products.php?delete=<?php echo $row['product_id']; ?>" 
                   onclick="return confirm('Are you sure you want to delete this product?')">Delete</a> | 
                <a href="manage_products.php?edit=<?php echo $row['product_id']; ?>">Edit</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <br>

    <?php
    // Edit Product Form
    if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $edit_sql = "SELECT * FROM products WHERE product_id='$id'";
        $edit_result = mysqli_query($conn, $edit_sql);
        $edit_row = mysqli_fetch_assoc($edit_result);
    ?>
        <h3>Edit Product</h3>
        <form method="POST">
            <input type="hidden" name="product_id" value="<?php echo $edit_row['product_id']; ?>">
            <input type="text" name="name" value="<?php echo $edit_row['name']; ?>" required>
            <input type="number" step="0.01" name="price" value="<?php echo $edit_row['price']; ?>" required>
            <select name="status">
                <option value="Available" <?php if ($edit_row['status'] == "Available") echo "selected"; ?>>Still Available</option>
                <option value="Sold Out" <?php if ($edit_row['status'] == "Sold Out") echo "selected"; ?>>Sold Out</option>
            </select>
            <button type="submit" name="update_product">Update Product</button>
        </form>
    <?php } ?>
</body>
</html>
