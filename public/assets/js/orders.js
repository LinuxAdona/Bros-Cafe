function viewOrderDetails(orderId) {
    const modal = document.getElementById('orderModal');
    const content = document.getElementById('orderDetailsContent');
    
    // Show modal
    modal.classList.remove('hidden');
    
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
    document.getElementById('orderModal').classList.add('hidden');
}

function updateOrderStatus(orderId, currentStatus) {
    const statuses = ['pending', 'preparing', 'ready', 'completed'];
    const currentIndex = statuses.indexOf(currentStatus);
    const nextStatus = statuses[currentIndex + 1] || 'completed';

    if (confirm(`Update order status to "${nextStatus}"?`)) {
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
                }
            });
    }
}