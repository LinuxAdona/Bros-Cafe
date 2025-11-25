<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

$db = new Database();
$conn = $db->getConnection();

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10; // Orders per page
$offset = ($page - 1) * $per_page;

// Build query based on filters
$where = ["1=1"];
$params = [];

if ($status_filter !== 'all') {
    $where[] = "o.status = :status";
    $params['status'] = $status_filter;
}

if ($date_filter && $date_filter !== '') {
    $where[] = "DATE(o.created_at) = :date";
    $params['date'] = $date_filter;
}

if ($search) {
    $where[] = "(o.order_number LIKE :search OR u.full_name LIKE :search)";
    $params['search'] = "%$search%";
}

$where_clause = implode(' AND ', $where);

// Get total count for pagination
$count_stmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM orders o
    LEFT JOIN users u ON o.customer_id = u.id
    LEFT JOIN users e ON o.employee_id = e.id
    WHERE $where_clause
");
$count_stmt->execute($params);
$total_orders = $count_stmt->fetch()['total'];
$total_pages = ceil($total_orders / $per_page);

// Get orders with pagination
$stmt = $conn->prepare("
    SELECT o.*, 
           u.full_name as customer_name,
           e.full_name as employee_name,
           COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN users u ON o.customer_id = u.id
    LEFT JOIN users e ON o.employee_id = e.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE $where_clause
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT :limit OFFSET :offset
");

// Bind parameters
foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$orders = $stmt->fetchAll();

// Get order statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END) as preparing,
        SUM(CASE WHEN status = 'ready' THEN 1 ELSE 0 END) as ready,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(total_amount) as total_revenue
    FROM orders
";

if ($date_filter && $date_filter !== '') {
    $stats_query .= " WHERE DATE(created_at) = :date";
    $stmt = $conn->prepare($stats_query);
    $stmt->execute(['date' => $date_filter]);
} else {
    $stmt = $conn->prepare($stats_query);
    $stmt->execute();
}
$stats = $stmt->fetch();

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Bro's Cafe</title>
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
                            class="flex items-center px-4 py-3 rounded-lg bg-amber-600">
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
                    <h2 class="text-3xl font-bold text-gray-800">Orders Management</h2>
                    <p class="text-md text-gray-600">View and manage customer orders</p>
                </div>
            </div>
            <div class="p-6">
                <!-- Order Statistics -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">
                            <?php if ($date_filter): ?>
                                Statistics for <?php echo date('F d, Y', strtotime($date_filter)); ?>
                            <?php else: ?>
                                All Time Statistics
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div class="p-6 bg-white rounded-lg shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Total Orders</p>
                                    <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total_orders']; ?>
                                    </p>
                                </div>
                                <div class="p-3 bg-blue-100 rounded-full">
                                    <i class="text-2xl text-blue-600 fa-solid fa-receipt"></i>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-white rounded-lg shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Pending</p>
                                    <p class="text-2xl font-bold text-yellow-600"><?php echo $stats['pending']; ?></p>
                                </div>
                                <div class="p-3 bg-yellow-100 rounded-full">
                                    <i class="text-2xl text-yellow-600 fa-solid fa-clock"></i>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-white rounded-lg shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Completed</p>
                                    <p class="text-2xl font-bold text-green-600"><?php echo $stats['completed']; ?></p>
                                </div>
                                <div class="p-3 bg-green-100 rounded-full">
                                    <i class="text-2xl text-green-600 fa-solid fa-check-circle"></i>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-white rounded-lg shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Revenue</p>
                                    <p class="text-2xl font-bold text-gray-800">
                                        ₱<?php echo number_format($stats['total_revenue'], 2); ?></p>
                                </div>
                                <div class="p-3 rounded-full bg-amber-100">
                                    <i class="text-2xl fa-solid fa-peso-sign text-amber-600"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
                    <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>"
                                placeholder="All dates"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Status</label>
                            <select name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status
                                </option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>
                                    Pending</option>
                                <option value="preparing"
                                    <?php echo $status_filter === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                <option value="ready" <?php echo $status_filter === 'ready' ? 'selected' : ''; ?>>Ready
                                </option>
                                <option value="completed"
                                    <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled"
                                    <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                                placeholder="Order # or customer name"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                class="flex-1 px-6 py-2 text-white transition-colors rounded-lg bg-amber-600 hover:bg-amber-700">
                                <i class="mr-2 fa-solid fa-filter"></i>Apply Filters
                            </button>
                            <a href="orders.php"
                                class="px-6 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                                <i class="fa-solid fa-times"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Orders Table -->
                <div class="overflow-hidden bg-white rounded-lg shadow-md">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Order #
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Customer
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Employee
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Items
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Amount
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Payment
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Type
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Date
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (count($orders) > 0): ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                                <?php echo $order['order_number']; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                                <?php echo $order['customer_name'] ?? 'Walk-in'; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                                <?php echo $order['employee_name']; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                                <?php echo $order['item_count']; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 whitespace-nowrap">
                                                ₱<?php echo number_format($order['total_amount'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                    <?php
                                                    echo match ($order['payment_method']) {
                                                        'cash' => 'bg-green-100 text-green-800',
                                                        'card' => 'bg-blue-100 text-blue-800',
                                                        'gcash' => 'bg-purple-100 text-purple-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    };
                                        ?>">
                                                    <?php echo strtoupper($order['payment_method']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                                <?php echo ucfirst($order['order_type']); ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                    <?php
                                        echo match ($order['status']) {
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'preparing' => 'bg-blue-100 text-blue-800',
                                            'ready' => 'bg-purple-100 text-purple-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                                <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                                <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)"
                                                    class="text-blue-600 hover:text-blue-800">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>
                                                <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                                                    <button
                                                        onclick="updateOrderStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')"
                                                        class="ml-3 text-green-600 hover:text-green-800">
                                                        <i class="fa-solid fa-edit"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="px-6 py-4 text-sm text-center text-gray-500">
                                            No orders found for the selected filters.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200">
                            <div class="flex items-center text-sm text-gray-700">
                                <span>Showing <span class="font-semibold"><?php echo $offset + 1; ?></span> to
                                    <span
                                        class="font-semibold"><?php echo min($offset + $per_page, $total_orders); ?></span>
                                    of
                                    <span class="font-semibold"><?php echo $total_orders; ?></span> orders</span>
                            </div>

                            <div class="flex gap-2">
                                <!-- Previous Button -->
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>"
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
                                        <a href="?page=1&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>"
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
                                            <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>"
                                                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endfor; ?>

                                    <?php if ($end_page < $total_pages): ?>
                                        <?php if ($end_page < $total_pages - 1): ?>
                                            <span class="px-3 py-2 text-sm text-gray-500">...</span>
                                        <?php endif; ?>
                                        <a href="?page=<?php echo $total_pages; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>"
                                            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                            <?php echo $total_pages; ?>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <!-- Next Button -->
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&search=<?php echo urlencode($search); ?>"
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
                    <button onclick="closeModal()" class="text-white transition-colors hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4" style="max-height: 500px; overflow-y: auto;">
                <div id="orderDetailsContent">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                <button onclick="closeModal()"
                    class="w-full py-2 font-semibold text-white transition-all rounded-lg bg-amber-600 hover:bg-amber-700">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div id="updateStatusModal" class="fixed inset-0 z-50 items-center justify-center hidden modal-backdrop">
        <div
            class="w-full max-w-md mx-4 overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl animate-modal">
            <!-- Modal Header -->
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-amber-500 to-amber-600">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-xl font-bold" id="updateModalTitle">Update Order Status</h3>
                        <p class="text-sm opacity-90" id="updateModalSubtitle"></p>
                    </div>
                    <button onclick="closeUpdateStatusModal()" class="text-white transition-colors hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4">
                <div id="updateStatusContent">
                    <p class="text-gray-700">Are you sure you want to change the order status?</p>
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500">Current Status</p>
                                <p id="currentStatusLabel" class="font-semibold text-gray-800">Pending</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">New Status</p>
                                <p id="nextStatusLabel" class="font-semibold text-amber-600">Preparing</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex gap-2">
                <button onclick="closeUpdateStatusModal()"
                    class="flex-1 py-2 font-semibold text-gray-700 transition-all rounded-lg bg-white border border-gray-300 hover:bg-gray-100">Cancel</button>
                <button id="confirmUpdateBtn"
                    class="flex-1 py-2 font-semibold text-white transition-all rounded-lg bg-amber-600 hover:bg-amber-700">Confirm</button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/admin.js"></script>
    <script src="../../assets/js/orders.js"></script>
</body>

</html>
