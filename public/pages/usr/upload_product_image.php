<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

$db = new Database();
$conn = $db->getConnection();

$message = '';
$error = '';

// Get all products
$stmt = $conn->query("SELECT id, name FROM products ORDER BY name");
$products = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_FILES['image'])) {
    $product_id = $_POST['product_id'];
    $file = $_FILES['image'];
    
    // Validate file
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    
    if (!in_array($file['type'], $allowed_types)) {
        $error = 'Invalid file type. Only JPEG, PNG, and GIF are allowed.';
    } elseif ($file['size'] > 5000000) { // 5MB limit
        $error = 'File size too large. Maximum 5MB allowed.';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload error occurred.';
    } else {
        // Read file content
        $imageData = file_get_contents($file['tmp_name']);
        
        // Update product with image
        $stmt = $conn->prepare("UPDATE products SET image = ? WHERE id = ?");
        
        if ($stmt->execute([$imageData, $product_id])) {
            $message = 'Image uploaded successfully!';
        } else {
            $error = 'Failed to update product image.';
        }
    }
}

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Product Image - Bro's Cafe</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="icon" type="image/png" href="../../assets/images/logo.png">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100">
    <div class="container px-4 py-8 mx-auto max-w-2xl">
        <div class="mb-6">
            <a href="products.php" class="text-amber-600 hover:text-amber-700">&larr; Back to Products</a>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-6">Upload Product Image</h1>
            
            <?php if ($message): ?>
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Product
                    </label>
                    <select name="product_id" id="product_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                        <option value="">-- Select a Product --</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                        Product Image
                    </label>
                    <input type="file" name="image" id="image" accept="image/jpeg,image/jpg,image/png,image/gif" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    <p class="mt-1 text-sm text-gray-500">Max file size: 5MB. Allowed formats: JPEG, PNG, GIF</p>
                </div>
                
                <div id="preview-container" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
                    <img id="preview" class="max-w-md rounded-lg shadow-md" alt="Image preview">
                </div>
                
                <button type="submit" 
                        class="w-full py-3 px-4 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition-colors">
                    Upload Image
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Image preview
        const imageInput = document.getElementById('image');
        const preview = document.getElementById('preview');
        const previewContainer = document.getElementById('preview-container');
        
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
