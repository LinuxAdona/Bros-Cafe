// Global variables
let cart = [];
let currentProduct = null;
let isSidebarVisible = true;
let isCartVisible = true;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const orderNumber = document.getElementById('order-number').textContent;
});

// Sidebar toggle with animation
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    
    if (isSidebarVisible) {
        sidebar.classList.add('sidebar-hidden');
        isSidebarVisible = false;
    } else {
        sidebar.classList.remove('sidebar-hidden');
        isSidebarVisible = true;
    }
}

// Cart toggle with animation
function toggleCart() {
    const cartSection = document.getElementById('cart-section');
    
    if (isCartVisible) {
        cartSection.classList.add('cart-hidden');
        isCartVisible = false;
        updateCartBadge();
    } else {
        cartSection.classList.remove('cart-hidden');
        isCartVisible = true;
        updateCartBadge();
    }
}

// Category filter
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
            product.classList.add('fade-in');
        } else {
            product.style.display = 'none';
        }
    });
}

// Add product to cart
function addToCart(product) {
    currentProduct = product;
    
    // Check if product has multiple sizes
    if (product.price_sedici && product.price_dodici) {
        showSizeModal(product);
    } else {
        // Single size, add directly
        const selectedSize = product.price_dodici ? 'dodici' : 'sedici';
        const price = product.price_dodici ? parseFloat(product.price_dodici) : parseFloat(product.price_sedici);
        addItemToCart(product.id, product.name, selectedSize, price);
    }
}

// Show size selection modal
function showSizeModal(product) {
    const modal = document.getElementById('size-modal');
    const modalName = document.getElementById('modal-product-name');
    const modalOptions = document.getElementById('modal-size-options');
    
    modalName.textContent = product.name;
    
    let optionsHtml = '';
    
    if (product.price_dodici) {
        optionsHtml += `
            <button onclick="selectSize('dodici', ${product.price_dodici})" 
                class="w-full p-4 text-left transition-all bg-white border-2 border-gray-200 rounded-xl hover:border-amber-500 hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-lg font-semibold text-gray-800 group-hover:text-amber-600">Dodici (12oz)</p>
                        <p class="text-sm text-gray-500">Regular Size</p>
                    </div>
                    <p class="text-2xl font-bold text-amber-600">${formatPHP(product.price_dodici)}</p>
                </div>
            </button>
        `;
    }
    
    if (product.price_sedici) {
        optionsHtml += `
            <button onclick="selectSize('sedici', ${product.price_sedici})" 
                class="w-full p-4 text-left transition-all bg-white border-2 border-gray-200 rounded-xl hover:border-amber-500 hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-lg font-semibold text-gray-800 group-hover:text-amber-600">Sedici (16oz)</p>
                        <p class="text-sm text-gray-500">Large Size</p>
                    </div>
                    <p class="text-2xl font-bold text-amber-600">${formatPHP(product.price_sedici)}</p>
                </div>
            </button>
        `;
    }
    
    modalOptions.innerHTML = optionsHtml;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Select size from modal
function selectSize(size, price) {
    if (currentProduct) {
        addItemToCart(currentProduct.id, currentProduct.name, size, price);
        closeModal();
    }
}

// Add item to cart
function addItemToCart(id, name, size, price) {
    const existingItem = cart.find(item => item.id === id && item.size === size);

    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id: id,
            name: name,
            size: size,
            price: price,
            quantity: 1
        });
    }

    updateCart();
}

// Close modal
function closeModal() {
    const modal = document.getElementById('size-modal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    currentProduct = null;
}

// Close modal on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

// Update cart display
function updateCart() {
    const cartItems = document.getElementById('cart-items');

    // Update cart badge
    updateCartBadge();

    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="py-8 text-center text-gray-400">No items in cart</p>';
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
            <div class="flex items-start justify-between pb-4 mb-4 border-b border-gray-200 fade-in">
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-800">${item.name}</h4>
                    <p class="text-sm text-gray-600">${item.size.charAt(0).toUpperCase() + item.size.slice(1)} - ${formatPHP(item.price)}</p>
                    <div class="flex items-center mt-2 space-x-2">
                        <button onclick="decreaseQuantity(${index})" class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300 transition-colors">-</button>
                        <span class="w-8 text-center">${item.quantity}</span>
                        <button onclick="increaseQuantity(${index})" class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300 transition-colors">+</button>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-amber-600">${formatPHP(subtotal)}</p>
                    <button onclick="removeFromCart(${index})" class="mt-1 text-sm text-red-500 hover:text-red-700 transition-colors">Remove</button>
                </div>
            </div>
        `;
    });

    cartItems.innerHTML = html;
    document.getElementById('subtotal').textContent = formatPHP(total);
    document.getElementById('total').textContent = formatPHP(total);
}

// Update cart badge
function updateCartBadge() {
    const badge = document.getElementById('cart-badge');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    // Only show badge when cart is hidden
    if (totalItems > 0 && !isCartVisible) {
        badge.textContent = totalItems;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

// Increase quantity
function increaseQuantity(index) {
    cart[index].quantity++;
    updateCart();
}

// Decrease quantity
function decreaseQuantity(index) {
    if (cart[index].quantity > 1) {
        cart[index].quantity--;
        updateCart();
    }
}

// Remove from cart
function removeFromCart(index) {
    cart.splice(index, 1);
    updateCart();
}

// Clear cart
function clearCart() {
    if (confirm('Clear all items from cart?')) {
        cart = [];
        updateCart();
    }
}

// Format currency
function formatPHP(amount) {
    return '₱' + parseFloat(amount).toFixed(2);
}

// Process order
function processOrder() {
    if (cart.length === 0) {
        alert('Cart is empty!');
        return;
    }

    const paymentMethod = document.getElementById('payment-method').value;
    const orderType = document.getElementById('order-type').value;
    const orderNumber = document.getElementById('order-number').textContent;

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
