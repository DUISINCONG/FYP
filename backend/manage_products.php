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

    // Check if product name already exists in the same category
    $table_name = $category;
    $check_sql = "SELECT * FROM $table_name WHERE product_name='$name'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if($check_result && mysqli_num_rows($check_result) > 0) {
        $error_message = "Error: Product name already exists in this category!";
    } else {
        $sql = "INSERT INTO $table_name (product_id, product_name, product_price, product_description, product_status) 
                VALUES ('$product_id', '$name', '$price', '$description', '$status')";
        if(mysqli_query($conn, $sql)) {
            $success_message = "Product added successfully to " . $categories[$category]['name'] . "! Auto-generated ID: " . $product_id;
        } else {
            $error_message = "Error adding product: " . mysqli_error($conn);
        }
    }
}

// Handle Update Product Status
if (isset($_POST['update_status'])) {
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $status = mysqli_real_escape_string($conn, $_POST['product_status']);

    $table_name = $category;
    $sql = "UPDATE $table_name SET product_status='$status' WHERE product_id='$id'";
    if(mysqli_query($conn, $sql)) {
        $success_message = "Product status updated successfully!";
    } else {
        $error_message = "Error updating product status: " . mysqli_error($conn);
    }
}

// Handle Update Product Details
if (isset($_POST['update_product'])) {
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $description = mysqli_real_escape_string($conn, $_POST['product_description']);

    $table_name = $category;
    // Check if product name already exists (excluding current product)
    $check_sql = "SELECT * FROM $table_name WHERE product_name='$name' AND product_id != '$id'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if($check_result && mysqli_num_rows($check_result) > 0) {
        $error_message = "Error: Product name already exists in this category!";
    } else {
        $sql = "UPDATE $table_name SET product_name='$name', product_price='$price', product_description='$description' WHERE product_id='$id'";
        if(mysqli_query($conn, $sql)) {
            $success_message = "Product updated successfully!";
        } else {
            $error_message = "Error updating product: " . mysqli_error($conn);
        }
    }
}

// Handle Search
$search_query = "";
$selected_category = isset($_GET['category']) ? $_GET['category'] : 'main_food';

if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
    $selected_category = mysqli_real_escape_string($conn, $_GET['category']);
    
    $table_name = $selected_category;
    $sql = "SELECT * FROM $table_name WHERE 
            product_id LIKE '%$search_query%' OR 
            product_name LIKE '%$search_query%' OR 
            product_description LIKE '%$search_query%'";
} else {
    $table_name = $selected_category;
    $sql = "SELECT * FROM $table_name ORDER BY product_id ASC";
}

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
    <title>JC Restaurant - Product Management</title>
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
        
        .status-btn {
            background-color: #f39c12;
            color: white;
        }
        
        .status-btn:hover {
            background-color: #e67e22;
        }
        
        .status-available {
            color: var(--success);
            font-weight: 600;
        }
        
        .status-soldout {
            color: var(--danger);
            font-weight: 600;
        }
        
        .search-container {
            display: flex;
            margin-bottom: 20px;
            gap: 15px;
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
        
        .category-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border);
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
            
            .category-tabs {
                flex-wrap: wrap;
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
            <a href="manage_products.php" class="active">PRODUCTS</a>
            <a href="#">ORDER HISTORY</a>
            <a href="#">REPORTS</a>
        </div>
        <div class="admin-info">Admin</div>
    </div>
    
    <div class="container">
        <header>
            <h1>Product Management</h1>
            <p class="subtitle">Manage menu items across different categories</p>
        </header>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Add New Product</h2>
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
            <form method="POST" action="">
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
                        <!-- Empty for alignment -->
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
            <h2>Product List</h2>
            
            <!-- Category Tabs -->
            <div class="category-tabs">
                <?php foreach($categories as $key => $cat): ?>
                    <button class="category-tab <?php echo $selected_category == $key ? 'active' : ''; ?>" 
                            onclick="changeCategory('<?php echo $key; ?>')">
                        <?php echo $cat['name']; ?>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <!-- Search Form -->
            <form method="GET" action="">
                <input type="hidden" name="category" value="<?php echo $selected_category; ?>">
                <div class="search-container">
                    <input type="text" class="search-input" name="search" placeholder="Search products by ID, name, or description..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="search-button">Search</button>
                </div>
            </form>
            
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price (RM)</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $row['product_id']; ?></td>
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
                        <td class="actions">
                            <button class="action-btn edit-btn" onclick="openEditModal(
                                '<?php echo $selected_category; ?>',
                                '<?php echo $row['product_id']; ?>',
                                '<?php echo addslashes($row['product_name']); ?>',
                                '<?php echo $row['product_price']; ?>',
                                '<?php echo addslashes($row['product_description']); ?>'
                            )">Edit</button>
                            <button class="action-btn status-btn" onclick="openStatusModal(
                                '<?php echo $selected_category; ?>',
                                '<?php echo $row['product_id']; ?>',
                                '<?php echo $row['product_status']; ?>'
                            )">Change Status</button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php elseif (!$result): ?>
                <div class="alert alert-error">Error loading product list. Please check if the database tables exist.</div>
            <?php else: ?>
                <p>No products found in <?php echo $categories[$selected_category]['name']; ?> category.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Edit Product Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Product</h2>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="editCategory" name="category">
                <input type="hidden" id="editId" name="product_id">
                <div class="form-group">
                    <label for="editName">Product Name</label>
                    <input type="text" id="editName" name="product_name" placeholder="Enter product name" required>
                </div>
                <div class="form-group">
                    <label for="editPrice">Price (RM)</label>
                    <input type="number" id="editPrice" name="product_price" step="0.01" min="0" placeholder="Enter price" required>
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
    
    <!-- Change Status Modal -->
    <div class="modal" id="statusModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Change Product Status</h2>
                <button class="close-btn" onclick="closeStatusModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="statusCategory" name="category">
                <input type="hidden" id="statusId" name="product_id">
                <div class="form-group">
                    <label for="statusSelect">Status</label>
                    <select id="statusSelect" name="product_status" required>
                        <option value="Available">Available</option>
                        <option value="Sold Out">Sold Out</option>
                    </select>
                </div>
                <div class="btn-group">
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function changeCategory(category) {
            window.location.href = '?category=' + category;
        }
        
        function openEditModal(category, id, name, price, description) {
            document.getElementById('editCategory').value = category;
            document.getElementById('editId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editPrice').value = price;
            document.getElementById('editDescription').value = description;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        function openStatusModal(category, id, status) {
            document.getElementById('statusCategory').value = category;
            document.getElementById('statusId').value = id;
            document.getElementById('statusSelect').value = status;
            document.getElementById('statusModal').style.display = 'flex';
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const statusModal = document.getElementById('statusModal');
            
            if (event.target === editModal) {
                closeEditModal();
            }
            if (event.target === statusModal) {
                closeStatusModal();
            }
        }
    </script>
</body>
</html>
