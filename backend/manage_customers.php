<?php
include 'db_connect.php';

// Handle Add Customer
if (isset($_POST['add_customer'])) {
    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if customer ID already exists
    $check_sql = "SELECT * FROM customers WHERE customer_id='$customer_id'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if($check_result && mysqli_num_rows($check_result) > 0) {
        $error_message = "Error: Customer ID already exists!";
    } else {
        $sql = "INSERT INTO customers (customer_id, name, email, phone, password) VALUES ('$customer_id', '$name', '$email', '$phone', '$password')";
        if(mysqli_query($conn, $sql)) {
            $success_message = "Customer added successfully!";
        } else {
            $error_message = "Error adding customer: " . mysqli_error($conn);
        }
    }
}

// Handle Delete Customer
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $sql = "DELETE FROM customers WHERE customer_id=$id";
    if(mysqli_query($conn, $sql)) {
        $success_message = "Customer deleted successfully!";
    } else {
        $error_message = "Error deleting customer: " . mysqli_error($conn);
    }
}

// Handle Update Customer
if (isset($_POST['update_customer'])) {
    $id = mysqli_real_escape_string($conn, $_POST['customer_id']);
    $new_id = mysqli_real_escape_string($conn, $_POST['new_customer_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if new customer ID already exists (if changed)
    if ($id != $new_id) {
        $check_sql = "SELECT * FROM customers WHERE customer_id='$new_id'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if($check_result && mysqli_num_rows($check_result) > 0) {
            $error_message = "Error: Customer ID already exists!";
        } else {
            $sql = "UPDATE customers SET customer_id='$new_id', name='$name', email='$email', phone='$phone', password='$password' WHERE customer_id=$id";
            if(mysqli_query($conn, $sql)) {
                $success_message = "Customer updated successfully!";
            } else {
                $error_message = "Error updating customer: " . mysqli_error($conn);
            }
        }
    } else {
        // ID didn't change, just update other fields
        $sql = "UPDATE customers SET name='$name', email='$email', phone='$phone', password='$password' WHERE customer_id=$id";
        if(mysqli_query($conn, $sql)) {
            $success_message = "Customer updated successfully!";
        } else {
            $error_message = "Error updating customer: " . mysqli_error($conn);
        }
    }
}

// Fetch customers
$sql = "SELECT * FROM customers";
$result = mysqli_query($conn, $sql);

if (!$result) {
    $error_message = "Database error: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC Restaurant - Customer Management</title>
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #16181bff;
            --light: #ecf0f1;
            --danger: #e74c3c;
            --success: #2ecc71;
            --border: #bdc3c7;
            --text: #2c3e50;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
            --active-color: #ff9800;
            --active-color-dark: #f57c00;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--text);
            line-height: 1.6;
        }
        
        .banner {
            background-color: var(--secondary);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }
        
        .restaurant-name {
            font-size: 24px;
            font-weight: bold;
        }
        
        .nav-menu {
            display: flex;
            gap: 20px;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .nav-menu a:hover {
            background-color: rgba(255,255,255,0.1);
            transform: translateY(-1px);
        }
        
        .nav-menu a.active {
            background-color: var(--active-color);
            color: white;
            box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
        }
        
        .nav-menu a.active:hover {
            background-color: var(--active-color-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.4);
        }
        
        .admin-info {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        h1 {
            color: var(--secondary);
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        h2 {
            color: var(--secondary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        input[type="number"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .divider {
            height: 1px;
            background-color: var(--border);
            margin: 25px 0;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-secondary {
            background-color: var(--light);
            color: var(--text);
        }
        
        .btn-secondary:hover {
            background-color: #dde4e6;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        th {
            background-color: var(--light);
            font-weight: 600;
            color: var(--secondary);
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .edit-btn {
            background-color: var(--primary);
            color: white;
        }
        
        .edit-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .delete-btn {
            background-color: #e74c3c;
            color: white;
        }
        
        .delete-btn:hover {
            background-color: #c0392b;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success);
            border: 1px solid rgba(46, 204, 113, 0.3);
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger);
            border: 1px solid rgba(231, 76, 60, 0.3);
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .banner {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="banner">
        <div class="restaurant-name">JC Restaurant</div>
        <div class="nav-menu">
            <a href="#">HOME</a>
            <a href="manage_admins.php">ADMIN</a>
            <a href="customer_management.php" class="active">CUSTOMERS</a>
            <a href="#">MENU</a>
            <a href="#">ORDER HISTORY</a>
            <a href="#">REPORTS</a>
        </div>
        <div class="admin-info">Admin</div>
    </div>
    
    <div class="container">
        <header>
            <h1>Customer Management</h1>
            <p class="subtitle">Manage customer information and accounts</p>
        </header>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Add New Customer</h2>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="customerId">Customer ID</label>
                        <input type="number" id="customerId" name="customer_id" placeholder="Enter customer ID" required>
                    </div>
                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="name" placeholder="Enter customer name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phoneNumber">Phone Number</label>
                        <input type="tel" id="phoneNumber" name="phone" placeholder="Enter phone number" required>
                    </div>
                    <div class="form-group">
                        <label for="emailAddress">Email Address</label>
                        <input type="email" id="emailAddress" name="email" placeholder="Enter email address" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                    </div>
                    <div class="form-group">
                        <!-- Empty for alignment -->
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <div class="btn-group">
                    <button type="submit" name="add_customer" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2>Customer List</h2>
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $row['customer_id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['phone']; ?></td>
                        <td class="actions">
                            <button class="action-btn edit-btn" onclick="openEditModal(
                                '<?php echo $row['customer_id']; ?>',
                                '<?php echo addslashes($row['name']); ?>',
                                '<?php echo addslashes($row['email']); ?>',
                                '<?php echo addslashes($row['phone']); ?>',
                                '<?php echo addslashes($row['password']); ?>'
                            )">Edit</button>
                            <a href="?delete=<?php echo $row['customer_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this customer?');">Delete</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php elseif (!$result): ?>
                <div class="alert alert-error">Error loading customer list. Please check if the 'customers' table exists.</div>
            <?php else: ?>
                <p>No customer accounts found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Edit Customer Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Customer</h2>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="editId" name="customer_id">
                <div class="form-group">
                    <label for="editCustomerId">Customer ID</label>
                    <input type="number" id="editCustomerId" name="new_customer_id" placeholder="Enter customer ID" required>
                </div>
                <div class="form-group">
                    <label for="editName">Full Name</label>
                    <input type="text" id="editName" name="name" placeholder="Enter customer name" required>
                </div>
                <div class="form-group">
                    <label for="editPhone">Phone Number</label>
                    <input type="tel" id="editPhone" name="phone" placeholder="Enter phone number" required>
                </div>
                <div class="form-group">
                    <label for="editEmail">Email Address</label>
                    <input type="email" id="editEmail" name="email" placeholder="Enter email address" required>
                </div>
                <div class="form-group">
                    <label for="editPassword">Password</label>
                    <input type="password" id="editPassword" name="password" placeholder="Enter password" required>
                </div>
                <div class="btn-group">
                    <button type="submit" name="update_customer" class="btn btn-primary">Update Customer</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, name, email, phone, password) {
            document.getElementById('editId').value = id;
            document.getElementById('editCustomerId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editPhone').value = phone;
            document.getElementById('editEmail').value = email;
            document.getElementById('editPassword').value = password;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>
