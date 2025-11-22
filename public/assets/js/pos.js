2// Global variables
let cart = [];
let currentProduct = null;
let isCartVisible = true;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const orderNumber = document.getElementById('order-number').textContent;
});

// Note: toggleSidebar() is handled by admin.js for consistency across all pages

// Search products
function searchProducts() {
    const searchInput = document.getElementById('product-search');
    const searchTerm = searchInput.value.toLowerCase().trim();
    const products = document.querySelectorAll('.product-card');
    
    products.forEach(product => {
        const productName = product.querySelector('h3').textContent.toLowerCase();
        
        if (productName.includes(searchTerm)) {
            product.style.display = 'block';
            product.classList.add('fade-in');
        } else {
            product.style.display = 'none';
        }
    });
    
    // If search is active, reset category filter to show all matching results
    if (searchTerm) {
        const buttons = document.querySelectorAll('.category-btn');
        buttons.forEach(btn => {
            btn.classList.remove('bg-amber-600', 'text-white', 'hover:bg-amber-700');
            btn.classList.add('bg-white', 'text-gray-700', 'hover:bg-gray-50');
        });
        // Highlight "All" button
        buttons[0].classList.add('bg-amber-600', 'text-white', 'hover:bg-amber-700');
        buttons[0].classList.remove('bg-white', 'text-gray-700', 'hover:bg-gray-50');
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
        btn.classList.remove('bg-amber-600', 'text-white', 'hover:bg-amber-700');
        btn.classList.add('bg-white', 'text-gray-700', 'hover:bg-gray-50');
    });
    event.target.classList.add('bg-amber-600', 'text-white', 'hover:bg-amber-700');
    event.target.classList.remove('bg-white', 'text-gray-700', 'hover:bg-gray-50');

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
    // Show modal confirmation instead of native confirm()
    if (cart.length === 0) {
        showErrorPopup('Cart is already empty!');
        return;
    }
    showClearCartModal();
}

