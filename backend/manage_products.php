<?php
session_start();
require_once 'admin_auth.php'; 
requireAdminLogin(); 

include 'db_connect.php';

$admin_id = $_SESSION['admin_id'];
$sql = "SELECT admin_name, admin_email, role FROM admins WHERE admin_id = ?";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $admin_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        $admin_name = htmlspecialchars($admin['admin_name']);
        $admin_email = htmlspecialchars($admin['admin_email']);
        $admin_role = htmlspecialchars($admin['role']);
    } else {
        $admin_name = getAdminName();
        $admin_email = "";
        $admin_role = getAdminRole();
    }
    
    mysqli_stmt_close($stmt);
} else {
    $admin_name = getAdminName();
    $admin_email = "";
    $admin_role = getAdminRole();
}

if (!function_exists('getAdminName')) {
    function getAdminName() {
        return isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin';
    }
}

if (!function_exists('getAdminRole')) {
    function getAdminRole() {
        return isset($_SESSION['admin_role']) ? htmlspecialchars($_SESSION['admin_role']) : 'admin';
    }
}

$category_query = "SELECT * FROM categories ORDER BY category_prefix ASC";
$category_result = mysqli_query($conn, $category_query);

$categories = [];
while ($cat = mysqli_fetch_assoc($category_result)) {
    $categories[$cat['category_id']] = [
        'id' => $cat['category_id'],
        'name' => $cat['category_name'],
        'prefix' => $cat['category_prefix'],
        'table_name' => $cat['table_name']
    ];
}

if (empty($categories)) {
    $default_categories = [
        ['Main Food', 'A', 'main_food'],
        ['Pizza', 'B', 'pizza'],
        ['Coffee', 'C', 'coffee'],
        ['Beverages', 'D', 'beverages'],
        ['Snack Food', 'E', 'snackfood']
    ];
    
    foreach ($default_categories as $cat) {
        $cat_name = mysqli_real_escape_string($conn, $cat[0]);
        $cat_prefix = mysqli_real_escape_string($conn, $cat[1]);
        $table_name = mysqli_real_escape_string($conn, $cat[2]);
        
        $insert_cat = "INSERT INTO categories (category_name, category_prefix, table_name) 
                       VALUES ('$cat_name', '$cat_prefix', '$table_name')";
        mysqli_query($conn, $insert_cat);
        
        $create_table = "CREATE TABLE IF NOT EXISTS $table_name (
            product_id VARCHAR(10) PRIMARY KEY,
            product_name VARCHAR(100) NOT NULL,
            product_price DECIMAL(10,2) NOT NULL,
            product_description TEXT,
            product_status ENUM('Available','Sold Out') DEFAULT 'Available',
            product_image VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        mysqli_query($conn, $create_table);
    }
    
    $category_result = mysqli_query($conn, $category_query);
    $categories = [];
    while ($cat = mysqli_fetch_assoc($category_result)) {
        $categories[$cat['category_id']] = [
            'id' => $cat['category_id'],
            'name' => $cat['category_name'],
            'prefix' => $cat['category_prefix'],
            'table_name' => $cat['table_name']
        ];
    }
}

function generateNextProductId($conn, $category_id, $categories) {
    $category_info = $categories[$category_id];
    $table_name = $category_info['table_name'];
    $prefix = $category_info['prefix'];
    
    $sql = "SELECT product_id FROM $table_name WHERE product_id LIKE '$prefix%' ORDER BY product_id DESC LIMIT 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $last_id = $row['product_id'];
        $number = intval(substr($last_id, 1)); 
        $next_number = $number + 1;
        $next_id = $prefix . str_pad($next_number, 2, '0', STR_PAD_LEFT);
    } else {
        $next_id = $prefix . '01'; 
    }
    
    return $next_id;
}

