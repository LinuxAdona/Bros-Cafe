function viewOrderDetails(orderId) {
    const modal = document.getElementById('orderModal');
    const content = document.getElementById('orderDetailsContent');
    
    // Show modal with flex
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Show loading state
    content.innerHTML = '<div class="text-center py-8"><p class="text-gray-500">Loading order details...</p></div>';
    
    // Fetch order details
    fetch(`get_order_details.php?id=${orderId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            content.innerHTML = data;
        })
        .catch(error => {
            console.error('Error fetching order details:', error);
            content.innerHTML = '<div class="text-center py-8"><p class="text-red-500">Error loading order details. Please try again.</p></div>';
        });
}

function closeModal() {
    const modal = document.getElementById('orderModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function updateOrderStatus(orderId, currentStatus) {
    // Open the update status modal instead of native confirm
    const statuses = ['pending', 'preparing', 'ready', 'completed'];
    const currentIndex = statuses.indexOf(currentStatus);
    const nextStatus = statuses[currentIndex + 1] || 'completed';

    showUpdateStatusModal(orderId, currentStatus, nextStatus);
}

// Show the update status modal and populate values
function showUpdateStatusModal(orderId, currentStatus, nextStatus) {
    const modal = document.getElementById('updateStatusModal');
    const currentLabel = document.getElementById('currentStatusLabel');
    const nextLabel = document.getElementById('nextStatusLabel');
    const subtitle = document.getElementById('updateModalSubtitle');
    const confirmBtn = document.getElementById('confirmUpdateBtn');

    if (!modal || !currentLabel || !nextLabel || !confirmBtn) return;

    currentLabel.textContent = capitalize(currentStatus);
    nextLabel.textContent = capitalize(nextStatus);
    subtitle.textContent = `Order will move from ${capitalize(currentStatus)} → ${capitalize(nextStatus)}`;

    // store data on confirm button
    confirmBtn.setAttribute('data-order-id', orderId);
    confirmBtn.setAttribute('data-next-status', nextStatus);

    // bind click handler
    confirmBtn.onclick = confirmUpdateStatus;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeUpdateStatusModal() {
    const modal = document.getElementById('updateStatusModal');
    const confirmBtn = document.getElementById('confirmUpdateBtn');
    if (confirmBtn) {
        confirmBtn.onclick = null;
        confirmBtn.removeAttribute('data-order-id');
        confirmBtn.removeAttribute('data-next-status');
    }
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function confirmUpdateStatus(e) {
    const btn = e.currentTarget || document.getElementById('confirmUpdateBtn');
    const orderId = btn.getAttribute('data-order-id');
    const nextStatus = btn.getAttribute('data-next-status');

    if (!orderId || !nextStatus) return;

    // disable button while processing
    btn.disabled = true;
    btn.textContent = 'Updating...';

    fetch('update_order_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                order_id: orderId,
                status: nextStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating order status');
                btn.disabled = false;
                btn.textContent = 'Confirm';
            }
        })
        .catch(err => {
            console.error(err);
            alert('Network error');
            btn.disabled = false;
            btn.textContent = 'Confirm';
        });
}

function capitalize(s) {
    if (!s) return '';
    return s.charAt(0).toUpperCase() + s.slice(1);
}

// Cancel Order Functions
let cancelOrderData = {
    orderId: null,
    orderNumber: null
};

let currentCancelVerificationMethod = 'password';

function cancelOrder(orderId, orderNumber) {
    cancelOrderData = {
        orderId: orderId,
        orderNumber: orderNumber
    };
    
    // Open admin verification modal
    document.getElementById('cancelOrderNumber').textContent = orderNumber;
    document.getElementById('cancelVerificationModal').classList.remove('hidden');
    document.getElementById('cancelVerificationModal').classList.add('flex');
    document.getElementById('cancelAdminPassword').value = '';
    document.getElementById('cancelQrCodeInput').value = '';
    document.getElementById('cancelVerificationError').classList.add('hidden');
}

function closeCancelVerificationModal() {
    document.getElementById('cancelVerificationModal').classList.add('hidden');
    document.getElementById('cancelVerificationModal').classList.remove('flex');
    cancelOrderData = {
        orderId: null,
        orderNumber: null
    };
}

function switchCancelVerificationMethod(method) {
    currentCancelVerificationMethod = method;
    
    const passwordTab = document.getElementById('cancelPasswordTab');
    const qrTab = document.getElementById('cancelQrTab');
    const passwordVerification = document.getElementById('cancelPasswordVerification');
    const qrVerification = document.getElementById('cancelQrVerification');
    
    if (method === 'password') {
        passwordTab.classList.add('border-indigo-600', 'text-indigo-600');
        passwordTab.classList.remove('border-transparent', 'text-gray-500');
        qrTab.classList.remove('border-indigo-600', 'text-indigo-600');
        qrTab.classList.add('border-transparent', 'text-gray-500');
        passwordVerification.classList.remove('hidden');
        qrVerification.classList.add('hidden');
    } else {
        qrTab.classList.add('border-indigo-600', 'text-indigo-600');
        qrTab.classList.remove('border-transparent', 'text-gray-500');
        passwordTab.classList.remove('border-indigo-600', 'text-indigo-600');
        passwordTab.classList.add('border-transparent', 'text-gray-500');
        qrVerification.classList.remove('hidden');
        passwordVerification.classList.add('hidden');
    }
}

function toggleCancelPasswordVisibility() {
    const passwordInput = document.getElementById('cancelAdminPassword');
    const toggleIcon = document.getElementById('cancelPasswordToggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fa-solid fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fa-solid fa-eye';
    }
}

function submitCancelVerification() {
    const errorDiv = document.getElementById('cancelVerificationError');
    const errorMsg = document.getElementById('cancelVerificationErrorMessage');
    
    let data = {
        method: currentCancelVerificationMethod
    };
    
    if (currentCancelVerificationMethod === 'password') {
        const password = document.getElementById('cancelAdminPassword').value;
        if (!password) {
            errorMsg.textContent = 'Please enter admin password';
            errorDiv.classList.remove('hidden');
            return;
        }
        data.password = password;
    } else {
        const qrCode = document.getElementById('cancelQrCodeInput').value;
        if (!qrCode) {
            errorMsg.textContent = 'Please enter or scan QR code';
            errorDiv.classList.remove('hidden');
            return;
        }
        data.qr_code = qrCode;
    }
    
    // Verify with server
    fetch('verify_admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Admin verified, proceed with cancellation
            errorDiv.classList.add('hidden');
            executeCancelOrder();
        } else {
            errorMsg.textContent = result.message || 'Verification failed';
            errorDiv.classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Verification error:', error);
        errorMsg.textContent = 'Network error. Please try again.';
        errorDiv.classList.remove('hidden');
    });
}

function executeCancelOrder() {
    if (!cancelOrderData.orderId) return;
    
    fetch('update_order_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            order_id: cancelOrderData.orderId,
            status: 'cancelled'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCancelVerificationModal();
            alert('Order cancelled successfully');
            location.reload();
        } else {
            alert('Error cancelling order: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error cancelling order:', error);
        alert('Network error. Please try again.');
    });
}

// Allow Enter key to submit verification
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('cancelAdminPassword');
    const qrInput = document.getElementById('cancelQrCodeInput');
    
    if (passwordInput) {
        passwordInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                submitCancelVerification();
            }
        });
    }
    
    if (qrInput) {
        qrInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                submitCancelVerification();
            }
        });
    }
});