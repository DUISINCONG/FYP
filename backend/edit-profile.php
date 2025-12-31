<?php
session_start();
include '../backend/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "<h3 style='color:red;'>You must be logged in to edit your profile.</h3>";
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM customers WHERE id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    $update_sql = "UPDATE customers 
                   SET name = '$name', phone = '$phone'
                   WHERE id = $user_id";

    if (mysqli_query($conn, $update_sql)) {

        $successMessage = "Information updated successfully!!";

        $_SESSION['user_name'] = $name;
    } else {
        $errorMessage = "Error updating profile.";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
</head>
<body>

<h2>Edit Your Profile</h2>

<?php if (!empty($successMessage)) { ?>
    <div style="
        width: 300px;
        padding: 10px;
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        border-radius: 5px;
        margin-bottom: 15px;
    ">
        <?php echo $successMessage; ?>
    </div>
<?php } ?>

<form method="POST" action="">
    <label>Name:</label><br>
    <input type="text" name="name" value="<?php echo $user['name']; ?>" required><br><br>

    <label>Phone:</label><br>
    <input type="text" name="phone" value="<?php echo $user['phone']; ?>" required><br><br>

    <button type="submit" 
        style="padding: 8px 15px; background-color: blue; color: white; border: none;">
        Update Profile
    </button>
</form>

<div style="margin-top: 20px;">
    <a href="../frontend/home.html" 
       style="padding: 10px 20px; background-color: #4CAF50; color: white; 
              text-decoration: none; border-radius: 5px;">
        Back to Home
    </a>
</div>

</body>
</html>