if (isset($_POST['add_category'])) {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $category_prefix = mysqli_real_escape_string($conn, $_POST['category_prefix']);
    $table_name = strtolower(str_replace(' ', '_', $category_name));
    
    $check_prefix = "SELECT * FROM categories WHERE category_prefix = '$category_prefix'";
    $prefix_result = mysqli_query($conn, $check_prefix);
    
    if ($prefix_result && mysqli_num_rows($prefix_result) > 0) {
        $error_message = "Error: Category prefix '$category_prefix' already exists!";
    } else {
        $insert_sql = "INSERT INTO categories (category_name, category_prefix, table_name) 
                       VALUES ('$category_name', '$category_prefix', '$table_name')";
        
        if (mysqli_query($conn, $insert_sql)) {
            $create_table = "CREATE TABLE $table_name (
                product_id VARCHAR(10) PRIMARY KEY,
                product_name VARCHAR(100) NOT NULL,
                product_price DECIMAL(10,2) NOT NULL,
                product_description TEXT,
                product_status ENUM('Available','Sold Out') DEFAULT 'Available',
                product_image VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            if (mysqli_query($conn, $create_table)) {
                $success_message = "Category '$category_name' added successfully with prefix '$category_prefix'!";
                $category_result = mysqli_query($conn, $category_query);
                $categories = [];
                while ($cat = mysqli_fetch_assoc($category_result)) {
                    $categories[$cat['category_id']] = [
                        'id' => $cat['category_id'],
                        'name' => $cat['category_name'],
                        'prefix' => $cat['category_prefix'],
                        'table_name' => $cat['table_name']
                    ];
                }
            } else {
                $error_message = "Error creating table: " . mysqli_error($conn);
                mysqli_query($conn, "DELETE FROM categories WHERE category_prefix = '$category_prefix'");
            }
        } else {
            $error_message = "Error adding category: " . mysqli_error($conn);
        }
    }
}

if (isset($_POST['add_product'])) {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    
    if (!isset($categories[$category_id])) {
        $error_message = "Error: Invalid category selected!";
    } else {
        $category_info = $categories[$category_id];
        $product_id = generateNextProductId($conn, $category_id, $categories);
        $name = mysqli_real_escape_string($conn, $_POST['product_name']);
        $price = mysqli_real_escape_string($conn, $_POST['product_price']);
        $description = mysqli_real_escape_string($conn, $_POST['product_description']);
        $status = "Available";
        $table_name = $category_info['table_name'];

        $image_path = '';
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $image_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $image_filename = $product_id . '.' . $image_extension;
            $target_file = $target_dir . $image_filename;
            
            $check = getimagesize($_FILES['product_image']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
                    $image_path = $target_file;
                } else {
                    $error_message = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error_message = "File is not an image.";
            }
        }

        $check_sql = "SELECT * FROM $table_name WHERE product_name='$name'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if($check_result && mysqli_num_rows($check_result) > 0) {
            $error_message = "Error: Product name already exists in this category!";
        } else {
            if ($image_path) {
                $sql = "INSERT INTO $table_name (product_id, product_name, product_price, product_description, product_status, product_image) 
                        VALUES ('$product_id', '$name', '$price', '$description', '$status', '$image_path')";
            } else {
                $sql = "INSERT INTO $table_name (product_id, product_name, product_price, product_description, product_status) 
                        VALUES ('$product_id', '$name', '$price', '$description', '$status')";
            }
            
            if(mysqli_query($conn, $sql)) {
                $success_message = "Product added successfully to " . $category_info['name'] . "! Auto-generated ID: " . $product_id;
            } else {
                $error_message = "Error adding product: " . mysqli_error($conn);
            }
        }
    }
}

