<?php
session_start();
require_once 'admin_auth.php'; 
requireAdminLogin(); 

include 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login/adminlogin.php");
    exit();
}

$current_admin_id = $_SESSION['admin_id'];
$admin_name = "Admin";
$admin_email = "";
$admin_role = "admin";

if (isset($current_admin_id)) {
    $sql = "SELECT admin_name, admin_email, role FROM admins WHERE admin_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $current_admin_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $admin = mysqli_fetch_assoc($result);
            $admin_name = htmlspecialchars($admin['admin_name']);
            $admin_email = htmlspecialchars($admin['admin_email']);
            $admin_role = htmlspecialchars($admin['role']);
        }
        
        mysqli_stmt_close($stmt);
    }
}

$default_date = date('Y-m-d');
$default_start_date = date('Y-m-d', strtotime('-7 days'));
$default_end_date = date('Y-m-d');

$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'daily';
$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : $default_date; 
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : $default_start_date; 
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : $default_end_date;
$selected_year = isset($_GET['selected_year']) ? $_GET['selected_year'] : date('Y');
$selected_month = isset($_GET['selected_month']) ? $_GET['selected_month'] : date('m');
$category_filter = isset($_GET['category_filter']) ? $_GET['category_filter'] : 'all';

$daily_sales = [];
$weekly_sales = [];
$monthly_sales = [];
$yearly_sales = [];
$product_sales = [];
$total_sales = 0;
$total_orders = 0;
$chart_data = [];
$category_data = [];


function getDailySales($conn, $selected_date) {
    $sales_data = [];
    
    $sql = "SELECT 
                HOUR(order_date) as order_hour,
                COUNT(*) as order_count,
                SUM(total_amount) as hourly_total
            FROM orders 
            WHERE order_status = 'completed'
            AND DATE(order_date) = ?
            GROUP BY HOUR(order_date)
            ORDER BY order_hour";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $selected_date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $sales_data[] = $row;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $sales_data;
}

function getDailySalesForChart($conn, $selected_date) {
    $sales_data = [];
    
    for ($hour = 12; $hour <= 23; $hour++) {
        $sales_data[$hour] = [
            'order_hour' => $hour,
            'order_count' => 0,
            'hourly_total' => 0
        ];
    }
    
    $sql = "SELECT 
                HOUR(order_date) as order_hour,
                COUNT(*) as order_count,
                SUM(total_amount) as hourly_total
            FROM orders 
            WHERE order_status = 'completed'
            AND DATE(order_date) = ?
            AND HOUR(order_date) BETWEEN 12 AND 23
            GROUP BY HOUR(order_date)
            ORDER BY order_hour";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $selected_date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $hour = $row['order_hour'];
            $sales_data[$hour] = $row;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return array_values($sales_data);
}

function getWeeklySales($conn, $start_date, $end_date) {
    $sales_data = [];
    
    $sql = "SELECT 
                YEARWEEK(order_date, 1) as week_number,
                MIN(DATE(order_date)) as week_start,
                MAX(DATE(order_date)) as week_end,
                COUNT(*) as order_count,
                SUM(total_amount) as weekly_total
            FROM orders 
            WHERE order_status = 'completed'
            AND DATE(order_date) BETWEEN ? AND ?
            GROUP BY YEARWEEK(order_date, 1)
            ORDER BY week_number DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $sales_data[] = $row;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $sales_data;
}

function getWeeklySalesForChart($conn, $start_date, $end_date) {
    $sales_data = [];
    
    $sql = "SELECT 
                DAYNAME(order_date) as day_name,
                DAYOFWEEK(order_date) as day_number,
                COUNT(*) as order_count,
                SUM(total_amount) as day_total
            FROM orders 
            WHERE order_status = 'completed'
            AND DATE(order_date) BETWEEN ? AND ?
            GROUP BY DAYNAME(order_date), DAYOFWEEK(order_date)
            ORDER BY day_number";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $sales_data[] = $row;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $sales_data;
}

function getMonthlySales($conn, $year, $month = null) {
    $sales_data = [];
    
    if ($month) {
        $sql = "SELECT 
                    DAY(order_date) as order_day,
                    COUNT(*) as order_count,
                    SUM(total_amount) as daily_total
                FROM orders 
                WHERE order_status = 'completed'
                AND YEAR(order_date) = ?
                AND MONTH(order_date) = ?
                GROUP BY DAY(order_date)
                ORDER BY order_day";
        
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $year, $month);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $sales_data[] = $row;
            }
            
            mysqli_stmt_close($stmt);
        }
    } else {
        $sql = "SELECT 
                    YEAR(order_date) as order_year,
                    MONTH(order_date) as order_month,
                    COUNT(*) as order_count,
                    SUM(total_amount) as monthly_total
                FROM orders 
                WHERE order_status = 'completed'
                AND YEAR(order_date) = ?
                GROUP BY YEAR(order_date), MONTH(order_date)
                ORDER BY order_year DESC, order_month DESC";
        
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $year);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $sales_data[] = $row;
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    
    return $sales_data;
}

function getYearlySales($conn, $year = null) {
    $sales_data = [];
    
    if ($year) {
        $sql = "SELECT 
                    MONTH(order_date) as order_month,
                    COUNT(*) as order_count,
                    SUM(total_amount) as monthly_total
                FROM orders 
                WHERE order_status = 'completed'
                AND YEAR(order_date) = ?
                GROUP BY MONTH(order_date)
                ORDER BY order_month";
        
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $year);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $sales_data[] = $row;
            }
            
            mysqli_stmt_close($stmt);
        }
    } else {
        $sql = "SELECT 
                    YEAR(order_date) as order_year,
                    COUNT(*) as order_count,
                    SUM(total_amount) as yearly_total
                FROM orders 
                WHERE order_status = 'completed'
                GROUP BY YEAR(order_date)
                ORDER BY order_year DESC";
        
        $result = mysqli_query($conn, $sql);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $sales_data[] = $row;
            }
        }
    }
    
    return $sales_data;
}

function getDailySalesStatistics($conn, $selected_date) {
    $stats = [
        'total_sales' => 0,
        'total_orders' => 0,
        'average_order_value' => 0,
        'busiest_hour' => '',
        'peak_hour_orders' => 0
    ];
    
    $sql = "SELECT 
                COUNT(*) as order_count,
                SUM(total_amount) as sales_total
            FROM orders 
            WHERE order_status = 'completed'
            AND DATE(order_date) = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $selected_date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $stats['total_orders'] = $row['order_count'] ?: 0;
            $stats['total_sales'] = $row['sales_total'] ?: 0;
            $stats['average_order_value'] = $stats['total_orders'] > 0 ? 
                $stats['total_sales'] / $stats['total_orders'] : 0;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    $sql = "SELECT 
                HOUR(order_date) as busiest_hour,
                COUNT(*) as hour_orders
            FROM orders 
            WHERE order_status = 'completed'
            AND DATE(order_date) = ?
            GROUP BY HOUR(order_date)
            ORDER BY hour_orders DESC
            LIMIT 1";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $selected_date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $stats['busiest_hour'] = $row['busiest_hour'] . ':00';
            $stats['peak_hour_orders'] = $row['hour_orders'];
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $stats;
}

