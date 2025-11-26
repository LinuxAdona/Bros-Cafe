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

if (!$data || !isset($data['name'])) {
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    // Insert category
    $stmt = $conn->prepare("
        INSERT INTO categories (name, description)
        VALUES (:name, :description)
    ");
    $stmt->execute([
        'name' => $data['name'],
        'description' => $data['description'] ?? ''
    ]);

    $category_id = $conn->lastInsertId();

    // Fetch the newly created category
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt->execute(['id' => $category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Category added successfully!',
        'category' => $category
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error adding category: ' . $e->getMessage()
    ]);
}
