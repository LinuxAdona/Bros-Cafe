<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$date_filter = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query based on filters
$where = ["1=1"];
$params = [];

if ($status_filter !== 'all') {
    $where[] = "o.status = :status";
    $params['status'] = $status_filter;
}

if ($date_filter) {
    $where[] = "DATE(o.created_at) = :date";
    $params['date'] = $date_filter;
}

if ($search) {
    $where[] = "(o.order_number LIKE :search OR u.full_name LIKE :search)";
    $params['search'] = "%$search%";
}

$where_clause = implode(' AND ', $where);

// Get orders
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
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get order statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END) as preparing,
        SUM(CASE WHEN status = 'ready' THEN 1 ELSE 0 END) as ready,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(total_amount) as total_revenue
    FROM orders
    WHERE DATE(created_at) = :date
");
$stmt->execute(['date' => $date_filter]);
$stats = $stmt->fetch();

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Bro's Cafe</title>
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body class="bg-gray-100 font-['Montserrat']">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="flex flex-col w-64 text-white bg-gray-900">
            <div class="p-4 border-b border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <img src="../assets/images/logo.png" alt="Logo" class="w-10 h-10 rounded-full">
                        <div class="ml-3">
                            <h1 class="text-lg font-bold">Bro's Cafe</h1>
                            <p class="text-xs text-gray-400">Admin Panel</p>
                        </div>
                    </div>
                    <button onclick="toggleSidebar()" class="text-gray-400 transition-colors hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <nav class="flex-1 p-4 overflow-y-auto">
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="../employee/pos.php" class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            POS
                        </a>
                    </li>
                    <li>
                        <a href="orders.php" class="flex items-center px-4 py-3 rounded-lg bg-amber-600">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Orders
                        </a>
                    </li>
                    <li>
                        <a href="inventory.php" class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            Inventory
                        </a>
                    </li>
                    <li>
                        <a href="analytics.php" class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <i class="w-5 h-5 mr-3 fa-solid fa-chart-simple"></i>
                            Analytics
                        </a>
                    </li>
                    <li>
                        <a href="products.php" class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Products
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
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
                <div class="flex items-center mb-6">
                    <button onclick="toggleSidebar()" id="hamburger-btn"
                        class="p-3 mr-4 text-white transition-all rounded-full shadow-lg bg-amber-600 hover:bg-amber-700 hover:shadow-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800">Orders Management</h2>
                        <p class="text-gray-600">View and manage customer orders</p>
                    </div>
                </div>

                <!-- Order Statistics -->
                <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2 lg:grid-cols-4">
                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Total Orders</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total_orders']; ?></p>
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
                                <p class="text-2xl font-bold text-gray-800">₱<?php echo number_format($stats['total_revenue'], 2); ?></p>
                            </div>
                            <div class="p-3 rounded-full bg-amber-100">
                                <i class="text-2xl fa-solid fa-peso-sign text-amber-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
                    <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" value="<?php echo $date_filter; ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Status</label>
                            <select name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="preparing" <?php echo $status_filter === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                <option value="ready" <?php echo $status_filter === 'ready' ? 'selected' : ''; ?>>Ready</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                                placeholder="Order # or customer name"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full px-6 py-2 text-white transition-colors rounded-lg bg-amber-600 hover:bg-amber-700">
                                <i class="mr-2 fa-solid fa-filter"></i>Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Orders Table -->
                <div class="overflow-hidden bg-white rounded-lg shadow-md">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Order #
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Customer
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Employee
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Items
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Amount
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Payment
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
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
                                                    <button onclick="updateOrderStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')"
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
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">Order Details</h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                    <div id="orderDetailsContent">
                        <!-- Content loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
            sidebar.classList.toggle('absolute');
            sidebar.classList.toggle('z-50');
        }

        function viewOrderDetails(orderId) {
            document.getElementById('orderModal').classList.remove('hidden');
            fetch(`get_order_details.php?id=${orderId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('orderDetailsContent').innerHTML = data;
                });
        }

        function closeModal() {
            document.getElementById('orderModal').classList.add('hidden');
        }

        function updateOrderStatus(orderId, currentStatus) {
            const statuses = ['pending', 'preparing', 'ready', 'completed'];
            const currentIndex = statuses.indexOf(currentStatus);
            const nextStatus = statuses[currentIndex + 1] || 'completed';

            if (confirm(`Update order status to "${nextStatus}"?`)) {
                fetch('update_order_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            order_id: orderId,
                            status: nextStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error updating order status');
                        }
                    });
            }
        }

        // Responsive sidebar
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                document.getElementById('sidebar').classList.remove('-translate-x-full', 'absolute', 'z-50');
            }
        });
    </script>
</body>

</html>