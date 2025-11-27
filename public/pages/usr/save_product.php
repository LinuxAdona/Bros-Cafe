<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Handle both FormData and JSON input
$data = [];
if (isset($_POST['action'])) {
    // FormData submission
    $data = $_POST;
    if (isset($data['sizes'])) {
        $data['sizes'] = json_decode($data['sizes'], true);
    }
    if (isset($data['ingredients'])) {
        $data['ingredients'] = json_decode($data['ingredients'], true);
    }
} else {
    // JSON submission (backward compatibility)
    $data = json_decode(file_get_contents('php://input'), true);
}

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->beginTransaction();

    $action = $data['action'] ?? 'add';

    // Process image upload if present
    $imageData = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($file['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPEG, PNG, and GIF are allowed.');
        }

        // Validate file size (5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File size must be less than 5MB.');
        }

        // Read file content
        $imageData = file_get_contents($file['tmp_name']);
    }

    if ($action === 'add') {
        // Insert product with image
        $sql = "INSERT INTO products (category_id, name, description, status";
        if ($imageData !== null) {
            $sql .= ", image";
        }
        $sql .= ") VALUES (:category_id, :name, :description, :status";
        if ($imageData !== null) {
            $sql .= ", :image";
        }
        $sql .= ")";

        $stmt = $conn->prepare($sql);

        $params = [
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'status' => $data['status'] ?? 'available'
        ];

        if ($imageData !== null) {
            $params['image'] = $imageData;
        }

        $stmt->execute($params);

        $product_id = $conn->lastInsertId();

        // Insert sizes (prices)
        if (!empty($data['sizes'])) {
            foreach ($data['sizes'] as $size) {
                $column = $size['type'] === 'dodici' ? 'price_dodici' : 'price_sedici';
                $stmt = $conn->prepare("
                    UPDATE products 
                    SET $column = :price 
                    WHERE id = :product_id
                ");
                $stmt->execute([
                    'price' => $size['price'],
                    'product_id' => $product_id
                ]);
            }
        }

        // Insert ingredients
        if (!empty($data['ingredients'])) {
            $stmt = $conn->prepare("
                INSERT INTO product_ingredients (product_id, ingredient_id, quantity, unit)
                VALUES (:product_id, :ingredient_id, :quantity, :unit)
            ");

            foreach ($data['ingredients'] as $ingredient) {
                $stmt->execute([
                    'product_id' => $product_id,
                    'ingredient_id' => $ingredient['id'],
                    'quantity' => $ingredient['quantity'],
                    'unit' => $ingredient['unit']
                ]);
            }
        }

        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Product added successfully!',
            'product_id' => $product_id
        ]);
    } elseif ($action === 'edit') {
        $product_id = $data['product_id'];

        // Build update query
        $sql = "UPDATE products 
                SET category_id = :category_id, 
                    name = :name, 
                    description = :description, 
                    price_dodici = NULL,
                    price_sedici = NULL,
                    status = :status";

        $params = [
            'id' => $product_id,
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'status' => $data['status'] ?? 'available'
        ];

        // Handle image update or removal
        if ($imageData !== null) {
            $sql .= ", image = :image";
            $params['image'] = $imageData;
        } elseif (isset($data['remove_image']) && $data['remove_image'] == '1') {
            $sql .= ", image = NULL";
        }

        $sql .= " WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        // Update sizes
        if (!empty($data['sizes'])) {
            foreach ($data['sizes'] as $size) {
                $column = $size['type'] === 'dodici' ? 'price_dodici' : 'price_sedici';
                $stmt = $conn->prepare("
                    UPDATE products 
                    SET $column = :price 
                    WHERE id = :product_id
                ");
                $stmt->execute([
                    'price' => $size['price'],
                    'product_id' => $product_id
                ]);
            }
        }

        // Delete old ingredients
        $stmt = $conn->prepare("DELETE FROM product_ingredients WHERE product_id = :product_id");
        $stmt->execute(['product_id' => $product_id]);

        // Insert new ingredients
        if (!empty($data['ingredients'])) {
            $stmt = $conn->prepare("
                INSERT INTO product_ingredients (product_id, ingredient_id, quantity, unit)
                VALUES (:product_id, :ingredient_id, :quantity, :unit)
            ");

            foreach ($data['ingredients'] as $ingredient) {
                $stmt->execute([
                    'product_id' => $product_id,
                    'ingredient_id' => $ingredient['id'],
                    'quantity' => $ingredient['quantity'],
                    'unit' => $ingredient['unit']
                ]);
            }
        }

        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Product updated successfully!'
        ]);
    }
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error saving product: ' . $e->getMessage()
    ]);
}
