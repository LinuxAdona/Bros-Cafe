<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->beginTransaction();

    $quantity = floatval($input['quantity']);
    $category = $input['category'] ?? '';
    $notes = $input['notes'] ?? '';
    $user_id = $_SESSION['user_id'];

    // Build query based on category filter
    if (!empty($category)) {
        // Get ingredients for specific category
        $sql = "
            SELECT DISTINCT i.ingredient_id 
            FROM inventory i
            JOIN product_ingredients pi ON i.ingredient_id = pi.ingredient_id
            JOIN products p ON pi.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            WHERE c.name = :category
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['category' => $category]);
    } else {
        // Get all ingredients
        $sql = "SELECT ingredient_id FROM inventory";
        $stmt = $conn->query($sql);
    }

    $ingredients = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($ingredients)) {
        throw new Exception('No items found to restock');
    }

    $updated_count = 0;

    foreach ($ingredients as $ingredient_id) {
        // Update inventory
        $stmt = $conn->prepare("
            UPDATE inventory 
            SET quantity = quantity + :quantity, last_restocked = NOW() 
            WHERE ingredient_id = :ingredient_id
        ");
        $stmt->execute([
            'quantity' => $quantity,
            'ingredient_id' => $ingredient_id
        ]);

        if ($stmt->rowCount() > 0) {
            $updated_count++;

            // Log transaction
            $stmt = $conn->prepare("
                INSERT INTO inventory_transactions (ingredient_id, transaction_type, quantity, user_id, notes) 
                VALUES (:ingredient_id, 'restock', :quantity, :user_id, :notes)
            ");
            $stmt->execute([
                'ingredient_id' => $ingredient_id,
                'quantity' => $quantity,
                'user_id' => $user_id,
                'notes' => $notes
            ]);
        }
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully restocked $updated_count items"
    ]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
