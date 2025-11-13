function openAddModal() {
    document.getElementById('modal-title').textContent = 'Add Product';
    document.getElementById('formAction').value = 'add';
    document.getElementById('productForm').reset();
    document.getElementById('productModal').classList.remove('hidden');
}

function editProduct(product) {
    document.getElementById('modal-title').textContent = 'Edit Product';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name;
    document.getElementById('categoryId').value = product.category_id;
    document.getElementById('productDescription').value = product.description || '';
    document.getElementById('priceDodici').value = product.price_dodici || '';
    document.getElementById('priceSedici').value = product.price_sedici || '';
    document.getElementById('productStatus').value = product.status;
    document.getElementById('productModal').classList.remove('hidden');
}

function deleteProduct(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        document.getElementById('deleteProductId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

function closeModal() {
    document.getElementById('productModal').classList.add('hidden');
}