function getSalesStatistics($conn, $start_date, $end_date) {
    $stats = [
        'total_sales' => 0,
        'total_orders' => 0,
        'average_order_value' => 0,
        'best_selling_product' => '',
        'best_selling_category' => ''
    ];
    
    $sql = "SELECT 
                COUNT(*) as order_count,
                SUM(total_amount) as sales_total
            FROM orders 
            WHERE order_status = 'completed'
            AND DATE(order_date) BETWEEN ? AND ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $stats['total_orders'] = $row['order_count'] ?: 0;
            $stats['total_sales'] = $row['sales_total'] ?: 0;
            $stats['average_order_value'] = $stats['total_orders'] > 0 ? 
                $stats['total_sales'] / $stats['total_orders'] : 0;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $stats;
}


function getProductCategories($conn) {
    $categories = ['all' => 'All Categories'];
    
    $sql = "SELECT DISTINCT category_name FROM categories ORDER BY category_name";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($row['category_name'])) {
                $categories[$row['category_name']] = $row['category_name'];
            }
        }
    }
    
    return $categories;
}

function getProductTableMapping($conn) {
    $mapping = [];
    
    $sql = "SELECT category_prefix, category_name, table_name FROM categories ORDER BY category_prefix";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $mapping[$row['category_prefix']] = [
                'name' => $row['category_name'],
                'table' => $row['table_name']
            ];
        }
    }
    
    return $mapping;
}

function getProductCurrentInfo($conn, $product_id) {
    $prefix = substr($product_id, 0, 1);
    
    $mapping = getProductTableMapping($conn);
    
    if (!isset($mapping[$prefix])) {
        return null;
    }
    
    $table_name = $mapping[$prefix]['table'];
    
    $check_sql = "SHOW TABLES LIKE '$table_name'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) == 0) {
        return null;
    }
    
    $sql = "SELECT product_name, product_price FROM `$table_name` WHERE product_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            return [
                'product_name' => $row['product_name'],
                'price' => $row['product_price'],
                'category' => $mapping[$prefix]['name'],
                'source_table' => $table_name
            ];
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return null;
}

function getProductSales($conn, $start_date, $end_date, $category_filter = 'all') {
    $sales_data = [];
    
    $mapping = getProductTableMapping($conn);
    
    $sql = "SELECT 
                product_id,
                COUNT(*) as order_count,
                SUM(quantity) as total_quantity,
                SUM(total_amount) as total_sales
            FROM orders 
            WHERE order_status = 'completed'
            AND DATE(order_date) BETWEEN ? AND ?
            GROUP BY product_id
            HAVING SUM(quantity) > 0
            ORDER BY total_quantity DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $product_id = $row['product_id'];
            
            $product_info = getProductCurrentInfo($conn, $product_id);
            
            if (!$product_info) {
                $product_info = [
                    'product_name' => 'Unknown Product (' . $product_id . ')',
                    'price' => 0,
                    'category' => 'Unknown',
                    'source_table' => 'unknown'
                ];
            }
            
            if ($category_filter !== 'all' && $product_info['category'] !== $category_filter) {
                continue;
            }
            
            $average_price = $row['total_quantity'] > 0 ? 
                $row['total_sales'] / $row['total_quantity'] : 0;
            
            $sales_data[] = [
                'source_table' => $product_info['source_table'],
                'product_id' => $product_id,
                'product_name' => $product_info['product_name'],
                'category' => $product_info['category'],
                'price' => $average_price,
                'total_quantity' => $row['total_quantity'],
                'total_sales' => $row['total_sales'],
                'order_count' => $row['order_count'],
                'current_price' => $product_info['price']
            ];
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $sales_data;
}

function getCategorySales($conn, $start_date, $end_date) {
    $category_data = [];
    
    $product_sales = getProductSales($conn, $start_date, $end_date, 'all');
    
    foreach ($product_sales as $product) {
        $category = $product['category'];
        
        if (!isset($category_data[$category])) {
            $category_data[$category] = [
                'category' => $category,
                'total_quantity' => 0,
                'total_sales' => 0,
                'unique_products' => 0
            ];
        }
        
        $category_data[$category]['total_quantity'] += $product['total_quantity'];
        $category_data[$category]['total_sales'] += $product['total_sales'];
        $category_data[$category]['unique_products']++;
    }
    
    usort($category_data, function($a, $b) {
        return $b['total_quantity'] <=> $a['total_quantity'];
    });
    
    return array_values($category_data);
}

function getProductSalesStatistics($conn, $start_date, $end_date) {
    $stats = [
        'total_sales' => 0,
        'total_orders' => 0,
        'total_quantity' => 0,
        'best_selling_product' => '',
        'best_selling_category' => '',
        'worst_selling_product' => '',
        'worst_selling_category' => '',
        'average_order_value' => 0,
        'unique_products_sold' => 0,
        'categories_count' => 0,
        'top_products' => []
    ];
    
    $product_sales = getProductSales($conn, $start_date, $end_date, 'all');
    $category_data = getCategorySales($conn, $start_date, $end_date);
    
    if (!empty($product_sales)) {
        foreach ($product_sales as $product) {
            $stats['total_sales'] += $product['total_sales'];
            $stats['total_quantity'] += $product['total_quantity'];
        }
        
        if (count($product_sales) > 0) {
            $best_product = $product_sales[0];
            $worst_product = end($product_sales);
            
            $stats['best_selling_product'] = $best_product['product_name'] . 
                                            ' (' . $best_product['total_quantity'] . ' units, RM' . 
                                            number_format($best_product['total_sales'], 2) . ')';
            
            $stats['worst_selling_product'] = $worst_product['product_name'] . 
                                             ' (' . $worst_product['total_quantity'] . ' units, RM' . 
                                             number_format($worst_product['total_sales'], 2) . ')';
            
            $stats['unique_products_sold'] = count($product_sales);
            
            $stats['top_products'] = array_slice($product_sales, 0, 5);
        }
    }
    
    if (!empty($category_data)) {
        if (count($category_data) > 0) {
            $best_category = $category_data[0];
            $worst_category = end($category_data);
            
            $stats['best_selling_category'] = $best_category['category'] . 
                                             ' (' . $best_category['total_quantity'] . ' units, RM' . 
                                             number_format($best_category['total_sales'], 2) . ')';
            
            $stats['worst_selling_category'] = $worst_category['category'] . 
                                              ' (' . $worst_category['total_quantity'] . ' units, RM' . 
                                              number_format($worst_category['total_sales'], 2) . ')';
            
            $stats['categories_count'] = count($category_data);
        }
    }
    
    $sql = "SELECT 
                COUNT(*) as order_count,
                SUM(total_amount) as sales_total
            FROM orders 
            WHERE order_status = 'completed'
            AND DATE(order_date) BETWEEN ? AND ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $stats['total_orders'] = $row['order_count'] ?: 0;
            $stats['total_sales'] = $row['sales_total'] ?: 0;
            $stats['average_order_value'] = $stats['total_orders'] > 0 ? 
                $stats['total_sales'] / $stats['total_orders'] : 0;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $stats;
}


