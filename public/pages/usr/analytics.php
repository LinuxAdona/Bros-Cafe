<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// Get date range filter
$period = isset($_GET['period']) ? $_GET['period'] : '7days';
$start_date = '';
$end_date = date('Y-m-d');

// Set period label for display
$period_label = '';
switch ($period) {
    case 'today':
        $start_date = date('Y-m-d');
        $period_label = 'Today';
        break;
    case '7days':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $period_label = 'Last 7 Days';
        break;
    case '30days':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $period_label = 'Last 30 Days';
        break;
    case 'thismonth':
        $start_date = date('Y-m-01');
        $period_label = 'This Month';
        break;
    case 'lastmonth':
        $start_date = date('Y-m-01', strtotime('-1 month'));
        $end_date = date('Y-m-t', strtotime('-1 month'));
        $period_label = 'Last Month';
        break;
}

// Total Revenue
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(total_amount), 0) as revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN :start AND :end
    AND status != 'cancelled'
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$total_revenue = $stmt->fetch()['revenue'];

// Total Orders
$stmt = $conn->prepare("
    SELECT COUNT(*) as count
    FROM orders
    WHERE DATE(created_at) BETWEEN :start AND :end
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$total_orders = $stmt->fetch()['count'];

// Average Order Value
$avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;

// Total Items Sold
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(oi.quantity), 0) as total
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN :start AND :end
    AND o.status != 'cancelled'
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$total_items = $stmt->fetch()['total'];

