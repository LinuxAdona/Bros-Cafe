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

// Recent orders
$stmt = $conn->query("
    SELECT o.*, u.full_name as employee_name 
    FROM orders o 
    LEFT JOIN users u ON o.employee_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$recent_orders = $stmt->fetchAll();

// Get pending orders count
$stmt = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$pending_orders = $stmt->fetch()['count'];

// Get low stock products
$stmt = $conn->query("
    SELECT p.name, i.quantity, i.reorder_level 
    FROM inventory i 
    JOIN products p ON i.product_id = p.id 
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

                <!-- Quick Actions & Alerts Grid -->
                <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-3">
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

                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Recent Orders</h3>
                    </div>
                    <div class="p-4" style="height: 250px; overflow-y: auto;">
                        <div class="space-y-3">
                            <?php foreach ($recent_orders as $order): ?>
                                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo $order['order_number']; ?></p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-amber-600">
                                            <?php echo formatCurrency($order['total_amount']); ?></p>
                                        <span
                                            class="inline-block px-2 py-0.5 text-xs rounded-full
                                            <?php echo $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
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
        const salesCtx = document.getElementById('salesTrendChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(fn($d) => date('M d', strtotime($d['date'])), $daily_sales)); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_map(fn($d) => $d['revenue'], $daily_sales)); ?>,
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
                labels: <?php echo json_encode(array_map(fn($c) => $c['name'], $category_sales)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map(fn($c) => $c['revenue'], $category_sales)); ?>,
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

        // Hourly Sales Chart
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(fn($h) => $h['hour'] . ':00', $hourly_sales)); ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?php echo json_encode(array_map(fn($h) => $h['orders'], $hourly_sales)); ?>,
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