switch ($report_type) {
    case 'daily':
        $daily_sales = getDailySales($conn, $selected_date);
        $chart_data = getDailySalesForChart($conn, $selected_date);
        $stats = getDailySalesStatistics($conn, $selected_date);
        $total_sales = $stats['total_sales'];
        $total_orders = $stats['total_orders'];
        break;
        
    case 'weekly':
        $weekly_sales = getWeeklySales($conn, $start_date, $end_date);
        $chart_data = getWeeklySalesForChart($conn, $start_date, $end_date);
        $stats = getSalesStatistics($conn, $start_date, $end_date);
        $total_sales = $stats['total_sales'];
        $total_orders = $stats['total_orders'];
        break;
        
    case 'monthly':
        if ($selected_month) {
            $monthly_sales = getMonthlySales($conn, $selected_year, $selected_month);
            foreach ($monthly_sales as $day) {
                $total_sales += $day['daily_total'];
                $total_orders += $day['order_count'];
            }
            $chart_data = $monthly_sales;
        } else {
            $monthly_sales = getMonthlySales($conn, $selected_year);
            foreach ($monthly_sales as $month) {
                $total_sales += $month['monthly_total'];
                $total_orders += $month['order_count'];
            }
            $chart_data = $monthly_sales;
        }
        break;
        
    case 'yearly':
        if ($selected_year) {
            $yearly_sales = getYearlySales($conn, $selected_year);
            foreach ($yearly_sales as $month) {
                $total_sales += $month['monthly_total'];
                $total_orders += $month['order_count'];
            }
            $chart_data = $yearly_sales;
        } else {
            $yearly_sales = getYearlySales($conn);
            foreach ($yearly_sales as $year_data) {
                $total_sales += $year_data['yearly_total'];
                $total_orders += $year_data['order_count'];
            }
        }
        break;
        
    case 'products':
        $categories = getProductCategories($conn);
        
        $product_sales = getProductSales($conn, $start_date, $end_date, $category_filter);
        
        $category_data = getCategorySales($conn, $start_date, $end_date);
        
        $stats = getProductSalesStatistics($conn, $start_date, $end_date);
        $total_sales = $stats['total_sales'];
        $total_orders = $stats['total_orders'];
        $total_quantity = $stats['total_quantity'];
        break;
}


