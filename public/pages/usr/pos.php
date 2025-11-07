<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

$db = new Database();
$conn = $db->getConnection();

// Get all categories
$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Get all products with inventory
$stmt = $conn->query("
    SELECT p.*, c.name as category_name, i.quantity as stock 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN inventory i ON p.id = i.product_id 
    WHERE p.status = 'available'
    ORDER BY c.name, p.name
");
$products = $stmt->fetchAll();

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System - Bro's Cafe</title>
    <link rel="stylesheet" href="../../../src/output.css">
    <link rel="stylesheet" href="../../assets/css/pos.css">
    <link rel="icon" type="image/png" href="../../assets/images/logo.png">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body class="bg-gray-100 font-['Montserrat']">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="flex flex-col w-64 text-white bg-gray-900">
            <div class="p-4 border-b border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <img src="../../assets/images/logo.png" alt="Logo" class="w-10 h-10 rounded-full">
                        <div class="ml-3">
                            <h1 class="text-lg font-bold">Bro's Cafe</h1>
                            <p class="text-xs text-gray-400"><?php echo ucfirst($current_user['role']); ?> Panel</p>
                        </div>
                    </div>
                    <button onclick="toggleSidebar()" class="text-gray-400 transition-colors hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <nav class="flex-1 p-4">
                <ul class="space-y-2">
                    <?php if (isAdmin()): ?>
                        <li>
                            <a href="dashboard.php"
                                class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                Dashboard
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (isEmployee()): ?>
                        <li>
                            <a href="pos.php" class="flex items-center px-4 py-3 rounded-lg bg-amber-600">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                POS
                            </a>
                        </li>
                        <li>
                            <a href="orders.php"
                                class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Orders
                            </a>
                        </li>
                        <li>
                            <a href="inventory.php"
                                class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Inventory
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <li>
                            <a href="analytics.php"
                                class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                                <i class="w-5 h-5 mr-3 fa-solid fa-chart-simple"></i>
                                Analytics
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="products.php"
                            class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Products
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li>
                            <a href="users.php"
                                class="flex items-center px-4 py-3 transition-colors rounded-lg hover:bg-gray-800">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Employees
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="p-4 border-t border-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold"><?php echo $current_user['full_name']; ?></p>
                        <p class="text-xs text-gray-400"><?php echo ucfirst($current_user['role']); ?></p>
                    </div>
                    <a href="../logout.php" class="text-red-400 hover:text-red-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex flex-1 overflow-hidden">
            <!-- Products Section -->
            <div class="flex flex-col flex-1 overflow-y-auto">
                <div class="flex items-center justify-between p-6 pb-4">
                    <!-- Hamburger Menu Button -->
                    <button onclick="toggleSidebar()" id="hamburger-btn"
                        class="p-3 mr-3 text-white transition-all rounded-full shadow-lg bg-amber-600 hover:bg-amber-700 hover:shadow-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-800">Products</h2>
                        <p class="text-gray-600">Select items to add to order</p>
                    </div>
                    <!-- Cart Toggle Button -->
                    <div class="header-cart-btn">
                        <button onclick="toggleCart()"
                            class="relative p-3 text-white transition-all rounded-full shadow-lg bg-amber-600 hover:bg-amber-700 hover:shadow-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <!-- Badge -->
                            <span id="cart-badge"
                                class="absolute items-center justify-center h-6 px-2 text-xs font-bold text-white bg-red-500 rounded-full -top-1 -right-1 min-w-6"
                                style="display: none;">0</span>
                        </button>
                    </div>
                </div>

                <div class="px-6">
                    <!-- Category Filter -->
                    <div class="flex gap-2 pb-2 mb-6 overflow-x-auto">
                        <button onclick="filterCategory('all')"
                            class="px-4 py-2 text-white rounded-lg category-btn bg-amber-600 whitespace-nowrap">All</button>
                        <?php foreach ($categories as $category): ?>
                            <button onclick="filterCategory('<?php echo $category['id']; ?>')"
                                class="px-4 py-2 text-gray-700 transition-all bg-white rounded-lg category-btn whitespace-nowrap"><?php echo $category['name']; ?></button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Products Grid -->
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4" id="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="transition-shadow bg-white rounded-lg shadow cursor-pointer product-card hover:shadow-lg"
                                data-category="<?php echo $product['category_id']; ?>"
                                onclick='addToCart(<?php echo json_encode($product); ?>)'>
                                <div class="p-4">
                                    <div
                                        class="flex items-center justify-center w-full h-32 mb-3 rounded-lg bg-linear-to-br from-amber-100 to-amber-200">
                                        <span class="text-4xl">☕</span>
                                    </div>
                                    <h3 class="mb-1 font-semibold text-gray-800"><?php echo $product['name']; ?></h3>
                                    <p class="mb-2 text-sm text-gray-600">Stock: <?php echo $product['stock']; ?></p>
                                    <div class="text-sm">
                                        <?php if ($product['price_dodici']): ?>
                                            <div class="flex items-center justify-between font-semibold text-amber-600">
                                                <p>Dodici</p>
                                                <span><?php echo formatCurrency($product['price_dodici']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($product['price_sedici']): ?>
                                            <div class="flex items-center justify-between font-semibold text-amber-600">
                                                <p>Sedici</p>
                                                <span><?php echo formatCurrency($product['price_sedici']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Cart Section -->
            <div id="cart-section" class="flex flex-col bg-white border-l border-gray-200 w-96">
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Current Order</h2>
                        <p class="text-sm text-gray-600">Order #<span
                                id="order-number"><?php echo generateOrderNumber(); ?></span></p>
                    </div>
                    <button onclick="toggleCart()" class="text-gray-400 transition-colors hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 p-6 overflow-y-auto" id="cart-items">
                    <p class="py-8 text-center text-gray-400">No items in cart</p>
                </div>

                <div class="p-6 space-y-4 border-t border-gray-200">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-semibold" id="subtotal">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total</span>
                            <span class="text-amber-600" id="total">₱0.00</span>
                        </div>
                    </div>

                    <select id="payment-method" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                    </select>

                    <select id="order-type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="dine-in">Dine-in</option>
                        <option value="takeout">Takeout</option>
                    </select>

                    <button onclick="processOrder()"
                        class="w-full py-3 font-semibold text-white transition-colors rounded-lg bg-amber-600 hover:bg-amber-700">
                        Process Order
                    </button>
                    <button onclick="clearCart()"
                        class="w-full py-3 font-semibold text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                        Clear Cart
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Size Selection Modal -->
    <div id="size-modal" class="fixed inset-0 z-50 items-center justify-center hidden modal-backdrop">
        <div class="w-full max-w-md mx-4 bg-white shadow-2xl rounded-2xl animate-modal">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-gray-800" id="modal-product-name">Select Size</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-3" id="modal-size-options">
                <!-- Size options will be inserted here -->
            </div>
        </div>
    </div>

    <script src="../../assets/js/pos.js"></script>
</body>

</html>