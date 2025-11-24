<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

$db = new Database();
$conn = $db->getConnection();

// Get all products with inventory
$stmt = $conn->query("
    SELECT p.*, c.name as category_name, i.quantity, i.reorder_level, i.last_restocked, i.unit
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN inventory i ON p.id = i.product_id
    ORDER BY p.name
");
$products = $stmt->fetchAll();

// Get low stock items
$stmt = $conn->query("
    SELECT p.name, i.quantity, i.reorder_level
    FROM products p
    JOIN inventory i ON p.id = i.product_id
    WHERE i.quantity <= i.reorder_level
");
$low_stock = $stmt->fetchAll();

// Get inventory analytics data
$stmt = $conn->query("
    SELECT 
        c.name as category,
        COUNT(p.id) as product_count,
        SUM(i.quantity) as total_stock,
        AVG(i.quantity) as avg_stock,
        SUM(CASE WHEN i.quantity <= i.reorder_level THEN 1 ELSE 0 END) as low_stock_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    LEFT JOIN inventory i ON p.id = i.product_id
    GROUP BY c.id, c.name
");
$category_analytics = $stmt->fetchAll();

// Get stock status distribution
$stmt = $conn->query("
    SELECT 
        SUM(CASE WHEN i.quantity > i.reorder_level THEN 1 ELSE 0 END) as in_stock,
        SUM(CASE WHEN i.quantity <= i.reorder_level AND i.quantity > 0 THEN 1 ELSE 0 END) as low_stock,
        SUM(CASE WHEN i.quantity = 0 THEN 1 ELSE 0 END) as out_of_stock
    FROM inventory i
");
$stock_distribution = $stmt->fetch();

// Get recent restocks
$stmt = $conn->query("
    SELECT p.name, i.last_restocked, i.quantity
    FROM inventory i
    JOIN products p ON i.product_id = p.id
    WHERE i.last_restocked IS NOT NULL
    ORDER BY i.last_restocked DESC
    LIMIT 5
");
$recent_restocks = $stmt->fetchAll();

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Bro's Cafe</title>
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
    <link rel="stylesheet" href="../../assets/css/inventory.css">
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
                                class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                                <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                <span class="ml-3 sidebar-text">Dashboard</span>
                            </a>
                        </li>
                    <?php endif; ?>
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
                            class="flex items-center px-4 py-3 rounded-lg bg-amber-600">
                            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span class="ml-3 sidebar-text">Inventory</span>
                        </a>
                    </li>
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
            <div class="flex items-center bg-white shadow-lg p-6">
                <div class="flex flex-col justify-center">
                    <h2 class="text-3xl font-bold text-gray-800">Inventory Management</h2>
                    <p class="text-md text-gray-600">Track and manage product stock levels</p>
                </div>
            </div>
            <div class="p-6">
                <!-- Low Stock Alert -->
                <?php if (count($low_stock) > 0): ?>
                    <div class="p-4 mb-6 border-l-4 border-red-500 bg-red-50">
                        <div class="flex">
                            <div class="shrink-0">
                                <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Low Stock Alert</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="pl-5 space-y-1 list-disc">
                                        <?php foreach ($low_stock as $item): ?>
                                            <li><?php echo $item['name']; ?>: <?php echo $item['quantity']; ?> remaining
                                                (reorder at <?php echo $item['reorder_level']; ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-4">
                    <div class="p-6 bg-white rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-500 rounded-md shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm text-gray-500">Total Products</p>
                                <p class="text-2xl font-semibold text-gray-900"><?php echo count($products); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-red-500 rounded-md shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm text-gray-500">Low Stock Items</p>
                                <p class="text-2xl font-semibold text-gray-900"><?php echo count($low_stock); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-500 rounded-md shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm text-gray-500">Available Items</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    <?php echo count(array_filter($products, fn($p) => $p['quantity'] > $p['reorder_level'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-amber-500 rounded-md shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm text-gray-500">Out of Stock</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    <?php echo count(array_filter($products, fn($p) => $p['quantity'] == 0)); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (isAdmin()): ?>
                    <!-- Analytics Section -->
                    <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
                        <!-- Stock Status Chart -->
                        <div class="p-6 bg-white rounded-lg shadow">
                            <h3 class="mb-4 text-lg font-semibold text-gray-800">Stock Status Distribution</h3>
                            <div class="h-64">
                                <canvas id="stockStatusChart"></canvas>
                            </div>
                        </div>

                        <!-- Category Stock Chart -->
                        <div class="p-6 bg-white rounded-lg shadow">
                            <h3 class="mb-4 text-lg font-semibold text-gray-800">Stock by Category</h3>
                            <div class="h-64">
                                <canvas id="categoryStockChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Restocks and Category Analytics -->
                    <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
                        <!-- Recent Restocks -->
                        <div class="p-6 bg-white rounded-lg shadow">
                            <h3 class="mb-4 text-lg font-semibold text-gray-800">Recent Restocks</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                Product</th>
                                            <th
                                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                Date</th>
                                            <th
                                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php if (count($recent_restocks) > 0): ?>
                                            <?php foreach ($recent_restocks as $restock): ?>
                                                <tr>
                                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">
                                                        <?php echo $restock['name']; ?></td>
                                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                                                        <?php echo date('M d, Y', strtotime($restock['last_restocked'])); ?></td>
                                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                                                        <?php echo $restock['quantity']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="px-4 py-3 text-sm text-center text-gray-500">No recent
                                                    restocks</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Category Analytics -->
                        <div class="p-6 bg-white rounded-lg shadow">
                            <h3 class="mb-4 text-lg font-semibold text-gray-800">Category Analytics</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                Category</th>
                                            <th
                                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                Products</th>
                                            <th
                                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                Low Stock</th>
                                            <th
                                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                Avg Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($category_analytics as $category): ?>
                                            <tr>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">
                                                    <?php echo $category['category']; ?></td>
                                                <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                                                    <?php echo $category['product_count']; ?></td>
                                                <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                                                    <?php echo $category['low_stock_count']; ?></td>
                                                <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                                                    <?php echo round($category['avg_stock'], 1); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Products Table -->
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Product Inventory</h3>
                            <div class="flex space-x-2">
                                <button onclick="exportInventory()"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                    Export
                                </button>
                                <button onclick="openBulkRestockModal()"
                                    class="px-4 py-2 text-sm font-medium text-white rounded-md bg-amber-600 hover:bg-amber-700">
                                    Bulk Restock
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Product</th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Category</th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Stock</th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Reorder Level</th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Last Restocked</th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo $product['name']; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            <?php echo $product['category_name']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?php echo $product['quantity']; ?>
                                                <?php echo $product['unit'] ?? 'servings'; ?></div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            <?php echo $product['reorder_level']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($product['quantity'] == 0): ?>
                                                <span
                                                    class="inline-flex px-2 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full">Out
                                                    of Stock</span>
                                            <?php elseif ($product['quantity'] <= $product['reorder_level']): ?>
                                                <span
                                                    class="inline-flex px-2 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full">Low
                                                    Stock</span>
                                            <?php else: ?>
                                                <span
                                                    class="inline-flex px-2 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">In
                                                    Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            <?php echo $product['last_restocked'] ? date('M d, Y', strtotime($product['last_restocked'])) : 'Never'; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <button
                                                onclick="openRestockModal(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['quantity']; ?>)"
                                                class="mr-3 text-amber-600 hover:text-amber-900">Restock</button>
                                            <button
                                                onclick="openAdjustModal(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['quantity']; ?>)"
                                                class="text-blue-600 hover:text-blue-900">Adjust</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Restock Modal -->
    <div id="restockModal" class="fixed inset-0 z-50 hidden w-full h-full overflow-y-auto bg-gray-600 bg-opacity-50">
        <div class="relative p-5 mx-auto bg-white border rounded-md shadow-lg top-20 w-96">
            <div class="mt-3">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Restock Product</h3>
                <form id="restockForm" onsubmit="submitRestock(event)">
                    <input type="hidden" id="restock_product_id">
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700">Product</label>
                        <p id="restock_product_name" class="font-semibold text-gray-900"></p>
                    </div>
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700">Current Stock</label>
                        <p id="restock_current_stock" class="text-gray-900"></p>
                    </div>
                    <div class="mb-4">
                        <label for="restock_quantity" class="block mb-2 text-sm font-medium text-gray-700">Add
                            Quantity</label>
                        <input type="number" id="restock_quantity" min="1" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                    </div>
                    <div class="mb-4">
                        <label for="restock_notes" class="block mb-2 text-sm font-medium text-gray-700">Notes
                            (Optional)</label>
                        <textarea id="restock_notes" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-amber-500 focus:border-amber-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRestockModal()"
                            class="px-4 py-2 text-gray-700 bg-gray-300 rounded-md hover:bg-gray-400">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-white rounded-md bg-amber-600 hover:bg-amber-700">Restock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Restock Modal -->
    <div id="bulkRestockModal"
        class="fixed inset-0 z-50 hidden w-full h-full overflow-y-auto bg-gray-600 bg-opacity-50">
        <div class="relative p-5 mx-auto bg-white border rounded-md shadow-lg top-20 w-96">
            <div class="mt-3">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Bulk Restock</h3>
                <form id="bulkRestockForm" onsubmit="submitBulkRestock(event)">
                    <div class="mb-4">
                        <label for="bulk_category" class="block mb-2 text-sm font-medium text-gray-700">Category</label>
                        <select id="bulk_category"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                            <option value="">All Categories</option>
                            <?php foreach ($category_analytics as $category): ?>
                                <option value="<?php echo $category['category']; ?>"><?php echo $category['category']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="bulk_quantity" class="block mb-2 text-sm font-medium text-gray-700">Add
                            Quantity</label>
                        <input type="number" id="bulk_quantity" min="1" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                    </div>
                    <div class="mb-4">
                        <label for="bulk_notes" class="block mb-2 text-sm font-medium text-gray-700">Notes
                            (Optional)</label>
                        <textarea id="bulk_notes" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-amber-500 focus:border-amber-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeBulkRestockModal()"
                            class="px-4 py-2 text-gray-700 bg-gray-300 rounded-md hover:bg-gray-400">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-white rounded-md bg-amber-600 hover:bg-amber-700">Restock All</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize charts when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Stock Status Chart
            const stockStatusCtx = document.getElementById('stockStatusChart').getContext('2d');
            const stockStatusChart = new Chart(stockStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                    datasets: [{
                        data: [
                            <?php echo $stock_distribution['in_stock']; ?>,
                            <?php echo $stock_distribution['low_stock']; ?>,
                            <?php echo $stock_distribution['out_of_stock']; ?>
                        ],
                        backgroundColor: [
                            'rgb(16, 185, 129)', // Green
                            'rgb(245, 158, 11)', // Amber
                            'rgb(239, 68, 68)' // Red
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
                                    return label + ': ' + value + ' items (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });

            // Category Stock Chart
            const categoryStockCtx = document.getElementById('categoryStockChart').getContext('2d');
            const categoryStockChart = new Chart(categoryStockCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($category_analytics, 'category')); ?>,
                    datasets: [{
                        label: 'Total Stock',
                        data: <?php echo json_encode(array_column($category_analytics, 'total_stock')); ?>,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 2,
                        borderRadius: 5
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
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
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
                                    size: 11,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });

        // Export inventory function
        function exportInventory() {
            // In a real implementation, this would generate a CSV or PDF
            alert('Export functionality would be implemented here');
        }

        // Bulk restock modal functions
        function openBulkRestockModal() {
            document.getElementById('bulkRestockModal').classList.remove('hidden');
        }

        function closeBulkRestockModal() {
            document.getElementById('bulkRestockModal').classList.add('hidden');
        }

        function submitBulkRestock(event) {
            event.preventDefault();
            // In a real implementation, this would submit the bulk restock form
            alert('Bulk restock functionality would be implemented here');
            closeBulkRestockModal();
        }
    </script>
    <script src="../../assets/js/inventory.js"></script>
    <script src="../../assets/js/admin.js"></script>
</body>

</html>
