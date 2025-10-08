<?php
include 'db_connect.php';

// Handle Add Admin
if (isset($_POST['add'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // hash password
    $role = $_POST['role'];

    $sql = "INSERT INTO admins (username, password, role) VALUES ('$username', '$password', '$role')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Admin added successfully!'); window.location='manage_admins.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM admins WHERE id=$id";
    mysqli_query($conn, $sql);
    header("Location: manage_admins.php");
    exit;
}

// Fetch Admins
$result = mysqli_query($conn, "SELECT * FROM admins");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Admins</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Manage Admins</h2>

    <!-- Add Admin Form -->
    <form method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="col-md-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-control">
                    <option value="admin">Admin</option>
                    <option value="superadmin">Superadmin</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" name="add" class="btn btn-primary w-100">Add Admin</button>
            </div>
        </div>
    </form>

    <!-- Admin List -->
    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['username']; ?></td>
            <td><?php echo ucfirst($row['role']); ?></td>
            <td>
                <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this admin?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
