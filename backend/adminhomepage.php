<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit();
}

require_once 'admin_auth.php';

require_once 'db_connect.php';

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
        $admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : "Admin";
        $admin_email = "";
        $admin_role = isset($_SESSION['role']) ? $_SESSION['role'] : "admin";
    }
    
    mysqli_stmt_close($stmt);
} else {
    $admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : "Admin";
    $admin_email = "";
    $admin_role = isset($_SESSION['role']) ? $_SESSION['role'] : "admin";
}

require_once 'get_counts.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC Restaurant - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #fefefe;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background-color: #040404;
            color: #ecebea;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(255, 152, 0, 0.2);
            position: relative;
            z-index: 100;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        
        .logo i {
            margin-right: 10px;
            color: #ff9800;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            align-items: center;
        }
        
        nav ul li {
            margin-left: 1.5rem;
            position: relative;
        }
        
        nav ul li a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            display: flex;
            align-items: center;
        }
        
        nav ul li a i {
            margin-right: 5px;
        }
        
        nav ul li a:hover {
            color: #ff9800;
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
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .user-info:hover {
            background-color: rgba(255, 255, 255, 0.1);
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
            background-color: <?php echo (isset($admin_role) && $admin_role === 'superadmin') ? '#dc3545' : '#ff9800'; ?>;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 8px;
        }
        
        .logout-link {
            display: flex;
            align-items: center;
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            padding: 5px 10px;
            border-radius: 4px;
        }
        
        .logout-link:hover {
            color: #ff9800;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .hero {
            height: 30vh;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.5)), 
                        url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80') no-repeat center center;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgb(255, 255, 255);
            text-align: center;
            position: relative;
        }
        
        .hero-content {
            max-width: 800px;
            padding: 0 20px;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
            color: #fffffe;
        }
        
        .hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        main {
            padding: 2rem 0;
        }
        
        .dashboard-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #ddd;
        }
        
        .dashboard-header h2 {
            color: #000000;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .dashboard-header p {
            color: #000000;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .dashboard-card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 1.8rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            border-top: 4px solid #ff9800;
            display: flex;
            flex-direction: column;
            color: #000000;
        }
        
        .dashboard-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 8px 20px rgba(255, 152, 0, 0.2);
        }
        
        .card-icon {
            font-size: 2.2rem;
            margin-bottom: 1rem;
        }
        
        .dashboard-card h3 {
            color: #000000;
            margin-bottom: 1rem;
            font-size: 1.4rem;
        }
        
        .dashboard-card p {
            color: #333;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #ff9800;
            color: #ffffff;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s, transform 0.2s;
            align-self: flex-start;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn:hover {
            background-color: #ffb74d;
            transform: translateY(-2px);
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 3px solid #ff9800;
            color: #000000;
        }
        
        .stat-value {
            font-size: 2.2rem;
            font-weight: bold;
            color: #ff9800;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #000000;
            font-size: 0.9rem;
        }
        
        .product-breakdown {
            background: #ffffff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .product-breakdown h3 {
            color: #000000;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .product-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .product-category {
            text-align: center;
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 8px;
            border-left: 4px solid #ff9800;
        }
        
        .product-category .count {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ff9800;
            margin-bottom: 0.5rem;
        }
        
        .product-category .label {
            color: #333;
            font-size: 0.9rem;
        }
        
        footer {
            background-color: #000;
            color: #ff9800;
            padding: 2rem 0;
            margin-top: 2rem;
        }
        
        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .footer-nav {
            margin-bottom: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .footer-nav a {
            color: #ff9800;
            text-decoration: none;
            margin: 0 1rem 1rem;
            transition: color 0.3s;
        }
        
        .footer-nav a:hover {
            color: #ffb74d;
        }
        
        .social-links {
            margin-bottom: 1.5rem;
        }
        
        .social-links a {
            color: #ff9800;
            font-size: 1.2rem;
            margin: 0 0.7rem;
            transition: color 0.3s;
        }
        
        .social-links a:hover {
            color: #ffb74d;
        }
        
        .copyright {
            font-size: 0.9rem;
            opacity: 0.8;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .header-container {
                flex-direction: column;
                text-align: center;
            }
            
            nav ul {
                margin-top: 1rem;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            nav ul li {
                margin: 0.5rem 0.7rem;
            }
            
            .dropdown-content {
                right: auto;
                left: 50%;
                transform: translateX(-50%);
            }
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background-color: #ff0000;
            color: white;
            border-radius: 50%;
            width: 12px;
            height: 12px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 1.5s infinite;
        }

        .card-icon {
            position: relative; 
            font-size: 2.2rem;
            margin-bottom: 1rem;
            display: inline-block;
        }

        .new-order-count {
            background-color: #ff0000;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-left: 5px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 0, 0, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(255, 0, 0, 0);
            }
            100% {
                 box-shadow: 0 0 0 0 rgba(255, 0, 0, 0);
            }
        }

    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="logo">
                <i class="fas fa-utensils"></i>
                JC Restaurant
            </div>
            <nav>
                <ul>
                    <li class="user-dropdown">
                        <div class="user-info" onclick="toggleDropdown()">
                            <i class="fas fa-user-cog"></i>
                            HI <?php echo getAdminName(); ?>
                            <span class="user-role">
                                <?php echo isSuperAdmin() ? 'Super Admin' : 'Admin'; ?>
                            </span>
                            <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
                        </div>
                        <div class="dropdown-content" id="userDropdown">
                            <div style="padding: 12px 16px; background-color: #f9f9f9;">
                                <strong><?php echo getAdminName(); ?></strong>
                                <?php if (!empty($admin_email)): ?>
                                    <div class="user-email"><?php echo $admin_email; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                    <li>
                        <a href="login/adminlogin.php" class="logout-link">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>JC Restaurant Admin Dashboard</h1>
            <p>Efficiently manage your restaurant operations with our comprehensive admin tools</p>
        </div>
    </section>

    <main>
        <div class="container">
            <div class="dashboard-header">
                <h2>Management Dashboard</h2>
                <p style="color: #000000;">Welcome back, <?php echo getAdminName(); ?>! Monitor and control all aspects of your restaurant</p>
            </div>

            <div class="stats">
                <div class="stat-card">
                    <div class="stat-value" id="admin-count"><?php echo $adminCount; ?></div>
                    <div class="stat-label">Administrators</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="customer-count"><?php echo $customerCount; ?></div>
                    <div class="stat-label">Customers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="product-count"><?php echo $productCount; ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="order-count"><?php echo $orderCount; ?></div>
                    <div class="stat-label">Orders</div>
                </div>
            </div>

            <div class="product-breakdown">
                <h3>Product Categories</h3>
                <div class="product-categories" id="productCategories">
                    <?php 
                    if (!empty($categoryCounts)): 
                        $colors = ['#ff9800', '#4CAF50', '#2196F3', '#9C27B0', '#FF5722', '#795548', '#607D8B'];
                        $colorIndex = 0;
                        
                        foreach ($categoryCounts as $categoryName => $count): 
                            $color = $colors[$colorIndex % count($colors)];
                            $colorIndex++;
                    ?>
                        <div class="product-category">
                            <div class="count" style="color: <?php echo $color; ?>;"><?php echo $count; ?></div>
                            <div class="label"><?php echo htmlspecialchars($categoryName); ?></div>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <div class="product-category">
                            <div class="count">0</div>
                            <div class="label">No Categories</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-grid">
                <?php if (isSuperAdmin()): ?>
                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Admin Management</h3>
                    <p>Manage administrator accounts, roles, permissions, and access levels for your restaurant management system.</p>
                    <a href="manage_admins.php" class="btn"><i class="fas fa-cogs"></i> Manage Admins</a>
                </div>
                <?php endif; ?>

                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Customer Management</h3>
                    <p>View and manage customer profiles, preferences, loyalty programs, and communication preferences.</p>
                    <a href="manage_customers.php" class="btn"><i class="fas fa-user-friends"></i> Manage Customers</a>
                </div>

                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3>Menu Management</h3>
                    <p>Manage your restaurant's menu items, categories, pricing, ingredients, and availability status.</p>
                    <a href="manage_products.php" class="btn"><i class="fas fa-edit"></i> Manage Menu</a>
                </div>

                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="fas fa-shopping-cart"></i>
                    <span id="orderNotificationBadge" class="notification-badge" style="display: none;"></span>
                    </div>
                    <h3>Order Management <span id="newOrderCount" class="new-order-count" style="display: none;"></span></h3>
                    <p>Track, process, and manage customer orders from placement to fulfillment and payment.</p>
                    <a href="manage_orders.php" class="btn"><i class="fas fa-clipboard-list"></i> Manage Orders</a>
                </div>

                <div class="dashboard-card">
                    <div class="card-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>Report Management</h3>
                    <p>Generate and analyze sales reports, performance metrics, inventory reports, and customer insights.</p>
                    <a href="manage_report.php" class="btn"><i class="fas fa-file-alt"></i> View Reports</a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container footer-content">
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-tripadvisor"></i></a>
            </div>
            <div class="copyright">
                © 2024 JC Restaurant. All rights reserved. | Designed for Restaurant Management
            </div>
        </div>
    </footer>

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

    function checkNewOrders() {
        fetch('check_new_orders.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('网络响应不正常');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    console.error('检查新订单时出错:', data.error);
                    return;
                }
                
                const newOrderCount = data.new_order_count || 0;
                updateOrderNotification(newOrderCount);
            })
            .catch(error => {
                console.error('检查新订单时出错:', error);
            });
    }

    function updateOrderNotification(newOrderCount) {
        const badge = document.getElementById('orderNotificationBadge');
        const countElement = document.getElementById('newOrderCount');
        
        const previousCount = parseInt(sessionStorage.getItem('newOrderCount') || '0');
        
        if (newOrderCount > 0) {
            badge.style.display = 'block';
            
            countElement.textContent = newOrderCount;
            countElement.style.display = 'inline-flex';
            
            if (newOrderCount > previousCount && sessionStorage.getItem('firstLoad') !== 'true') {
            }
            
            sessionStorage.setItem('newOrderCount', newOrderCount.toString());
        } else {
            badge.style.display = 'none';
            countElement.style.display = 'none';
            sessionStorage.setItem('newOrderCount', '0');
        }
    }

    function clearOrderNotifications() {
        fetch('mark_orders_read.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('网络响应不正常');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const badge = document.getElementById('orderNotificationBadge');
                    const countElement = document.getElementById('newOrderCount');
                    
                    badge.style.display = 'none';
                    countElement.style.display = 'none';
                    
                    sessionStorage.setItem('newOrderCount', '0');
                    sessionStorage.setItem('lastOrderCheckTime', Date.now().toString());
                    
                    console.log('订单通知已清除');
                }
            })
            .catch(error => {
                console.error('清除通知时出错:', error);
                const badge = document.getElementById('orderNotificationBadge');
                const countElement = document.getElementById('newOrderCount');
                if (badge) badge.style.display = 'none';
                if (countElement) countElement.style.display = 'none';
            });
    }

    function setupOrderLinkClickHandler() {
        const orderLinks = document.querySelectorAll('a[href="manage_orders.php"]');
        
        orderLinks.forEach(link => {
            if (link.getAttribute('data-notification-handler')) {
                return;
            }
            
            link.setAttribute('data-notification-handler', 'true');
            
            link.addEventListener('click', function(e) {
                clearOrderNotifications();
            });
        });
    }

    function initOrderNotificationSystem() {
        if (!sessionStorage.getItem('firstLoad')) {
            sessionStorage.setItem('firstLoad', 'true');
        }
        
        const badge = document.getElementById('orderNotificationBadge');
        const countElement = document.getElementById('newOrderCount');
        
        if (!badge || !countElement) {
            console.log('未找到订单通知元素，跳过初始化');
            return;
        }
        
        setupOrderLinkClickHandler();
        
        checkNewOrders();
        
        setInterval(checkNewOrders, 30000); 
        
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                checkNewOrders();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const statValues = document.querySelectorAll('.stat-value');
        
        statValues.forEach(stat => {
            const target = parseFloat(stat.textContent) || 0;
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    stat.textContent = Math.round(target);
                    clearInterval(timer);
                } else {
                    stat.textContent = Math.ceil(current);
                }
            }, 30);
        });
        
        const productCounts = document.querySelectorAll('.product-category .count');
        
        productCounts.forEach(countElement => {
            const target = parseFloat(countElement.textContent) || 0;
            let current = 0;
            const increment = target / 30;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    countElement.textContent = Math.round(target);
                    clearInterval(timer);
                } else {
                    countElement.textContent = Math.ceil(current);
                }
            }, 40);
        });
        
        initOrderNotificationSystem();
    });

    window.addEventListener('beforeunload', function() {
        sessionStorage.setItem('lastCheckOnUnload', Date.now().toString());
    });
    </script>
</body>
</html>
