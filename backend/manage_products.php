<?php
include 'db_connect.php';

// Handle Add Product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $sql = "INSERT INTO products (name, price, stock) VALUES ('$name', '$price', '$stock')";
    mysqli_query($conn, $sql);
    header("Location: manage_products.php");
    exit;
}

// Handle Delete Product
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM products WHERE product_id = $id";
    mysqli_query($conn, $sql);
    header("Location: manage_products.php");
    exit;
}

// Handle Update Product
if (isset($_POST['update_product'])) {
    $id = $_POST['product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $sql = "UPDATE products SET name='$name', price='$price', stock='$stock' WHERE product_id=$id";
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
</head>
<body>
    <h2>Products</h2>

    <!-- Add Product Form -->
    <form method="POST">
        <input type="text" name="name" placeholder="Product Name" required>
        <input type="number" step="0.01" name="price" placeholder="Price" required>
        <input type="number" name="stock" placeholder="Stock Quantity" required>
        <button type="submit" name="add_product">Add Product</button>
    </form>

    <br>

    <!-- Product List -->
    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price (RM)</th>
            <th>Stock</th>
            <th>Action</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['product_id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo number_format($row['price'], 2); ?></td>
            <td><?php echo $row['stock']; ?></td>
            <td>
                <!-- Delete Button -->
                <a href="manage_products.php?delete=<?php echo $row['product_id']; ?>" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a> | 

                <!-- Edit Button (opens form) -->
                <a href="manage_products.php?edit=<?php echo $row['product_id']; ?>">Edit</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <br>

    <?php
    // If edit button clicked
    if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $edit_sql = "SELECT * FROM products WHERE product_id=$id";
        $edit_result = mysqli_query($conn, $edit_sql);
        $edit_row = mysqli_fetch_assoc($edit_result);
    ?>
        <h3>Edit Product</h3>
        <form method="POST">
            <input type="hidden" name="product_id" value="<?php echo $edit_row['product_id']; ?>">
            <input type="text" name="name" value="<?php echo $edit_row['name']; ?>" required>
            <input type="number" step="0.01" name="price" value="<?php echo $edit_row['price']; ?>" required>
            <input type="number" name="stock" value="<?php echo $edit_row['stock']; ?>" required>
            <button type="submit" name="update_product">Update Product</button>
        </form>
    <?php } ?>
</body>
</html>
