<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// Get filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

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

// Build query based on filters
$where = ["1=1"];
$params = [];

if ($search) {
    $where[] = "(u.full_name LIKE :search OR u.username LIKE :search2 OR u.email LIKE :search3)";
    $params['search'] = "%$search%";
    $params['search2'] = "%$search%";
    $params['search3'] = "%$search%";
}

if ($role_filter && $role_filter !== '') {
    $where[] = "u.role = :role";
    $params['role'] = $role_filter;
}

if ($status_filter && $status_filter !== '') {
    $where[] = "u.status = :status";
    $params['status'] = $status_filter;
}

$where_clause = implode(' AND ', $where);

// Get filtered users with pagination
try {
    // DEBUG: Log query details
    error_log("=== USERS.PHP DEBUG ===");
    error_log("Search param: '$search'");
    error_log("Role filter: '$role_filter'");
    error_log("Status filter: '$status_filter'");
    error_log("WHERE clause: $where_clause");
    error_log("Params array: " . print_r($params, true));

    // First, get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM users u WHERE $where_clause AND u.role IN ('admin', 'employee')";
    error_log("Count SQL: $count_sql");
    $count_stmt = $conn->prepare($count_sql);
    foreach ($params as $key => $value) {
        error_log("Binding :$key = '$value'");
        $count_stmt->bindValue(":$key", $value);
    }
    $count_stmt->execute();
    $total_users = $count_stmt->fetch()['total'];
    error_log("Total users found: $total_users");
    $total_pages = ceil($total_users / $per_page);

    // Then get the actual users with pagination
    $sql = "
        SELECT u.*, 
               COALESCE((SELECT COUNT(*) FROM orders WHERE employee_id = u.id), 0) as total_orders,
               COALESCE((SELECT SUM(total_amount) FROM orders WHERE employee_id = u.id AND status != 'cancelled'), 0) as total_sales
        FROM users u
        WHERE $where_clause AND u.role IN ('admin', 'employee')
        ORDER BY u.created_at DESC
        LIMIT $per_page OFFSET $offset
    ";
    error_log("Main SQL: $sql");

    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->execute();
    $users = $stmt->fetchAll();
    error_log("Users fetched: " . count($users));
    foreach ($users as $user) {
        error_log("  - {$user['username']} ({$user['full_name']})");
    }
    error_log("=== END DEBUG ===");
} catch (PDOException $e) {
    error_log("Database error in users.php: " . $e->getMessage());
    $users = [];
    $total_users = 0;
    $total_pages = 0;
}

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
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 z-30 bg-black transition-opacity duration-300 opacity-0 pointer-events-none lg:hidden" onclick="toggleMobileSidebar()"></div>

    <div class="flex h-screen overflow-hidden flex-col lg:flex-row">
        <!-- Mobile Header -->
        <div class="lg:hidden bg-white border-b border-gray-200 flex items-center px-4 py-3 z-20">
            <button id="mobileSidebarBtn"
                class="p-2 text-gray-900 bg-gray-100 rounded-lg shadow transition-all duration-300 hover:bg-gray-200"
                onclick="toggleMobileSidebar()">
                <svg class="w-6 h-6 transition-transform duration-300" id="hamburgerIcon" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <h1 class="ml-4 text-lg font-bold text-gray-800">Bro's Cafe</h1>
        </div>

        <!-- Sidebar -->
        <aside id="sidebar" class="flex flex-col text-white bg-gray-900 fixed inset-y-0 left-0 z-40 w-64 transform -translate-x-full transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:w-64 shadow-2xl">
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
                        <a href="users.php" data-tooltip="Employees"
                            class="flex items-center px-4 py-3 rounded-lg bg-amber-600">
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

                <!-- Filters -->
                <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
                    <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                                placeholder="Name, username, or email..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Role</label>
                            <select name="role"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <option value="">All Roles</option>
                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>
                                    Administrator</option>
                                <option value="employee" <?php echo $role_filter === 'employee' ? 'selected' : ''; ?>>
                                    Employee</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Status</label>
                            <select name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>
                                    Active</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>
                                    Inactive</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                class="flex-1 px-4 py-2 text-white transition-colors rounded-lg bg-amber-600 hover:bg-amber-700">
                                <i class="mr-2 fa-solid fa-filter"></i>Filter
                            </button>
                            <a href="users.php"
                                class="px-4 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                                <i class="fa-solid fa-times"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="overflow-hidden bg-white rounded-lg shadow-md">
                    <div class="overflow-x-auto">
                        <table class="w-full" id="usersTable">
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
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                            <i class="fa-solid fa-users text-4xl text-gray-300 mb-3"></i>
                                            <p class="text-lg font-medium">No users found</p>
                                            <p class="text-sm">Try adjusting your search filters</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
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
                                                <button
                                                    onclick="editUser(<?php echo $user['id']; ?>, '<?php echo addslashes($user['username']); ?>', '<?php echo addslashes($user['email']); ?>', '<?php echo addslashes($user['full_name']); ?>', '<?php echo $user['role']; ?>', '<?php echo addslashes($user['phone'] ?? ''); ?>', '<?php echo $user['status']; ?>')"
                                                    class="mr-3 text-blue-600 hover:text-blue-800" title="Edit">
                                                    <i class="fa-solid fa-edit"></i>
                                                </button>
                                                <button
                                                    onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo addslashes($user['full_name']); ?>', '<?php echo $user['status']; ?>')"
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
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200">
                            <div class="flex items-center text-sm text-gray-700">
                                Showing <span class="font-medium mx-1"><?php echo $offset + 1; ?></span> to
                                <span class="font-medium mx-1"><?php echo min($offset + $per_page, $total_users); ?></span>
                                of
                                <span class="font-medium mx-1"><?php echo $total_users; ?></span> results
                            </div>

                            <div class="flex gap-2">
                                <?php
                                // Build query string for pagination
                                $query_params = [];
                                if ($search) $query_params[] = 'search=' . urlencode($search);
                                if ($role_filter) $query_params[] = 'role=' . urlencode($role_filter);
                                if ($status_filter) $query_params[] = 'status=' . urlencode($status_filter);
                                $query_string = !empty($query_params) ? '&' . implode('&', $query_params) : '';

                                // Previous button
                                if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?><?php echo $query_string; ?>"
                                        class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </a>
                                <?php else: ?>
                                    <span
                                        class="px-3 py-2 text-sm text-gray-400 bg-gray-100 border border-gray-200 rounded cursor-not-allowed">
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </span>
                                <?php endif; ?>

                                <?php
                                // Page numbers
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);

                                // First page
                                if ($start_page > 1): ?>
                                    <a href="?page=1<?php echo $query_string; ?>"
                                        class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                                        1
                                    </a>
                                    <?php if ($start_page > 2): ?>
                                        <span class="px-3 py-2 text-sm text-gray-400">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <span class="px-3 py-2 text-sm text-white rounded bg-amber-600 border border-amber-600">
                                            <?php echo $i; ?>
                                        </span>
                                    <?php else: ?>
                                        <a href="?page=<?php echo $i; ?><?php echo $query_string; ?>"
                                            class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php
                                // Last page
                                if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                        <span class="px-3 py-2 text-sm text-gray-400">...</span>
                                    <?php endif; ?>
                                    <a href="?page=<?php echo $total_pages; ?><?php echo $query_string; ?>"
                                        class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                                        <?php echo $total_pages; ?>
                                    </a>
                                <?php endif; ?>

                                <!-- Next button -->
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?><?php echo $query_string; ?>"
                                        class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                <?php else: ?>
                                    <span
                                        class="px-3 py-2 text-sm text-gray-400 bg-gray-100 border border-gray-200 rounded cursor-not-allowed">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit User Modal -->
    <div id="userModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-30 backdrop-blur modal-backdrop">
        <div
            class="w-full max-w-2xl mx-4 overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl animate-modal">
            <form method="POST" id="userForm">
                <!-- Modal Header with Gradient -->
                <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-8 rounded-t-2xl">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-white text-center" id="modal-title">Add User</h3>
                </div>

                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="user_id" id="userId">

                <!-- Modal Body with Form Fields -->
                <div class="px-6 py-6 max-h-96 overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                <i class="fas fa-user mr-2 text-gray-400"></i>Full Name *
                            </label>
                            <input type="text" name="full_name" id="fullName" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                <i class="fas fa-user-circle mr-2 text-gray-400"></i>Username *
                            </label>
                            <input type="text" name="username" id="username" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                <i class="fas fa-envelope mr-2 text-gray-400"></i>Email *
                            </label>
                            <input type="email" name="email" id="email" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                <i class="fas fa-phone mr-2 text-gray-400"></i>Phone
                            </label>
                            <input type="tel" name="phone" id="phone"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                <i class="fas fa-lock mr-2 text-gray-400"></i>Password <span
                                    id="passwordRequired">*</span>
                            </label>
                            <input type="password" name="password" id="password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                            <p class="mt-1 text-xs text-gray-500 hidden" id="passwordHint">Leave blank to keep current
                                password</p>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                <i class="fas fa-user-tag mr-2 text-gray-400"></i>Role *
                            </label>
                            <select name="role" id="role" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                                <option value="employee">Employee</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                <i class="fas fa-toggle-on mr-2 text-gray-400"></i>Status *
                            </label>
                            <select name="status" id="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer with Buttons -->
                <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end gap-3">
                    <button type="button" onclick="closeModal()"
                        class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 transition">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-amber-500 to-orange-600 rounded-lg hover:from-amber-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-amber-500 transition">
                        <i class="fas fa-save mr-2"></i>Save User
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

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-30 backdrop-blur modal-backdrop">
        <div
            class="w-full max-w-md mx-4 overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl animate-modal">
            <div class="p-6 bg-gradient-to-r from-red-500 to-red-600">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-white rounded-full">
                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="p-6 text-center">
                <h3 class="mb-2 text-xl font-bold text-gray-900">Delete User</h3>
                <p class="text-gray-600 mb-1">Are you sure you want to delete</p>
                <p id="deleteUserName" class="text-lg font-semibold text-gray-900 mb-2"></p>
                <p class="text-sm text-red-600">This action cannot be undone.</p>
            </div>
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button onclick="closeDeleteModal()"
                    class="px-4 py-2 text-gray-700 bg-gray-300 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button onclick="confirmDelete()"
                    class="px-4 py-2 text-white rounded-lg bg-red-600 hover:bg-red-700 transition-colors">
                    <i class="fa-solid fa-trash mr-1"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Toggle Status Modal -->
    <div id="toggleStatusModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-30 backdrop-blur modal-backdrop">
        <div
            class="w-full max-w-md mx-4 overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl animate-modal">
            <div class="p-6 bg-gradient-to-r from-blue-500 to-blue-600">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-white rounded-full">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="p-6 text-center">
                <h3 class="mb-2 text-xl font-bold text-gray-900">Toggle User Status</h3>
                <p class="text-gray-600 mb-1">Change status of</p>
                <p id="toggleUserName" class="text-lg font-semibold text-gray-900 mb-2"></p>
                <p class="text-sm text-gray-600">to <span id="toggleStatusAction" class="font-semibold"></span>?</p>
            </div>
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button onclick="closeToggleStatusModal()"
                    class="px-4 py-2 text-gray-700 bg-gray-300 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button onclick="confirmToggleStatus()"
                    class="px-4 py-2 text-white rounded-lg bg-blue-600 hover:bg-blue-700 transition-colors">
                    <i class="fa-solid fa-toggle-on mr-1"></i> Confirm
                </button>
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

    <script src="../../assets/js/admin.js"></script>
    <script>
        function toggleMobileSidebar() {
            const e = document.getElementById("sidebar"),
                t = document.getElementById("sidebarOverlay"),
                s = document.getElementById("hamburgerIcon"),
                a = document.getElementById("mobileSidebarBtn"),
                l = !e.classList.contains("-translate-x-full");
            l ? (e.classList.add("-translate-x-full"), t.classList.add("opacity-0", "pointer-events-none"), t.classList.remove("opacity-50"), s.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />', s.classList.remove("rotate-90"), a.classList.remove("bg-gray-200"), a.classList.add("bg-gray-100")) : (e.classList.remove("-translate-x-full"), t.classList.remove("opacity-0", "pointer-events-none"), t.classList.add("opacity-50"), s.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />', s.classList.add("rotate-90"), a.classList.add("bg-gray-200"), a.classList.remove("bg-gray-100"))
        }
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll("#sidebar nav a").forEach(e => {
                e.addEventListener("click", function() {
                    window.innerWidth < 1024 && !document.getElementById("sidebar").classList.contains("-translate-x-full") && toggleMobileSidebar()
                })
            })
        });
        window.addEventListener("resize", function() {
            const e = document.getElementById("sidebar"),
                t = document.getElementById("sidebarOverlay"),
                s = document.getElementById("hamburgerIcon"),
                a = document.getElementById("mobileSidebarBtn");
            window.innerWidth >= 1024 ? (e.classList.remove("-translate-x-full"), t.classList.add("opacity-0", "pointer-events-none"), t.classList.remove("opacity-50")) : (e.classList.add("-translate-x-full"), t.classList.add("opacity-0", "pointer-events-none"), s.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />', s.classList.remove("rotate-90"), a.classList.remove("bg-gray-200"), a.classList.add("bg-gray-100"))
        });
        if (window.innerWidth < 1024) document.getElementById("sidebar").classList.add("-translate-x-full");
    </script>
    <script src="../../assets/js/users.js"></script>
    <script>
        // Modal helper functions
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

        // Add User Modal
        function openAddModal() {
            document.getElementById('modal-title').textContent = 'Add New User';
            document.getElementById('formAction').value = 'add';
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';

            // Make password required for new users
            document.getElementById('password').required = true;
            document.getElementById('passwordRequired').textContent = '*';
            document.getElementById('passwordHint').classList.add('hidden');

            document.getElementById('userModal').classList.remove('hidden');
        }

        // Close User Modal
        function closeModal() {
            document.getElementById('userModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Edit User Modal
        function editUser(id, username, email, fullName, role, phone, status) {
            document.getElementById('modal-title').textContent = 'Edit User';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('userId').value = id;
            document.getElementById('username').value = username;
            document.getElementById('email').value = email;
            document.getElementById('fullName').value = fullName;
            document.getElementById('role').value = role;
            document.getElementById('phone').value = phone || '';
            document.getElementById('status').value = status;

            // Make password optional for edit
            document.getElementById('password').required = false;
            document.getElementById('password').value = '';
            document.getElementById('passwordRequired').textContent = '';
            document.getElementById('passwordHint').classList.remove('hidden');

            document.getElementById('userModal').classList.remove('hidden');
        }

        // Delete User Modal
        let deleteUserData = {
            id: null,
            name: null
        };

        function deleteUser(id, name) {
            deleteUserData = {
                id,
                name
            };
            document.getElementById('deleteUserName').textContent = `"${name}"?`;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            deleteUserData = {
                id: null,
                name: null
            };
        }

        function confirmDelete() {
            if (deleteUserData.id) {
                document.getElementById('deleteUserId').value = deleteUserData.id;
                document.getElementById('deleteForm').submit();
            }
        }

        // Toggle Status Modal
        let toggleStatusData = {
            id: null,
            name: null,
            currentStatus: null
        };

        function toggleUserStatus(id, name, currentStatus) {
            toggleStatusData = {
                id,
                name,
                currentStatus
            };
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            document.getElementById('toggleUserName').textContent = name;
            document.getElementById('toggleStatusAction').textContent = newStatus;
            document.getElementById('toggleStatusModal').classList.remove('hidden');
        }

        function closeToggleStatusModal() {
            document.getElementById('toggleStatusModal').classList.add('hidden');
            toggleStatusData = {
                id: null,
                name: null,
                currentStatus: null
            };
        }

        function confirmToggleStatus() {
            if (toggleStatusData.id) {
                document.getElementById('toggleUserId').value = toggleStatusData.id;
                document.getElementById('toggleStatusForm').submit();
            }
        }
    </script>
</body>

</html>