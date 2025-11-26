<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $conn->prepare("
                    INSERT INTO products (category_id, name, description, price_dodici, price_sedici, status)
                    VALUES (:category_id, :name, :description, :price_dodici, :price_sedici, :status)
                ");
                $success = $stmt->execute([
                    'category_id' => $_POST['category_id'],
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'price_dodici' => $_POST['price_dodici'] ?: null,
                    'price_sedici' => $_POST['price_sedici'] ?: null,
                    'status' => $_POST['status']
                ]);

                if ($success) {
                    $product_id = $conn->lastInsertId();
                    // Create inventory entry
                    $stmt = $conn->prepare("
                        INSERT INTO inventory (product_id, quantity, unit, reorder_level)
                        VALUES (:product_id, 0, 'pcs', 10)
                    ");
                    $stmt->execute(['product_id' => $product_id]);
                    $message = 'Product added successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error adding product.';
                    $message_type = 'error';
                }
                break;

            case 'edit':
                $stmt = $conn->prepare("
                    UPDATE products 
                    SET category_id = :category_id, 
                        name = :name, 
                        description = :description, 
                        price_dodici = :price_dodici, 
                        price_sedici = :price_sedici, 
                        status = :status
                    WHERE id = :id
                ");
                $success = $stmt->execute([
                    'id' => $_POST['product_id'],
                    'category_id' => $_POST['category_id'],
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'price_dodici' => $_POST['price_dodici'] ?: null,
                    'price_sedici' => $_POST['price_sedici'] ?: null,
                    'status' => $_POST['status']
                ]);
                $message = $success ? 'Product updated successfully!' : 'Error updating product.';
                $message_type = $success ? 'success' : 'error';
                break;

            case 'delete':
                $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
                $success = $stmt->execute(['id' => $_POST['product_id']]);
                $message = $success ? 'Product deleted successfully!' : 'Error deleting product.';
                $message_type = $success ? 'success' : 'error';
                break;
        }
    }
}