// Top selling products
$stmt = $conn->prepare("
    SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN :start AND :end
    AND o.status != 'cancelled'
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$top_products = $stmt->fetchAll();

// Sales by day
$stmt = $conn->prepare("
    SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN :start AND :end
    AND status != 'cancelled'
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$daily_sales = $stmt->fetchAll();

// Sales by category
$stmt = $conn->prepare("
    SELECT c.name, SUM(oi.subtotal) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN :start AND :end
    AND o.status != 'cancelled'
    GROUP BY c.id
    ORDER BY revenue DESC
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$category_sales = $stmt->fetchAll();

// Hourly sales pattern
$stmt = $conn->prepare("
    SELECT HOUR(created_at) as hour, COUNT(*) as orders, SUM(total_amount) as revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN :start AND :end
    AND status != 'cancelled'
    GROUP BY HOUR(created_at)
    ORDER BY hour ASC
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$hourly_sales = $stmt->fetchAll();

// Order Types
$stmt = $conn->prepare("
    SELECT order_type, COUNT(*) as count
    FROM orders
    WHERE DATE(created_at) BETWEEN :start AND :end
    AND status != 'cancelled'
    GROUP BY order_type
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$order_types = $stmt->fetchAll();

// Employee Performance
$stmt = $conn->prepare("
    SELECT u.full_name,
           COUNT(o.id) as orders_processed,
           SUM(o.total_amount) as revenue_generated
    FROM orders o
    JOIN users u ON o.employee_id = u.id
    WHERE DATE(o.created_at) BETWEEN :start AND :end
    AND o.status != 'cancelled'
    GROUP BY o.employee_id
    ORDER BY revenue_generated DESC
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$employee_performance = $stmt->fetchAll();

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Bro's Cafe</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="icon" type="image/png" href="../../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Prevent sidebar jitter on page load -->
    <script>
        (function() {
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                document.documentElement.classList.add('sidebar-collapsed-init');
            }
        })();
    </script>
    <style>
        /* Apply collapsed state immediately to prevent jitter */
        .sidebar-collapsed-init #sidebar {
            width: 5rem;
        }

        .sidebar-collapsed-init #sidebar .sidebar-text,
        .sidebar-collapsed-init #sidebar .sidebar-logo-text {
            display: none;
        }

        .sidebar-collapsed-init #sidebar .sidebar-logo {
            justify-content: center;
        }

        .sidebar-collapsed-init #sidebar .logo-content {
            display: none;
        }

        .sidebar-collapsed-init #sidebar .toggle-btn-collapsed {
            display: flex;
        }

        .sidebar-collapsed-init #sidebar .toggle-btn-expanded {
            display: none;
        }

        .sidebar-collapsed-init #sidebar nav ul li a {
            justify-content: center;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .sidebar-collapsed-init #sidebar .user-info {
            justify-content: center;
        }

        .sidebar-collapsed-init #sidebar .user-info>div {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-100 font-['Montserrat']">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="flex flex-col text-white bg-gray-900">
            <div class="p-4 border-b border-gray-800">
                <div class="flex items-center justify-between sidebar-logo">
                    <!-- Logo and text (shown when expanded) -->
                    <div class="flex items-center logo-content">
                        <img src="../../assets/images/logo.png" alt="Logo" class="flex-shrink-0 w-10 h-10 rounded-full">
                        <div class="ml-3 sidebar-logo-text">
                            <h1 class="text-lg font-bold">Bro's Cafe</h1>
                            <p class="text-xs text-gray-400"><?php echo ucfirst($current_user['role']); ?> Panel</p>
                        </div>
                    </div>

                    <!-- Toggle button when expanded -->
                    <button onclick="toggleSidebar()" data-tooltip="Collapse"
                        class="text-gray-400 transition-colors toggle-btn-expanded hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Toggle button when collapsed (replaces logo) -->
                    <button onclick="toggleSidebar()" data-tooltip="Expand"
                        class="flex items-center justify-center w-10 h-10 text-gray-400 transition-colors rounded-full toggle-btn-collapsed hover:text-white hover:bg-gray-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <nav class="flex-1 p-4 overflow-y-auto">
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" data-tooltip="Dashboard"
                            class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span class="ml-3 sidebar-text">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="pos.php" data-tooltip="POS"
                            class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="ml-3 sidebar-text">POS</span>
                        </a>
                    </li>
                    <li>
                        <a href="orders.php" data-tooltip="Orders"
                            class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span class="ml-3 sidebar-text">Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="inventory.php" data-tooltip="Inventory"
                            class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span class="ml-3 sidebar-text">Inventory</span>
                        </a>
                    </li>
                    <li>
                        <a href="analytics.php" data-tooltip="Analytics"
                            class="flex items-center px-4 py-3 rounded-lg bg-amber-600">
                            <i class="flex-shrink-0 w-5 h-5 fa-solid fa-chart-simple"></i>
                            <span class="ml-3 sidebar-text">Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="products.php" data-tooltip="Products"
                            class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span class="ml-3 sidebar-text">Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php" data-tooltip="Employees"
                            class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span class="ml-3 sidebar-text">Employees</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="p-4 border-t border-gray-800">
                <div class="flex items-center justify-between user-info">
                    <div>
                        <p class="text-sm font-semibold"><?php echo $current_user['full_name']; ?></p>
                        <p class="text-xs text-gray-400"><?php echo ucfirst($current_user['role']); ?></p>
                    </div>
                    <a href="../logout.php" data-tooltip="Logout" class="text-red-400 hover:text-red-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="flex items-center justify-between bg-white shadow-lg p-6">
                <div class="flex items-center">
                    <div class="flex flex-col justify-center">
                        <h2 class="text-3xl font-bold text-gray-800">Sales Analytics</h2>
                        <p class="text-md text-gray-600">Comprehensive business insights and reports</p>
                    </div>
                </div>
                <div>
                    <form method="GET" class="flex items-center gap-2">
                        <select name="period" onchange="this.form.submit()"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                            <option value="today" <?php echo $period === 'today' ? 'selected' : ''; ?>>Today
                            </option>
                            <option value="7days" <?php echo $period === '7days' ? 'selected' : ''; ?>>Last 7 Days
                            </option>
                            <option value="30days" <?php echo $period === '30days' ? 'selected' : ''; ?>>Last 30
                                Days</option>
                            <option value="thismonth" <?php echo $period === 'thismonth' ? 'selected' : ''; ?>>This
                                Month</option>
                            <option value="lastmonth" <?php echo $period === 'lastmonth' ? 'selected' : ''; ?>>Last
                                Month</option>
                        </select>
                    </form>
                </div>
            </div>
            <div class="p-6">

                <!-- Key Metrics -->
                <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2 lg:grid-cols-4">
                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Total Revenue</p>
                                <p class="text-2xl font-bold text-gray-800">
                                    ₱<?php echo number_format($total_revenue, 2); ?></p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <i class="text-2xl text-green-600 fa-solid fa-peso-sign"></i>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Total Orders</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($total_orders); ?>
                                </p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <i class="text-2xl text-blue-600 fa-solid fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Avg Order Value</p>
                                <p class="text-2xl font-bold text-gray-800">
                                    ₱<?php echo number_format($avg_order_value, 2); ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-amber-100">
                                <i class="text-2xl fa-solid fa-chart-line text-amber-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Items Sold</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($total_items); ?>
                                </p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <i class="text-2xl text-purple-600 fa-solid fa-box"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="bg-white rounded-lg shadow-lg lg:col-span-2 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Top Selling Products
                            (<?php echo $period_label; ?>)</h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <?php foreach ($top_products as $index => $product): ?>
                                <div class="flex items-center p-3 border border-gray-200 rounded-lg">
                                    <div
                                        class="flex items-center justify-center flex-shrink-0 w-10 h-10 mr-3 text-white rounded-full bg-gradient-to-br from-amber-400 to-amber-600">
                                        <span class="font-bold"><?php echo $index + 1; ?></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            <?php echo $product['name']; ?></p>
                                        <p class="text-xs text-gray-500"><?php echo $product['total_sold']; ?> sold</p>
                                        <p class="text-sm font-semibold text-green-600">
                                            <?php echo formatCurrency($product['revenue']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
                    <!-- Sales Trend Chart -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Sales Trend (<?php echo $period_label; ?>)
                            </h3>
                        </div>
                        <div class="p-4">
                            <div style="height: 250px;">
                                <canvas id="salesTrendChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Category Sales Chart -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Sales by Category
                                (<?php echo $period_label; ?>)</h3>
                        </div>
                        <div class="p-4">
                            <div style="height: 250px;">
                                <canvas id="categorySalesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Peak Hours & Order Type Charts -->
                <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
                    <!-- Peak Hours Chart -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Peak Hours Analysis
                                (<?php echo $period_label; ?>)</h3>
                        </div>
                        <div class="p-4">
                            <div style="height: 250px;">
                                <canvas id="hourlyChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Order Types -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Order Types (<?php echo $period_label; ?>)
                            </h3>
                        </div>
                        <div class="p-4">
                            <div style="height: 250px;">
                                <canvas id="orderTypeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products & Employee Performance -->
                <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-1">
                    <!-- Employee Performance -->
                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Employee Performance
                            (<?php echo $period_label; ?>)</h3>
                        <div class="space-y-4">
                            <?php foreach ($employee_performance as $employee): ?>
                                <div class="p-4 border border-gray-200 rounded-lg">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="font-medium text-gray-800"><?php echo $employee['full_name']; ?></p>
                                        <p class="font-semibold text-gray-800">
                                            ₱<?php echo number_format($employee['revenue_generated'], 2); ?></p>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="mr-2 fa-solid fa-receipt"></i>
                                        <span><?php echo $employee['orders_processed']; ?> orders processed</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/admin.js"></script>
    <script>
        // Prepare data with fallbacks for empty datasets
        const dailySalesData = <?php echo json_encode($daily_sales); ?>;
        const categorySalesData = <?php echo json_encode($category_sales); ?>;
        const hourlySalesData = <?php echo json_encode($hourly_sales); ?>;
        const orderTypesData = <?php echo json_encode($order_types); ?>;

        // Sales Trend Chart
        const salesCtx = document.getElementById('salesTrendChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: dailySalesData.length > 0 ? dailySalesData.map(d => {
                    const date = new Date(d.date);
                    return date.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric'
                    });
                }) : ['No Data'],
                datasets: [{
                    label: 'Revenue',
                    data: dailySalesData.length > 0 ? dailySalesData.map(d => parseFloat(d.revenue)) : [0],
                    borderColor: 'rgb(245, 158, 11)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Category Sales Chart
        const categoryCtx = document.getElementById('categorySalesChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categorySalesData.length > 0 ? categorySalesData.map(c => c.name) : ['No Data'],
                datasets: [{
                    data: categorySalesData.length > 0 ? categorySalesData.map(c => parseFloat(c.revenue)) : [1],
                    backgroundColor: [
                        'rgb(245, 158, 11)',
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Order Type Chart
        const orderTypeCtx = document.getElementById('orderTypeChart').getContext('2d');
        new Chart(orderTypeCtx, {
            type: 'bar',
            data: {
                labels: orderTypesData.length > 0 ? orderTypesData.map(t => t.order_type.charAt(0).toUpperCase() + t
                    .order_type.slice(1)) : ['No Data'],
                datasets: orderTypesData.length > 0 ? orderTypesData.map((t, index) => ({
                    label: t.order_type.charAt(0).toUpperCase() + t.order_type.slice(1),
                    data: orderTypesData.map((item, i) => i === index ? parseInt(item.count) : 0),
                    backgroundColor: [
                        'rgba(245, 158, 11, 0.8)', // Amber for first type
                        'rgba(59, 130, 246, 0.8)', // Blue for second type
                        'rgba(16, 185, 129, 0.8)', // Green for third type
                        'rgba(139, 92, 246, 0.8)' // Purple for fourth type
                    ][index],
                    borderColor: [
                        'rgb(245, 158, 11)',
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(139, 92, 246)'
                    ][index],
                    borderWidth: 2
                })) : [{
                    label: 'No Data',
                    data: [0],
                    backgroundColor: 'rgba(209, 213, 219, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Hourly Sales Chart
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: hourlySalesData.length > 0 ? hourlySalesData.map(h => h.hour + ':00') : ['No Data'],
                datasets: [{
                    label: 'Orders',
                    data: hourlySalesData.length > 0 ? hourlySalesData.map(h => parseInt(h.orders)) : [0],
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>