if (isset($_POST['update_product'])) {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    
    if (!isset($categories[$category_id])) {
        $error_message = "Error: Invalid category!";
    } else {
        $category_info = $categories[$category_id];
        $id = mysqli_real_escape_string($conn, $_POST['product_id']);
        $name = mysqli_real_escape_string($conn, $_POST['product_name']);
        $price = mysqli_real_escape_string($conn, $_POST['product_price']);
        $description = mysqli_real_escape_string($conn, $_POST['product_description']);
        $status = mysqli_real_escape_string($conn, $_POST['product_status']);
        $table_name = $category_info['table_name'];

        $image_update = '';
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $image_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $image_filename = $id . '.' . $image_extension;
            $target_file = $target_dir . $image_filename;
            
            $check = getimagesize($_FILES['product_image']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
                    $image_update = ", product_image = '$target_file'";
                } else {
                    $error_message = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error_message = "File is not an image.";
            }
        }

        $check_sql = "SELECT * FROM $table_name WHERE product_name='$name' AND product_id != '$id'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if($check_result && mysqli_num_rows($check_result) > 0) {
            $error_message = "Error: Product name already exists in this category!";
        } else {
            $sql = "UPDATE $table_name SET product_name='$name', product_price='$price', product_description='$description', product_status='$status' $image_update WHERE product_id='$id'";
            if(mysqli_query($conn, $sql)) {
                $success_message = "Product updated successfully!";
            } else {
                $error_message = "Error updating product: " . mysqli_error($conn);
            }
        }
    }
}

$search = '';
$selected_category = 'all';
$sort_by = 'product_id';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $selected_category = mysqli_real_escape_string($conn, $_GET['category']);
}

if (isset($_GET['sort_by']) && !empty($_GET['sort_by'])) {
    $sort_by = mysqli_real_escape_string($conn, $_GET['sort_by']);
}

if (!empty($search) || $selected_category != 'all') {
    if ($selected_category == 'all') {
        $all_results = [];
        foreach($categories as $cat_id => $cat) {
            $table_name = $cat['table_name'];
            $sql = "SELECT *, '$cat_id' as category_id FROM $table_name WHERE 
                    product_id LIKE '%$search%' OR 
                    product_name LIKE '%$search%' OR 
                    product_description LIKE '%$search%' 
                    ORDER BY $sort_by";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    $row['category_name'] = $cat['name'];
                    $all_results[] = $row;
                }
            }
        }
        $result = $all_results;
    } else {
        $category_info = $categories[$selected_category];
        $table_name = $category_info['table_name'];
        $sql = "SELECT * FROM $table_name WHERE 
                product_id LIKE '%$search%' OR 
                product_name LIKE '%$search%' OR 
                product_description LIKE '%$search%' 
                ORDER BY $sort_by";
        $result = mysqli_query($conn, $sql);
    }
} else {
    if ($selected_category == 'all') {
        $all_results = [];
        foreach($categories as $cat_id => $cat) {
            $table_name = $cat['table_name'];
            $sql = "SELECT *, '$cat_id' as category_id FROM $table_name ORDER BY $sort_by";
            $cat_result = mysqli_query($conn, $sql);
            if ($cat_result && mysqli_num_rows($cat_result) > 0) {
                while($row = mysqli_fetch_assoc($cat_result)) {
                    $row['category_name'] = $cat['name'];
                    $all_results[] = $row;
                }
            }
        }
        $result = $all_results;
    } else {
        $category_info = $categories[$selected_category];
        $table_name = $category_info['table_name'];
        $sql = "SELECT * FROM $table_name ORDER BY $sort_by";
        $result = mysqli_query($conn, $sql);
    }
}

