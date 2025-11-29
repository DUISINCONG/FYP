<?php
include 'db_connect.php';

// Define product categories and their prefixes
$categories = [
    'main_food' => ['name' => 'Main Food', 'prefix' => 'A'],
    'pizza' => ['name' => 'Pizza', 'prefix' => 'B'],
    'coffee' => ['name' => 'Coffee', 'prefix' => 'C'],
    'beverages' => ['name' => 'Beverages', 'prefix' => 'D'],
    'snackfood' => ['name' => 'Snack Food', 'prefix' => 'E']
];

// Function to generate next product ID based on category
function generateNextProductId($conn, $category, $categories) {
    $prefix = $categories[$category]['prefix'];
    $table_name = $category;
    
    $sql = "SELECT product_id FROM $table_name WHERE product_id LIKE '$prefix%' ORDER BY product_id DESC LIMIT 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $last_id = $row['product_id'];
        $number = intval(substr($last_id, 1)); // Remove prefix and get number
        $next_number = $number + 1;
        $next_id = $prefix . str_pad($next_number, 2, '0', STR_PAD_LEFT);
    } else {
        $next_id = $prefix . '01'; // Start with prefix+01 if no products exist
    }
    
    return $next_id;
}

// Handle Add Product
if (isset($_POST['add_product'])) {
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $product_id = generateNextProductId($conn, $category, $categories);
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $status = "Available"; // Automatically set to Available

    // Handle image upload
    $image_path = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $image_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $image_filename = $product_id . '.' . $image_extension;
        $target_file = $target_dir . $image_filename;
        
        // Check if image file is a actual image
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

    // Check if product name already exists in the same category
    $table_name = $category;
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
            $success_message = "Product added successfully to " . $categories[$category]['name'] . "! Auto-generated ID: " . $product_id;
        } else {
            $error_message = "Error adding product: " . mysqli_error($conn);
        }
    }
}

