<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireEmployee();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['id']) || !isset($input['status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $input['status'], 'id' => $input['id']]);

    echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: '.$e->getMessage()]);
}