if (!$result) {
    $error_message = "Database error: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC Restaurant - Menu Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            --warning: #f39c12;
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
        
        .user-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.3s;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .user-info:hover {
            background-color: rgba(36, 28, 28, 0.3);
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #fff;
            min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.3s;
        }
        
        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }
        
        .dropdown-divider {
            height: 1px;
            background-color: #eee;
            margin: 5px 0;
        }
        
        .user-email {
            color: #666;
            font-size: 0.9em;
        }
        
        .user-role {
            background-color: <?php echo $admin_role === 'superadmin' ? '#dc3545' : '#007bff'; ?>;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 8px;
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
        input[type="number"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        input[type="search"],
        textarea,
        select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input:focus,
        textarea:focus,
        select:focus {
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
        
        .btn-warning {
            background-color: var(--warning);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e67e22;
        }
        
        .search-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .search-input {
            flex: 1;
        }
        
        .search-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .search-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .filter-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .filter-label {
            font-weight: 600;
            color: var(--secondary);
            white-space: nowrap;
        }
        
        .category-select {
            min-width: 180px;
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
            cursor: pointer;
            position: relative;
            transition: background-color 0.3s;
        }
        
        th:hover {
            background-color: #dfe6e9;
        }
        
        .sort-indicator {
            margin-left: 5px;
            font-size: 12px;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
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
            min-width: 80px;
        }
        
        .edit-btn {
            height: 70px;
            background-color: var(--primary);
            color: white;
        }
        
        .edit-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }
        
        .status-available {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success);
        }
        
        .status-soldout {
            background-color: rgba(231, 76, 60, 0.2);
            color: var(--danger);
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid var(--border);
        }
        
        .no-image {
            width: 60px;
            height: 60px;
            background-color: #f5f5f5;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 11px;
            text-align: center;
            border: 1px dashed var(--border);
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
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
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
        
        .info-box {
            background-color: rgba(52, 152, 219, 0.1);
            border: 1px solid rgba(52, 152, 219, 0.3);
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .id-prefix-info {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }
        
        .prefix-item {
            background: var(--light);
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .next-prefix-info {
            font-size: 14px;
            color: var(--primary);
            margin-top: 5px;
        }
        
        .image-preview {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid var(--border);
            margin-bottom: 10px;
            display: none;
        }
        
        .image-upload-container {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }
        
        .image-upload-preview {
            flex: 0 0 auto;
        }
        
        .image-upload-controls {
            flex: 1;
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
            
            .search-container {
                flex-direction: column;
            }
            
            .filter-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-dropdown {
                margin-top: 10px;
            }
            
            .dropdown-content {
                right: auto;
                left: 50%;
                transform: translateX(-50%);
            }
            
            .image-upload-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="banner">
        <div class="restaurant-name">JC Restaurant</div>
        <div class="nav-menu">
            <a href="adminhomepage.php">HOME</a>
            
            <?php if (isSuperAdmin()): ?>
                <a href="manage_admins.php">ADMIN</a>
            <?php endif; ?>
            
            <a href="manage_customers.php">CUSTOMERS</a>
            <a href="manage_products.php" class="active">MENU</a>
            <a href="manage_orders.php">ORDER</a>
            <a href="manage_report.php">REPORTS</a>
        </div>
        
        <div class="user-dropdown">
            <div class="user-info" onclick="toggleDropdown()">
                <i class="fas fa-user-cog"></i>
                HI <?php echo $admin_name; ?>
                <span class="user-role">
                    <?php echo $admin_role === 'superadmin' ? 'Super Admin' : 'Admin'; ?>
                </span>
                <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
            </div>
            <div class="dropdown-content" id="userDropdown">
                <div style="padding: 12px 16px; background-color: #f9f9f9;">
                    <?php if (!empty($admin_email)): ?>
                        <div class="user-email"><?php echo $admin_email; ?></div>
                    <?php endif; ?>
                </div>
                <div class="dropdown-divider"></div>
                <a href="login/adminlogin.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <header>
            <h1>Menu Management</h1>
            <p class="subtitle">Manage menu items across different categories</p>
        </header>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0;">Add New Menu Item</h2>
                <button class="btn btn-warning" onclick="openAddCategoryModal()">Add New Category</button>
            </div>
            
            <div class="info-box">
                <strong>Note:</strong> Product ID will be automatically generated based on the category you select.
                <div class="id-prefix-info">
                    <?php foreach($categories as $cat): ?>
                        <div class="prefix-item">
                            <strong><?php echo $cat['name']; ?>:</strong> <?php echo $cat['prefix']; ?>01, <?php echo $cat['prefix']; ?>02, ...
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="category_id">Product Category</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?> (<?php echo $cat['prefix']; ?>XX)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="product_name">Product Name</label>
                        <input type="text" id="product_name" name="product_name" placeholder="Enter product name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="product_price">Price (RM)</label>
                        <input type="number" id="product_price" name="product_price" step="0.01" min="0" placeholder="Enter price" required>
                    </div>
                    <div class="form-group">
                        <label for="product_image">Product Image</label>
                        <input type="file" id="product_image" name="product_image" accept="image/*" onchange="previewImage(this, 'addPreview')">
                        <div id="addPreviewContainer">
                            <img id="addPreview" class="image-preview" src="#" alt="Image Preview">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="product_description">Description</label>
                    <textarea id="product_description" name="product_description" rows="3" placeholder="Enter product description"></textarea>
                </div>
                
                <div class="divider"></div>
                
                <div class="btn-group">
                    <button type="submit" name="add_product" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2>Menu List</h2>
            
            <form method="GET" action="" class="search-container">
                <input type="search" name="search" class="search-input" placeholder="Search by ID, name, or description..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn">Search</button>
                <?php if (!empty($search) || $selected_category != 'all'): ?>
                    <a href="manage_products.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
            
            <div class="filter-container">
                <div class="filter-group">
                    <span class="filter-label">Category:</span>
                    <select name="category" id="category_filter" class="category-select" onchange="updateCategory()">
                        <option value="all" <?php echo $selected_category == 'all' ? 'selected' : ''; ?>>All Categories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $selected_category == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo $cat['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <span class="filter-label">Sort by:</span>
                    <select name="sort_by" id="sort_by" onchange="updateSort()">
                        <option value="product_id" <?php echo $sort_by == 'product_id' ? 'selected' : ''; ?>>ID</option>
                        <option value="product_name" <?php echo $sort_by == 'product_name' ? 'selected' : ''; ?>>Name</option>
                        <option value="product_price" <?php echo $sort_by == 'product_price' ? 'selected' : ''; ?>>Price</option>
                        <option value="product_status" <?php echo $sort_by == 'product_status' ? 'selected' : ''; ?>>Status</option>
                    </select>
                </div>
                
                <?php if (!empty($search) || $selected_category != 'all' || $sort_by != 'product_id'): ?>
                    <a href="manage_products.php" class="btn btn-secondary">Reset Filters</a>
                <?php endif; ?>
            </div>
            
            <?php if ((is_array($result) && count($result) > 0) || ($result && mysqli_num_rows($result) > 0)): ?>
            <table>
                <thead>
                    <tr>
                        <th onclick="sortTable('product_id')">
                            ID 
                            <span class="sort-indicator">
                                <?php if ($sort_by == 'product_id'): ?>↓<?php endif; ?>
                            </span>
                        </th>
                        <?php if ($selected_category == 'all'): ?>
                            <th>Category</th>
                        <?php endif; ?>
                        <th>Image</th>
                        <th onclick="sortTable('product_name')">
                            Name
                            <span class="sort-indicator">
                                <?php if ($sort_by == 'product_name'): ?>↓<?php endif; ?>
                            </span>
                        </th>
                        <th onclick="sortTable('product_price')">
                            Price (RM)
                            <span class="sort-indicator">
                                <?php if ($sort_by == 'product_price'): ?>↓<?php endif; ?>
                            </span>
                        </th>
                        <th onclick="sortTable('product_status')">
                            Status
                            <span class="sort-indicator">
                                <?php if ($sort_by == 'product_status'): ?>↓<?php endif; ?>
                            </span>
                        </th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($selected_category == 'all' && is_array($result)) {
                        foreach($result as $row): 
                            $cat_id = $row['category_id'];
                            $cat_name = isset($categories[$cat_id]) ? $categories[$cat_id]['name'] : 'Unknown';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                        <td><?php echo htmlspecialchars($cat_name); ?></td>
                        <td>
                            <?php if (!empty($row['product_image']) && file_exists($row['product_image'])): ?>
                                <img src="<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="product-image">
                            <?php else: ?>
                                <div class="no-image">No Image</div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo number_format($row['product_price'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '', $row['product_status'])); ?>">
                                <?php echo htmlspecialchars($row['product_status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars(substr($row['product_description'], 0, 50)); ?><?php echo strlen($row['product_description']) > 50 ? '...' : ''; ?></td>
                        <td class="actions">
                            <button class="action-btn edit-btn" onclick="openEditModal(
                                '<?php echo $row['category_id']; ?>',
                                '<?php echo addslashes($row['product_id']); ?>',
                                '<?php echo addslashes($row['product_name']); ?>',
                                '<?php echo $row['product_price']; ?>',
                                '<?php echo addslashes($row['product_description']); ?>',
                                '<?php echo !empty($row['product_image']) ? htmlspecialchars($row['product_image']) : ''; ?>',
                                '<?php echo $row['product_status']; ?>'
                            )">Edit</button>
                        </td>
                    </tr>
                    <?php endforeach; 
                    } else {
                        while($row = mysqli_fetch_assoc($result)): 
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                        <td>
                            <?php if (!empty($row['product_image']) && file_exists($row['product_image'])): ?>
                                <img src="<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="product-image">
                            <?php else: ?>
                                <div class="no-image">No Image</div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo number_format($row['product_price'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '', $row['product_status'])); ?>">
                                <?php echo htmlspecialchars($row['product_status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars(substr($row['product_description'], 0, 50)); ?><?php echo strlen($row['product_description']) > 50 ? '...' : ''; ?></td>
                        <td class="actions">
                            <button class="action-btn edit-btn" onclick="openEditModal(
                                '<?php echo $selected_category; ?>',
                                '<?php echo addslashes($row['product_id']); ?>',
                                '<?php echo addslashes($row['product_name']); ?>',
                                '<?php echo $row['product_price']; ?>',
                                '<?php echo addslashes($row['product_description']); ?>',
                                '<?php echo !empty($row['product_image']) ? htmlspecialchars($row['product_image']) : ''; ?>',
                                '<?php echo $row['product_status']; ?>'
                            )">Edit</button>
                        </td>
                    </tr>
                    <?php endwhile;
                    } ?>
                </tbody>
            </table>
            <?php elseif (!$result): ?>
                <div class="alert alert-error">Error loading product list. Please check if the database tables exist.</div>
            <?php else: ?>
                <p>No products found<?php echo $selected_category != 'all' ? ' in ' . $categories[$selected_category]['name'] : ''; ?> category.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="modal" id="addCategoryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Category</h2>
                <button class="close-btn" onclick="closeAddCategoryModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="category_name">Category Name</label>
                    <input type="text" id="category_name" name="category_name" placeholder="e.g., Desserts, Appetizers, etc." required>
                </div>
                <div class="form-group">
                    <label for="category_prefix">Category Prefix</label>
                    <input type="text" id="category_prefix" name="category_prefix" placeholder="e.g., F, G, H, etc." required maxlength="1" style="width: 100px; text-transform: uppercase;">
                    <div class="next-prefix-info">
                        Next available prefix: <?php
                            $used_prefixes = array_column($categories, 'prefix');
                            $next_letter = 'F';
                            for ($i = 0; $i < 26; $i++) {
                                $letter = chr(65 + $i); 
                                if (!in_array($letter, $used_prefixes)) {
                                    $next_letter = $letter;
                                    break;
                                }
                            }
                            echo "<strong>$next_letter</strong> (suggested)";
                        ?>
                    </div>
                </div>
                <div class="info-box">
                    <strong>Note:</strong> 
                    <ul style="margin: 10px 0 0 20px;">
                        <li>Product IDs will start with this prefix (e.g., F01, F02, etc.)</li>
                        <li>A new database table will be created for this category</li>
                        <li>Categories are sorted alphabetically by prefix</li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                    <button type="button" class="btn btn-secondary" onclick="closeAddCategoryModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Product</h2>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" id="editCategoryId" name="category_id">
                <input type="hidden" id="editId" name="product_id">
                <div class="form-group">
                    <label for="editName">Product Name</label>
                    <input type="text" id="editName" name="product_name" placeholder="Enter product name" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editPrice">Price (RM)</label>
                        <input type="number" id="editPrice" name="product_price" step="0.01" min="0" placeholder="Enter price" required>
                    </div>
                    <div class="form-group">
                        <label for="editStatus">Status</label>
                        <select id="editStatus" name="product_status" required>
                            <option value="Available">Available</option>
                            <option value="Sold Out">Sold Out</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="editImage">Product Image</label>
                    <div class="image-upload-container">
                        <div class="image-upload-preview">
                            <img id="editPreview" class="image-preview" src="#" alt="Image Preview">
                        </div>
                        <div class="image-upload-controls">
                            <input type="file" id="editImage" name="product_image" accept="image/*" onchange="previewImage(this, 'editPreview')">
                            <p style="font-size: 12px; color: #666; margin-top: 5px;">Leave empty to keep current image</p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="editDescription">Description</label>
                    <textarea id="editDescription" name="product_description" rows="3" placeholder="Enter product description"></textarea>
                </div>
                <div class="btn-group">
                    <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        window.onclick = function(event) {
            if (!event.target.matches('.user-info') && !event.target.closest('.user-info')) {
                const dropdowns = document.getElementsByClassName("dropdown-content");
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.style.display === 'block') {
                        openDropdown.style.display = 'none';
                    }
                }
            }
        }

        function openEditModal(category_id, id, name, price, description, imagePath, status) {
            document.getElementById('editCategoryId').value = category_id;
            document.getElementById('editId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editPrice').value = price;
            document.getElementById('editDescription').value = description;
            document.getElementById('editStatus').value = status;
            
            const preview = document.getElementById('editPreview');
            if (imagePath) {
                preview.src = imagePath;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
            
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function openAddCategoryModal() {
            const prefixInput = document.getElementById('category_prefix');
            const usedPrefixes = [<?php echo "'" . implode("','", array_column($categories, 'prefix')) . "'"; ?>];
            let nextPrefix = 'F';
            for (let i = 0; i < 26; i++) {
                const letter = String.fromCharCode(65 + i);
                if (!usedPrefixes.includes(letter)) {
                    nextPrefix = letter;
                    break;
                }
            }
            prefixInput.value = nextPrefix;
            prefixInput.focus();
            document.getElementById('addCategoryModal').style.display = 'flex';
        }

        function closeAddCategoryModal() {
            document.getElementById('addCategoryModal').style.display = 'none';
        }

        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }

        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const addCategoryModal = document.getElementById('addCategoryModal');
            
            if (event.target === editModal) {
                closeEditModal();
            }
            if (event.target === addCategoryModal) {
                closeAddCategoryModal();
            }
        }

        document.getElementById('category_prefix').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });

        function updateCategory() {
            const category = document.getElementById('category_filter').value;
            const search = '<?php echo $search; ?>';
            const sortBy = '<?php echo $sort_by; ?>';
            
            let url = 'manage_products.php?';
            
            if (search) {
                url += 'search=' + encodeURIComponent(search) + '&';
            }
            
            if (sortBy && sortBy !== 'product_id') {
                url += 'sort_by=' + sortBy + '&';
            }
            
            url += 'category=' + category;
            
            window.location.href = url;
        }

        function updateSort() {
            const sortBy = document.getElementById('sort_by').value;
            const search = '<?php echo $search; ?>';
            const category = '<?php echo $selected_category; ?>';
            
            let url = 'manage_products.php?';
            
            if (search) {
                url += 'search=' + encodeURIComponent(search) + '&';
            }
            
            if (category && category !== 'all') {
                url += 'category=' + category + '&';
            }
            
            url += 'sort_by=' + sortBy;
            
            window.location.href = url;
        }

        function sortTable(column) {
            const search = '<?php echo $search; ?>';
            const category = '<?php echo $selected_category; ?>';
            
            let url = 'manage_products.php?';
            
            if (search) {
                url += 'search=' + encodeURIComponent(search) + '&';
            }
            
            if (category && category !== 'all') {
                url += 'category=' + category + '&';
            }
            
            url += 'sort_by=' + column;
            
            window.location.href = url;
        }
    </script>
</body>
</html>
