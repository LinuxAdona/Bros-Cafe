<?php
ob_start();
session_start();

require_once '../../../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['product_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$product_id = $_GET['product_id'];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (/opt/lampp/htdocs/Bros-Cafe/public/pages/usr/get_product_details.phpproduct) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // Get product ingredients
    $stmt = $conn->prepare("
        SELECT pi.ingredient_id, pi.quantity, pi.unit, i.name 
        FROM product_ingredients pi
        JOIN ingredients i ON pi.ingredient_id = i.id
        WHERE pi.product_id = ?
    ");
    $stmt->execute([$product_id]);
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'product' => $product,
        'ingredients' => $ingredients
    ]);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
ob_end_flush();
