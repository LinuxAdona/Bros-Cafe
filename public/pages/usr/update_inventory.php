<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['ingredient_id']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->beginTransaction();

    $ingredient_id = $input['ingredient_id'];
    $quantity = intval($input['quantity']);
    $type = $input['type'] ?? 'adjustment';
    $notes = $input['notes'] ?? '';

    // Update inventory
    if ($type === 'restock') {
        $stmt = $conn->prepare("
            UPDATE inventory 
            SET quantity = quantity + :quantity, last_restocked = NOW() 
            WHERE ingredient_id = :ingredient_id
        ");
    } else {
        $stmt = $conn->prepare("
            UPDATE inventory 
            SET quantity = quantity + :quantity 
            WHERE ingredient_id = :ingredient_id
        ");
    }

    $stmt->execute([
        'quantity' => $quantity,
        'ingredient_id' => $ingredient_id
    ]);

    // Log transaction
    $stmt = $conn->prepare("
        INSERT INTO inventory_transactions (ingredient_id, transaction_type, quantity, user_id, notes) 
        VALUES (:ingredient_id, :type, :quantity, :user_id, :notes)
    ");

    $stmt->execute([
        'ingredient_id' => $ingredient_id,
        'type' => $type,
        'quantity' => $quantity,
        'user_id' => $_SESSION['user_id'],
        'notes' => $notes
    ]);

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Inventory updated successfully']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
