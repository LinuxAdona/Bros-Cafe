<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireEmployee();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->beginTransaction();

    $product_id = $input['product_id'];
    $quantity = intval($input['quantity']);
    $type = $input['type'] ?? 'adjustment';
    $notes = $input['notes'] ?? '';

    // Update inventory
    if ($type === 'restock') {
        $stmt = $conn->prepare("
            UPDATE inventory 
            SET quantity = quantity + :quantity, last_restocked = NOW() 
            WHERE product_id = :product_id
        ");
    } else {
        $stmt = $conn->prepare("
            UPDATE inventory 
            SET quantity = quantity + :quantity 
            WHERE product_id = :product_id
        ");
    }

    $stmt->execute([
        'quantity' => $quantity,
        'product_id' => $product_id
    ]);

    // Log transaction
    $stmt = $conn->prepare("
        INSERT INTO inventory_transactions (product_id, transaction_type, quantity, user_id, notes) 
        VALUES (:product_id, :type, :quantity, :user_id, :notes)
    ");

    $stmt->execute([
        'product_id' => $product_id,
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
