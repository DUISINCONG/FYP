<?php
include 'db_connect.php';

// Handle Delete Order
if (isset($_GET['delete'])) {
    $order_id = $_GET['delete'];

    // First delete all order items linked to this order
    $sql_items = "DELETE FROM order_items WHERE order_id = $order_id";
    mysqli_query($conn, $sql_items);

    // Then delete the order itself
    $sql_order = "DELETE FROM orders WHERE order_id = $order_id";
    mysqli_query($conn, $sql_order);

    header("Location: manage_orders.php");
    exit;
}


// Handle Update Status
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $sql = "UPDATE orders SET status='$status' WHERE order_id=$order_id";
    mysqli_query($conn, $sql);
    header("Location: manage_orders.php");
    exit;
}

// Fetch Orders (join with customers for name)
$sql = "SELECT o.order_id, o.status, o.order_date, c.name AS customer_name 
        FROM orders o 
        JOIN customers c ON o.customer_id = c.customer_id
        ORDER BY o.order_date DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders</title>
</head>
<body>
    <h2>Orders</h2>

    <table border="1" cellpadding="10">
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Status</th>
            <th>Order Date</th>
            <th>Action</th>
        </tr>

        <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['order_id']; ?></td>
            <td><?php echo $row['customer_name']; ?></td>
            <td><?php echo $row['status']; ?></td>
            <td><?php echo $row['order_date']; ?></td>
            <td>
                <!-- Update Status Form -->
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                    <select name="status">
                        <option value="Pending" <?php if($row['status']=="Pending") echo "selected"; ?>>Pending</option>
                        <option value="Delivery" <?php if($row['status']=="Delivery") echo "selected"; ?>>Delivery</option>
                        <option value="Complete" <?php if($row['status']=="Complete") echo "selected"; ?>>Complete</option>
                    </select>
                    <button type="submit" name="update_status">Update</button>
                </form>

                <!-- Delete Button -->
                <a href="manage_orders.php?delete=<?php echo $row['order_id']; ?>" 
                   onclick="return confirm('Are you sure you want to delete this order?');">
                   Delete
                </a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
