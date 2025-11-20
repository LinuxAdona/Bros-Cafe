<?php
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch();
    
    if ($product && $product['image']) {
        header("Content-Type: image/jpeg");
        echo $product['image'];
    } else {
        // Return a default placeholder image or 404
        header("HTTP/1.0 404 Not Found");
    }
    exit();
}
?>
