<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

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

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Bro's Cafe</title>
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
</head>

<body class="bg-gray-100 font-['Montserrat']">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white flex flex-col">
            <div class="p-4 border-b border-gray-800">
                <div class="flex items-center">
                    <img src="../../assets/images/logo.png" alt="Logo" class="w-10 h-10 rounded-full">
                    <div class="ml-3">
                        <h1 class="font-bold text-lg">Bro's Cafe</h1>
                        <p class="text-xs text-gray-400">Inventory</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 p-4">
                <ul class="space-y-2">
                    <?php if (isAdmin()): ?>
                        <li>
                            <a href="../admin/dashboard.php" class="flex items-center px-4 py-3 hover:bg-gray-800 rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                Dashboard
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="../employee/pos.php" class="flex items-center px-4 py-3 hover:bg-gray-800 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            POS
                        </a>
                    </li>
                    <li>
                        <a href="inventory.php" class="flex items-center px-4 py-3 bg-amber-600 rounded-lg">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            Inventory
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="p-4 border-t border-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold"><?php echo $current_user['full_name']; ?></p>
                        <p class="text-xs text-gray-400"><?php echo ucfirst($current_user['role']); ?></p>
                    </div>
                    <a href="../../pages/logout.php" class="text-red-400 hover:text-red-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold text-gray-800">Inventory Management</h2>
                    <p class="text-gray-600">Track and manage product stock levels</p>
                </div>

                <!-- Low Stock Alert -->
                <?php if (count($low_stock) > 0): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Low Stock Alert</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <?php foreach ($low_stock as $item): ?>
                                            <li><?php echo $item['name']; ?>: <?php echo $item['quantity']; ?> remaining (reorder at <?php echo $item['reorder_level']; ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <p class="text-gray-500 text-sm">Total Products</p>
                                <p class="text-gray-900 text-2xl font-semibold"><?php echo count($products); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <p class="text-gray-500 text-sm">Low Stock Items</p>
                                <p class="text-gray-900 text-2xl font-semibold"><?php echo count($low_stock); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <p class="text-gray-500 text-sm">Available Items</p>
                                <p class="text-gray-900 text-2xl font-semibold">
                                    <?php echo count(array_filter($products, fn($p) => $p['quantity'] > $p['reorder_level'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Product Inventory</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder Level</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Restocked</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo $product['name']; ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $product['category_name']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?php echo $product['quantity']; ?> <?php echo $product['unit'] ?? 'servings'; ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $product['reorder_level']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($product['quantity'] <= $product['reorder_level']): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Low Stock</span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">In Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $product['last_restocked'] ? date('M d, Y', strtotime($product['last_restocked'])) : 'Never'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="openRestockModal(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['quantity']; ?>)"
                                                class="text-amber-600 hover:text-amber-900 mr-3">Restock</button>
                                            <button onclick="openAdjustModal(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['quantity']; ?>)"
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
    <div id="restockModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Restock Product</h3>
                <form id="restockForm" onsubmit="submitRestock(event)">
                    <input type="hidden" id="restock_product_id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                        <p id="restock_product_name" class="text-gray-900 font-semibold"></p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Stock</label>
                        <p id="restock_current_stock" class="text-gray-900"></p>
                    </div>
                    <div class="mb-4">
                        <label for="restock_quantity" class="block text-sm font-medium text-gray-700 mb-2">Add Quantity</label>
                        <input type="number" id="restock_quantity" min="1" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                    </div>
                    <div class="mb-4">
                        <label for="restock_notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                        <textarea id="restock_notes" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-amber-500 focus:border-amber-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRestockModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700">Restock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openRestockModal(productId, productName, currentStock) {
            document.getElementById('restock_product_id').value = productId;
            document.getElementById('restock_product_name').textContent = productName;
            document.getElementById('restock_current_stock').textContent = currentStock + ' servings';
            document.getElementById('restockModal').classList.remove('hidden');
        }

        function closeRestockModal() {
            document.getElementById('restockModal').classList.add('hidden');
            document.getElementById('restockForm').reset();
        }

        function openAdjustModal(productId, productName, currentStock) {
            // Similar to restock but allows both increase and decrease
            const adjustment = prompt(`Adjust stock for ${productName}\nCurrent: ${currentStock}\nEnter adjustment (+/-):`);
            if (adjustment) {
                adjustStock(productId, parseInt(adjustment));
            }
        }

        function submitRestock(event) {
            event.preventDefault();

            const productId = document.getElementById('restock_product_id').value;
            const quantity = document.getElementById('restock_quantity').value;
            const notes = document.getElementById('restock_notes').value;

            fetch('update_inventory.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity,
                        type: 'restock',
                        notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Stock updated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        function adjustStock(productId, adjustment) {
            fetch('update_inventory.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: adjustment,
                        type: 'adjustment'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Stock adjusted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }
    </script>
</body>

</html>