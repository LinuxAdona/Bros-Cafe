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

switch ($period) {
    case 'today':
        $start_date = date('Y-m-d');
        break;
    case '7days':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        break;
    case '30days':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        break;
    case 'thismonth':
        $start_date = date('Y-m-01');
        break;
    case 'lastmonth':
        $start_date = date('Y-m-01', strtotime('-1 month'));
        $end_date = date('Y-m-t', strtotime('-1 month'));
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

// Sales by Day
$stmt = $conn->prepare("
    SELECT DATE(created_at) as date, 
           COUNT(*) as orders, 
           SUM(total_amount) as revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN :start AND :end
    AND status != 'cancelled'
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$daily_sales = $stmt->fetchAll();

// Top Selling Products
$stmt = $conn->prepare("
    SELECT p.name, 
           p.image,
           SUM(oi.quantity) as total_sold,
           SUM(oi.subtotal) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN :start AND :end
    AND o.status != 'cancelled'
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 10
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$top_products = $stmt->fetchAll();

// Sales by Category
$stmt = $conn->prepare("
    SELECT c.name, 
           SUM(oi.quantity) as items_sold,
           SUM(oi.subtotal) as revenue
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

// Sales by Payment Method
$stmt = $conn->prepare("
    SELECT payment_method, 
           COUNT(*) as count,
           SUM(total_amount) as revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN :start AND :end
    AND status != 'cancelled'
    GROUP BY payment_method
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$payment_methods = $stmt->fetchAll();

// Sales by Order Type
$stmt = $conn->prepare("
    SELECT order_type, 
           COUNT(*) as count,
           SUM(total_amount) as revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN :start AND :end
    AND status != 'cancelled'
    GROUP BY order_type
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$order_types = $stmt->fetchAll();

// Peak Hours
$stmt = $conn->prepare("
    SELECT HOUR(created_at) as hour,
           COUNT(*) as orders,
           SUM(total_amount) as revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN :start AND :end
    AND status != 'cancelled'
    GROUP BY HOUR(created_at)
    ORDER BY hour ASC
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$hourly_sales = $stmt->fetchAll();

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
</head>

<body class="bg-gray-100 font-['Montserrat']">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="flex flex-col w-64 text-white bg-gray-900">
            <div class="p-4 border-b border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <img src="../../assets/images/logo.png" alt="Logo" class="w-10 h-10 rounded-full">
                        <div class="ml-3">
                            <h1 class="text-lg font-bold">Bro's Cafe</h1>
                            <p class="text-xs text-gray-400"><?php echo ucfirst($current_user['role']); ?> Panel</p>
                        </div>
                    </div>
                    <button onclick="toggleSidebar()" class="text-gray-400 transition-colors hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <nav class="flex-1 p-4 overflow-y-auto">
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php"
                            class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="pos.php"
                            class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            POS
                        </a>
                    </li>
                    <li>
                        <a href="orders.php"
                            class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Orders
                        </a>
                    </li>
                    <li>
                        <a href="inventory.php"
                            class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            Inventory
                        </a>
                    </li>
                    <li>
                        <a href="analytics.php" class="flex items-center px-4 py-3 rounded-lg bg-amber-600">
                            <i class="w-5 h-5 mr-3 fa-solid fa-chart-simple"></i>
                            Analytics
                        </a>
                    </li>
                    <li>
                        <a href="products.php"
                            class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Products
                        </a>
                    </li>
                    <li>
                        <a href="users.php"
                            class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Employees
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="p-4 border-t border-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold"><?php echo $current_user['full_name']; ?></p>
                        <p class="text-xs text-gray-400">Administrator</p>
                    </div>
                    <a href="../logout.php" class="text-red-400 hover:text-red-300">
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
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <button onclick="toggleSidebar()" id="hamburger-btn"
                            class="p-3 mr-4 text-white transition-all rounded-full shadow-lg bg-amber-600 hover:bg-amber-700 hover:shadow-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div>
                            <h2 class="text-3xl font-bold text-gray-800">Sales Analytics</h2>
                            <p class="text-gray-600">Comprehensive business insights and reports</p>
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

                <!-- Charts Row -->
                <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
                    <!-- Sales Trend Chart -->
                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Sales Trend</h3>
                        <canvas id="salesTrendChart"></canvas>
                    </div>

                    <!-- Category Distribution -->
                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Sales by Category</h3>
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>

                <!-- Payment & Order Type Charts -->
                <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
                    <!-- Payment Methods -->
                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Payment Methods</h3>
                        <canvas id="paymentChart"></canvas>
                    </div>

                    <!-- Order Types -->
                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Order Types</h3>
                        <canvas id="orderTypeChart"></canvas>
                    </div>
                </div>

                <!-- Peak Hours Chart -->
                <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Peak Hours</h3>
                    <canvas id="peakHoursChart"></canvas>
                </div>

                <!-- Top Products & Employee Performance -->
                <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
                    <!-- Top Products -->
                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Top Selling Products</h3>
                        <div class="space-y-4">
                            <?php foreach ($top_products as $index => $product): ?>
                                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                    <div class="flex items-center">
                                        <span
                                            class="flex items-center justify-center w-8 h-8 mr-3 text-white rounded-full bg-amber-600">
                                            <?php echo $index + 1; ?>
                                        </span>
                                        <div>
                                            <p class="font-medium text-gray-800"><?php echo $product['name']; ?></p>
                                            <p class="text-sm text-gray-500"><?php echo $product['total_sold']; ?> sold</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-800">
                                            ₱<?php echo number_format($product['revenue'], 2); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Employee Performance -->
                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Employee Performance</h3>
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
        // Sales Trend Chart
        const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
        new Chart(salesTrendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(function ($d) {
                            return date('M d', strtotime($d['date']));
                        }, $daily_sales)); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_column($daily_sales, 'revenue')); ?>,
                    borderColor: 'rgb(217, 119, 6)',
                    backgroundColor: 'rgba(217, 119, 6, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($category_sales, 'name')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($category_sales, 'revenue')); ?>,
                    backgroundColor: [
                        'rgba(217, 119, 6, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });

        // Payment Methods Chart
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        new Chart(paymentCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_map('ucfirst', array_column($payment_methods, 'payment_method'))); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($payment_methods, 'count')); ?>,
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(251, 191, 36, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });

        // Order Type Chart
        const orderTypeCtx = document.getElementById('orderTypeChart').getContext('2d');
        new Chart(orderTypeCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map('ucfirst', array_column($order_types, 'order_type'))); ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?php echo json_encode(array_column($order_types, 'count')); ?>,
                    backgroundColor: 'rgba(217, 119, 6, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Peak Hours Chart
        const peakHoursCtx = document.getElementById('peakHoursChart').getContext('2d');
        new Chart(peakHoursCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function ($h) {
                            return $h['hour'] . ':00';
                        }, $hourly_sales)); ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?php echo json_encode(array_column($hourly_sales, 'orders')); ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>