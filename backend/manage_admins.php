<?php
include 'db_connect.php';

// Handle Add Admin
if (isset($_POST['add_admin'])) {
    $admin_id = mysqli_real_escape_string($conn, $_POST['admin_id']);
    $admin_name = mysqli_real_escape_string($conn, $_POST['admin_name']);
    $admin_password = mysqli_real_escape_string($conn, $_POST['admin_password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    // Set status to active by default for new admins
    $admin_status = 'active';

    // Check if admin ID already exists
    $check_sql = "SELECT * FROM admins WHERE admin_id='$admin_id'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if($check_result && mysqli_num_rows($check_result) > 0) {
        $error_message = "Error: Admin ID already exists!";
    } else {
        // Check if admin name already exists
        $check_name_sql = "SELECT * FROM admins WHERE admin_name='$admin_name'";
        $check_name_result = mysqli_query($conn, $check_name_sql);
        
        if($check_name_result && mysqli_num_rows($check_name_result) > 0) {
            $error_message = "Error: Admin name already exists!";
        } else {
            $sql = "INSERT INTO admins (admin_id, admin_name, admin_password, role, admin_status) VALUES ('$admin_id', '$admin_name', '$admin_password', '$role', '$admin_status')";
            if(mysqli_query($conn, $sql)) {
                $success_message = "Admin added successfully!";
            } else {
                $error_message = "Error adding admin: " . mysqli_error($conn);
            }
        }
    }
}

// Handle Update Admin
if (isset($_POST['update_admin'])) {
    $id = mysqli_real_escape_string($conn, $_POST['admin_id']);
    $new_id = mysqli_real_escape_string($conn, $_POST['new_admin_id']);
    $admin_name = mysqli_real_escape_string($conn, $_POST['admin_name']);
    $admin_password = mysqli_real_escape_string($conn, $_POST['admin_password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $admin_status = mysqli_real_escape_string($conn, $_POST['admin_status']);

    // Check if new admin ID already exists (if changed)
    if ($id != $new_id) {
        $check_sql = "SELECT * FROM admins WHERE admin_id='$new_id'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if($check_result && mysqli_num_rows($check_result) > 0) {
            $error_message = "Error: Admin ID already exists!";
        } else {
            // Check if admin name already exists (excluding current admin)
            $check_name_sql = "SELECT * FROM admins WHERE admin_name='$admin_name' AND admin_id != $id";
            $check_name_result = mysqli_query($conn, $check_name_sql);
            
            if($check_name_result && mysqli_num_rows($check_name_result) > 0) {
                $error_message = "Error: Admin name already exists!";
            } else {
                $sql = "UPDATE admins SET admin_id='$new_id', admin_name='$admin_name', admin_password='$admin_password', role='$role', admin_status='$admin_status' WHERE admin_id=$id";
                if(mysqli_query($conn, $sql)) {
                    $success_message = "Admin updated successfully!";
                } else {
                    $error_message = "Error updating admin: " . mysqli_error($conn);
                }
            }
        }
    } else {
        // ID didn't change, check admin name and update other fields
        $check_name_sql = "SELECT * FROM admins WHERE admin_name='$admin_name' AND admin_id != $id";
        $check_name_result = mysqli_query($conn, $check_name_sql);
        
        if($check_name_result && mysqli_num_rows($check_name_result) > 0) {
            $error_message = "Error: Admin name already exists!";
        } else {
            $sql = "UPDATE admins SET admin_name='$admin_name', admin_password='$admin_password', role='$role', admin_status='$admin_status' WHERE admin_id=$id";
            if(mysqli_query($conn, $sql)) {
                $success_message = "Admin updated successfully!";
            } else {
                $error_message = "Error updating admin: " . mysqli_error($conn);
            }
        }
    }
}

// Fetch admins with error handling
$sql = "SELECT * FROM admins ORDER BY admin_id";
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
    <title>JC Restaurant - Admin Management</title>
    <style>
        :root {
            --primary: orange;
            --primary-dark: #2980b9;
            --secondary: #16181bff;
            --light: #ecf0f1;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f39c12;
            --border: #bdc3c7;
            --text: #2c3e50;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .nav-menu a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .nav-menu a.active {
            background-color: var(--primary);
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
        input[type="password"],
        input[type="number"],
        select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input:focus,
        select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(176, 194, 206, 0.2);
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
        
        .superadmin-row {
            background-color: rgba(243, 156, 18, 0.1);
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
        
        .edit-btn:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }
        
        .role-badge, .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .role-superadmin {
            background-color: rgba(243, 156, 18, 0.2);
            color: #e67e22;
        }
        
        .role-admin {
            background-color: rgba(52, 152, 219, 0.2);
            color: var(--primary-dark);
        }
        
        .status-active {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success);
        }
        
        .status-inactive {
            background-color: rgba(149, 165, 166, 0.2);
            color: #7f8c8d;
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
        
        .alert-warning {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning);
            border: 1px solid rgba(243, 156, 18, 0.3);
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
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="banner">
        <div class="restaurant-name">JC Restaurant</div>
        <div class="nav-menu">
            <a href="#">HOME</a>
            <a href="manage_admins.php" class="active">ADMIN</a>
            <a href="customer_management.php">CUSTOMERS</a>
            <a href="#">MENU</a>
            <a href="#">ORDER HISTORY</a>
            <a href="#">REPORTS</a>
        </div>
        <div class="admin-info">Superadmin</div>
    </div>
    
    <div class="container">
        <header>
            <h1>Admin Management</h1>
            <p class="subtitle">Manage administrator accounts and permissions</p>
        </header>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Add New Admin</h2>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="admin_id">Admin ID</label>
                        <input type="number" id="admin_id" name="admin_id" placeholder="Enter admin ID" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_name">Admin Name</label>
                        <input type="text" id="admin_name" name="admin_name" placeholder="Enter admin name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="admin_password">Password</label>
                        <input type="password" id="admin_password" name="admin_password" placeholder="Enter password" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <div class="btn-group">
                    <button type="submit" name="add_admin" class="btn btn-primary">Save Admin</button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2>Admin List</h2>
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Admin Name</th>
                        <th>Password</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)) { 
                        $isSuperadmin = $row['admin_id'] == 1;
                        $status = !empty($row['admin_status']) ? $row['admin_status'] : 'active';
                    ?>
                    <tr class="<?php echo $isSuperadmin ? 'superadmin-row' : ''; ?>">
                        <td><?php echo $row['admin_id']; ?></td>
                        <td><?php echo $row['admin_name']; ?></td>
                        <td>••••••••</td>
                        <td>
                            <span class="role-badge role-<?php echo $row['role']; ?>">
                                <?php echo $row['role']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $status; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </td>
                        <td class="actions">
                            <button class="action-btn edit-btn" onclick="openEditModal(
                                '<?php echo $row['admin_id']; ?>',
                                '<?php echo addslashes($row['admin_name']); ?>',
                                '<?php echo addslashes($row['admin_password']); ?>',
                                '<?php echo addslashes($row['role']); ?>',
                                '<?php echo addslashes($status); ?>'
                            )" <?php echo $isSuperadmin ? 'disabled' : ''; ?>>Edit</button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php elseif (!$result): ?>
                <div class="alert alert-error">Error loading admin list. Please check if the 'admins' table exists.</div>
            <?php else: ?>
                <p>No admin accounts found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Edit Admin Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Admin</h2>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="editId" name="admin_id">
                <div class="form-group">
                    <label for="editAdminId">Admin ID</label>
                    <input type="number" id="editAdminId" name="new_admin_id" placeholder="Enter admin ID" required>
                </div>
                <div class="form-group">
                    <label for="editAdminName">Admin Name</label>
                    <input type="text" id="editAdminName" name="admin_name" placeholder="Enter admin name" required>
                </div>
                <div class="form-group">
                    <label for="editAdminPassword">Password</label>
                    <input type="password" id="editAdminPassword" name="admin_password" placeholder="Enter password" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editRole">Role</label>
                        <select id="editRole" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editStatus">Status</label>
                        <select id="editStatus" name="admin_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" name="update_admin" class="btn btn-primary">Update Admin</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, admin_name, admin_password, role, status) {
            document.getElementById('editId').value = id;
            document.getElementById('editAdminId').value = id;
            document.getElementById('editAdminName').value = admin_name;
            document.getElementById('editAdminPassword').value = admin_password;
            document.getElementById('editRole').value = role;
            document.getElementById('editStatus').value = status;
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

        // Show warning for superadmin account
        document.addEventListener('DOMContentLoaded', function() {
            const superadminEditButtons = document.querySelectorAll('.superadmin-row .edit-btn');
            superadminEditButtons.forEach(button => {
                if (button.disabled) {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        alert('Superadmin account cannot be modified!');
                    });
                }
            });
        });
    </script>
</body>
</html>
