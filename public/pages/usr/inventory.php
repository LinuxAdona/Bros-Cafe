<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

$db = new Database();
$conn = $db->getConnection();

// Get filter parameters
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10; // Orders per page
$offset = ($page - 1) * $per_page;

// Build query based on filters
$where = ["1=1"];
$params = [];

if ($date_filter && $date_filter !== '') {
    $where[] = "DATE(i.last_restocked) = :date";
    $params['date'] = $date_filter;
}

if ($search) {
    $where[] = "(pi.name LIKE :search)";
    $params['search'] = "%$search%";
}

$where_clause = implode(' AND ', $where);

// Get all ingredients with inventory
$sql = "
    SELECT DISTINCT pi.*, i.quantity, i.reorder_level, i.last_restocked, i.unit
    FROM ingredients pi
    LEFT JOIN product_ingredients pii ON pi.id = pii.ingredient_id 
    LEFT JOIN products p ON pii.product_id = p.id 
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN inventory i ON pi.id = i.ingredient_id
    WHERE $where_clause
    GROUP BY pi.name
    ORDER BY i.last_restocked DESC
    LIMIT :limit OFFSET :offset
";
$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll();

// Get low stock items
$stmt = $conn->query("
    SELECT pi.name, i.quantity, i.reorder_level
    FROM ingredients pi
    JOIN inventory i ON pi.id = i.ingredient_id
    WHERE i.quantity <= i.reorder_level
");
$low_stock = $stmt->fetchAll();

// Get inventory analytics data
$stmt = $conn->query("
    SELECT 
        c.name AS product_category,
        COUNT(DISTINCT pi.ingredient_id) AS total_ingredients_used,
        SUM(i.quantity) AS total_ingredient_stock,
        AVG(i.quantity) AS avg_ingredient_quantity,
        COUNT(DISTINCT pi.product_id) AS num_products_in_category
    FROM categories c
    JOIN products p ON c.id = p.category_id
    JOIN product_ingredients pi ON p.id = pi.product_id
    JOIN inventory i ON pi.ingredient_id = i.ingredient_id
    GROUP BY c.name
    ORDER BY c.name;
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
    SELECT ig.name, i.last_restocked, it.quantity, i.unit, it.notes
    FROM inventory i
    JOIN ingredients ig ON i.ingredient_id = ig.id
    JOIN inventory_transactions it ON ig.id = it.ingredient_id
    WHERE i.last_restocked IS NOT NULL
    ORDER BY i.last_restocked DESC
");
$recent_restocks = $stmt->fetchAll();

function convertUnit($stock, $unit)
{
    // Only convert if unit is 'ml' or 'g'
    if (($unit === 'ml' || $unit === 'g') && $stock >= 1000) {
        $converted = $stock / 1000;
        $newUnit = ($unit === 'ml') ? 'L' : 'kg';
        return [$converted, $newUnit];
    }
    // Otherwise, return original value and unit
    return [$stock, $unit];
}

// Get Total count for Pagination
$stmt = $conn->query("
    SELECT COUNT(*) as total 
    FROM ingredients 
    JOIN inventory ON ingredients.id = inventory.ingredient_id
");
$total_ingredients = $stmt->fetch()['total'];
$total_pages = ceil($total_ingredients / $per_page);

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Inventory Management - Bro's Cafe</title>
    <link rel="stylesheet" href="../../assets/css/admin.css?v=<?php echo time(); ?>">
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
                                <p class="text-sm text-gray-500">Total Items</p>
                                <p class="text-2xl font-semibold text-gray-900"><?php echo count($items); ?></p>
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
                                    <?php echo count(array_filter($items, fn($p) => $p['quantity'] > $p['reorder_level'])); ?>
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
                                    <?php echo count(array_filter($items, fn($p) => $p['quantity'] == 0)); ?>
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
                <div class="grid grid-cols-1 gap-6 mb-6">
                    <!-- Recent Restocks -->
                    <div class="p-6 bg-white rounded-lg shadow"
                        style="height: 500px; display: flex; flex-direction: column;">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Recent Restocks</h3>
                            <div class="relative">
                                <input type="text" id="restockSearch" placeholder="Search items..."
                                    class="px-4 py-2 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    onkeyup="searchRestocks()">
                                <i
                                    class="absolute text-gray-400 transform -translate-y-1/2 fa-solid fa-search right-3 top-1/2"></i>
                            </div>
                        </div>
                        <!-- Scrollable container with max height -->
                        <div class="overflow-x-auto flex-1" style="overflow-y: auto;">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0 z-10">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase bg-gray-50">
                                            Items</th>
                                        <th
                                            class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase bg-gray-50">
                                            Date</th>
                                        <th
                                            class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase bg-gray-50">
                                            Stock</th>
                                        <th
                                            class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase bg-gray-50">
                                            Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="restockTableBody">
                                    <?php if (count($recent_restocks) > 0): ?>
                                    <?php foreach ($recent_restocks as $restock): ?>
                                    <tr class="restock-row">
                                        <td
                                            class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap restock-name">
                                            <?php echo $restock['name']; ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                                            <?php echo date('M d, Y', strtotime($restock['last_restocked'])); ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                                            <?php
                                                        list($convertedQty, $convertedUnit) = convertUnit($restock['quantity'], $restock['unit']);
                                                        echo $convertedQty . ' ' . $convertedUnit;
                                                        ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                                            <?php echo $restock['notes'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr id="noRestocksRow">
                                        <td colspan="3" class="px-4 py-3 text-sm text-center text-gray-500">No recent
                                            restocks</td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr id="noMatchRow" class="hidden">
                                        <td colspan="3" class="px-4 py-3 text-sm text-center text-gray-500">No matching
                                            items found</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
                    <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>"
                                placeholder="All dates"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                                placeholder="Item name"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                class="flex-1 px-6 py-2 text-white transition-colors rounded-lg bg-amber-600 hover:bg-amber-700">
                                <i class="mr-2 fa-solid fa-filter"></i>Apply Filters
                            </button>
                            <a href="inventory.php"
                                class="px-6 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                                <i class="fa-solid fa-times"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Inventory Table -->
                <div class="overflow-hidden bg-white rounded-lg shadow-md">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Inventory</h3>
                            <div class="flex space-x-2">
                                <div class="relative inline-block text-left">
                                    <button onclick="toggleExportDropdown()" type="button"
                                        class="inline-flex justify-between items-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                        Export
                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div id="exportDropdown"
                                        class="hidden absolute right-0 z-10 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                        <div class="py-1" role="menu">
                                            <button onclick="exportInventory('csv')"
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                role="menuitem">
                                                Export as CSV
                                            </button>
                                            <button onclick="exportInventory('pdf')"
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                role="menuitem">
                                                Export as PDF
                                            </button>
                                        </div>
                                    </div>
                                </div>
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
                                        Items</th>
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
                                <?php foreach ($items as $product): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo $product['name']; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php
                                                list($convertedQty, $convertedUnit) = convertUnit($product['quantity'], $product['unit']);
                                                echo $convertedQty . ' ' . $convertedUnit;
                                                ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        <?php
                                            list($convertedReorderQty, $convertedReorderUnit) = convertUnit($product['reorder_level'], $product['unit']);
                                            echo $convertedReorderQty . ' ' . $convertedReorderUnit;
                                            ?>
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
                                            onclick="openRestockModal(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['quantity']; ?>, '<?php echo addslashes($product['unit']); ?>')"
                                            class="cursor-pointer mr-3 text-amber-600 hover:text-amber-900">Restock</button>
                                        <button
                                            onclick="openAdjustModal(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['quantity']; ?>, '<?php echo addslashes($product['unit']); ?>')"
                                            class="cursor-pointer text-blue-600 hover:text-blue-900">Adjust</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center text-sm text-gray-700">
                            <span>Showing <span class="font-semibold"><?php echo $offset + 1; ?></span> to
                                <span
                                    class="font-semibold"><?php echo min($offset + $per_page, $total_ingredients); ?></span>
                                of
                                <span class="font-semibold"><?php echo $total_ingredients; ?></span> orders</span>
                        </div>

                        <div class="flex gap-2">
                            <!-- Previous Button -->
                            <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                <i class="mr-1 fa-solid fa-chevron-left"></i> Previous
                            </a>
                            <?php else: ?>
                            <span
                                class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                                <i class="mr-1 fa-solid fa-chevron-left"></i> Previous
                            </span>
                            <?php endif; ?>

                            <!-- Page Numbers -->
                            <div class="flex gap-1">
                                <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);

                                    if ($start_page > 1): ?>
                                <a href="?page=1&&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>"
                                    class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    1
                                </a>
                                <?php if ($start_page > 2): ?>
                                <span class="px-3 py-2 text-sm text-gray-500">...</span>
                                <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <?php if ($i == $page): ?>
                                <span class="px-3 py-2 text-sm font-medium text-white rounded-lg bg-amber-600">
                                    <?php echo $i; ?>
                                </span>
                                <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>"
                                    class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    <?php echo $i; ?>
                                </a>
                                <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                <span class="px-3 py-2 text-sm text-gray-500">...</span>
                                <?php endif; ?>
                                <a href="?page=<?php echo $total_pages; ?>&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>"
                                    class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    <?php echo $total_pages; ?>
                                </a>
                                <?php endif; ?>
                            </div>

                            <!-- Next Button -->
                            <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                Next <i class="ml-1 fa-solid fa-chevron-right"></i>
                            </a>
                            <?php else: ?>
                            <span
                                class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                                Next <i class="ml-1 fa-solid fa-chevron-right"></i>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div id="restockModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-30 backdrop-blur modal-backdrop">
        <div
            class="w-full max-w-md mx-4 overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl animate-modal border border-amber-200">
            <!-- Modal Header -->
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-amber-500 to-amber-600">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-xl font-bold flex items-center gap-2">
                            <i class="fa-solid fa-boxes-stacked mr-2"></i> Restock Item
                        </h3>
                    </div>
                    <button onclick="closeRestockModal()" class="text-white transition-colors hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4" style="max-height: 500px; overflow-y: auto;">
                <form id="restockForm" onsubmit="submitRestock(event)">
                    <input type="hidden" id="restock_product_id">
                    <input type="hidden" id="restock_base_unit">
                    <input type="hidden" id="restock_base_quantity">

                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700">Item Name</label>
                        <p id="restock_product_name" class="font-semibold text-gray-900 text-lg"></p>
                    </div>

                    <!-- Current Stock Display with Unit Selector -->
                    <div class="p-4 mb-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">Current Stock</label>
                            <select id="restock_display_unit" onchange="updateDisplayUnit()"
                                class="text-sm px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span id="restock_current_stock" class="text-2xl font-bold text-gray-900"></span>
                            <span id="restock_current_unit" class="text-lg font-medium text-gray-500"></span>
                        </div>
                    </div>

                    <!-- Add Quantity with Unit Selector -->
                    <div class="mb-4">
                        <label for="restock_quantity" class="block mb-2 text-sm font-medium text-gray-700">
                            Add Quantity
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" id="restock_quantity" step="0.01" min="0.01" required
                                placeholder="0.00"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            <select id="restock_input_unit"
                                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Base unit: <span id="restock_base_unit_display"></span>
                        </p>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label for="restock_notes" class="block mb-2 text-sm font-medium text-gray-700">
                            Notes (Optional)
                        </label>
                        <textarea id="restock_notes" rows="3" placeholder="Add any notes about this restock..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                    </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeRestockModal()"
                    class="px-4 py-2 text-gray-700 bg-gray-300 rounded-lg hover:bg-gray-400">Cancel</button>
                <button type="submit"
                    class="px-4 py-2 text-white rounded-lg bg-amber-600 hover:bg-amber-700">Restock</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Adjust Stock Modal -->
    <div id="adjustModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-30 backdrop-blur modal-backdrop">
        <div
            class="w-full max-w-md mx-4 overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl animate-modal border border-blue-200">
            <!-- Modal Header -->
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-500 to-blue-600">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-xl font-bold flex items-center gap-2">
                            <i class="fa-solid fa-sliders mr-2"></i> Adjust Stock
                        </h3>
                    </div>
                    <button onclick="closeAdjustModal()" class="text-white transition-colors hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4" style="max-height: 500px; overflow-y: auto;">
                <form id="adjustForm" onsubmit="submitAdjust(event)">
                    <input type="hidden" id="adjust_product_id">
                    <input type="hidden" id="adjust_base_unit">
                    <input type="hidden" id="adjust_base_quantity">

                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700">Item Name</label>
                        <p id="adjust_product_name" class="font-semibold text-gray-900 text-lg"></p>
                    </div>

                    <!-- Current Stock Display with Unit Selector -->
                    <div class="p-4 mb-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">Current Stock</label>
                            <select id="adjust_display_unit" onchange="updateAdjustDisplayUnit()"
                                class="text-sm px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span id="adjust_current_stock" class="text-2xl font-bold text-gray-900"></span>
                            <span id="adjust_current_unit" class="text-lg font-medium text-gray-500"></span>
                        </div>
                    </div>

                    <!-- Adjustment Type -->
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700">Adjustment Type</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" id="adjust_type_add" onclick="setAdjustmentType('add')"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                <i class="fa-solid fa-plus mr-1"></i> Add Stock
                            </button>
                            <button type="button" id="adjust_type_subtract" onclick="setAdjustmentType('subtract')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400">
                                <i class="fa-solid fa-minus mr-1"></i> Subtract Stock
                            </button>
                        </div>
                        <input type="hidden" id="adjustment_type" value="add">
                    </div>

                    <!-- Adjustment Quantity with Unit Selector -->
                    <div class="mb-4">
                        <label for="adjust_quantity" class="block mb-2 text-sm font-medium text-gray-700">
                            Adjustment Quantity
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" id="adjust_quantity" step="0.01" min="0.01" required placeholder="0.00"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <select id="adjust_input_unit"
                                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Base unit: <span id="adjust_base_unit_display"></span>
                        </p>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label for="adjust_notes" class="block mb-2 text-sm font-medium text-gray-700">
                            Notes (Optional)
                        </label>
                        <textarea id="adjust_notes" rows="3" placeholder="Reason for adjustment..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeAdjustModal()"
                    class="px-4 py-2 text-gray-700 bg-gray-300 rounded-lg hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 text-white rounded-lg bg-blue-600 hover:bg-blue-700">Adjust
                    Stock</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Restock Modal -->
    <div id="bulkRestockModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-30 backdrop-blur modal-backdrop">
        <div
            class="w-full max-w-md mx-4 overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl animate-modal border border-amber-200">
            <!-- Modal Header -->
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-amber-500 to-amber-600">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-xl font-bold flex items-center gap-2">
                            <i class="fa-solid fa-layer-group mr-2"></i> Bulk Restock
                        </h3>
                        <p class="text-sm text-amber-100 mt-1">Restock multiple items at once</p>
                    </div>
                    <button onclick="closeBulkRestockModal()" class="text-white transition-colors hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4" style="max-height: 500px; overflow-y: auto;">
                <form id="bulkRestockForm" onsubmit="submitBulkRestock(event)">
                    <!-- Category Selection -->
                    <div class="mb-4">
                        <label for="bulk_category" class="block mb-2 text-sm font-medium text-gray-700">
                            <i class="fa-solid fa-filter mr-1"></i> Category Filter
                        </label>
                        <select id="bulk_category"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            <option value="">All Categories</option>
                            <?php foreach ($category_analytics as $category): ?>
                            <option value="<?php echo $category['product_category']; ?>">
                                <?php echo $category['product_category']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Select a category or leave empty to restock all items</p>
                    </div>

                    <!-- Quantity Input -->
                    <div class="mb-4">
                        <label for="bulk_quantity" class="block mb-2 text-sm font-medium text-gray-700">
                            Add Quantity to Each Item
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" id="bulk_quantity" step="0.01" min="0.01" required placeholder="0.00"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            <span class="px-3 py-2 text-gray-600 bg-gray-100 border border-gray-300 rounded-md">
                                Base Units
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            This quantity will be added to each item in their base unit (ml, g, etc.)
                        </p>
                    </div>

                    <!-- Preview Section -->
                    <div class="p-4 mb-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-info-circle text-blue-600 mt-1"></i>
                            <div>
                                <p class="text-sm font-medium text-blue-900">Preview</p>
                                <p class="text-xs text-blue-700 mt-1">
                                    Each selected item will have the specified quantity added to its current stock.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label for="bulk_notes" class="block mb-2 text-sm font-medium text-gray-700">
                            Notes (Optional)
                        </label>
                        <textarea id="bulk_notes" rows="3" placeholder="Add notes about this bulk restock operation..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                    </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeBulkRestockModal()"
                    class="px-4 py-2 text-gray-700 bg-gray-300 rounded-lg hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 text-white rounded-lg bg-amber-600 hover:bg-amber-700">
                    <i class="fa-solid fa-check mr-1"></i> Restock All
                </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-30 backdrop-blur modal-backdrop">
        <div
            class="w-full max-w-sm mx-4 overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl animate-modal">
            <div class="p-6 bg-gradient-to-r from-green-500 to-green-600">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-white rounded-full">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
            <div class="p-6 text-center">
                <h3 class="mb-2 text-xl font-bold text-gray-900">Success!</h3>
                <p id="successMessage" class="text-gray-600"></p>
            </div>
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                <button onclick="closeSuccessModal()"
                    class="w-full px-4 py-2 text-white transition-colors rounded-lg bg-green-600 hover:bg-green-700">
                    OK
                </button>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-30 backdrop-blur modal-backdrop">
        <div
            class="w-full max-w-sm mx-4 overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl animate-modal">
            <div class="p-6 bg-gradient-to-r from-red-500 to-red-600">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-white rounded-full">
                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="p-6 text-center">
                <h3 class="mb-2 text-xl font-bold text-gray-900">Error!</h3>
                <p id="errorMessage" class="text-gray-600"></p>
            </div>
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                <button onclick="closeErrorModal()"
                    class="w-full px-4 py-2 text-white transition-colors rounded-lg bg-red-600 hover:bg-red-700">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-30 backdrop-blur modal-backdrop">
        <div
            class="w-full max-w-sm mx-4 overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl animate-modal">
            <div class="p-6 bg-gradient-to-r from-amber-500 to-amber-600">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-white rounded-full">
                    <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="p-6 text-center">
                <h3 class="mb-2 text-xl font-bold text-gray-900">Confirm Bulk Restock</h3>
                <p id="confirmMessage" class="text-gray-600"></p>
            </div>
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button onclick="closeConfirmModal()"
                    class="px-4 py-2 text-gray-700 bg-gray-300 rounded-lg hover:bg-gray-400">Cancel</button>
                <button onclick="confirmBulkRestock()"
                    class="px-4 py-2 text-white rounded-lg bg-amber-600 hover:bg-amber-700">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Admin Verification Modal -->
    <div id="verificationModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-50 backdrop-blur modal-backdrop">
        <div class="w-full max-w-md mx-4 overflow-hidden bg-white shadow-2xl rounded-2xl border border-indigo-200">
            <!-- Modal Header -->
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-indigo-600 to-indigo-700">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-xl font-bold flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved"></i> Admin Verification Required
                        </h3>
                        <p class="text-sm text-indigo-100 mt-1">Please verify your admin credentials</p>
                    </div>
                    <button onclick="closeVerificationModal()" class="text-white transition-colors hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <!-- Password Verification -->
                <div id="passwordVerification" class="verification-method">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Admin Password
                        </label>
                        <div class="relative">
                            <input type="password" id="adminPassword"
                                class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Enter admin password">
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div id="verificationError" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600" id="verificationErrorMessage"></p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-6 bg-gray-50 border-t border-gray-200 flex gap-3">
                <button onclick="closeVerificationModal()"
                    class="flex-1 px-4 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                    Cancel
                </button>
                <button onclick="submitVerification()"
                    class="flex-1 px-4 py-3 text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 font-medium transition-colors">
                    Verify & Continue
                </button>
            </div>
        </div>
    </div>

    <script>
    // Modal functions
    function showSuccessModal(message) {
        document.getElementById('successMessage').textContent = message;
        document.getElementById('successModal').classList.remove('hidden');
    }

    function closeSuccessModal() {
        document.getElementById('successModal').classList.add('hidden');
        location.reload();
    }

    function showErrorModal(message) {
        document.getElementById('errorMessage').textContent = message;
        document.getElementById('errorModal').classList.remove('hidden');
    }

    function closeErrorModal() {
        document.getElementById('errorModal').classList.add('hidden');
    }

    // Confirmation modal functions
    let bulkRestockData = null;

    function showConfirmModal(message) {
        document.getElementById('confirmMessage').textContent = message;
        document.getElementById('confirmModal').classList.remove('hidden');
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
        bulkRestockData = null;
    }

    function confirmBulkRestock() {
        const dataToSend = bulkRestockData;
        closeConfirmModal();
        if (dataToSend) {
            // Require admin verification before bulk restock
            openVerificationModal(() => {
                executeBulkRestock(dataToSend);
            });
        }
    }

    // Admin Verification System
    let verificationCallback = null;
    let currentVerificationMethod = 'password';

    function openVerificationModal(callback) {
        verificationCallback = callback;
        document.getElementById('verificationModal').classList.remove('hidden');
        document.getElementById('adminPassword').value = '';
        document.getElementById('qrCodeInput').value = '';
        document.getElementById('verificationError').classList.add('hidden');
    }

    function closeVerificationModal() {
        document.getElementById('verificationModal').classList.add('hidden');
        verificationCallback = null;
    }

    function switchVerificationMethod(method) {
        currentVerificationMethod = method;

        // Update tab styles
        const passwordTab = document.getElementById('passwordTab');
        const qrTab = document.getElementById('qrTab');

        if (method === 'password') {
            passwordTab.className =
                'flex-1 py-2 px-4 rounded-md font-medium transition-all bg-white text-indigo-600 shadow-sm';
            qrTab.className =
                'flex-1 py-2 px-4 rounded-md font-medium transition-all text-gray-600 hover:text-indigo-600';
            document.getElementById('passwordVerification').classList.remove('hidden');
            document.getElementById('qrVerification').classList.add('hidden');
        } else {
            passwordTab.className =
                'flex-1 py-2 px-4 rounded-md font-medium transition-all text-gray-600 hover:text-indigo-600';
            qrTab.className =
                'flex-1 py-2 px-4 rounded-md font-medium transition-all bg-white text-indigo-600 shadow-sm';
            document.getElementById('passwordVerification').classList.add('hidden');
            document.getElementById('qrVerification').classList.remove('hidden');
        }
    }

    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('adminPassword');
        const toggleIcon = document.getElementById('passwordToggleIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.className = 'fa-solid fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            toggleIcon.className = 'fa-solid fa-eye';
        }
    }

    function submitVerification() {
        const errorDiv = document.getElementById('verificationError');
        const errorMsg = document.getElementById('verificationErrorMessage');

        let data = {
            method: currentVerificationMethod
        };

        if (currentVerificationMethod === 'password') {
            const password = document.getElementById('adminPassword').value;
            if (!password) {
                errorMsg.textContent = 'Please enter admin password';
                errorDiv.classList.remove('hidden');
                return;
            }
            data.password = password;
        } else {
            const qrCode = document.getElementById('qrCodeInput').value;
            if (!qrCode) {
                errorMsg.textContent = 'Please enter or scan QR code';
                errorDiv.classList.remove('hidden');
                return;
            }
            data.qr_code = qrCode;
        }

        // Verify with server
        fetch('verify_admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                console.log('Verification result:', result);
                if (result.success) {
                    console.log('Verification successful, executing callback');
                    // Store callback before closing modal
                    const callbackToExecute = verificationCallback;
                    closeVerificationModal();
                    // Execute the callback function after closing
                    if (callbackToExecute) {
                        console.log('Callback exists, executing...');
                        callbackToExecute();
                    } else {
                        console.error('No callback function found!');
                    }
                } else {
                    console.error('Verification failed:', result.message);
                    errorMsg.textContent = result.message || 'Verification failed';
                    errorDiv.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Verification error:', error);
                errorMsg.textContent = 'Verification error: ' + error.message;
                errorDiv.classList.remove('hidden');
            });
    } // Allow Enter key to submit verification
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('adminPassword');
        const qrInput = document.getElementById('qrCodeInput');

        if (passwordInput) {
            passwordInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    submitVerification();
                }
            });
        }

        if (qrInput) {
            qrInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    submitVerification();
                }
            });
        }
    });

    // Initialize charts when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Only initialize charts if they exist (admin view)
        const stockStatusCtx = document.getElementById('stockStatusChart');
        if (stockStatusCtx) {
            const stockStatusChart = new Chart(stockStatusCtx.getContext('2d'), {
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
        }

        // Category Stock Chart
        const categoryStockCtx = document.getElementById('categoryStockChart');
        if (categoryStockCtx) {
            const categoryLabels =
                <?php echo json_encode(array_column($category_analytics, 'product_category')); ?>;
            const categoryStockData =
                <?php echo json_encode(array_map(fn($c) => $c['total_ingredient_stock'] ?? 0, $category_analytics)); ?>;
            const avgStockData =
                <?php echo json_encode(array_map(fn($c) => round($c['avg_ingredient_quantity'] ?? 0, 1), $category_analytics)); ?>;
            const numProductsData =
                <?php echo json_encode(array_map(fn($c) => $c['num_products_in_category'] ?? 0, $category_analytics)); ?>;

            const categoryStockChart = new Chart(categoryStockCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        label: 'Total Stock',
                        data: categoryStockData,
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
                            },
                            callbacks: {
                                label: function(context) {
                                    const idx = context.dataIndex;
                                    const stock = categoryStockData[idx];
                                    const avg = avgStockData[idx];
                                    const products = numProductsData[idx];
                                    return [
                                        'Total Stock: ' + stock,
                                        'Avg Stock: ' + avg,
                                        'Products: ' + products
                                    ];
                                }
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
        }
    });

    // Low Stock Products by Category Chart
    const lowStockCategoryCtx = document.getElementById('lowStockCategoryChart');
    if (lowStockCategoryCtx) {
        const lowStockLabels =
            <?php echo json_encode(array_map(fn($c) => $c['category'] ?? '', $category_analytics)); ?>;
        const lowStockData =
            <?php echo json_encode(array_map(fn($c) => $c['low_stock_count'] ?? 0, $category_analytics)); ?>;

        const lowStockCategoryChart = new Chart(lowStockCategoryCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: lowStockLabels,
                datasets: [{
                    label: 'Low Stock Products',
                    data: lowStockData,
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 2,
                    borderRadius: 5
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Low Stock Products: ' + context.parsed.x;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    },
                    y: {
                        ticks: {
                            font: {
                                size: 11,
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        });
    }

    // Search function for Recent Restocks table
    function searchRestocks() {
        const searchInput = document.getElementById('restockSearch');
        const filter = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('.restock-row');
        const noMatchRow = document.getElementById('noMatchRow');
        let visibleCount = 0;

        rows.forEach(row => {
            const itemName = row.querySelector('.restock-name').textContent.toLowerCase();
            if (itemName.includes(filter)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Show "no match" message if no rows are visible
        if (visibleCount === 0 && rows.length > 0) {
            noMatchRow.classList.remove('hidden');
        } else {
            noMatchRow.classList.add('hidden');
        }
    }

    // Export inventory function
    function exportInventory(format) {
        window.location.href = 'export_inventory.php?format=' + format;
        toggleExportDropdown(); // Close dropdown after selection
    }

    // Toggle export dropdown
    function toggleExportDropdown() {
        const dropdown = document.getElementById('exportDropdown');
        dropdown.classList.toggle('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('exportDropdown');
        const button = event.target.closest('button[onclick="toggleExportDropdown()"]');

        if (!button && dropdown && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Bulk restock modal functions
    function openBulkRestockModal() {
        document.getElementById('bulkRestockModal').classList.remove('hidden');
    }

    function closeBulkRestockModal() {
        document.getElementById('bulkRestockModal').classList.add('hidden');
    }

    function submitBulkRestock(event) {
        event.preventDefault();

        const quantity = parseFloat(document.getElementById('bulk_quantity').value);
        const category = document.getElementById('bulk_category').value;
        const notes = document.getElementById('bulk_notes').value;

        // Store data for confirmation
        bulkRestockData = {
            quantity: quantity,
            category: category,
            notes: notes
        };

        // Show confirmation modal
        const categoryText = category ? `in category "${category}"` : 'in all categories';
        showConfirmModal(`Are you sure you want to add ${quantity} units to all items ${categoryText}?`);
    }

    function executeBulkRestock(data) {
        console.log('Executing bulk restock with data:', data);
        fetch('bulk_restock.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            })
            .then((response) => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then((data) => {
                console.log('Response data:', data);
                if (data.success) {
                    closeBulkRestockModal();
                    showSuccessModal(data.message);
                } else {
                    showErrorModal(data.message || 'Failed to bulk restock');
                }
            })
            .catch((error) => {
                console.error('Fetch error:', error);
                showErrorModal('An error occurred: ' + error.message);
            });
    }
    </script>
    <script src="../../assets/js/inventory.js"></script>
    <script src="../../assets/js/admin.js"></script>
</body>

</html>