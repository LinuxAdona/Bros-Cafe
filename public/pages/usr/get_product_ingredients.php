<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

header('Content-Type: application/json');

if (!isset($_GET['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$product_id = $_GET['product_id'];

$db = new Database();
$conn = $db->getConnection();

try {
    $stmt = $conn->prepare("
        SELECT 
            i.id,
            i.name,
            i.description,
            pi.quantity,
            pi.unit
        FROM product_ingredients pi
        INNER JOIN ingredients i ON pi.ingredient_id = i.id
        WHERE pi.product_id = :product_id
        ORDER BY i.name
    ");

    $stmt->execute(['product_id' => $product_id]);
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'ingredients' => $ingredients
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching ingredients: ' . $e->getMessage()
    ]);
}
