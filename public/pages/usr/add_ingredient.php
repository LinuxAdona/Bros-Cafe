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
    echo json_encode(['success' => false, 'message' => 'Ingredient name is required']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->beginTransaction();

    // Insert ingredient
    $stmt = $conn->prepare("
        INSERT INTO ingredients (name, description, price)
        VALUES (:name, :description, :price)
    ");
    $stmt->execute([
        'name' => $data['name'],
        'description' => $data['description'] ?? '',
        'price' => $data['price'] ?? 0
    ]);

    $ingredient_id = $conn->lastInsertId();

    // Create inventory entry for the ingredient
    $stmt = $conn->prepare("
        INSERT INTO inventory (ingredient_id, quantity, unit, reorder_level)
        VALUES (:ingredient_id, 0, :unit, 100)
    ");
    $stmt->execute([
        'ingredient_id' => $ingredient_id,
        'unit' => $data['unit'] ?? 'g'
    ]);

    $conn->commit();

    // Fetch the newly created ingredient
    $stmt = $conn->prepare("SELECT * FROM ingredients WHERE id = :id");
    $stmt->execute(['id' => $ingredient_id]);
    $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Ingredient added successfully!',
        'ingredient' => $ingredient
    ]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error adding ingredient: ' . $e->getMessage()
    ]);
}
