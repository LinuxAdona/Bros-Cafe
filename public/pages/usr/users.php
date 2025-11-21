<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Check if username or email already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
                $stmt->execute(['username' => $_POST['username'], 'email' => $_POST['email']]);
                if ($stmt->fetch()) {
                    $message = 'Username or email already exists.';
                    $message_type = 'error';
                } else {
                    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("
                        INSERT INTO users (username, email, password, full_name, role, phone, status)
                        VALUES (:username, :email, :password, :full_name, :role, :phone, :status)
                    ");
                    $success = $stmt->execute([
                        'username' => $_POST['username'],
                        'email' => $_POST['email'],
                        'password' => $hashed_password,
                        'full_name' => $_POST['full_name'],
                        'role' => $_POST['role'],
                        'phone' => $_POST['phone'] ?: null,
                        'status' => $_POST['status']
                    ]);
                    $message = $success ? 'User added successfully!' : 'Error adding user.';
                    $message_type = $success ? 'success' : 'error';
                }
                break;

            case 'edit':
                $updateFields = [
                    'id' => $_POST['user_id'],
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'full_name' => $_POST['full_name'],
                    'role' => $_POST['role'],
                    'phone' => $_POST['phone'] ?: null,
                    'status' => $_POST['status']
                ];

                $sql = "UPDATE users SET username = :username, email = :email, full_name = :full_name, 
                        role = :role, phone = :phone, status = :status";

                // Update password only if provided
                if (!empty($_POST['password'])) {
                    $updateFields['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $sql .= ", password = :password";
                }

                $sql .= " WHERE id = :id";

                $stmt = $conn->prepare($sql);
                $success = $stmt->execute($updateFields);
                $message = $success ? 'User updated successfully!' : 'Error updating user.';
                $message_type = $success ? 'success' : 'error';
                break;

            case 'delete':
                // Prevent deleting yourself
                $current_user = getCurrentUser();
                if ($current_user['id'] == $_POST['user_id']) {
                    $message = 'You cannot delete your own account.';
                    $message_type = 'error';
                } else {
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
                    $success = $stmt->execute(['id' => $_POST['user_id']]);
                    $message = $success ? 'User deleted successfully!' : 'Error deleting user.';
                    $message_type = $success ? 'success' : 'error';
                }
                break;

            case 'toggle_status':
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET status = IF(status = 'active', 'inactive', 'active') 
                    WHERE id = :id
                ");
                $success = $stmt->execute(['id' => $_POST['user_id']]);
                $message = $success ? 'User status updated!' : 'Error updating status.';
                $message_type = $success ? 'success' : 'error';
                break;
        }
    }
}

// Get all users (admins and employees only, not customers)
$stmt = $conn->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM orders WHERE employee_id = u.id) as total_orders,
           (SELECT SUM(total_amount) FROM orders WHERE employee_id = u.id AND status != 'cancelled') as total_sales
    FROM users u
    WHERE u.role IN ('admin', 'employee')
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

// Get user statistics
$stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$admin_count = $stmt->fetch()['count'];

$stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'employee'");
$employee_count = $stmt->fetch()['count'];

$stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'active' AND role IN ('admin', 'employee')");
$active_count = $stmt->fetch()['count'];

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Bro's Cafe</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
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
                            class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
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
                        <a href="users.php" data-tooltip="Employees" class="flex items-center px-4 py-3 rounded-lg bg-amber-600">
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
            <div class="flex items-center justify-between bg-white shadow-lg p-6 mb-6">
                <div class="flex items-center">
                    <div class="flex flex-col justify-center">
                        <h2 class="text-3xl font-bold text-gray-800">User Management</h2>
                        <p class="text-gray-600">Manage employees and administrators</p>
                    </div>
                </div>
                <button onclick="openAddModal()"
                    class="px-6 py-3 text-white transition-colors rounded-lg shadow-md bg-amber-600 hover:bg-amber-700">
                    <i class="mr-2 fa-solid fa-user-plus"></i>Add User
                </button>
            </div>
            <div class="p-6">

                <!-- Alert Messages -->
                <?php if ($message): ?>
                    <div
                        class="p-4 mb-6 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-l-4 border-green-500' : 'bg-red-100 text-red-800 border-l-4 border-red-500'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Statistics -->
                <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-3">
                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Administrators</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $admin_count; ?></p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <i class="text-2xl text-purple-600 fa-solid fa-user-shield"></i>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Employees</p>
                                <p class="text-2xl font-bold text-blue-600"><?php echo $employee_count; ?></p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <i class="text-2xl text-blue-600 fa-solid fa-users"></i>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Active Users</p>
                                <p class="text-2xl font-bold text-green-600"><?php echo $active_count; ?></p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <i class="text-2xl text-green-600 fa-solid fa-user-check"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="overflow-hidden bg-white rounded-lg shadow-md">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        User
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Username
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Email
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Phone
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Role
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Orders
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Total Sales
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($users as $user): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="shrink-0 w-10 h-10">
                                                    <div
                                                        class="flex items-center justify-center w-10 h-10 text-white rounded-full bg-amber-600">
                                                        <?php echo strtoupper(substr($user['full_name'], 0, 2)); ?>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo $user['full_name']; ?></div>
                                                    <div class="text-sm text-gray-500">Joined
                                                        <?php echo date('M Y', strtotime($user['created_at'])); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            <?php echo $user['username']; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            <?php echo $user['email']; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            <?php echo $user['phone'] ?: '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full 
                                                <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                            <?php echo $user['total_orders'] ?? 0; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-900 whitespace-nowrap">
                                            ₱<?php echo number_format($user['total_sales'] ?? 0, 2); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full 
                                                <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                                            <button onclick='editUser(<?php echo json_encode($user); ?>)'
                                                class="mr-3 text-blue-600 hover:text-blue-800" title="Edit">
                                                <i class="fa-solid fa-edit"></i>
                                            </button>
                                            <button
                                                onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')"
                                                class="mr-3 text-yellow-600 hover:text-yellow-800" title="Toggle Status">
                                                <i
                                                    class="fa-solid fa-toggle-<?php echo $user['status'] === 'active' ? 'on' : 'off'; ?>"></i>
                                            </button>
                                            <?php if ($user['id'] != $current_user['id']): ?>
                                                <button
                                                    onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo addslashes($user['full_name']); ?>')"
                                                    class="text-red-600 hover:text-red-800" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
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

    <!-- Add/Edit User Modal -->
    <div id="userModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form method="POST" id="userForm">
                    <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">Add User</h3>
                        </div>

                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="user_id" id="userId">

                        <div class="space-y-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Full Name *</label>
                                <input type="text" name="full_name" id="fullName" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Username *</label>
                                <input type="text" name="username" id="username" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Email *</label>
                                <input type="email" name="email" id="email" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Phone</label>
                                <input type="tel" name="phone" id="phone"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Password <span
                                        id="passwordRequired">*</span></label>
                                <input type="password" name="password" id="password"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                                <p class="mt-1 text-xs text-gray-500" id="passwordHint">Leave blank to keep current
                                    password</p>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Role *</label>
                                <select name="role" id="role" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                                    <option value="employee">Employee</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Status *</label>
                                <select name="status" id="status" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-md shadow-sm bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Save User
                        </button>
                        <button type="button" onclick="closeModal()"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="user_id" id="deleteUserId">
    </form>

    <!-- Toggle Status Form -->
    <form id="toggleStatusForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="toggle_status">
        <input type="hidden" name="user_id" id="toggleUserId">
    </form>

    <script src="../../assets/js/admin.js"></script>
    <script src="../../assets/js/users.js"></script>
</body>

</html>