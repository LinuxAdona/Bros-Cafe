<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

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
                        <p class="text-xs text-gray-400">POS System</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="pos.php" class="flex items-center px-4 py-3 bg-amber-600 rounded-lg">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            New Order
                        </a>
                    </li>
                    <li>
                        <a href="orders.php" class="flex items-center px-4 py-3 hover:bg-gray-800 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Orders
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li>
                            <a href="../admin/dashboard.php" class="flex items-center px-4 py-3 hover:bg-gray-800 rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="../admin/inventory.php" class="flex items-center px-4 py-3 hover:bg-gray-800 rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Inventory
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
                    <a href="../../pages/logout.php" class="text-red-400 hover:text-red-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Products Section -->
            <div class="flex-1 overflow-y-auto p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Products</h2>
                    <p class="text-gray-600">Select items to add to order</p>
                </div>

                <!-- Category Filter -->
                <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
                    <button onclick="filterCategory('all')" class="category-btn px-4 py-2 bg-amber-600 text-white rounded-lg whitespace-nowrap">All</button>
                    <?php foreach ($categories as $category): ?>
                        <button onclick="filterCategory('<?php echo $category['id']; ?>')" class="category-btn px-4 py-2 bg-white text-gray-700 rounded-lg whitespace-nowrap hover:bg-gray-50"><?php echo $category['name']; ?></button>
                    <?php endforeach; ?>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card bg-white rounded-lg shadow hover:shadow-lg transition-shadow cursor-pointer"
                            data-category="<?php echo $product['category_id']; ?>"
                            onclick='addToCart(<?php echo json_encode($product); ?>)'>
                            <div class="p-4">
                                <div class="w-full h-32 bg-gradient-to-br from-amber-100 to-amber-200 rounded-lg flex items-center justify-center mb-3">
                                    <span class="text-4xl">☕</span>
                                </div>
                                <h3 class="font-semibold text-gray-800 mb-1"><?php echo $product['name']; ?></h3>
                                <p class="text-sm text-gray-600 mb-2">Stock: <?php echo $product['stock']; ?></p>
                                <div class="text-sm">
                                    <?php if ($product['price_dodici']): ?>
                                        <p class="text-amber-600 font-semibold">Dodici: <?php echo formatCurrency($product['price_dodici']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($product['price_sedici']): ?>
                                        <p class="text-amber-600 font-semibold">Sedici: <?php echo formatCurrency($product['price_sedici']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Cart Section -->
            <div class="w-96 bg-white border-l border-gray-200 flex flex-col">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">Current Order</h2>
                    <p class="text-sm text-gray-600">Order #<span id="order-number"><?php echo generateOrderNumber(); ?></span></p>
                </div>

                <div class="flex-1 overflow-y-auto p-6" id="cart-items">
                    <p class="text-gray-400 text-center py-8">No items in cart</p>
                </div>

                <div class="border-t border-gray-200 p-6 space-y-4">
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
                        <option value="gcash">GCash</option>
                    </select>

                    <select id="order-type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="dine-in">Dine-in</option>
                        <option value="takeout">Takeout</option>
                        <option value="delivery">Delivery</option>
                    </select>

                    <button onclick="processOrder()" class="w-full bg-amber-600 text-white py-3 rounded-lg font-semibold hover:bg-amber-700 transition-colors">
                        Process Order
                    </button>
                    <button onclick="clearCart()" class="w-full bg-gray-200 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                        Clear Cart
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cart = [];
        const orderNumber = document.getElementById('order-number').textContent;

        function filterCategory(categoryId) {
            const products = document.querySelectorAll('.product-card');
            const buttons = document.querySelectorAll('.category-btn');

            buttons.forEach(btn => {
                btn.classList.remove('bg-amber-600', 'text-white');
                btn.classList.add('bg-white', 'text-gray-700');
            });
            event.target.classList.add('bg-amber-600', 'text-white');
            event.target.classList.remove('bg-white', 'text-gray-700');

            products.forEach(product => {
                if (categoryId === 'all' || product.dataset.category === categoryId) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        }

        function addToCart(product) {
            const size = product.price_sedici ? prompt('Select size:\n1. Dodici - ' + formatPHP(product.price_dodici) + '\n2. Sedici - ' + formatPHP(product.price_sedici), '1') : '1';

            const selectedSize = size === '2' ? 'sedici' : 'dodici';
            const price = selectedSize === 'sedici' ? parseFloat(product.price_sedici) : parseFloat(product.price_dodici);

            const existingItem = cart.find(item => item.id === product.id && item.size === selectedSize);

            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    size: selectedSize,
                    price: price,
                    quantity: 1
                });
            }

            updateCart();
        }

        function updateCart() {
            const cartItems = document.getElementById('cart-items');

            if (cart.length === 0) {
                cartItems.innerHTML = '<p class="text-gray-400 text-center py-8">No items in cart</p>';
                document.getElementById('subtotal').textContent = '₱0.00';
                document.getElementById('total').textContent = '₱0.00';
                return;
            }

            let html = '';
            let total = 0;

            cart.forEach((item, index) => {
                const subtotal = item.price * item.quantity;
                total += subtotal;

                html += `
                    <div class="flex justify-between items-start mb-4 pb-4 border-b border-gray-200">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800">${item.name}</h4>
                            <p class="text-sm text-gray-600">${item.size.charAt(0).toUpperCase() + item.size.slice(1)} - ${formatPHP(item.price)}</p>
                            <div class="flex items-center mt-2 space-x-2">
                                <button onclick="decreaseQuantity(${index})" class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300">-</button>
                                <span class="w-8 text-center">${item.quantity}</span>
                                <button onclick="increaseQuantity(${index})" class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300">+</button>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-amber-600">${formatPHP(subtotal)}</p>
                            <button onclick="removeFromCart(${index})" class="text-red-500 text-sm mt-1 hover:text-red-700">Remove</button>
                        </div>
                    </div>
                `;
            });

            cartItems.innerHTML = html;
            document.getElementById('subtotal').textContent = formatPHP(total);
            document.getElementById('total').textContent = formatPHP(total);
        }

        function increaseQuantity(index) {
            cart[index].quantity++;
            updateCart();
        }

        function decreaseQuantity(index) {
            if (cart[index].quantity > 1) {
                cart[index].quantity--;
                updateCart();
            }
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCart();
        }

        function clearCart() {
            if (confirm('Clear all items from cart?')) {
                cart = [];
                updateCart();
            }
        }

        function formatPHP(amount) {
            return '₱' + parseFloat(amount).toFixed(2);
        }

        function processOrder() {
            if (cart.length === 0) {
                alert('Cart is empty!');
                return;
            }

            const paymentMethod = document.getElementById('payment-method').value;
            const orderType = document.getElementById('order-type').value;

            const orderData = {
                order_number: orderNumber,
                items: cart,
                payment_method: paymentMethod,
                order_type: orderType,
                total: parseFloat(document.getElementById('total').textContent.replace('₱', ''))
            };

            fetch('process_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(orderData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order placed successfully!\nOrder #: ' + orderNumber);
                        cart = [];
                        updateCart();
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error processing order');
                    console.error(error);
                });
        }
    </script>
</body>

</html>