<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// Get today's stats
$today = date('Y-m-d');

// Total sales today
$stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE DATE(created_at) = :today AND status != 'cancelled'");
$stmt->execute(['today' => $today]);
$today_sales = $stmt->fetch()['total'];

// Total orders today
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = :today");
$stmt->execute(['today' => $today]);
$today_orders = $stmt->fetch()['count'];

// Low stock items count
$stmt = $conn->query("SELECT COUNT(*) as count FROM inventory i WHERE i.quantity <= i.reorder_level");
$low_stock_count = $stmt->fetch()['count'];

// Recent orders with items
$stmt = $conn->query("
    SELECT o.*, u.full_name as employee_name 
    FROM orders o 
    LEFT JOIN users u ON o.employee_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$recent_orders = $stmt->fetchAll();

// Get order items for each recent order
foreach ($recent_orders as &$order) {
    $stmt = $conn->prepare("
        SELECT oi.*, p.name as product_name 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = :order_id
    ");
    $stmt->execute(['order_id' => $order['id']]);
    $order['items'] = $stmt->fetchAll();
}
unset($order); // Break reference

// Get pending orders count
$stmt = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$pending_orders = $stmt->fetch()['count'];

// Get low stock ingredients
$stmt = $conn->query("
    SELECT pi.name, i.quantity, i.reorder_level 
    FROM inventory i 
    JOIN ingredients pi ON i.ingredient_id = pi.id 
    WHERE i.quantity <= i.reorder_level 
    ORDER BY i.quantity ASC 
    LIMIT 5
");
$low_stock_items = $stmt->fetchAll();

// Get top selling products today
$stmt = $conn->prepare("
    SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) = :today AND o.status != 'cancelled'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
");
$stmt->execute(['today' => $today]);
$top_products = $stmt->fetchAll();

// Get active employees count
$stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'employee' AND status = 'active'");
$active_employees = $stmt->fetch()['count'];

// Get today's hourly sales for chart
$stmt = $conn->query("
    SELECT HOUR(created_at) as hour, 
           COUNT(*) as order_count,
           COALESCE(SUM(total_amount), 0) as revenue
    FROM orders 
    WHERE DATE(created_at) = CURDATE()
    AND status != 'cancelled'
    GROUP BY HOUR(created_at)
    ORDER BY hour ASC
");
$hourly_sales = $stmt->fetchAll();

// Fill in missing hours with zero sales (business hours: 8 AM to 10 PM)
$sales_by_hour = [];
foreach ($hourly_sales as $sale) {
    $sales_by_hour[$sale['hour']] = $sale;
}

$chart_data = [];
for ($hour = 8; $hour <= 22; $hour++) {
    $hour_label = date('g A', strtotime($hour . ':00'));
    $chart_data[] = [
        'hour' => $hour,
        'hour_label' => $hour_label,
        'order_count' => isset($sales_by_hour[$hour]) ? $sales_by_hour[$hour]['order_count'] : 0,
        'revenue' => isset($sales_by_hour[$hour]) ? $sales_by_hour[$hour]['revenue'] : 0
    ];
}

// Get sales by category for pie chart (today only)
$stmt = $conn->query("
    SELECT c.name as category,
           COUNT(DISTINCT o.id) as order_count,
           COALESCE(SUM(oi.quantity * oi.price), 0) as revenue
    FROM orders o
    INNER JOIN order_items oi ON o.id = oi.order_id
    INNER JOIN products p ON oi.product_id = p.id
    INNER JOIN categories c ON p.category_id = c.id
    WHERE DATE(o.created_at) = CURDATE()
    AND o.status != 'cancelled'
    GROUP BY c.id, c.name

");
$category_sales = $stmt->fetchAll();

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bro's Cafe</title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <!-- Prevent sidebar jitter on page load -->
    <script>
        (function() {
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                document.documentElement.classList.add('sidebar-collapsed-init');
            }
        })();
    </script>
</head>

<body class="bg-gray-100 font-['Montserrat']">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay"
        class="fixed inset-0 z-30 bg-black transition-opacity duration-300 opacity-0 pointer-events-none lg:hidden"
        onclick="toggleMobileSidebar()"></div>

    <div class="flex h-screen overflow-hidden flex-col lg:flex-row">
        <!-- Mobile Header -->
        <div class="lg:hidden bg-white border-b border-gray-200 flex items-center px-4 py-3 z-20">
            <button id="mobileSidebarBtn"
                class="p-2 text-gray-900 bg-gray-100 rounded-lg shadow transition-all duration-300 hover:bg-gray-200"
                onclick="toggleMobileSidebar()">
                <svg class="w-6 h-6 transition-transform duration-300" id="hamburgerIcon" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <h1 class="ml-4 text-lg font-bold text-gray-800">Bro's Cafe</h1>
        </div>

        <!-- Sidebar -->
        <aside id="sidebar"
            class="flex flex-col text-white bg-gray-900 fixed inset-y-0 left-0 z-40 w-64 transform -translate-x-full transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:w-64 shadow-2xl">
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
                    <?php if (isAdmin()): ?>
                        <li>
                            <a href="dashboard.php" data-tooltip="Dashboard"
                                class="flex items-center px-4 py-3 rounded-lg bg-amber-600">
                                <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                <span class="ml-3 sidebar-text">Dashboard</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (isEmployee()): ?>
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
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <li>
                            <a href="analytics.php" data-tooltip="Analytics"
                                class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                                <i class="flex-shrink-0 w-5 h-5 fa-solid fa-chart-simple"></i>
                                <span class="ml-3 sidebar-text">Analytics</span>
                            </a>
                        </li>
                    <?php endif; ?>
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
                    <?php if (isAdmin()): ?>
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
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="p-4 border-t border-gray-800">
                <div class="flex items-center justify-between user-info">
                    <div>
                        <p class="text-sm font-semibold"><?php echo $current_user['full_name']; ?></p>
                        <p class="text-xs text-gray-400">Administrator</p>
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
            <div class="bg-white shadow-lg">
                <div class="flex flex-col justify-center p-6">
                    <h2 class="text-3xl font-bold text-gray-800">Dashboard</h2>
                    <p class="text-md text-gray-600">Welcome back, <?php echo $current_user['full_name']; ?>!</p>
                </div>
            </div>
            <div class="p-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2 lg:grid-cols-4">
                    <div class="p-6 transition-transform bg-white rounded-lg shadow-lg hover:scale-105">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Today's Sales</p>
                                <p class="text-3xl font-bold text-gray-900"><?php echo formatCurrency($today_sales); ?>
                                </p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 transition-transform bg-white rounded-lg shadow-lg hover:scale-105">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Today's Orders</p>
                                <p class="text-3xl font-bold text-gray-900"><?php echo $today_orders; ?></p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 transition-transform bg-white rounded-lg shadow-lg hover:scale-105">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Low Stock Items</p>
                                <p class="text-3xl font-bold text-gray-900"><?php echo $low_stock_count; ?></p>
                            </div>
                            <div class="p-3 bg-red-100 rounded-full">
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 transition-transform bg-white rounded-lg shadow-lg hover:scale-105">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Avg Order Value</p>
                                <p class="text-3xl font-bold text-gray-900">
                                    <?php echo $today_orders > 0 ? formatCurrency($today_sales / $today_orders) : '₱0.00'; ?>
                                </p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Grid: Sales Charts -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
                    <!-- Today's Hourly Sales Chart -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Today's Sales</h3>
                        </div>
                        <div class="p-6">
                            <canvas id="hourlySalesChart" style="height: 300px;"></canvas>
                        </div>
                        <div class="px-6 pb-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-3 text-center rounded-lg bg-green-50">
                                    <p class="text-xs text-gray-600">Orders Today</p>
                                    <p class="text-2xl font-bold text-green-600">
                                        <?php echo array_sum(array_column($chart_data, 'order_count')); ?>
                                    </p>
                                </div>
                                <div class="p-3 text-center rounded-lg bg-amber-50">
                                    <p class="text-xs text-gray-600">Revenue Today</p>
                                    <p class="text-2xl font-bold text-amber-600">
                                        <?php echo formatCurrency(array_sum(array_column($chart_data, 'revenue'))); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales by Category Chart -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Today's Sales by Category</h3>
                        </div>
                        <div class="p-6 flex items-center justify-center">
                            <?php if (count($category_sales) > 0): ?>
                                <canvas id="categorySalesChart" style="height: 300px; max-width: 350px;"></canvas>
                            <?php else: ?>
                                <div class="py-12 text-center">
                                    <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    <p class="text-sm text-gray-500">No sales data for today</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (count($category_sales) > 0): ?>
                            <div class="px-6 pb-6">
                                <div class="space-y-2">
                                    <?php foreach (array_slice($category_sales, 0, 3) as $cat): ?>
                                        <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                                            <span
                                                class="text-sm font-medium text-gray-700"><?php echo $cat['category']; ?></span>
                                            <span
                                                class="text-sm font-bold text-amber-600"><?php echo formatCurrency($cat['revenue']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 mb-6">
                    <!-- Recent Orders -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Recent Orders</h3>
                        </div>
                        <div class="p-4" style="max-height: 262px; overflow-y: auto;">
                            <div class="space-y-3">
                                <?php foreach ($recent_orders as $order): ?>
                                    <div
                                        class="flex items-center justify-between p-3 transition-all border border-gray-200 rounded-lg hover:shadow-md">
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-gray-900">
                                                <?php echo $order['order_number']; ?>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?>
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="text-right">
                                                <p class="text-sm font-bold text-amber-600">
                                                    <?php echo formatCurrency($order['total_amount']); ?>
                                                </p>
                                                <span
                                                    class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full
                                                <?php echo $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'); ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </div>
                                            <button
                                                onclick="showOrderModal(<?php echo htmlspecialchars(json_encode($order)); ?>)"
                                                class="px-3 py-1.5 text-xs font-semibold text-white transition-all rounded-lg bg-amber-600 hover:bg-amber-700">
                                                View
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions, Alerts, and Recent Orders Grid -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Quick Actions</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <a href="pos.php"
                                class="flex items-center p-3 transition-all rounded-lg bg-amber-50 hover:bg-amber-100 group">
                                <div class="p-2 rounded-lg bg-amber-100 group-hover:bg-amber-200">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="font-semibold text-gray-900">New Order</p>
                                    <p class="text-xs text-gray-500">Create a new order</p>
                                </div>
                            </a>

                            <a href="products.php"
                                class="flex items-center p-3 transition-all rounded-lg bg-blue-50 hover:bg-blue-100 group">
                                <div class="p-2 rounded-lg bg-blue-100 group-hover:bg-blue-200">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="font-semibold text-gray-900">Add Product</p>
                                    <p class="text-xs text-gray-500">Add new menu item</p>
                                </div>
                            </a>

                            <a href="users.php"
                                class="flex items-center p-3 transition-all rounded-lg bg-green-50 hover:bg-green-100 group">
                                <div class="p-2 rounded-lg bg-green-100 group-hover:bg-green-200">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="font-semibold text-gray-900">Add Employee</p>
                                    <p class="text-xs text-gray-500">Register new staff</p>
                                </div>
                            </a>

                            <a href="analytics.php"
                                class="flex items-center p-3 transition-all rounded-lg bg-purple-50 hover:bg-purple-100 group">
                                <div class="p-2 rounded-lg bg-purple-100 group-hover:bg-purple-200">
                                    <i class="w-6 h-6 text-purple-600 fa-solid fa-chart-simple"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="font-semibold text-gray-900">View Analytics</p>
                                    <p class="text-xs text-gray-500">Detailed reports</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Top Products Today -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Top Products Today</h3>
                        </div>
                        <div class="p-4" style="max-height: 300px; overflow-y: auto;">
                            <?php if (count($top_products) > 0): ?>
                                <div class="space-y-3">
                                    <?php foreach ($top_products as $index => $product): ?>
                                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                                            <div class="flex items-center">
                                                <div
                                                    class="flex items-center justify-center flex-shrink-0 w-8 h-8 text-sm font-bold text-white rounded-full bg-amber-600">
                                                    <?php echo $index + 1; ?>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-semibold text-gray-900">
                                                        <?php echo $product['name']; ?></p>
                                                    <p class="text-xs text-gray-500"><?php echo $product['total_sold']; ?> sold
                                                    </p>
                                                </div>
                                            </div>
                                            <p class="text-sm font-bold text-amber-600">
                                                <?php echo formatCurrency($product['revenue']); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="py-8 text-sm text-center text-gray-400">No sales today yet</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- System Alerts -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">System Alerts</h3>
                        </div>
                        <div class="p-4 space-y-3" style="max-height: 300px; overflow-y: auto;">
                            <!-- Pending Orders Alert -->
                            <?php if ($pending_orders > 0): ?>
                                <a href="orders.php"
                                    class="flex items-start p-3 transition-all border-l-4 border-yellow-500 rounded-lg bg-yellow-50 hover:bg-yellow-100">
                                    <svg class="flex-shrink-0 w-5 h-5 mt-0.5 text-yellow-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm font-semibold text-yellow-800"><?php echo $pending_orders; ?>
                                            Pending Order<?php echo $pending_orders > 1 ? 's' : ''; ?></p>
                                        <p class="text-xs text-yellow-600">Action required</p>
                                    </div>
                                </a>
                            <?php endif; ?>

                            <!-- Low Stock Alert -->
                            <?php if ($low_stock_count > 0): ?>
                                <a href="inventory.php"
                                    class="flex items-start p-3 transition-all border-l-4 border-red-500 rounded-lg bg-red-50 hover:bg-red-100">
                                    <svg class="flex-shrink-0 w-5 h-5 mt-0.5 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm font-semibold text-red-800">Low Stock Alert</p>
                                        <p class="text-xs text-red-600"><?php echo $low_stock_count; ?>
                                            item<?php echo $low_stock_count > 1 ? 's need' : ' needs'; ?> restocking</p>
                                        <?php if (count($low_stock_items) > 0): ?>
                                            <ul class="mt-2 ml-3 space-y-1 text-xs text-red-700 list-disc">
                                                <?php foreach ($low_stock_items as $item): ?>
                                                    <li><?php echo $item['name']; ?> (<?php echo $item['quantity']; ?> left)</li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endif; ?>

                            <!-- Active Status -->
                            <div class="flex items-start p-3 border-l-4 border-green-500 rounded-lg bg-green-50">
                                <svg class="flex-shrink-0 w-5 h-5 mt-0.5 text-green-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm font-semibold text-green-800">System Active</p>
                                    <p class="text-xs text-green-600"><?php echo $active_employees; ?>
                                        employee<?php echo $active_employees > 1 ? 's' : ''; ?> online</p>
                                </div>
                            </div>

                            <?php if ($pending_orders == 0 && $low_stock_count == 0): ?>
                                <div class="flex items-center justify-center py-6">
                                    <div class="text-center">
                                        <svg class="w-12 h-12 mx-auto text-green-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="mt-2 text-sm font-medium text-gray-600">All systems normal</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="fixed inset-0 z-50 items-center justify-center hidden modal-backdrop">
        <div
            class="w-full max-w-md mx-4 overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl animate-modal">
            <!-- Modal Header -->
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-amber-500 to-amber-600">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-xl font-bold" id="modalOrderNumber">Order Receipt</h3>
                        <p class="text-sm opacity-90" id="modalDateTime"></p>
                    </div>
                    <button onclick="closeOrderModal()" class="text-white transition-colors hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4" style="max-height: 500px; overflow-y: auto;">
                <!-- Order Info -->
                <div class="p-4 rounded-lg bg-gray-50">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-500">Employee</p>
                            <p class="font-semibold text-gray-800" id="modalEmployee"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Status</p>
                            <span id="modalStatus"
                                class="inline-block px-2 py-1 text-xs font-semibold rounded-full"></span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Order Type</p>
                            <p class="font-semibold text-gray-800" id="modalOrderType"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Payment Method</p>
                            <p class="font-semibold text-gray-800" id="modalPayment"></p>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div>
                    <h4 class="mb-3 text-sm font-bold text-gray-700 uppercase">Order Items</h4>
                    <div id="modalItems" class="space-y-2">
                        <!-- Items will be inserted here by JavaScript -->
                    </div>
                </div>

                <!-- Total -->
                <div class="pt-4 border-t-2 border-gray-300">
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-bold text-gray-800">Total Amount</span>
                        <span id="modalTotal" class="text-2xl font-bold text-amber-600"></span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                <button onclick="closeOrderModal()"
                    class="w-full py-2 font-semibold text-white transition-all rounded-lg bg-amber-600 hover:bg-amber-700">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/admin.js"></script>
    <script>
        // Mobile sidebar toggle with smooth animations
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const hamburgerIcon = document.getElementById('hamburgerIcon');
            const mobileBtn = document.getElementById('mobileSidebarBtn');

            const isOpen = !sidebar.classList.contains('-translate-x-full');

            if (isOpen) {
                // Close sidebar
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                overlay.classList.remove('opacity-50');
                hamburgerIcon.innerHTML =
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
                hamburgerIcon.classList.remove('rotate-90');
                mobileBtn.classList.remove('bg-gray-200');
                mobileBtn.classList.add('bg-gray-100');
            } else {
                // Open sidebar
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('opacity-0', 'pointer-events-none');
                overlay.classList.add('opacity-50');
                hamburgerIcon.innerHTML =
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />';
                hamburgerIcon.classList.add('rotate-90');
                mobileBtn.classList.add('bg-gray-200');
                mobileBtn.classList.remove('bg-gray-100');
            }
        }

        // Close sidebar when clicking on nav links (mobile)
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('#sidebar nav a');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 1024) {
                        const sidebar = document.getElementById('sidebar');
                        if (!sidebar.classList.contains('-translate-x-full')) {
                            toggleMobileSidebar();
                        }
                    }
                });
            });
        });

        // Ensure sidebar is hidden on mobile by default
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const hamburgerIcon = document.getElementById('hamburgerIcon');
            const mobileBtn = document.getElementById('mobileSidebarBtn');

            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                overlay.classList.remove('opacity-50');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                hamburgerIcon.innerHTML =
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
                hamburgerIcon.classList.remove('rotate-90');
                mobileBtn.classList.remove('bg-gray-200');
                mobileBtn.classList.add('bg-gray-100');
            }
        });

        // Initial state
        if (window.innerWidth < 1024) {
            document.getElementById('sidebar').classList.add('-translate-x-full');
        }
        // Show order modal
        function showOrderModal(order) {
            // Set order details
            document.getElementById('modalOrderNumber').textContent = order.order_number;
            document.getElementById('modalDateTime').textContent = new Date(order.created_at).toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            document.getElementById('modalEmployee').textContent = order.employee_name || 'N/A';
            document.getElementById('modalOrderType').textContent = order.order_type.charAt(0).toUpperCase() + order
                .order_type.slice(1);
            document.getElementById('modalPayment').textContent = order.payment_method.charAt(0).toUpperCase() + order
                .payment_method.slice(1);

            // Set status with color
            const statusEl = document.getElementById('modalStatus');
            statusEl.textContent = order.status.charAt(0).toUpperCase() + order.status.slice(1);
            statusEl.className = 'inline-block px-2 py-1 text-xs font-semibold rounded-full ';
            if (order.status === 'completed') {
                statusEl.className += 'bg-green-100 text-green-800';
            } else if (order.status === 'pending') {
                statusEl.className += 'bg-yellow-100 text-yellow-800';
            } else {
                statusEl.className += 'bg-gray-100 text-gray-800';
            }

            // Set items
            const itemsContainer = document.getElementById('modalItems');
            itemsContainer.innerHTML = '';
            order.items.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'flex items-center justify-between p-3 rounded-lg bg-gray-50';
                itemDiv.innerHTML = `
                    <div class="flex items-center flex-1">
                        <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 text-sm font-bold rounded-full bg-amber-100 text-amber-600">
                            ${item.quantity}x
                        </div>
                        <div class="ml-3">
                            <p class="font-semibold text-gray-800">${item.product_name}</p>
                            <p class="text-xs text-gray-500">Size: <span class="font-medium">${item.size.charAt(0).toUpperCase() + item.size.slice(1)}</span></p>
                        </div>
                    </div>
                    <p class="font-semibold text-gray-700">₱${parseFloat(item.subtotal).toFixed(2)}</p>
                `;
                itemsContainer.appendChild(itemDiv);
            });

            // Set total
            document.getElementById('modalTotal').textContent = '₱' + parseFloat(order.total_amount).toFixed(2);

            // Show modal
            document.getElementById('orderModal').classList.remove('hidden');
            document.getElementById('orderModal').classList.add('flex');
        }

        // Close order modal
        function closeOrderModal() {
            document.getElementById('orderModal').classList.add('hidden');
            document.getElementById('orderModal').classList.remove('flex');
        }

        // Close modal when clicking outside
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });

        // Today's Hourly Sales Chart
        const hourlyCtx = document.getElementById('hourlySalesChart').getContext('2d');
        new Chart(hourlyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($chart_data, 'hour_label')); ?>,
                datasets: [{
                    label: 'Revenue (₱)',
                    data: <?php echo json_encode(array_column($chart_data, 'revenue')); ?>,
                    borderColor: 'rgb(245, 158, 11)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgb(245, 158, 11)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                family: 'Montserrat',
                                size: 12,
                                weight: 'bold'
                            },
                            color: '#374151'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: ₱' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            },
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 10,
                                weight: 'bold'
                            },
                            maxRotation: 45,
                            minRotation: 45
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Category Sales Pie Chart
        <?php if (count($category_sales) > 0): ?>
            const categoryCtx = document.getElementById('categorySalesChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_column($category_sales, 'category')); ?>,
                    datasets: [{
                        label: 'Revenue',
                        data: <?php echo json_encode(array_column($category_sales, 'revenue')); ?>,
                        backgroundColor: [
                            'rgb(245, 158, 11)', // Amber
                            'rgb(59, 130, 246)', // Blue
                            'rgb(16, 185, 129)', // Green
                            'rgb(239, 68, 68)', // Red
                            'rgb(168, 85, 247)', // Purple
                            'rgb(236, 72, 153)', // Pink
                        ],
                        borderColor: '#fff',
                        borderWidth: 3,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    family: 'Montserrat',
                                    size: 11
                                },
                                color: '#374151',
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return label + ': ₱' + value.toFixed(2) + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>