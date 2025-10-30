<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

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

// Recent orders
$stmt = $conn->query("
    SELECT o.*, u.full_name as employee_name 
    FROM orders o 
    LEFT JOIN users u ON o.employee_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$recent_orders = $stmt->fetchAll();

// Top selling products (last 30 days)
$stmt = $conn->query("
    SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND o.status != 'cancelled'
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");
$top_products = $stmt->fetchAll();

// Sales by day (last 7 days)
$stmt = $conn->query("
    SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status != 'cancelled'
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$daily_sales = $stmt->fetchAll();

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bro's Cafe</title>
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
</head>

<body class="bg-gray-100 font-['Montserrat']">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="flex flex-col w-64 text-white bg-gray-900">
            <div class="p-4 border-b border-gray-800">
                <div class="flex items-center">
                    <img src="../../assets/images/logo.png" alt="Logo" class="w-10 h-10 rounded-full">
                    <div class="ml-3">
                        <h1 class="text-lg font-bold">Bro's Cafe</h1>
                        <p class="text-xs text-gray-400">Admin Panel</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="flex items-center px-4 py-3 rounded-lg bg-amber-600">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="../employee/pos.php"
                            class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            POS
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
                            Users
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
                    <a href="../pages/logout.php" class="text-red-400 hover:text-red-300">
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
                <div class="mb-6">
                    <h2 class="text-3xl font-bold text-gray-800">Dashboard</h2>
                    <p class="text-gray-600">Welcome back, <?php echo $current_user['full_name']; ?>!</p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2 lg:grid-cols-4">
                    <div class="p-6 bg-white rounded-lg shadow-lg">
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

                    <div class="p-6 bg-white rounded-lg shadow-lg">
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

                    <div class="p-6 bg-white rounded-lg shadow-lg">
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

                    <div class="p-6 bg-white rounded-lg shadow-lg">
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

                <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
                    <!-- Recent Orders -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Recent Orders</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <?php foreach ($recent_orders as $order): ?>
                                    <div
                                        class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo $order['order_number']; ?></p>
                                            <p class="text-sm text-gray-500">
                                                <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-semibold text-amber-600">
                                                <?php echo formatCurrency($order['total_amount']); ?></p>
                                            <span
                                                class="inline-block px-2 py-1 text-xs rounded-full
                                            <?php echo $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Top Products -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Top Selling Products (30 Days)</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <?php foreach ($top_products as $product): ?>
                                    <div
                                        class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo $product['name']; ?></p>
                                            <p class="text-sm text-gray-500"><?php echo $product['total_sold']; ?> sold</p>
                                        </div>
                                        <p class="font-semibold text-green-600">
                                            <?php echo formatCurrency($product['revenue']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Chart -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Sales Overview (Last 7 Days)</h3>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="px-4 py-2 font-semibold text-left text-gray-700">Date</th>
                                        <th class="px-4 py-2 font-semibold text-left text-gray-700">Orders</th>
                                        <th class="px-4 py-2 font-semibold text-left text-gray-700">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($daily_sales as $day): ?>
                                        <tr class="border-b border-gray-100">
                                            <td class="px-4 py-3 text-gray-900">
                                                <?php echo date('M d, Y', strtotime($day['date'])); ?></td>
                                            <td class="px-4 py-3 text-gray-900"><?php echo $day['orders']; ?></td>
                                            <td class="px-4 py-3 font-semibold text-green-600">
                                                <?php echo formatCurrency($day['revenue']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>