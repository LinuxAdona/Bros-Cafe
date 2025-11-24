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
    LEFT JOIN inventory i ON p.id = i.ingredient_id 
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
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/pos.css">
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
                    <?php if (isEmployee()): ?>
                    <li>
                        <a href="pos.php" data-tooltip="POS"
                            class="flex items-center px-4 py-3 rounded-lg bg-amber-600">
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
                    <?php endif; ?>
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
        <div class="flex flex-1 overflow-hidden">
            <!-- Products Section -->
            <div class="flex flex-col flex-1 overflow-y-auto">
                <div class="flex items-center bg-white shadow-lg justify-between p-6 mb-6">
                    <div class="flex flex-col justify-center">
                        <h2 class="text-3xl font-bold text-gray-800">Products</h2>
                        <p class="text-md text-gray-600">Select items to add to order</p>
                    </div>
                </div>

                <div class="px-6">
                    <!-- Search Bar -->
                    <div class="mb-4">
                        <div class="relative">
                            <input type="text" id="product-search" placeholder="Search products..."
                                class="w-full px-4 py-3 pl-12 text-gray-700 transition-all bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                oninput="searchProducts()">
                            <svg class="absolute w-5 h-5 text-gray-400 transform -translate-y-1/2 left-4 top-1/2"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="flex gap-2 mb-6 overflow-x-auto">
                        <button onclick="filterCategory('all')"
                            class="px-4 py-2 text-white rounded-lg category-btn bg-amber-600 whitespace-nowrap">All</button>
                        <?php foreach ($categories as $category): ?>
                        <button onclick="filterCategory('<?php echo $category['id']; ?>')"
                            class="px-4 py-2 text-gray-700 transition-all bg-white rounded-lg category-btn whitespace-nowrap"><?php echo $category['name']; ?></button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Products Grid -->
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4" id="products-grid">
                        <?php foreach ($products as $product):
                            // Create product data without the image blob for onclick
                            $productData = [
                                'id' => $product['id'],
                                'name' => $product['name'],
                                'price_dodici' => $product['price_dodici'],
                                'price_sedici' => $product['price_sedici'],
                                'category_id' => $product['category_id']
                            ];
                            ?>
                        <div class="transition-shadow bg-white rounded-lg shadow cursor-pointer product-card hover:shadow-lg"
                            data-category="<?php echo $product['category_id']; ?>"
                            onclick='addToCart(<?php echo json_encode($productData); ?>)'>
                            <div class="p-4">
                                <div class="flex items-center justify-center w-full mb-3 overflow-hidden bg-gray-100 rounded-lg"
                                    style="aspect-ratio: 1 / 1;">
                                    <?php if ($product['image']): ?>
                                    <img src="../get_image.php?id=<?php echo $product['id']; ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                                        class="object-cover w-full h-full">
                                    <?php else: ?>
                                    <span class="text-4xl">☕</span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="mb-1 font-bold text-gray-800"><?php echo $product['name']; ?></h3>
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

            <!-- Cart Toggle Button - moves to avoid cart overlap -->
            <div id="cart-toggle-btn" class="fixed top-6 z-50 transition-all duration-400" style="right: 416px;">
                <button onclick="toggleCart()"
                    class="relative p-3 text-white transition-all rounded-full shadow-lg bg-amber-600 hover:bg-amber-700 hover:shadow-xl">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </button>
            </div>

            <!-- Cart Section -->
            <div id="cart-section" class="flex flex-col bg-white border-l border-gray-200 w-96">
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Order Details</h2>
                        <p class="hidden text-sm text-gray-600">Order #<span
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
                        <option value="gcash">GCash</option>
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

    <!-- Order Success Modal -->
    <div id="order-popup" class="fixed inset-0 z-50 items-center justify-center hidden modal-backdrop">
        <div class="w-full max-w-md mx-4 bg-white shadow-2xl rounded-2xl animate-modal">
            <div class="p-6 text-center border-b border-gray-200">
                <div class="flex justify-center mb-4">
                    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-green-100">
                        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2l4-4m6 2a9 9 0 11-18 0a9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-800">Order Completed!</h3>
            </div>
            <div class="p-6 text-center">
                <p id="popup-message" class="mb-2 text-gray-600">Your order has been processed successfully.</p>
                <p id="popup-order-number" class="mb-6 text-xl font-bold text-amber-600"></p>
                <div class="space-y-3">
                    <div class="flex gap-2">
                        <button id="popup-print-btn" onclick="printReceipt()"
                            class="flex-1 py-3 font-semibold text-white transition-all rounded-lg bg-amber-600 hover:bg-amber-700">Print
                            Receipt</button>
                    </div>

                    <div id="popup-order-details" class="p-4 text-left bg-gray-50 rounded-lg"
                        style="max-height:300px;overflow-y:auto"></div>

                    <button onclick="closeOrderPopup()" id="popup-close-btn"
                        class="w-full py-3 font-semibold text-white transition-all rounded-lg bg-amber-600 hover:bg-amber-700">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="error-popup" class="fixed inset-0 z-150 items-center justify-center hidden modal-backdrop">
        <div class="w-full max-w-md mx-4 bg-white shadow-2xl rounded-2xl animate-modal">
            <div class="p-6 text-center border-b border-gray-200">
                <div class="flex justify-center mb-4">
                    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-red-100">
                        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-800">Oops!</h3>
            </div>
            <div class="p-6 text-center">
                <p id="error-message" class="mb-6 text-gray-600"></p>
                <button onclick="closeErrorPopup()"
                    class="w-full py-3 font-semibold text-white transition-all rounded-lg bg-red-600 hover:bg-red-700">
                    OK
                </button>
            </div>
        </div>
    </div>

    <!-- Clear Cart Confirmation Modal -->
    <div id="clear-cart-modal" class="fixed inset-0 z-150 items-center justify-center hidden modal-backdrop">
        <div class="w-full max-w-md mx-4 bg-white shadow-2xl rounded-2xl animate-modal">
            <div class="p-6 text-center border-b border-gray-200"
                style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);">
                <div class="flex justify-center mb-2">
                    <div class="flex items-center justify-center w-16 h-16 bg-white rounded-full">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V4a1 1 0 011-1h6a1 1 0 011 1v3" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-white">Clear Cart</h3>
            </div>
            <div class="p-6 text-center">
                <p class="mb-4 text-gray-700">Are you sure you want to remove all items from the cart? This action
                    cannot be undone.</p>
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="closeClearCartModal()"
                        class="py-3 px-4 font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">Cancel</button>
                    <button id="confirm-clear-cart-btn" onclick="confirmClearCart()"
                        class="py-3 px-4 font-semibold text-white rounded-lg"
                        style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);">Clear Cart</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cash Confirmation Modal -->
    <div id="cash-confirm-modal" class="fixed inset-0 z-100 items-center justify-center hidden modal-backdrop">
        <div class="w-full max-w-2xl mx-4 bg-white shadow-2xl rounded-2xl animate-modal max-h-[90vh] overflow-y-auto">
            <div class="p-4 text-center border-b border-gray-200"
                style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center justify-center w-10 h-10 bg-white rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="10"
                                    font-weight="bold" fill="currentColor">$</text>
                            </svg>
                        </div>
                        <div class="text-left">
                            <h3 class="text-xl font-bold text-white">Confirm Cash Payment</h3>
                            <p class="text-xs text-green-100">Review order details before completing the
                                transaction</p>
                        </div>
                    </div>
                    <button onclick="closeCashConfirmModal()" class="text-white hover:text-green-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div id="cash-confirm-details" class="mb-4 p-4 bg-gray-50 rounded-lg"
                    style="max-height:320px;overflow-y:auto"></div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Amount Received</label>
                    <input type="number" id="cash-amount-received" step="0.01" min="0" placeholder="0.00"
                        class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-2">Enter the amount received from customer to record
                        amount paid (optional).</p>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-4">
                    <button onclick="closeCashConfirmModal()"
                        class="py-3 px-4 font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button id="cash-confirm-btn" onclick="confirmCashPayment()"
                        class="py-3 px-4 font-semibold text-white rounded-lg transition-colors shadow-md hover:shadow-lg"
                        style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
                        Confirm & Process
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Display Modal -->
    <div id="change-modal" class="fixed inset-0 z-100 items-center justify-center hidden modal-backdrop">
        <div class="w-full max-w-md mx-4 bg-white shadow-2xl rounded-2xl animate-modal">
            <div class="p-6 text-center border-b border-gray-200"
                style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                <div class="flex justify-center mb-2">
                    <div class="flex items-center justify-center w-16 h-16 bg-white rounded-full">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-white">Customer Change</h3>
            </div>
            <div class="p-6 text-center">
                <div class="mb-6 space-y-3">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Total Amount</p>
                        <p class="text-2xl font-bold text-gray-800" id="change-modal-total">₱0.00</p>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Amount Received</p>
                        <p class="text-2xl font-bold text-blue-600" id="change-modal-received">₱0.00</p>
                    </div>
                    <div class="p-4 bg-green-50 rounded-lg border-2 border-green-500">
                        <p class="text-sm text-gray-600 mb-1">Change Due</p>
                        <p class="text-4xl font-bold text-green-600" id="change-modal-change">₱0.00</p>
                    </div>
                </div>
                <button onclick="closeChangeModal()"
                    class="w-full py-3 font-semibold text-white transition-all rounded-lg"
                    style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                    OK, Process Order
                </button>
            </div>
        </div>
    </div>

    <!-- GCash Payment Modal -->
    <div id="gcash-payment-modal" class="fixed inset-0 z-100 items-center justify-center hidden modal-backdrop">
        <div class="w-full max-w-4xl mx-4 bg-white shadow-2xl rounded-2xl animate-modal max-h-[90vh] overflow-y-auto">
            <div class="p-4 text-center border-b border-gray-200"
                style="background: linear-gradient(135deg, #007DFF 0%, #0062CC 100%);">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center justify-center w-10 h-10 bg-white rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="10"
                                    font-weight="bold" fill="currentColor">G</text>
                            </svg>
                        </div>
                        <div class="text-left">
                            <h3 class="text-xl font-bold text-white">GCash Payment</h3>
                            <p class="text-xs text-blue-100">Scan QR code to complete payment</p>
                        </div>
                    </div>
                    <button onclick="closeGCashModal()" class="text-white hover:text-blue-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                <!-- Left Column - QR Code & Amount -->
                <div class="flex flex-col items-center justify-center space-y-4">
                    <!-- Amount Display -->
                    <div class="w-full p-4 bg-blue-50 rounded-lg text-center">
                        <p class="text-sm text-gray-600 mb-2">Amount to Pay</p>
                        <p class="text-5xl font-bold text-blue-600" id="gcash-amount">₱0.00</p>
                    </div>

                    <!-- QR Code Display -->
                    <div class="flex justify-center">
                        <div class="p-4 bg-white border-4 border-blue-500 rounded-xl shadow-lg">
                            <img src="../../assets/images/QR-Pay.jpg" alt="GCash QR Code"
                                class="w-80 h-80 object-contain">
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 text-center">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Point your GCash app camera at this QR code
                    </p>
                </div>

                <!-- Right Column - Instructions & Inputs -->
                <div class="flex flex-col justify-between">
                    <!-- Instructions -->
                    <div class="mb-6">
                        <div class="mb-4 bg-gray-50 p-4 rounded-lg">
                            <p class="font-semibold text-gray-800 mb-3 text-base flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                                Payment Instructions
                            </p>
                            <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                                <li>Open your <strong>GCash app</strong></li>
                                <li>Tap <strong>"Scan QR"</strong> or <strong>"Pay QR"</strong></li>
                                <li>Point your camera at the QR code on the left</li>
                                <li>Confirm the amount: <span class="font-bold text-blue-600"
                                        id="gcash-amount-instruction">₱0.00</span></li>
                                <li>Complete the payment in your GCash app</li>
                                <li>Enter the payment details below</li>
                            </ol>
                        </div>

                        <!-- Payment Verification Section -->
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                            <p class="text-sm font-semibold text-amber-800 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                                After Payment Completed
                            </p>
                            <p class="text-xs text-amber-700">Please verify the payment details below before confirming
                                the transaction.</p>
                        </div>
                    </div>

                    <!-- Input Fields -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Amount Paid by Customer
                            </label>
                            <input type="number" id="gcash-paid-amount" step="0.01" min="0" placeholder="0.00"
                                class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                GCash Reference Number
                            </label>
                            <input type="text" id="gcash-reference-number" placeholder="Enter 13-digit reference number"
                                maxlength="13" inputmode="numeric" pattern="\d{13}"
                                class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono">
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-3 pt-4">
                            <button onclick="closeGCashModal()"
                                class="py-3 px-4 font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                                Cancel
                            </button>
                            <button id="gcash-verify-btn" onclick="verifyGCashPayment()" disabled
                                class="py-3 px-4 font-semibold text-white rounded-lg transition-colors shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                style="background: linear-gradient(135deg, #007DFF 0%, #0062CC 100%);">
                                Verify Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/admin.js"></script>
    <script src="../../assets/js/pos.js"></script>
</body>

</html>
