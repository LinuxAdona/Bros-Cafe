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