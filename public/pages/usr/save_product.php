<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->beginTransaction();

    $action = $data['action'] ?? 'add';

    if ($action === 'add') {
        // Insert product
        $stmt = $conn->prepare("
            INSERT INTO products (category_id, name, description, status)
            VALUES (:category_id, :name, :description, :status)
        ");
        $stmt->execute([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'status' => $data['status'] ?? 'available'
        ]);

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

        // Update product
        $stmt = $conn->prepare("
            UPDATE products 
            SET category_id = :category_id, 
                name = :name, 
                description = :description, 
                price_dodici = NULL,
                price_sedici = NULL,
                status = :status
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $product_id,
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'status' => $data['status'] ?? 'available'
        ]);

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
