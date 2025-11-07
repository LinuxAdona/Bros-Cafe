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