if (isset($_GET['export'])) {
    $filename = $report_type . '_sales_report_' . date('Ymd_His') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    fwrite($output, "\xEF\xBB\xBF");
    
    switch ($report_type) {
        case 'daily':
            fputcsv($output, ['Hour', 'Orders', 'Sales Amount (RM)', 'Average Order Value (RM)']);
            foreach ($daily_sales as $sale) {
                fputcsv($output, [
                    $sale['order_hour'] . ':00',
                    $sale['order_count'],
                    $sale['hourly_total'],
                    number_format($sale['order_count'] > 0 ? $sale['hourly_total'] / $sale['order_count'] : 0, 2)
                ]);
            }
            fputcsv($output, ['Total', $total_orders, $total_sales, '']);
            break;
            
        case 'weekly':
            fputcsv($output, ['Week', 'Start Date', 'End Date', 'Orders', 'Sales Amount (RM)', 'Average Order Value (RM)']);
            foreach ($weekly_sales as $sale) {
                fputcsv($output, [
                    'Week ' . $sale['week_number'],
                    $sale['week_start'],
                    $sale['week_end'],
                    $sale['order_count'],
                    $sale['weekly_total'],
                    number_format($sale['weekly_total'] / $sale['order_count'], 2)
                ]);
            }
            fputcsv($output, ['Total', '', '', $total_orders, $total_sales, '']);
            break;
            
        case 'monthly':
            if ($selected_month) {
                fputcsv($output, ['Day', 'Orders', 'Sales Amount (RM)', 'Average Order Value (RM)']);
                foreach ($monthly_sales as $day) {
                    fputcsv($output, [
                        'Day ' . $day['order_day'],
                        $day['order_count'],
                        $day['daily_total'],
                        number_format($day['order_count'] > 0 ? $day['daily_total'] / $day['order_count'] : 0, 2)
                    ]);
                }
            } else {
                fputcsv($output, ['Year', 'Month', 'Orders', 'Sales Amount (RM)', 'Average Order Value (RM)']);
                foreach ($monthly_sales as $sale) {
                    fputcsv($output, [
                        $sale['order_year'],
                        date('F', mktime(0, 0, 0, $sale['order_month'], 1)),
                        $sale['order_count'],
                        $sale['monthly_total'],
                        number_format($sale['monthly_total'] / $sale['order_count'], 2)
                    ]);
                }
            }
            fputcsv($output, ['Total', '', $total_orders, $total_sales, '']);
            break;
            
        case 'yearly':
            if ($selected_year) {
                fputcsv($output, ['Month', 'Orders', 'Sales Amount (RM)', 'Average Order Value (RM)']);
                foreach ($yearly_sales as $month) {
                    fputcsv($output, [
                        date('F', mktime(0, 0, 0, $month['order_month'], 1)),
                        $month['order_count'],
                        $month['monthly_total'],
                        number_format($month['monthly_total'] / $month['order_count'], 2)
                    ]);
                }
            } else {
                fputcsv($output, ['Year', 'Orders', 'Sales Amount (RM)', 'Average Order Value (RM)']);
                foreach ($yearly_sales as $sale) {
                    fputcsv($output, [
                        $sale['order_year'],
                        $sale['order_count'],
                        $sale['yearly_total'],
                        number_format($sale['yearly_total'] / $sale['order_count'], 2)
                    ]);
                }
            }
            break;
            
        case 'products':
            fputcsv($output, ['Source Table', 'Product ID', 'Product Name', 'Category', 'Unit Price (RM)', 'Quantity Sold', 'Total Sales (RM)', 'Orders']);
            foreach ($product_sales as $product) {
                fputcsv($output, [
                    $product['source_table'],
                    $product['product_id'],
                    $product['product_name'],
                    $product['category'],
                    $product['price'],
                    $product['total_quantity'],
                    $product['total_sales'],
                    $product['order_count']
                ]);
            }
            fputcsv($output, ['', '', 'Total', '', '', $total_quantity, $total_sales, $total_orders]);
            break;
    }
    
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC Restaurant - Sales Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: orange;
            --primary-dark: #2980b9;
            --secondary: #16181bff;
            --light: #ecf0f1;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f39c12;
            --info: #3498db;
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
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            font-weight: 500;
        }
        
        .nav-menu a:hover {
            background-color: rgba(255,255,255,0.1);
            transform: translateY(-1px);
        }
        
        .nav-menu a.active {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
        }
        
        .nav-menu a.active:hover {
            background-color: #f57c00;
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
            max-width: 1400px;
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
        input[type="date"],
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
            margin-bottom: 20px;
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
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        .btn-info {
            background-color: var(--info);
            color: white;
        }
        
        .btn-info:hover {
            background-color: #2980b9;
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
            padding: 12px 15px;
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
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: var(--shadow);
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
            margin: 10px 0;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }
        
        .report-type-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .report-tab {
            padding: 12px 25px;
            background-color: var(--light);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .report-tab.active {
            background-color: var(--primary);
            color: white;
        }
        
        .report-tab:hover {
            background-color: #dde4e6;
        }
        
        .report-tab.active:hover {
            background-color: var(--primary-dark);
        }
        
        .date-range-selector {
            display: flex;
            gap: 20px;
            align-items: flex-end;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .date-input-group {
            flex: 1;
            min-width: 200px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .data-table {
            max-height: 500px;
            overflow-y: auto;
            margin-top: 20px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-info {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--info);
            border: 1px solid rgba(52, 152, 219, 0.3);
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success);
            border: 1px solid rgba(46, 204, 113, 0.3);
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            font-style: italic;
        }
        
        .month-year-selector {
            display: flex;
            gap: 20px;
            align-items: flex-end;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .month-year-group {
            flex: 1;
            min-width: 200px;
        }
        
        .hourly-table td {
            text-align: center;
        }
        
        .single-date-selector {
            display: flex;
            gap: 20px;
            align-items: flex-end;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .single-date-group {
            flex: 1;
            min-width: 200px;
        }
        
        .product-stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .product-stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: var(--shadow);
        }
        
        .stat-title {
            font-size: 1rem;
            color: var(--secondary);
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .stat-content {
            font-size: 1.1rem;
            color: var(--text);
        }
        
        .best-selling {
            color: var(--success);
            font-weight: bold;
        }
        
        .worst-selling {
            color: var(--danger);
            font-weight: bold;
        }
        
        .category-filter {
            margin-bottom: 20px;
        }
        
        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-label {
            font-weight: 600;
            color: var(--secondary);
        }
        
        .source-table-badge {
            background-color: #e3f2fd;
            color: #1976d2;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 8px;
        }
        
        .product-rank {
            display: inline-block;
            width: 24px;
            height: 24px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            margin-right: 10px;
            font-size: 0.9em;
        }
        
        .rank-1 {
            background-color: #ffd700;
            color: #000;
        }
        
        .rank-2 {
            background-color: #c0c0c0;
        }
        
        .rank-3 {
            background-color: #cd7f32;
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
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .date-range-selector, 
            .month-year-selector,
            .single-date-selector {
                flex-direction: column;
                gap: 15px;
            }
            
            .user-dropdown {
                margin-top: 10px;
            }
            
            .dropdown-content {
                right: auto;
                left: 50%;
                transform: translateX(-50%);
            }
            
            .report-tab {
                padding: 10px 15px;
                font-size: 14px;
            }
            
            .product-stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="banner">
        <div class="restaurant-name">JC Restaurant</div>
        <div class="nav-menu">
            <a href="adminhomepage.php">HOME</a>
            <a href="manage_admins.php">ADMIN</a>
            <a href="manage_customers.php">CUSTOMERS</a>
            <a href="manage_products.php">MENU</a>
            <a href="manage_orders.php">ORDER</a>
            <a href="manage_report.php" class="active">REPORTS</a>
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
            <h1>Sales Reports</h1>
            <p class="subtitle">Analyze sales performance and generate insights</p>
        </header>
        
        <div class="report-type-tabs">
            <button class="report-tab <?php echo $report_type == 'daily' ? 'active' : ''; ?>" 
                    onclick="changeReportType('daily')">
                <i class="fas fa-calendar-day"></i> Daily Sales
            </button>
            <button class="report-tab <?php echo $report_type == 'weekly' ? 'active' : ''; ?>" 
                    onclick="changeReportType('weekly')">
                <i class="fas fa-calendar-week"></i> Weekly Sales
            </button>
            <button class="report-tab <?php echo $report_type == 'monthly' ? 'active' : ''; ?>" 
                    onclick="changeReportType('monthly')">
                <i class="fas fa-calendar-alt"></i> Monthly Sales
            </button>
            <button class="report-tab <?php echo $report_type == 'yearly' ? 'active' : ''; ?>" 
                    onclick="changeReportType('yearly')">
                <i class="fas fa-calendar"></i> Yearly Sales
            </button>
            <button class="report-tab <?php echo $report_type == 'products' ? 'active' : ''; ?>" 
                    onclick="changeReportType('products')">
                <i class="fas fa-chart-bar"></i> Product Sales
            </button>
        </div>
        
        <div class="card">
            <form method="GET" action="">
                <input type="hidden" name="report_type" value="<?php echo $report_type; ?>" id="reportType">
                
                <?php if ($report_type == 'daily'): ?>
                <div class="single-date-selector">
                    <div class="single-date-group">
                        <label for="selected_date">Select Date</label>
                        <input type="date" id="selected_date" name="selected_date" value="<?php echo $selected_date; ?>" required>
                    </div>
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <a href="?report_type=<?php echo $report_type; ?>&selected_date=<?php echo $default_date; ?>" 
                           class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Today
                        </a>
                    </div>
                </div>
                <?php elseif ($report_type == 'weekly'): ?>
                <div class="date-range-selector">
                    <div class="date-input-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>" required>
                    </div>
                    <div class="date-input-group">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>" required>
                    </div>
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <a href="?report_type=<?php echo $report_type; ?>&start_date=<?php echo $default_start_date; ?>&end_date=<?php echo $default_end_date; ?>" 
                           class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Last 7 Days
                        </a>
                    </div>
                </div>
                <?php elseif ($report_type == 'monthly'): ?>
                <div class="month-year-selector">
                    <div class="month-year-group">
                        <label for="selected_year">Year</label>
                        <select id="selected_year" name="selected_year" class="form-control">
                            <?php 
                            $current_year = date('Y');
                            for ($y = $current_year; $y >= $current_year - 5; $y--) {
                                echo '<option value="' . $y . '" ' . ($selected_year == $y ? 'selected' : '') . '>' . $y . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="month-year-group">
                        <label for="selected_month">Month (Optional)</label>
                        <select id="selected_month" name="selected_month" class="form-control">
                            <option value="">All Months</option>
                            <?php 
                            $months = [
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                            ];
                            foreach ($months as $num => $name) {
                                echo '<option value="' . $num . '" ' . ($selected_month == $num ? 'selected' : '') . '>' . $name . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                    </div>
                </div>
                <?php elseif ($report_type == 'yearly'): ?>
                <div class="month-year-selector">
                    <div class="month-year-group">
                        <label for="selected_year">Year (Optional)</label>
                        <select id="selected_year" name="selected_year" class="form-control">
                            <option value="">All Years</option>
                            <?php 
                            $current_year = date('Y');
                            for ($y = $current_year; $y >= $current_year - 10; $y--) {
                                echo '<option value="' . $y . '" ' . ($selected_year == $y ? 'selected' : '') . '>' . $y . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                    </div>
                </div>
                <?php elseif ($report_type == 'products'): ?>
                <div class="date-range-selector">
                    <div class="date-input-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>" required>
                    </div>
                    <div class="date-input-group">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>" required>
                    </div>
                    <div class="category-filter">
                        <div class="filter-group">
                            <div class="filter-label">Category Filter:</div>
                            <select id="category_filter" name="category_filter" class="form-control" style="width: auto;">
                                <?php 
                                if (isset($categories)) {
                                    foreach ($categories as $value => $label) {
                                        echo '<option value="' . $value . '" ' . ($category_filter == $value ? 'selected' : '') . '>';
                                        echo htmlspecialchars($label);
                                        echo '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <a href="?report_type=<?php echo $report_type; ?>&start_date=<?php echo $default_start_date; ?>&end_date=<?php echo $default_end_date; ?>&category_filter=all" 
                           class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Last 7 Days
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if ($report_type == 'products'): ?>
        <div class="product-stats-container">
            <div class="product-stat-card">
                <div class="stat-title">Best Selling Product</div>
                <div class="stat-content best-selling">
                    <?php echo isset($stats['best_selling_product']) && !empty($stats['best_selling_product']) ? $stats['best_selling_product'] : 'No data'; ?>
                </div>
            </div>
            
            <div class="product-stat-card">
                <div class="stat-title">Best Selling Category</div>
                <div class="stat-content best-selling">
                    <?php echo isset($stats['best_selling_category']) && !empty($stats['best_selling_category']) ? $stats['best_selling_category'] : 'No data'; ?>
                </div>
            </div>
            
            <div class="product-stat-card">
                <div class="stat-title">Total Quantity Sold</div>
                <div class="stat-value"><?php echo isset($total_quantity) ? number_format($total_quantity) : 0; ?></div>
                <div class="stat-label">units</div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($report_type != 'products'): ?>
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-label">Total Sales</div>
                <div class="stat-value">RM <?php echo number_format($total_sales, 2); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?php echo number_format($total_orders); ?></div>
            </div>
            
            <?php if ($report_type == 'daily'): ?>
            <div class="stat-card">
                <div class="stat-label">Selected Date</div>
                <div class="stat-value" style="font-size: 1.5rem;">
                    <?php echo date('l, M d, Y', strtotime($selected_date)); ?>
                </div>
            </div>
            <?php if (isset($stats['average_order_value'])): ?>
            <div class="stat-card">
                <div class="stat-label">Average Order Value</div>
                <div class="stat-value">RM <?php echo number_format($stats['average_order_value'], 2); ?></div>
            </div>
            <?php endif; ?>
            <?php if ($stats['busiest_hour']): ?>
            <div class="stat-card">
                <div class="stat-label">Busiest Hour</div>
                <div class="stat-value"><?php echo $stats['busiest_hour']; ?></div>
                <div class="stat-label">(<?php echo $stats['peak_hour_orders']; ?> orders)</div>
            </div>
            <?php endif; ?>
            
            <?php elseif ($report_type == 'weekly'): ?>
            <div class="stat-card">
                <div class="stat-label">Date Range</div>
                <div class="stat-value" style="font-size: 1.2rem;">
                    <?php echo date('M d, Y', strtotime($start_date)); ?> - <br>
                    <?php echo date('M d, Y', strtotime($end_date)); ?>
                </div>
            </div>
            <?php if (isset($stats['average_order_value'])): ?>
            <div class="stat-card">
                <div class="stat-label">Average Order Value</div>
                <div class="stat-value">RM <?php echo number_format($stats['average_order_value'], 2); ?></div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($report_type == 'monthly'): ?>
            <div class="stat-card">
                <div class="stat-label">Selected Year</div>
                <div class="stat-value" style="font-size: 2rem;"><?php echo $selected_year; ?></div>
            </div>
            <?php if ($selected_month): ?>
            <div class="stat-card">
                <div class="stat-label">Selected Month</div>
                <div class="stat-value" style="font-size: 1.5rem;">
                    <?php echo date('F', mktime(0, 0, 0, $selected_month, 1)); ?>
                </div>
            </div>
            <?php else: ?>
            <div class="stat-card">
                <div class="stat-label">Months with Data</div>
                <div class="stat-value"><?php echo count($monthly_sales); ?></div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($report_type == 'yearly'): ?>
            <?php if ($selected_year): ?>
            <div class="stat-card">
                <div class="stat-label">Selected Year</div>
                <div class="stat-value" style="font-size: 2rem;"><?php echo $selected_year; ?></div>
            </div>
            <?php else: ?>
            <div class="stat-card">
                <div class="stat-label">Years with Data</div>
                <div class="stat-value"><?php echo count($yearly_sales); ?></div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($daily_sales) || !empty($weekly_sales) || !empty($monthly_sales) || !empty($yearly_sales) || !empty($product_sales)): ?>
        <div class="btn-group">
            <a href="?export=1&report_type=<?php echo $report_type; ?>&selected_date=<?php echo $selected_date; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&selected_year=<?php echo $selected_year; ?>&selected_month=<?php echo $selected_month; ?>&category_filter=<?php echo $category_filter; ?>" 
               class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export to CSV
            </a>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <h2>
                <?php 
                $title = '';
                switch($report_type) {
                    case 'daily': 
                        $title = 'Daily Sales Report - ' . date('l, M d, Y', strtotime($selected_date));
                        break;
                    case 'weekly': 
                        $title = 'Weekly Sales Report - ' . date('M d, Y', strtotime($start_date)) . ' to ' . date('M d, Y', strtotime($end_date));
                        break;
                    case 'monthly': 
                        $title = 'Monthly Sales Report - ' . $selected_year;
                        if ($selected_month) {
                            $title .= ' ' . date('F', mktime(0, 0, 0, $selected_month, 1));
                        }
                        break;
                    case 'yearly': 
                        $title = 'Yearly Sales Report';
                        if ($selected_year) {
                            $title .= ' - ' . $selected_year;
                        }
                        break;
                    case 'products': 
                        $title = 'Product Sales Report - ' . date('M d, Y', strtotime($start_date)) . ' to ' . date('M d, Y', strtotime($end_date));
                        if ($category_filter !== 'all') {
                            $title .= ' (Category: ' . htmlspecialchars($category_filter) . ')';
                        }
                        break;
                }
                echo $title;
                ?>
            </h2>
            
            <?php if ($report_type == 'daily'): ?>
                <?php if (!empty($daily_sales)): ?>
                <div class="data-table">
                    <table class="hourly-table">
                        <thead>
                            <tr>
                                <th>Hour</th>
                                <th>Orders</th>
                                <th>Sales Amount (RM)</th>
                                <th>Average Order Value (RM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $hourly_data = [];
                            for ($h = 12; $h <= 23; $h++) {
                                $hourly_data[$h] = [
                                    'order_hour' => $h,
                                    'order_count' => 0,
                                    'hourly_total' => 0
                                ];
                            }
                            
                            foreach ($daily_sales as $sale) {
                                $hour = $sale['order_hour'];
                                if ($hour >= 12 && $hour <= 23) {
                                    $hourly_data[$hour] = $sale;
                                }
                            }
                            
                            foreach ($hourly_data as $sale): 
                            ?>
                            <tr>
                                <td><?php echo sprintf('%02d:00', $sale['order_hour']); ?></td>
                                <td><?php echo $sale['order_count']; ?></td>
                                <td>RM <?php echo number_format($sale['hourly_total'], 2); ?></td>
                                <td>RM <?php echo number_format($sale['order_count'] > 0 ? $sale['hourly_total'] / $sale['order_count'] : 0, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr style="background-color: #e8f4fd; font-weight: bold;">
                                <td>Total</td>
                                <td><?php echo $total_orders; ?></td>
                                <td>RM <?php echo number_format($total_sales, 2); ?></td>
                                <td>RM <?php echo number_format($total_orders > 0 ? $total_sales / $total_orders : 0, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-chart-line fa-3x" style="margin-bottom: 20px; color: #ddd;"></i>
                    <p>No sales data found for <?php echo date('M d, Y', strtotime($selected_date)); ?>.</p>
                </div>
                <?php endif; ?>
                
            <?php elseif ($report_type == 'weekly'): ?>
                <?php if (!empty($weekly_sales)): ?>
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Week</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Orders</th>
                                <th>Sales Amount (RM)</th>
                                <th>Average Order Value (RM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($weekly_sales as $sale): ?>
                            <tr>
                                <td>Week <?php echo $sale['week_number']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($sale['week_start'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($sale['week_end'])); ?></td>
                                <td><?php echo $sale['order_count']; ?></td>
                                <td>RM <?php echo number_format($sale['weekly_total'], 2); ?></td>
                                <td>RM <?php echo number_format($sale['weekly_total'] / $sale['order_count'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr style="background-color: #e8f4fd; font-weight: bold;">
                                <td colspan="3">Total</td>
                                <td><?php echo $total_orders; ?></td>
                                <td>RM <?php echo number_format($total_sales, 2); ?></td>
                                <td>RM <?php echo number_format($total_orders > 0 ? $total_sales / $total_orders : 0, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-chart-line fa-3x" style="margin-bottom: 20px; color: #ddd;"></i>
                    <p>No sales data found for the selected date range.</p>
                </div>
                <?php endif; ?>
                
            <?php elseif ($report_type == 'monthly'): ?>
                <?php if (!empty($monthly_sales)): ?>
                <div class="data-table">
                    <table>
                        <thead>
                            <?php if ($selected_month): ?>
                            <tr>
                                <th>Day</th>
                                <th>Orders</th>
                                <th>Sales Amount (RM)</th>
                                <th>Average Order Value (RM)</th>
                            </tr>
                            <?php else: ?>
                            <tr>
                                <th>Year</th>
                                <th>Month</th>
                                <th>Orders</th>
                                <th>Sales Amount (RM)</th>
                                <th>Average Order Value (RM)</th>
                            </tr>
                            <?php endif; ?>
                        </thead>
                        <tbody>
                            <?php if ($selected_month): ?>
                                <?php foreach ($monthly_sales as $day): ?>
                                <tr>
                                    <td>Day <?php echo $day['order_day']; ?> (<?php echo date('D', mktime(0, 0, 0, $selected_month, $day['order_day'], $selected_year)); ?>)</td>
                                    <td><?php echo $day['order_count']; ?></td>
                                    <td>RM <?php echo number_format($day['daily_total'], 2); ?></td>
                                    <td>RM <?php echo number_format($day['order_count'] > 0 ? $day['daily_total'] / $day['order_count'] : 0, 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php foreach ($monthly_sales as $sale): ?>
                                <tr>
                                    <td><?php echo $sale['order_year']; ?></td>
                                    <td><?php echo date('F', mktime(0, 0, 0, $sale['order_month'], 1)); ?></td>
                                    <td><?php echo $sale['order_count']; ?></td>
                                    <td>RM <?php echo number_format($sale['monthly_total'], 2); ?></td>
                                    <td>RM <?php echo number_format($sale['monthly_total'] / $sale['order_count'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr style="background-color: #e8f4fd; font-weight: bold;">
                                <?php if ($selected_month): ?>
                                <td>Total for <?php echo date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)); ?></td>
                                <?php else: ?>
                                <td colspan="2">Total for <?php echo $selected_year; ?></td>
                                <?php endif; ?>
                                <td><?php echo $total_orders; ?></td>
                                <td>RM <?php echo number_format($total_sales, 2); ?></td>
                                <td>RM <?php echo number_format($total_orders > 0 ? $total_sales / $total_orders : 0, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-chart-line fa-3x" style="margin-bottom: 20px; color: #ddd;"></i>
                    <p>No sales data found for the selected criteria.</p>
                </div>
                <?php endif; ?>
                
            <?php elseif ($report_type == 'yearly'): ?>
                <?php if (!empty($yearly_sales)): ?>
                <div class="data-table">
                    <table>
                        <thead>
                            <?php if ($selected_year): ?>
                            <tr>
                                <th>Month</th>
                                <th>Orders</th>
                                <th>Sales Amount (RM)</th>
                                <th>Average Order Value (RM)</th>
                            </tr>
                            <?php else: ?>
                            <tr>
                                <th>Year</th>
                                <th>Orders</th>
                                <th>Sales Amount (RM)</th>
                                <th>Average Order Value (RM)</th>
                            </tr>
                            <?php endif; ?>
                        </thead>
                        <tbody>
                            <?php if ($selected_year): ?>
                                <?php foreach ($yearly_sales as $month): ?>
                                <tr>
                                    <td><?php echo date('F', mktime(0, 0, 0, $month['order_month'], 1)); ?></td>
                                    <td><?php echo $month['order_count']; ?></td>
                                    <td>RM <?php echo number_format($month['monthly_total'], 2); ?></td>
                                    <td>RM <?php echo number_format($month['monthly_total'] / $month['order_count'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php foreach ($yearly_sales as $sale): ?>
                                <tr>
                                    <td><?php echo $sale['order_year']; ?></td>
                                    <td><?php echo $sale['order_count']; ?></td>
                                    <td>RM <?php echo number_format($sale['yearly_total'], 2); ?></td>
                                    <td>RM <?php echo number_format($sale['yearly_total'] / $sale['order_count'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if ($selected_year): ?>
                            <tr style="background-color: #e8f4fd; font-weight: bold;">
                                <td>Total for <?php echo $selected_year; ?></td>
                                <td><?php echo $total_orders; ?></td>
                                <td>RM <?php echo number_format($total_sales, 2); ?></td>
                                <td>RM <?php echo number_format($total_orders > 0 ? $total_sales / $total_orders : 0, 2); ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-chart-line fa-3x" style="margin-bottom: 20px; color: #ddd;"></i>
                    <p>No sales data found for the selected criteria.</p>
                </div>
                <?php endif; ?>
                
            <?php elseif ($report_type == 'products'): ?>
                <?php if (!empty($product_sales)): ?>
                <?php if (isset($stats)): ?>
                <div class="alert alert-info">
                    <strong>Report Summary:</strong><br>
                    <strong>Date Range:</strong> <?php echo date('M d, Y', strtotime($start_date)); ?> to <?php echo date('M d, Y', strtotime($end_date)); ?><br>
                    <strong>Total Products Sold:</strong> <?php echo number_format($total_quantity); ?> units<br>
                    <strong>Total Sales Value:</strong> RM <?php echo number_format($total_sales, 2); ?><br>
                    <strong>Unique Products:</strong> <?php echo count($product_sales); ?> different products<br>
                    <strong>Categories:</strong> <?php echo count($category_data); ?> categories with sales
                </div>
                <?php endif; ?>
                
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Unit Price (RM)</th>
                                <th>Quantity Sold</th>
                                <th>Total Sales (RM)</th>
                                <th>Orders</th>
                                <th>Source Table</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            foreach ($product_sales as $product): 
                                $rank_class = $rank <= 3 ? "rank-$rank" : "";
                            ?>
                            <tr>
                                <td>
                                    <span class="product-rank <?php echo $rank_class; ?>"><?php echo $rank; ?></span>
                                </td>
                                <td><?php echo $product['product_id']; ?></td>
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                <td>RM <?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo number_format($product['total_quantity']); ?></td>
                                <td>RM <?php echo number_format($product['total_sales'], 2); ?></td>
                                <td><?php echo $product['order_count']; ?></td>
                                <td><span class="source-table-badge"><?php echo $product['source_table']; ?></span></td>
                            </tr>
                            <?php 
                            $rank++;
                            endforeach; 
                            ?>
                            <tr style="background-color: #e8f4fd; font-weight: bold;">
                                <td colspan="5">Total</td>
                                <td><?php echo number_format($total_quantity); ?></td>
                                <td>RM <?php echo number_format($total_sales, 2); ?></td>
                                <td><?php echo $total_orders; ?></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-chart-bar fa-3x" style="margin-bottom: 20px; color: #ddd;"></i>
                    <p>No product sales data found for the selected date range and category.</p>
                    <?php if ($category_filter !== 'all'): ?>
                    <p>Try selecting "All Categories" to see all products.</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($chart_data) && $report_type == 'daily'): ?>
        <div class="chart-container">
            <h2>Sales Trend - Orders by Hour (12:00 - 23:00)</h2>
            <canvas id="salesChart"></canvas>
        </div>
        <script>
            const dailyHours = [];
            const dailyOrders = [];
            
            for (let h = 12; h <= 23; h++) {
                dailyHours.push(h + ':00');
                dailyOrders.push(0);
            }
            
            <?php 
            $hourly_data = [];
            foreach ($chart_data as $sale) {
                $hour = $sale['order_hour'];
                $hourly_data[$hour] = $sale['order_count'];
            }
            
            for ($h = 12; $h <= 23; $h++) {
                $count = isset($hourly_data[$h]) ? $hourly_data[$h] : 0;
                echo "dailyOrders[" . ($h-12) . "] = $count;\n";
            }
            ?>
            
            const ctx = document.getElementById('salesChart').getContext('2d');
            const salesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dailyHours,
                    datasets: [{
                        label: 'Orders per Hour',
                        data: dailyOrders,
                        backgroundColor: 'rgba(255, 152, 0, 0.7)',
                        borderColor: 'rgba(255, 152, 0, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Daily Orders by Hour (12:00 - 23:00) - <?php echo date('M d, Y', strtotime($selected_date)); ?>'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Orders'
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Hour of Day'
                            }
                        }
                    }
                }
            });
        </script>
        <?php endif; ?>
        
        <?php if (!empty($chart_data) && $report_type == 'weekly'): ?>
        <div class="chart-container">
            <h2>Sales Trend - Orders by Day</h2>
            <canvas id="weeklyChart"></canvas>
        </div>
        <script>
            const weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const weekOrders = [0, 0, 0, 0, 0, 0, 0];
            
            <?php 
            foreach ($chart_data as $day) {
                $day_num = $day['day_number'] - 1; 
                $order_count = $day['order_count'];
                echo "weekOrders[$day_num] = $order_count;\n";
            }
            ?>
            
            const ctx2 = document.getElementById('weeklyChart').getContext('2d');
            const weeklyChart = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: weekDays,
                    datasets: [{
                        label: 'Orders per Day',
                        data: weekOrders,
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Weekly Orders (Monday - Sunday)'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Orders'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Day of Week'
                            }
                        }
                    }
                }
            });
        </script>
        <?php endif; ?>
        
        <?php if (!empty($chart_data) && $report_type == 'monthly' && $selected_month): ?>
        <div class="chart-container">
            <h2>Sales Trend - Orders by Day</h2>
            <canvas id="monthlyChart"></canvas>
        </div>
        <script>
            const monthDays = [];
            const monthOrders = [];
            
            const daysInMonth = new Date(<?php echo $selected_year; ?>, <?php echo $selected_month; ?>, 0).getDate();
            
            for (let d = 1; d <= daysInMonth; d++) {
                monthDays.push('Day ' + d);
                monthOrders.push(0);
            }
            
            <?php 
            $daily_orders = [];
            foreach ($chart_data as $day) {
                $day_num = $day['order_day'];
                $daily_orders[$day_num] = $day['order_count'];
            }
            
            for ($d = 1; $d <= cal_days_in_month(CAL_GREGORIAN, $selected_month, $selected_year); $d++) {
                $count = isset($daily_orders[$d]) ? $daily_orders[$d] : 0;
                echo "monthOrders[" . ($d-1) . "] = $count;\n";
            }
            ?>
            
            const ctx3 = document.getElementById('monthlyChart').getContext('2d');
            const monthlyChart = new Chart(ctx3, {
                type: 'line',
                data: {
                    labels: monthDays,
                    datasets: [{
                        label: 'Orders per Day',
                        data: monthOrders,
                        borderColor: 'rgba(46, 204, 113, 1)',
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Monthly Orders for <?php echo date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)); ?>'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Orders'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Day of Month'
                            }
                        }
                    }
                }
            });
        </script>
        <?php endif; ?>
        
        <?php if (!empty($chart_data) && $report_type == 'monthly' && !$selected_month): ?>
        <div class="chart-container">
            <h2>Sales Trend - Orders by Month</h2>
            <canvas id="monthlyChart"></canvas>
        </div>
        <script>
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const monthlyOrders = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            
            <?php 
            foreach ($chart_data as $month) {
                $month_num = $month['order_month'] - 1; 
                $order_count = $month['order_count'];
                echo "monthlyOrders[$month_num] = $order_count;\n";
            }
            ?>
            
            const ctx4 = document.getElementById('monthlyChart').getContext('2d');
            const monthlyChart = new Chart(ctx4, {
                type: 'bar',
                data: {
                    labels: monthNames,
                    datasets: [{
                        label: 'Orders per Month',
                        data: monthlyOrders,
                        backgroundColor: 'rgba(155, 89, 182, 0.7)',
                        borderColor: 'rgba(155, 89, 182, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Monthly Orders for <?php echo $selected_year; ?>'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Orders'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        }
                    }
                }
            });
        </script>
        <?php endif; ?>
        
        <?php if (!empty($chart_data) && $report_type == 'yearly' && $selected_year): ?>
        <div class="chart-container">
            <h2>Sales Trend - Orders by Month</h2>
            <canvas id="yearlyChart"></canvas>
        </div>
        <script>
            const yearMonthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const yearOrders = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            
            <?php 
            foreach ($chart_data as $month) {
                $month_num = $month['order_month'] - 1; 
                $order_count = $month['order_count'];
                echo "yearOrders[$month_num] = $order_count;\n";
            }
            ?>
            
            const ctx5 = document.getElementById('yearlyChart').getContext('2d');
            const yearlyChart = new Chart(ctx5, {
                type: 'line',
                data: {
                    labels: yearMonthNames,
                    datasets: [{
                        label: 'Orders per Month',
                        data: yearOrders,
                        borderColor: 'rgba(231, 76, 60, 1)',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Yearly Orders for <?php echo $selected_year; ?>'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Orders'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Month (Jan - Dec)'
                            }
                        }
                    }
                }
            });
        </script>
        <?php endif; ?>
        
        <?php if ($report_type == 'products' && !empty($category_data)): ?>
        <div class="chart-container">
            <h2>Sales Trend by Category (Quantity Sold)</h2>
            <canvas id="categoryChart"></canvas>
        </div>
        
        <div class="chart-container">
            <h2>Top 10 Best Selling Products</h2>
            <canvas id="topProductsChart"></canvas>
        </div>
        
        <script>
            const categoryLabels = [];
            const categoryQuantities = [];
            const categoryColors = [];
            
            <?php 
            $chart_limit = min(15, count($category_data));
            for ($i = 0; $i < $chart_limit; $i++) {
                $category = $category_data[$i];
                echo "categoryLabels.push('" . addslashes($category['category']) . "');\n";
                echo "categoryQuantities.push(" . $category['total_quantity'] . ");\n";
                
                $hue = ($i * 137) % 360; 
                echo "categoryColors.push('hsl(" . $hue . ", 70%, 60%)');\n";
            }
            ?>
            
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const categoryChart = new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        label: 'Quantity Sold (units)',
                        data: categoryQuantities,
                        backgroundColor: categoryColors,
                        borderColor: categoryColors.map(color => color.replace('60%)', '40%)')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Sales by Category - Quantity Sold'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.parsed.y.toLocaleString() + ' units';
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantity Sold (units)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Product Category'
                            }
                        }
                    }
                }
            });
            
            const productLabels = [];
            const productQuantities = [];
            const productColors = [];
            
            <?php 
            $top_limit = min(10, count($product_sales));
            for ($i = 0; $i < $top_limit; $i++) {
                $product = $product_sales[$i];
                $label = substr($product['product_name'], 0, 20);
                if (strlen($product['product_name']) > 20) {
                    $label .= '...';
                }
                echo "productLabels.push('" . addslashes($label) . "');\n";
                echo "productQuantities.push(" . $product['total_quantity'] . ");\n";
                
                $red = 255 - ($i * 20);
                $green = 100 + ($i * 15);
                $blue = 50;
                echo "productColors.push('rgba($red, $green, $blue, 0.7)');\n";
            }
            ?>
            
            const productCtx = document.getElementById('topProductsChart').getContext('2d');
            const topProductsChart = new Chart(productCtx, {
                type: 'bar',
                data: {
                    labels: productLabels,
                    datasets: [{
                        label: 'Quantity Sold',
                        data: productQuantities,
                        backgroundColor: productColors,
                        borderColor: productColors.map(color => color.replace('0.7)', '1)')),
                        borderWidth: 2
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Top 10 Best Selling Products'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const product = <?php echo json_encode(array_slice($product_sales, 0, 10)); ?>[context.dataIndex];
                                    let tooltip = [
                                        'Product: ' + product.product_name,
                                        'Quantity: ' + product.total_quantity.toLocaleString() + ' units',
                                        'Sales: RM ' + product.total_sales.toFixed(2),
                                        'Category: ' + product.category,
                                        'Source: ' + product.source_table
                                    ];
                                    return tooltip;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantity Sold (units)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        </script>
        <?php endif; ?>
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

        function changeReportType(type) {
            document.getElementById('reportType').value = type;
            
            let url = 'manage_report.php?report_type=' + type;
            
            if (type === 'daily') {
                const selectedDate = document.getElementById('selected_date') ? 
                    document.getElementById('selected_date').value : '<?php echo $default_date; ?>';
                url += '&selected_date=' + selectedDate;
            } else if (type === 'weekly') {
                const startDate = document.getElementById('start_date') ? 
                    document.getElementById('start_date').value : '<?php echo $default_start_date; ?>';
                const endDate = document.getElementById('end_date') ? 
                    document.getElementById('end_date').value : '<?php echo $default_end_date; ?>';
                url += '&start_date=' + startDate + '&end_date=' + endDate;
            } else if (type === 'products') {
                const startDate = document.getElementById('start_date') ? 
                    document.getElementById('start_date').value : '<?php echo $default_start_date; ?>';
                const endDate = document.getElementById('end_date') ? 
                    document.getElementById('end_date').value : '<?php echo $default_end_date; ?>';
                const categoryFilter = document.getElementById('category_filter') ? 
                    document.getElementById('category_filter').value : 'all';
                url += '&start_date=' + startDate + '&end_date=' + endDate + '&category_filter=' + categoryFilter;
            } else if (type === 'monthly') {
                const year = document.getElementById('selected_year') ? 
                    document.getElementById('selected_year').value : new Date().getFullYear();
                const month = document.getElementById('selected_month') ? 
                    document.getElementById('selected_month').value : '';
                url += '&selected_year=' + year + '&selected_month=' + month;
            } else if (type === 'yearly') {
                const year = document.getElementById('selected_year') ? 
                    document.getElementById('selected_year').value : '';
                url += '&selected_year=' + year;
            }
            
            window.location.href = url;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const selectedDateInput = document.getElementById('selected_date');
            const endDateInput = document.getElementById('end_date');
            const startDateInput = document.getElementById('start_date');
            
            if (selectedDateInput) {
                const today = new Date().toISOString().split('T')[0];
                selectedDateInput.max = today;
            }
            
            if (endDateInput && startDateInput) {
                const today = new Date().toISOString().split('T')[0];
                endDateInput.max = today;
                startDateInput.max = today;
                
                endDateInput.addEventListener('change', function() {
                    startDateInput.max = this.value;
                });
                
                startDateInput.addEventListener('change', function() {
                    endDateInput.min = this.value;
                });
                
                if (startDateInput.value && endDateInput.value) {
                    startDateInput.max = endDateInput.value;
                    endDateInput.min = startDateInput.value;
                }
            }
        });
    </script>
</body>
</html>
