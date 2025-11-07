<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

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
    SELECT p.*, c.name as category_name, i.quantity as stock_quantity
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN inventory i ON p.id = i.product_id
    ORDER BY p.name
");
$products = $stmt->fetchAll();

// Get all categories
$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Bro's Cafe</title>
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
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
                        <a href="orders.php" class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
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
                        <a href="products.php" class="flex items-center px-4 py-3 rounded-lg bg-amber-600">
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
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <button onclick="toggleSidebar()" id="hamburger-btn"
                            class="p-3 mr-4 text-white transition-all rounded-full shadow-lg bg-amber-600 hover:bg-amber-700 hover:shadow-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div>
                            <h2 class="text-3xl font-bold text-gray-800">Products Management</h2>
                            <p class="text-gray-600">Manage your menu items and pricing</p>
                        </div>
                    </div>
                    <button onclick="openAddModal()"
                        class="px-6 py-3 text-white transition-colors rounded-lg shadow-md bg-amber-600 hover:bg-amber-700">
                        <i class="mr-2 fa-solid fa-plus"></i>Add Product
                    </button>
                </div>

                <!-- Alert Messages -->
                <?php if ($message): ?>
                    <div class="p-4 mb-6 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border-l-4 border-green-500' : 'bg-red-100 text-red-800 border-l-4 border-red-500'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Statistics -->
                <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-3">
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
                                <p class="text-sm text-gray-600">Available</p>
                                <p class="text-2xl font-bold text-green-600">
                                    <?php echo count(array_filter($products, fn($p) => $p['status'] === 'available')); ?>
                                </p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <i class="text-2xl text-green-600 fa-solid fa-check-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white rounded-lg shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Unavailable</p>
                                <p class="text-2xl font-bold text-red-600">
                                    <?php echo count(array_filter($products, fn($p) => $p['status'] === 'unavailable')); ?>
                                </p>
                            </div>
                            <div class="p-3 bg-red-100 rounded-full">
                                <i class="text-2xl text-red-600 fa-solid fa-times-circle"></i>
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
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Product Name
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Category
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Dodici (12oz)
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Sedici (16oz)
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Stock
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
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
                                                    <div class="text-sm font-medium text-gray-900"><?php echo $product['name']; ?></div>
                                                    <div class="text-sm text-gray-500"><?php echo substr($product['description'] ?? '', 0, 50); ?></div>
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
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                            <?php echo $product['stock_quantity'] ?? 0; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                <?php echo $product['status'] === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo ucfirst($product['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                                            <button onclick='editProduct(<?php echo json_encode($product); ?>)'
                                                class="mr-3 text-blue-600 hover:text-blue-800">
                                                <i class="fa-solid fa-edit"></i>
                                            </button>
                                            <button onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')"
                                                class="text-red-600 hover:text-red-800">
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
    <div id="productModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form method="POST" id="productForm">
                    <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">Add Product</h3>
                        </div>

                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="product_id" id="productId">

                        <div class="space-y-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Product Name *</label>
                                <input type="text" name="name" id="productName" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Category *</label>
                                <select name="category_id" id="categoryId" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="productDescription" rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700">Dodici (12oz) Price</label>
                                    <input type="number" name="price_dodici" id="priceDodici" step="0.01" min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700">Sedici (16oz) Price</label>
                                    <input type="number" name="price_sedici" id="priceSedici" step="0.01" min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                                </div>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Status *</label>
                                <select name="status" id="productStatus" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                                    <option value="available">Available</option>
                                    <option value="unavailable">Unavailable</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-md shadow-sm bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Save Product
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
        <input type="hidden" name="product_id" id="deleteProductId">
    </form>

    <script src="../assets/js/admin.js"></script>
    <script>
        function openAddModal() {
            document.getElementById('modal-title').textContent = 'Add Product';
            document.getElementById('formAction').value = 'add';
            document.getElementById('productForm').reset();
            document.getElementById('productModal').classList.remove('hidden');
        }

        function editProduct(product) {
            document.getElementById('modal-title').textContent = 'Edit Product';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('categoryId').value = product.category_id;
            document.getElementById('productDescription').value = product.description || '';
            document.getElementById('priceDodici').value = product.price_dodici || '';
            document.getElementById('priceSedici').value = product.price_sedici || '';
            document.getElementById('productStatus').value = product.status;
            document.getElementById('productModal').classList.remove('hidden');
        }

        function deleteProduct(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
                document.getElementById('deleteProductId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        function closeModal() {
            document.getElementById('productModal').classList.add('hidden');
        }
    </script>
</body>

</html>