// Handle Update Product (combined with status update)
if (isset($_POST['update_product'])) {
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $status = mysqli_real_escape_string($conn, $_POST['product_status']);

    // Handle image upload for update
    $image_update = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $image_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $image_filename = $id . '.' . $image_extension;
        $target_file = $target_dir . $image_filename;
        
        // Check if image file is a actual image
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

    $table_name = $category;
    // Check if product name already exists (excluding current product)
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

// Handle Search and Sorting
$search_query = "";
$selected_category = isset($_GET['category']) ? $_GET['category'] : 'all';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'product_id';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
    $selected_category = mysqli_real_escape_string($conn, $_GET['category']);
    $sort_by = mysqli_real_escape_string($conn, $_GET['sort_by']);
    $sort_order = mysqli_real_escape_string($conn, $_GET['sort_order']);
    
    if ($selected_category == 'all') {
        // Search across all categories
        $all_results = [];
        foreach($categories as $key => $cat) {
            $table_name = $key;
            $sql = "SELECT *, '$key' as category FROM $table_name WHERE 
                    product_id LIKE '%$search_query%' OR 
                    product_name LIKE '%$search_query%' OR 
                    product_description LIKE '%$search_query%'";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    $all_results[] = $row;
                }
            }
        }
        // Sort the combined results
        usort($all_results, function($a, $b) use ($sort_by, $sort_order) {
            if ($sort_order == 'ASC') {
                return $a[$sort_by] <=> $b[$sort_by];
            } else {
                return $b[$sort_by] <=> $a[$sort_by];
            }
        });
        $result = $all_results;
    } else {
        $table_name = $selected_category;
        $sql = "SELECT * FROM $table_name WHERE 
                product_id LIKE '%$search_query%' OR 
                product_name LIKE '%$search_query%' OR 
                product_description LIKE '%$search_query%' 
                ORDER BY $sort_by $sort_order";
        $result = mysqli_query($conn, $sql);
    }
} else {
    if ($selected_category == 'all') {
        // Get all products from all categories
        $all_results = [];
        foreach($categories as $key => $cat) {
            $table_name = $key;
            $sql = "SELECT *, '$key' as category FROM $table_name";
            $cat_result = mysqli_query($conn, $sql);
            if ($cat_result && mysqli_num_rows($cat_result) > 0) {
                while($row = mysqli_fetch_assoc($cat_result)) {
                    $all_results[] = $row;
                }
            }
        }
        // Sort the combined results
        usort($all_results, function($a, $b) use ($sort_by, $sort_order) {
            if ($sort_order == 'ASC') {
                return $a[$sort_by] <=> $b[$sort_by];
            } else {
                return $b[$sort_by] <=> $a[$sort_by];
            }
        });
        $result = $all_results;
    } else {
        $table_name = $selected_category;
        $sql = "SELECT * FROM $table_name ORDER BY $sort_by $sort_order";
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
        input[type="number"],
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
            user-select: none;
        }
        
        th:hover {
            background-color: #dfe6e9;
        }
        
        .sortable:after {
            content: '↕';
            margin-left: 5px;
            color: var(--primary);
            font-size: 12px;
        }
        
        .sort-asc:after {
            content: '↑';
            color: var(--primary);
        }
        
        .sort-desc:after {
            content: '↓';
            color: var(--primary);
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
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
            background-color: var(--primary);
            color: white;
        }
        
        .edit-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .status-available {
            color: var(--success);
            font-weight: 600;
        }
        
        .status-soldout {
            color: var(--danger);
            font-weight: 600;
        }
        
        /* Center the actions column */
        td:last-child {
            text-align: center;
        }
        
        /* Ensure the actions column header is also centered */
        th:last-child {
            text-align: center;
        }
        
        .search-sort-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .search-container {
            display: flex;
            gap: 15px;
            flex: 1;
            min-width: 300px;
        }
        
        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 16px;
        }
        
        .search-button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .search-button:hover {
            background-color: var(--primary-dark);
        }
        
        .sort-container {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .sort-select {
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 16px;
            background-color: white;
        }
        
        .sort-order-btn {
            background-color: var(--light);
            color: var(--text);
            border: 1px solid var(--border);
            padding: 12px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .sort-order-btn:hover {
            background-color: #dde4e6;
        }
        
        .category-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
        }
        
        .category-tab {
            padding: 12px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            color: var(--text);
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .category-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .category-tab:hover {
            color: var(--primary-dark);
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
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid var(--border);
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
        
        .no-image {
            width: 80px;
            height: 80px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            color: #999;
            font-size: 12px;
            text-align: center;
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
            
            .search-sort-container {
                flex-direction: column;
            }
            
            .search-container {
                min-width: 100%;
            }
            
            .category-tabs {
                flex-wrap: wrap;
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
            <a href="#">HOME</a>
            <a href="manage_admins.php">ADMIN</a>
            <a href="customer_management.php">CUSTOMERS</a>
            <a href="manage_products.php" class="active">MENU</a>
            <a href="#">ORDER HISTORY</a>
            <a href="#">REPORTS</a>
        </div>
        <div class="admin-info">Admin</div>
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
            <h2>Add New Menu Item</h2>
            <div class="info-box">
                <strong>Note:</strong> Product ID will be automatically generated based on the category you select.
                <div class="id-prefix-info">
                    <?php foreach($categories as $key => $cat): ?>
                        <div class="prefix-item">
                            <strong><?php echo $cat['name']; ?>:</strong> <?php echo $cat['prefix']; ?>01, <?php echo $cat['prefix']; ?>02, ...
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Product Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach($categories as $key => $cat): ?>
                                <option value="<?php echo $key; ?>"><?php echo $cat['name']; ?> (<?php echo $cat['prefix']; ?>XX)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="productName">Product Name</label>
                        <input type="text" id="productName" name="product_name" placeholder="Enter product name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="productPrice">Price (RM)</label>
                        <input type="number" id="productPrice" name="product_price" step="0.01" min="0" placeholder="Enter price" required>
                    </div>
                    <div class="form-group">
                        <label for="productImage">Product Image</label>
                        <input type="file" id="productImage" name="product_image" accept="image/*" onchange="previewImage(this, 'addPreview')">
                        <div id="addPreviewContainer">
                            <img id="addPreview" class="image-preview" src="#" alt="Image Preview">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="productDescription">Description</label>
                    <textarea id="productDescription" name="product_description" rows="3" placeholder="Enter product description"></textarea>
                </div>
                
                <div class="divider"></div>
                
                <div class="btn-group">
                    <button type="submit" name="add_product" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2>Menu List</h2>
            
            <!-- Category Tabs -->
            <div class="category-tabs">
                <button class="category-tab <?php echo $selected_category == 'all' ? 'active' : ''; ?>" 
                        onclick="changeCategory('all')">
                    All Categories
                </button>
                <?php foreach($categories as $key => $cat): ?>
                    <button class="category-tab <?php echo $selected_category == $key ? 'active' : ''; ?>" 
                            onclick="changeCategory('<?php echo $key; ?>')">
                        <?php echo $cat['name']; ?>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <!-- Search and Sort Form -->
            <form method="GET" action="">
                <input type="hidden" name="category" value="<?php echo $selected_category; ?>">
                <div class="search-sort-container">
                    <div class="search-container">
                        <input type="text" class="search-input" name="search" placeholder="Search products by ID, name, or description..." value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" class="search-button">Search</button>
                    </div>
                    <div class="sort-container">
                        <select name="sort_by" class="sort-select" onchange="this.form.submit()">
                            <option value="product_id" <?php echo $sort_by == 'product_id' ? 'selected' : ''; ?>>Sort by ID</option>
                            <option value="product_name" <?php echo $sort_by == 'product_name' ? 'selected' : ''; ?>>Sort by Name</option>
                            <option value="product_price" <?php echo $sort_by == 'product_price' ? 'selected' : ''; ?>>Sort by Price</option>
                            <option value="product_status" <?php echo $sort_by == 'product_status' ? 'selected' : ''; ?>>Sort by Status</option>
                        </select>
                        <button type="submit" name="sort_order" value="<?php echo $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-order-btn">
                            <?php echo $sort_order == 'ASC' ? '↑ Asc' : '↓ Desc'; ?>
                        </button>
                    </div>
                </div>
            </form>
            
            <?php if (($selected_category == 'all' && is_array($result) && count($result) > 0) || ($selected_category != 'all' && $result && mysqli_num_rows($result) > 0)): ?>
            <table>
                <thead>
                    <tr>
                        <th class="sortable <?php echo $sort_by == 'product_id' ? ($sort_order == 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" onclick="sortTable('product_id')">ID</th>
                        <th>Image</th>
                        <th class="sortable <?php echo $sort_by == 'product_name' ? ($sort_order == 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" onclick="sortTable('product_name')">Name</th>
                        <th class="sortable <?php echo $sort_by == 'product_price' ? ($sort_order == 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" onclick="sortTable('product_price')">Price (RM)</th>
                        <th class="sortable <?php echo $sort_by == 'product_status' ? ($sort_order == 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" onclick="sortTable('product_status')">Status</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($selected_category == 'all') {
                        foreach($result as $row) { 
                    ?>
                    <tr>
                        <td><?php echo $row['product_id']; ?></td>
                        <td>
                            <?php if (!empty($row['product_image']) && file_exists($row['product_image'])): ?>
                                <img src="<?php echo $row['product_image']; ?>" alt="<?php echo $row['product_name']; ?>" class="product-image">
                            <?php else: ?>
                                <div class="no-image">No Image</div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo number_format($row['product_price'], 2); ?></td>
                        <td>
                            <?php if ($row['product_status'] == 'Available'): ?>
                                <span class="status-available">Available</span>
                            <?php else: ?>
                                <span class="status-soldout">Sold Out</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['product_description']; ?></td>
                        <td>
                            <div class="actions">
                                <button class="action-btn edit-btn" onclick="openEditModal(
                                    '<?php echo $row['category']; ?>',
                                    '<?php echo $row['product_id']; ?>',
                                    '<?php echo addslashes($row['product_name']); ?>',
                                    '<?php echo $row['product_price']; ?>',
                                    '<?php echo addslashes($row['product_description']); ?>',
                                    '<?php echo !empty($row['product_image']) ? $row['product_image'] : ''; ?>',
                                    '<?php echo $row['product_status']; ?>'
                                )">Edit</button>
                            </div>
                        </td>
                    </tr>
                    <?php } 
                    } else {
                        while($row = mysqli_fetch_assoc($result)) { 
                    ?>
                    <tr>
                        <td><?php echo $row['product_id']; ?></td>
                        <td>
                            <?php if (!empty($row['product_image']) && file_exists($row['product_image'])): ?>
                                <img src="<?php echo $row['product_image']; ?>" alt="<?php echo $row['product_name']; ?>" class="product-image">
                            <?php else: ?>
                                <div class="no-image">No Image</div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo number_format($row['product_price'], 2); ?></td>
                        <td>
                            <?php if ($row['product_status'] == 'Available'): ?>
                                <span class="status-available">Available</span>
                            <?php else: ?>
                                <span class="status-soldout">Sold Out</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['product_description']; ?></td>
                        <td>
                            <div class="actions">
                                <button class="action-btn edit-btn" onclick="openEditModal(
                                    '<?php echo $selected_category; ?>',
                                    '<?php echo $row['product_id']; ?>',
                                    '<?php echo addslashes($row['product_name']); ?>',
                                    '<?php echo $row['product_price']; ?>',
                                    '<?php echo addslashes($row['product_description']); ?>',
                                    '<?php echo !empty($row['product_image']) ? $row['product_image'] : ''; ?>',
                                    '<?php echo $row['product_status']; ?>'
                                )">Edit</button>
                            </div>
                        </td>
                    </tr>
                    <?php } 
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
    
    <!-- Edit Product Modal (Combined with Status) -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Product</h2>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" id="editCategory" name="category">
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
        function changeCategory(category) {
            window.location.href = '?category=' + category + '&sort_by=<?php echo $sort_by; ?>&sort_order=<?php echo $sort_order; ?>';
        }
        
        function sortTable(column) {
            const currentSortBy = '<?php echo $sort_by; ?>';
            const currentSortOrder = '<?php echo $sort_order; ?>';
            
            let newSortOrder = 'ASC';
            if (currentSortBy === column) {
                newSortOrder = currentSortOrder === 'ASC' ? 'DESC' : 'ASC';
            }
            
            window.location.href = '?category=<?php echo $selected_category; ?>&search=<?php echo urlencode($search_query); ?>&sort_by=' + column + '&sort_order=' + newSortOrder;
        }
        
        function openEditModal(category, id, name, price, description, imagePath, status) {
            document.getElementById('editCategory').value = category;
            document.getElementById('editId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editPrice').value = price;
            document.getElementById('editDescription').value = description;
            document.getElementById('editStatus').value = status;
            
            // Set image preview
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

        // Close modal when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            if (event.target === editModal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>