// Get all products
$stmt = $conn->query("
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.name
");
$products = $stmt->fetchAll();

// Get all categories
$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Get all ingredients
$stmt = $conn->query("SELECT * FROM ingredients ORDER BY name");
$ingredients = $stmt->fetchAll();

// Calculate statistics
$products_with_both_sizes = count(array_filter($products, fn($p) => $p['price_dodici'] && $p['price_sedici']));
$products_single_size = count($products) - $products_with_both_sizes;

// Get average price (considering both sizes)
$total_prices = 0;
$price_count = 0;
foreach ($products as $product) {
    if ($product['price_dodici']) {
        $total_prices += $product['price_dodici'];
        $price_count++;
    }
    if ($product['price_sedici']) {
        $total_prices += $product['price_sedici'];
        $price_count++;
    }
}
$avg_price = $price_count > 0 ? $total_prices / $price_count : 0;

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Bro's Cafe</title>
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
                            class="flex items-center px-4 py-3 rounded-lg bg-amber-600">
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
            <div class="flex items-center justify-between bg-white shadow-lg p-6">
                <div class="flex items-center">
                    <div class="flex flex-col justify-center">
                        <h2 class="text-3xl font-bold text-gray-800">Products Management</h2>
                        <p class="text-md text-gray-600">Manage your menu items and pricing</p>
                    </div>
                </div>
                <button onclick="openAddModal()"
                    class="px-6 py-3 text-white transition-colors rounded-lg shadow-md bg-amber-600 hover:bg-amber-700">
                    <i class="mr-2 fa-solid fa-plus"></i>Add Product
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
                <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2 lg:grid-cols-4">
                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Total Products</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo count($products); ?></p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <i class="text-2xl text-blue-600 fa-solid fa-coffee"></i>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Dual Size Options</p>
                                <p class="text-2xl font-bold text-green-600">
                                    <?php echo $products_with_both_sizes; ?>
                                </p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <i class="text-2xl text-green-600 fa-solid fa-arrows-left-right"></i>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Average Price</p>
                                <p class="text-2xl font-bold text-amber-600">
                                    ₱<?php echo number_format($avg_price, 2); ?>
                                </p>
                            </div>
                            <div class="p-3 bg-amber-100 rounded-full">
                                <i class="text-2xl text-amber-600 fa-solid fa-peso-sign"></i>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Categories</p>
                                <p class="text-2xl font-bold text-purple-600"><?php echo count($categories); ?></p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <i class="text-2xl text-purple-600 fa-solid fa-layer-group"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="overflow-hidden bg-white rounded-lg shadow-md">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Product Name
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Category
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Dodici (12oz)
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Sedici (16oz)
                                    </th>
                                    <th
                                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($products as $product): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo $product['name']; ?></div>
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo substr($product['description'] ?? '', 0, 50); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            <?php echo $product['category_name']; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                            <?php echo $product['price_dodici'] ? '₱' . number_format($product['price_dodici'], 2) : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                            <?php echo $product['price_sedici'] ? '₱' . number_format($product['price_sedici'], 2) : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                                            <button
                                                onclick="viewIngredients(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')"
                                                class="mr-3 text-purple-600 hover:text-purple-800" title="View Ingredients">
                                                <i class="fa-solid fa-list"></i>
                                            </button>
                                            <button onclick='editProduct(<?php echo json_encode($product); ?>)'
                                                class="mr-3 text-blue-600 hover:text-blue-800" title="Edit Product">
                                                <i class="fa-solid fa-edit"></i>
                                            </button>
                                            <button
                                                onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')"
                                                class="text-red-600 hover:text-red-800" title="Delete Product">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
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

    <!-- Add/Edit Product Modal -->
    <div id="productModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-30 backdrop-blur modal-backdrop"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div
            class="w-full max-w-4xl mx-4 overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl animate-modal border border-amber-200">
            <form id="productForm" onsubmit="saveProduct(event)">
                <!-- Modal Header -->
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-amber-500 to-amber-600">
                    <div class="flex items-center justify-between text-white">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-white bg-opacity-20 rounded-lg">
                                <i class="text-xl fa-solid fa-box"></i>
                            </div>
                            <h3 class="text-xl font-bold" id="modal-title">Add Product</h3>
                        </div>
                        <button type="button" onclick="closeModal()"
                            class="text-white hover:text-gray-200 transition-colors">
                            <i class="text-2xl fa-solid fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4" style="max-height: 600px; overflow-y: auto;">

                    <input type="hidden" id="formAction" value="add">
                    <input type="hidden" id="productId">

                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Product Name *</label>
                            <input type="text" id="productName" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Category *</label>
                            <div class="flex gap-2">
                                <select id="categoryId" required
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" onclick="openAddCategoryModal()"
                                    class="px-3 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700"
                                    title="Add New Category">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Description</label>
                        <textarea id="productDescription" rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"></textarea>
                    </div>

                    <!-- Sizes Section -->
                    <div class="border-t pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-sm font-medium text-gray-700">Sizes & Prices</label>
                            <button type="button" onclick="addSize()"
                                class="px-3 py-1 text-sm text-white bg-green-600 rounded-lg hover:bg-green-700">
                                <i class="fa-solid fa-plus mr-1"></i> Add Size
                            </button>
                        </div>
                        <div id="sizesContainer" class="space-y-2">
                            <!-- Sizes will be added here dynamically -->
                        </div>
                    </div>

                    <!-- Ingredients Section -->
                    <div class="border-t pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-sm font-medium text-gray-700">Ingredients</label>
                            <div class="space-x-2">
                                <button type="button" onclick="openAddIngredientModal()"
                                    class="px-3 py-1 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                    <i class="fa-solid fa-flask mr-1"></i> New Ingredient
                                </button>
                                <button type="button" onclick="addIngredientRow()"
                                    class="px-3 py-1 text-sm text-white bg-green-600 rounded-lg hover:bg-green-700">
                                    <i class="fa-solid fa-plus mr-1"></i> Add Ingredient
                                </button>
                            </div>
                        </div>
                        <div id="ingredientsContainer" class="space-y-2">
                            <!-- Ingredients will be added here dynamically -->
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Status *</label>
                        <select id="productStatus" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()"
                        class="px-4 py-2 text-gray-700 bg-gray-300 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-white rounded-lg bg-amber-600 hover:bg-amber-700 transition-colors">
                        <i class="fa-solid fa-save mr-1"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="product_id" id="deleteProductId">
    </form>

    <!-- Ingredients Modal -->
    <div id="ingredientsModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-30 backdrop-blur modal-backdrop"
        aria-labelledby="ingredients-modal-title" role="dialog" aria-modal="true">
        <div
            class="w-full max-w-2xl mx-4 overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl animate-modal border border-purple-200">
            <!-- Modal Header -->
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-purple-500 to-purple-600">
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center justify-center w-10 h-10 bg-white bg-opacity-20 rounded-lg">
                            <i class="text-xl fa-solid fa-list"></i>
                        </div>
                        <h3 class="text-xl font-bold" id="ingredients-modal-title">
                            Ingredients for <span id="productNameDisplay" class="font-bold"></span>
                        </h3>
                    </div>
                    <button onclick="closeIngredientsModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i class="text-2xl fa-solid fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6" style="max-height: 500px; overflow-y: auto;">
                <div id="ingredientsContent">
                    <!-- Ingredients will be loaded here -->
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end">
                <button type="button" onclick="closeIngredientsModal()"
                    class="px-4 py-2 text-white rounded-lg bg-purple-600 hover:bg-purple-700 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Add Ingredient Modal -->
    <div id="addIngredientModal"
        class="fixed inset-0 z-[60] hidden flex items-center justify-center bg-black bg-opacity-30 backdrop-blur modal-backdrop">
        <div
            class="w-full max-w-md mx-4 overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl animate-modal border border-blue-200">
            <form id="addIngredientForm" onsubmit="saveNewIngredient(event)">
                <!-- Modal Header -->
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-500 to-blue-600">
                    <div class="flex items-center justify-between text-white">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-white bg-opacity-20 rounded-lg">
                                <i class="text-xl fa-solid fa-flask"></i>
                            </div>
                            <h3 class="text-xl font-bold">Add New Ingredient</h3>
                        </div>
                        <button type="button" onclick="closeAddIngredientModal()"
                            class="text-white hover:text-gray-200 transition-colors">
                            <i class="text-2xl fa-solid fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Ingredient Name *</label>
                        <input type="text" id="newIngredientName" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Description</label>
                        <textarea id="newIngredientDescription" rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Unit</label>
                            <select id="newIngredientUnit"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="g">Grams (g)</option>
                                <option value="ml">Milliliters (ml)</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="kg">Kilograms (kg)</option>
                                <option value="L">Liters (L)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Price (₱)</label>
                            <input type="number" id="newIngredientPrice" step="0.01" min="0" value="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="closeAddIngredientModal()"
                        class="px-4 py-2 text-gray-700 bg-gray-300 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-white rounded-lg bg-blue-600 hover:bg-blue-700 transition-colors">
                        <i class="fa-solid fa-save mr-1"></i> Add Ingredient
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal"
        class="fixed inset-0 z-[60] hidden flex items-center justify-center bg-black bg-opacity-30 backdrop-blur modal-backdrop">
        <div
            class="w-full max-w-md mx-4 overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl animate-modal border border-green-200">
            <form id="addCategoryForm" onsubmit="saveNewCategory(event)">
                <!-- Modal Header -->
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-green-500 to-green-600">
                    <div class="flex items-center justify-between text-white">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-white bg-opacity-20 rounded-lg">
                                <i class="text-xl fa-solid fa-tag"></i>
                            </div>
                            <h3 class="text-xl font-bold">Add New Category</h3>
                        </div>
                        <button type="button" onclick="closeAddCategoryModal()"
                            class="text-white hover:text-gray-200 transition-colors">
                            <i class="text-2xl fa-solid fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Category Name *</label>
                        <input type="text" id="newCategoryName" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Description</label>
                        <textarea id="newCategoryDescription" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="closeAddCategoryModal()"
                        class="px-4 py-2 text-gray-700 bg-gray-300 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-white rounded-lg bg-green-600 hover:bg-green-700 transition-colors">
                        <i class="fa-solid fa-save mr-1"></i> Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/admin.js"></script>
    <script src="../../assets/js/products.js"></script>
    <script>
        // Available ingredients from PHP
        const availableIngredients = <?php echo json_encode($ingredients); ?>;

        // Initialize the form when adding a product
        function openAddModal() {
            document.getElementById('formAction').value = 'add';
            document.getElementById('modal-title').textContent = 'Add Product';
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';

            // Clear sizes and ingredients
            document.getElementById('sizesContainer').innerHTML = '';
            document.getElementById('ingredientsContainer').innerHTML = '';

            // Add default sizes
            addSize('dodici', 'Dodici (12oz)', '');
            addSize('sedici', 'Sedici (16oz)', '');

            document.getElementById('productModal').classList.remove('hidden');
        }

        function addSize(type = '', label = '', price = '') {
            const container = document.getElementById('sizesContainer');
            const sizeId = Date.now() + Math.random();

            const sizeTypes = [{
                    value: 'dodici',
                    label: 'Dodici (12oz)'
                },
                {
                    value: 'sedici',
                    label: 'Sedici (16oz)'
                },
                {
                    value: 'custom',
                    label: 'Custom Size'
                }
            ];

            const sizeHtml = `
                <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg" data-size-id="${sizeId}">
                    <select class="size-type flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                        ${sizeTypes.map(st => `<option value="${st.value}" ${st.value === type ? 'selected' : ''}>${st.label}</option>`).join('')}
                    </select>
                    <input type="text" class="size-label flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500" 
                        placeholder="Size Label" value="${label}">
                    <input type="number" class="size-price w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500" 
                        placeholder="Price" step="0.01" min="0" value="${price}" required>
                    <button type="button" onclick="removeSize('${sizeId}')" class="px-3 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', sizeHtml);
        }

        function removeSize(sizeId) {
            const element = document.querySelector(`[data-size-id="${sizeId}"]`);
            if (element) element.remove();
        }

        function addIngredientRow(ingredientId = '', quantity = '', unit = 'g') {
            const container = document.getElementById('ingredientsContainer');
            const rowId = Date.now() + Math.random();

            const selectedIngredient = ingredientId ? availableIngredients.find(ing => ing.id == ingredientId) : null;
            const ingredientName = selectedIngredient ? selectedIngredient.name : '';

            const ingredientHtml = `
                <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg" data-ingredient-id="${rowId}">
                    <div class="flex-1 relative">
                        <input type="text" class="ingredient-search w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500" 
                            placeholder="Search ingredient..." value="${ingredientName}" autocomplete="off" required
                            onfocus="showIngredientDropdown('${rowId}')" 
                            oninput="filterIngredients('${rowId}', this.value)">
                        <input type="hidden" class="ingredient-id" value="${ingredientId}">
                        <div class="ingredient-dropdown hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            ${availableIngredients.map(ing => `
                                <div class="ingredient-option px-3 py-2 hover:bg-amber-50 cursor-pointer" data-id="${ing.id}" data-name="${ing.name}"
                                    onclick="selectIngredient('${rowId}', ${ing.id}, '${ing.name}')">
                                    ${ing.name}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <input type="number" class="ingredient-quantity w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500" 
                        placeholder="Quantity" step="0.01" min="0.01" value="${quantity}" required>
                    <select class="ingredient-unit w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                        <option value="g" ${unit === 'g' ? 'selected' : ''}>g</option>
                        <option value="ml" ${unit === 'ml' ? 'selected' : ''}>ml</option>
                        <option value="pcs" ${unit === 'pcs' ? 'selected' : ''}>pcs</option>
                        <option value="kg" ${unit === 'kg' ? 'selected' : ''}>kg</option>
                        <option value="L" ${unit === 'L' ? 'selected' : ''}>L</option>
                    </select>
                    <button type="button" onclick="removeIngredient('${rowId}')" class="px-3 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', ingredientHtml);
        }

        function removeIngredient(rowId) {
            const element = document.querySelector(`[data-ingredient-id="${rowId}"]`);
            if (element) element.remove();
        }

        function showIngredientDropdown(rowId) {
            const row = document.querySelector(`[data-ingredient-id="${rowId}"]`);
            const dropdown = row.querySelector('.ingredient-dropdown');
            dropdown.classList.remove('hidden');
        }

        function filterIngredients(rowId, searchTerm) {
            const row = document.querySelector(`[data-ingredient-id="${rowId}"]`);
            const dropdown = row.querySelector('.ingredient-dropdown');
            const options = dropdown.querySelectorAll('.ingredient-option');

            searchTerm = searchTerm.toLowerCase();
            let hasVisible = false;

            options.forEach(option => {
                const name = option.getAttribute('data-name').toLowerCase();
                if (name.includes(searchTerm)) {
                    option.classList.remove('hidden');
                    hasVisible = true;
                } else {
                    option.classList.add('hidden');
                }
            });

            if (hasVisible) {
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
        }

        function selectIngredient(rowId, ingredientId, ingredientName) {
            const row = document.querySelector(`[data-ingredient-id="${rowId}"]`);
            row.querySelector('.ingredient-search').value = ingredientName;
            row.querySelector('.ingredient-id').value = ingredientId;
            row.querySelector('.ingredient-dropdown').classList.add('hidden');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.ingredient-search') && !event.target.closest('.ingredient-dropdown')) {
                document.querySelectorAll('.ingredient-dropdown').forEach(dropdown => {
                    dropdown.classList.add('hidden');
                });
            }
        });

        function saveProduct(event) {
            event.preventDefault();

            // Collect sizes
            const sizes = [];
            document.querySelectorAll('#sizesContainer > div').forEach(sizeDiv => {
                const type = sizeDiv.querySelector('.size-type').value;
                const price = parseFloat(sizeDiv.querySelector('.size-price').value);
                if (type && price) {
                    sizes.push({
                        type,
                        price
                    });
                }
            });

            // Collect ingredients
            const ingredients = [];
            document.querySelectorAll('#ingredientsContainer > div').forEach(ingDiv => {
                const id = ingDiv.querySelector('.ingredient-id').value;
                const quantity = parseFloat(ingDiv.querySelector('.ingredient-quantity').value);
                const unit = ingDiv.querySelector('.ingredient-unit').value;
                if (id && quantity) {
                    ingredients.push({
                        id,
                        quantity,
                        unit
                    });
                }
            });

            const data = {
                action: document.getElementById('formAction').value,
                product_id: document.getElementById('productId').value,
                name: document.getElementById('productName').value,
                category_id: document.getElementById('categoryId').value,
                description: document.getElementById('productDescription').value,
                status: document.getElementById('productStatus').value,
                sizes: sizes,
                ingredients: ingredients
            };

            fetch('save_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        closeModal();
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to save product');
                });
        }

        function openAddIngredientModal() {
            document.getElementById('addIngredientForm').reset();
            document.getElementById('addIngredientModal').classList.remove('hidden');
        }

        function closeAddIngredientModal() {
            document.getElementById('addIngredientModal').classList.add('hidden');
        }

        function saveNewIngredient(event) {
            event.preventDefault();

            const data = {
                name: document.getElementById('newIngredientName').value,
                description: document.getElementById('newIngredientDescription').value,
                unit: document.getElementById('newIngredientUnit').value,
                price: parseFloat(document.getElementById('newIngredientPrice').value)
            };

            fetch('add_ingredient.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        // Add new ingredient to the available list
                        availableIngredients.push(result.ingredient);
                        closeAddIngredientModal();

                        // Refresh ingredient selects
                        document.querySelectorAll('.ingredient-select').forEach(select => {
                            const currentValue = select.value;
                            select.innerHTML = '<option value="">Select Ingredient</option>' +
                                availableIngredients.map(ing =>
                                    `<option value="${ing.id}">${ing.name}</option>`).join('');
                            select.value = currentValue;
                        });
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to add ingredient');
                });
        }

        function openAddCategoryModal() {
            document.getElementById('addCategoryForm').reset();
            document.getElementById('addCategoryModal').classList.remove('hidden');
        }

        function closeAddCategoryModal() {
            document.getElementById('addCategoryModal').classList.add('hidden');
        }

        function saveNewCategory(event) {
            event.preventDefault();

            const data = {
                name: document.getElementById('newCategoryName').value,
                description: document.getElementById('newCategoryDescription').value
            };

            fetch('add_category.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        closeAddCategoryModal();

                        // Add new category to the dropdown
                        const categorySelect = document.getElementById('category_id');
                        const newOption = document.createElement('option');
                        newOption.value = result.category.id;
                        newOption.textContent = result.category.name;
                        categorySelect.appendChild(newOption);
                        categorySelect.value = result.category.id;
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to add category');
                });
        }

        function viewIngredients(productId, productName) {
            document.getElementById('productNameDisplay').textContent = productName;
            document.getElementById('ingredientsModal').classList.remove('hidden');
            document.getElementById('ingredientsContent').innerHTML =
                '<div class="text-center py-8"><i class="fa-solid fa-spinner fa-spin text-3xl text-amber-600"></i><p class="mt-2 text-gray-600">Loading ingredients...</p></div>';

            // Fetch ingredients
            fetch('get_product_ingredients.php?product_id=' + productId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.ingredients.length > 0) {
                            let html = '<div class="overflow-x-auto">';
                            html += '<table class="w-full">';
                            html += '<thead class="bg-gray-50">';
                            html += '<tr>';
                            html +=
                                '<th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Ingredient</th>';
                            html +=
                                '<th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Quantity</th>';
                            html +=
                                '<th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Description</th>';
                            html += '</tr>';
                            html += '</thead>';
                            html += '<tbody class="bg-white divide-y divide-gray-200">';

                            data.ingredients.forEach(ing => {
                                html += '<tr class="hover:bg-gray-50">';
                                html += '<td class="px-4 py-3 text-sm font-medium text-gray-900">' + ing.name +
                                    '</td>';
                                html += '<td class="px-4 py-3 text-sm text-gray-700">' + ing.quantity + ' ' +
                                    ing.unit + '</td>';
                                html += '<td class="px-4 py-3 text-sm text-gray-500">' + (ing.description ||
                                    '-') + '</td>';
                                html += '</tr>';
                            });

                            html += '</tbody>';
                            html += '</table>';
                            html += '</div>';
                            document.getElementById('ingredientsContent').innerHTML = html;
                        } else {
                            document.getElementById('ingredientsContent').innerHTML =
                                '<div class="text-center py-8"><i class="fa-solid fa-info-circle text-4xl text-gray-400"></i><p class="mt-2 text-gray-600">No ingredients found for this product.</p></div>';
                        }
                    } else {
                        document.getElementById('ingredientsContent').innerHTML =
                            '<div class="text-center py-8"><i class="fa-solid fa-exclamation-triangle text-4xl text-red-500"></i><p class="mt-2 text-red-600">Error: ' +
                            data.message + '</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('ingredientsContent').innerHTML =
                        '<div class="text-center py-8"><i class="fa-solid fa-exclamation-triangle text-4xl text-red-500"></i><p class="mt-2 text-red-600">Failed to load ingredients.</p></div>';
                });
        }

        function closeIngredientsModal() {
            document.getElementById('ingredientsModal').classList.add('hidden');
        }
    </script>
</body>

</html>