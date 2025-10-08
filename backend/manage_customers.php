<?php
include 'db_connect.php';

// Handle Add Customer
if (isset($_POST['add_customer'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $sql = "INSERT INTO customers (name, email, phone) VALUES ('$name', '$email', '$phone')";
    mysqli_query($conn, $sql);
    header("Location: manage_customers.php"); // refresh
    exit();
}

// Handle Delete Customer
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM customers WHERE customer_id=$id";
    mysqli_query($conn, $sql);
    header("Location: manage_customers.php"); // refresh
    exit();
}

// Fetch customers
$sql = "SELECT * FROM customers";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Customers</title>
</head>
<body>
    <h2>Customers List</h2>

    <!-- Add Customer Form -->
    <form method="POST" action="">
        <input type="text" name="name" placeholder="Customer Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone" required>
        <button type="submit" name="add_customer">Add Customer</button>
    </form>

    <br>

    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Customer Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Action</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['customer_id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['phone']; ?></td>
            <td>
                <a href="manage_customers.php?delete=<?php echo $row['customer_id']; ?>" onclick="return confirm('Are you sure you want to delete this customer?');">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
