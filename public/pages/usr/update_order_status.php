<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['order_id']) || !isset($input['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$order_id = $input['order_id'];
$status = $input['status'];

// Validate status
$valid_statuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $success = $stmt->execute([
        'status' => $status,
        'id' => $order_id
    ]);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