// Show the clear-cart modal
function showClearCartModal() {
    const modal = document.getElementById('clear-cart-modal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeClearCartModal() {
    const modal = document.getElementById('clear-cart-modal');
    if (!modal) return;
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

function confirmClearCart() {
    // Perform the clear action
    cart = [];
    updateCart();
    closeClearCartModal();
}

// Format currency
function formatPHP(amount) {
    return '₱' + parseFloat(amount).toFixed(2);
}

// Process order
function processOrder() {
    // Check if cart is empty
    if (cart.length === 0) {
        showErrorPopup('No items selected. Please add items to your cart first.');
        return;
    }
    const paymentMethod = (document.getElementById('payment-method').value || '').toLowerCase().trim();
    const orderType = (document.getElementById('order-type').value || '').toLowerCase().trim();
    console.debug('processOrder called', { paymentMethod, orderType, cartLength: cart.length });
    const total = parseFloat(document.getElementById('total').textContent.replace('₱', ''));

    // If GCash payment, show QR modal for payment verification
    if (paymentMethod === 'gcash') {
        showGCashPaymentModal(total);
        return;
    }

    // For cash payments, show confirmation modal first
    const orderNumber = document.getElementById('order-number').textContent;

    const orderData = {
        order_number: orderNumber,
        items: cart,
        payment_method: paymentMethod,
        order_type: orderType,
        total: total
    };

    if (paymentMethod === 'cash') {
        console.debug('processOrder: detected cash payment, showing cash confirm modal');
        showCashConfirmModal(orderData);
        return;
    }

    // Non-cash direct submit
    submitOrder(orderData);
}

// Cash confirmation modal flow
let cashPendingOrder = null;

function showCashConfirmModal(orderData) {
    // Defensive: only show this modal for cash payment orders
    if (!orderData || (orderData.payment_method || '').toLowerCase().trim() !== 'cash') {
        console.debug('showCashConfirmModal: guard prevented showing modal, payment_method:', (orderData && orderData.payment_method) || null);
        return;
    }

    cashPendingOrder = orderData;
    const modal = document.getElementById('cash-confirm-modal');
    const detailsEl = document.getElementById('cash-confirm-details');
    const amountInput = document.getElementById('cash-amount-received');

    if (detailsEl) {
        // Reuse buildOrderDetailsHtml but ensure total is present
        detailsEl.innerHTML = buildOrderDetailsHtml(orderData);
    }

    if (amountInput) amountInput.value = '';

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    console.debug('showCashConfirmModal: modal opened for order', { order_number: orderData.order_number, total: orderData.total });
}

function closeCashConfirmModal() {
    const modal = document.getElementById('cash-confirm-modal');
    const amountInput = document.getElementById('cash-amount-received');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    if (amountInput) amountInput.value = '';
    cashPendingOrder = null;
}

function confirmCashPayment() {
    if (!cashPendingOrder) {
        showErrorPopup('No pending order to confirm.');
        return;
    }
    console.debug('confirmCashPayment: confirming cash for', { order_number: cashPendingOrder.order_number, total: cashPendingOrder.total });

    const total = parseFloat(cashPendingOrder.total) || 0;
    const amountInput = document.getElementById('cash-amount-received');
    let amountPaid = null;

    if (amountInput && amountInput.value !== '') {
        amountPaid = parseFloat(amountInput.value);
        if (isNaN(amountPaid) || amountPaid < 0) {
            showErrorPopup('Please enter a valid amount received.');
            return;
        }
        if (amountPaid < total) {
            showErrorPopup(`Insufficient payment. Total: ${formatPHP(total)}, Received: ${formatPHP(amountPaid)}. Please collect the remaining amount.`);
            return;
        }
        
        // If amount paid is more than total, show change modal
        if (amountPaid > total) {
            const change = amountPaid - total;
            showChangeModal(total, amountPaid, change);
            return; // Don't process yet, wait for change modal confirmation
        }
    }

    // Close modal and submit order (include amount_paid if provided)
    closeCashConfirmModal();

    if (amountPaid !== null) {
        cashPendingOrder.amount_paid = amountPaid;
    }

    // proceed to submit
    submitOrder(cashPendingOrder);
    cashPendingOrder = null;
}

// Show change modal
function showChangeModal(total, amountReceived, change) {
    const modal = document.getElementById('change-modal');
    const totalEl = document.getElementById('change-modal-total');
    const receivedEl = document.getElementById('change-modal-received');
    const changeEl = document.getElementById('change-modal-change');
    
    if (totalEl) totalEl.textContent = formatPHP(total);
    if (receivedEl) receivedEl.textContent = formatPHP(amountReceived);
    if (changeEl) changeEl.textContent = formatPHP(change);
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Close change modal and proceed with order
function closeChangeModal() {
    const modal = document.getElementById('change-modal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    
    // Get the pending order and amount before closing/clearing
    const orderToSubmit = cashPendingOrder;
    const amountInput = document.getElementById('cash-amount-received');
    const amountPaid = amountInput && amountInput.value !== '' ? parseFloat(amountInput.value) : null;
    
    // Close the cash confirm modal
    closeCashConfirmModal();
    
    // Now submit the order with the saved reference
    if (orderToSubmit) {
        if (amountPaid !== null) {
            orderToSubmit.amount_paid = amountPaid;
        }
        submitOrder(orderToSubmit);
    }
}

// Show success popup
// Store last order for popup/printing
let lastOrderForPopup = null;

// Show success popup (accepts orderNumber string, orderData object, and optional orderId from server)
function showSuccessPopup(orderNumber, orderData = null, orderId = null) {
    const popup = document.getElementById('order-popup');
    const message = document.getElementById('popup-message');
    const orderNumberEl = document.getElementById('popup-order-number');
    const detailsEl = document.getElementById('popup-order-details');
    const viewBtn = document.getElementById('popup-view-details-btn');

    message.textContent = `Your order has been processed successfully.`;
    orderNumberEl.textContent = orderNumber;

    // store for printing
    lastOrderForPopup = {
        orderNumber: orderNumber,
        orderData: orderData,
        orderId: orderId
    };

    // populate details area (shown by default)
    if (detailsEl) {
        if (orderData) {
            detailsEl.innerHTML = buildOrderDetailsHtml(orderData);
        } else {
            detailsEl.innerHTML = '<p class="text-sm text-gray-500">Order details unavailable.</p>';
        }
    }
    // (details are visible by default)

    // attach print button handler defensively (in case inline handler doesn't work)
    const printBtn = document.getElementById('popup-print-btn');
    if (printBtn) {
        printBtn.onclick = printReceipt;
    }

    popup.classList.remove('hidden');
    popup.classList.add('flex');
}

function buildOrderDetailsHtml(orderData) {
    if (!orderData) return '<p class="text-sm text-gray-500">No details</p>';

    const items = orderData.items || [];
    let html = '<div class="space-y-3">';
    html += '<div class="text-sm text-gray-700">';
    html += '<strong>Payment:</strong> ' + (orderData.payment_method ? orderData.payment_method.toUpperCase() : 'N/A') + '<br/>';
    if (orderData.amount_paid) html += '<strong>Amount Paid:</strong> ' + formatPHP(orderData.amount_paid) + '<br/>';
    if (orderData.reference_number) html += '<strong>Reference #:</strong> ' + orderData.reference_number + '<br/>';
    html += '</div>';

    html += '<div class="pt-2">';
    html += '<h4 class="mb-2 text-sm font-bold text-gray-700 uppercase">Order Items</h4>';
    items.forEach(item => {
        html += `<div class="flex items-center justify-between p-2 bg-white rounded mb-2 border border-gray-200">
            <div>
                <p class="font-semibold text-gray-800">${escapeHtml(item.name)}</p>
                <p class="text-xs text-gray-500">Size: ${escapeHtml(item.size.charAt(0).toUpperCase() + item.size.slice(1))} • Qty: ${item.quantity}</p>
            </div>
            <div class="font-semibold text-gray-700">${formatPHP(item.price * item.quantity)}</div>
        </div>`;
    });
    html += '</div>';

    html += '<div class="pt-3 border-t border-gray-200">';
    html += `<div class="flex items-center justify-between"><span class="font-bold">Total</span><span class="font-bold text-amber-600">${formatPHP(orderData.total)}</span></div>`;
    html += '</div>';
    html += '</div>';
    return html;
}

function escapeHtml(text) {
    if (!text && text !== 0) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Toggle details area in popup
function togglePopupDetails() {
    const detailsEl = document.getElementById('popup-order-details');
    const viewBtn = document.getElementById('popup-view-details-btn');
    if (!detailsEl) return;

    if (detailsEl.classList.contains('hidden')) {
        detailsEl.classList.remove('hidden');
        if (viewBtn) viewBtn.textContent = 'Hide Details';
    } else {
        detailsEl.classList.add('hidden');
        if (viewBtn) viewBtn.textContent = 'View Details';
    }
}

// Print the last order (opens a new window with printable HTML)
function printReceipt() {
    if (!lastOrderForPopup) {
        alert('No order data available to print.');
        return;
    }

    const orderNumber = lastOrderForPopup.orderNumber || ('Order');
    const orderData = lastOrderForPopup.orderData || {};
    const businessName = document.querySelector('.logo-content h1') ? document.querySelector('.logo-content h1').textContent : 'Bro\'s Cafe';

    let printHtml = `<!doctype html><html><head><meta charset="utf-8"><title>Receipt - ${escapeHtml(orderNumber)}</title>`;
    printHtml += `<style>body{font-family: Arial, sans-serif;padding:20px;color:#111} .header{text-align:center;margin-bottom:10px} .items{width:100%;border-collapse:collapse} .items td, .items th{padding:8px;border-bottom:1px solid #eee} .total{font-weight:bold;text-align:right;margin-top:10px}</style>`;
    printHtml += `</head><body>`;
    printHtml += `<div class="header"><h2>${escapeHtml(businessName)}</h2><div>${escapeHtml(orderNumber)}</div><div>${new Date().toLocaleString()}</div></div>`;

    // Items
    printHtml += `<table class="items"><thead><tr><th style="text-align:left">Item</th><th style="text-align:center">Qty</th><th style="text-align:right">Price</th></tr></thead><tbody>`;
    const items = orderData.items || [];
    items.forEach(item => {
        const name = escapeHtml(item.name || '');
        const qty = item.quantity || 0;
        const price = (item.price * item.quantity) ? formatPHP(item.price * item.quantity) : formatPHP(item.price || 0);
        printHtml += `<tr><td>${name} <div style="font-size:11px;color:#666">${escapeHtml(item.size || '')}</div></td><td style="text-align:center">${qty}</td><td style="text-align:right">${price}</td></tr>`;
    });
    printHtml += `</tbody></table>`;

    printHtml += `<div class="total">Total: ${formatPHP(orderData.total || 0)}</div>`;

    if (orderData.payment_method) printHtml += `<div style="margin-top:10px">Payment: ${escapeHtml(orderData.payment_method)}</div>`;
    if (orderData.amount_paid) printHtml += `<div>Amount Paid: ${formatPHP(orderData.amount_paid)}</div>`;
    if (orderData.reference_number) printHtml += `<div>Reference #: ${escapeHtml(orderData.reference_number)}</div>`;

    printHtml += `<div style="margin-top:20px;font-size:12px;color:#666;">Thank you for your purchase!</div>`;
    printHtml += `</body></html>`;

    const w = window.open('', '_blank', 'width=600,height=800');
    if (!w) {
        alert('Please allow popups to print the receipt.');
        return;
    }
    w.document.write(printHtml);
    w.document.close();
    w.focus();
    setTimeout(() => {
        w.print();
        // Optionally close after printing
        // w.close();
    }, 500);
}

// Show error popup
function showErrorPopup(message) {
    const popup = document.getElementById('error-popup');
    const messageElement = document.getElementById('error-message');
    
    messageElement.textContent = message;
    popup.classList.remove('hidden');
    popup.classList.add('flex');
}

// Close success popup
function closeOrderPopup() {
    const popup = document.getElementById('order-popup');
    popup.classList.remove('flex');
    popup.classList.add('hidden');
    // Reload page to get new order number
    location.reload();
}

// Close error popup
function closeErrorPopup() {
    const popup = document.getElementById('error-popup');
    popup.classList.remove('flex');
    popup.classList.add('hidden');
}

// Show GCash payment modal
function showGCashPaymentModal(amount) {
    const modal = document.getElementById('gcash-payment-modal');
    const amountDisplay = document.getElementById('gcash-amount');
    const amountInstruction = document.getElementById('gcash-amount-instruction');
    const paidAmountInput = document.getElementById('gcash-paid-amount');
    const referenceInput = document.getElementById('gcash-reference-number');
    const verifyBtn = document.getElementById('gcash-verify-btn');
    
    // Set the amount to pay
    amountDisplay.textContent = formatPHP(amount);
    amountInstruction.textContent = formatPHP(amount);
    
    // Clear previous input
    paidAmountInput.value = '';
    if (referenceInput) referenceInput.value = '';
    if (verifyBtn) verifyBtn.disabled = true;
    
    // Show modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    // attach live validation listeners
    if (paidAmountInput) paidAmountInput.addEventListener('input', updateGCashVerifyState);
    if (referenceInput) referenceInput.addEventListener('input', updateGCashVerifyState);
    console.debug('showGCashPaymentModal: opened GCash modal for amount', amount);
}

// Update the state of the GCash Verify button based on inputs
function updateGCashVerifyState() {
    const total = parseFloat(document.getElementById('total').textContent.replace('₱', '')) || 0;
    const paidAmount = parseFloat(document.getElementById('gcash-paid-amount').value) || 0;
    const referenceRaw = (document.getElementById('gcash-reference-number').value || '').trim();
    const referenceDigits = referenceRaw.replace(/\D/g, '');
    const verifyBtn = document.getElementById('gcash-verify-btn');

    const enoughPaid = paidAmount >= total && paidAmount > 0;
    const validRef = referenceDigits.length === 13;

    if (verifyBtn) verifyBtn.disabled = !(enoughPaid && validRef);
}

// Close GCash payment modal
function closeGCashModal() {
    const modal = document.getElementById('gcash-payment-modal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    
    // Clear inputs
    const paidEl = document.getElementById('gcash-paid-amount');
    const refEl = document.getElementById('gcash-reference-number');
    const verifyBtn = document.getElementById('gcash-verify-btn');
    if (paidEl) {
        paidEl.value = '';
        paidEl.removeEventListener('input', updateGCashVerifyState);
    }
    if (refEl) {
        refEl.value = '';
        refEl.removeEventListener('input', updateGCashVerifyState);
    }
    if (verifyBtn) verifyBtn.disabled = true;
}

// Verify GCash payment
function verifyGCashPayment() {
    const total = parseFloat(document.getElementById('total').textContent.replace('₱', ''));
    const paidAmount = parseFloat(document.getElementById('gcash-paid-amount').value);
    const referenceRaw = document.getElementById('gcash-reference-number').value.trim();
    // Normalize to digits only
    const referenceDigits = referenceRaw.replace(/\D/g, '');

    // Validate amount input
    if (!paidAmount || paidAmount <= 0) {
        showErrorPopup('Please enter the amount paid by the customer.');
        return;
    }

    // Validate reference number presence
    if (!referenceRaw) {
        showErrorPopup('Please enter the GCash reference number.');
        return;
    }

    // Enforce exactly 13 digits
    if (referenceDigits.length !== 13) {
        showErrorPopup('GCash reference number must be exactly 13 digits. Please re-check the number.');
        return;
    }
    
    // Check if payment is sufficient
    if (paidAmount < total) {
        showErrorPopup(`Insufficient payment. Required: ${formatPHP(total)}, Received: ${formatPHP(paidAmount)}`);
        return;
    }
    
    // Payment verified, process the order
    closeGCashModal();
    
    const paymentMethod = (document.getElementById('payment-method').value || '').toLowerCase().trim();
    const orderType = (document.getElementById('order-type').value || '').toLowerCase().trim();
    const orderNumber = document.getElementById('order-number').textContent;

    const orderData = {
        order_number: orderNumber,
        items: cart,
        payment_method: paymentMethod,
        order_type: orderType,
        total: total,
        amount_paid: paidAmount,
        reference_number: referenceDigits
    };

    submitOrder(orderData);
}

// Submit order to backend
function submitOrder(orderData) {
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
                // Clear cart first
                cart = [];
                updateCart();

                // Show success popup with order data and server order id
                showSuccessPopup('Order #' + orderData.order_number, orderData, data.order_id || null);
            } else {
                showErrorPopup(data.message || 'Failed to process order');
            }
        })
        .catch(error => {
            showErrorPopup('Error processing order. Please try again.');
            console.error(error);
        });
}
