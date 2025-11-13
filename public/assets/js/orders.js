function viewOrderDetails(orderId) {
    document.getElementById('orderModal').classList.remove('hidden');
    fetch(`get_order_details.php?id=${orderId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('orderDetailsContent').innerHTML = data;
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