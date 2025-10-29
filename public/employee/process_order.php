<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireEmployee();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['items']) || empty($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->beginTransaction();

    // Insert order
    $stmt = $conn->prepare("
        INSERT INTO orders (order_number, employee_id, total_amount, payment_method, order_type, status) 
        VALUES (:order_number, :employee_id, :total_amount, :payment_method, :order_type, 'pending')
    ");

    $stmt->execute([
        'order_number' => $input['order_number'],
        'employee_id' => $_SESSION['user_id'],
        'total_amount' => $input['total'],
        'payment_method' => $input['payment_method'],
        'order_type' => $input['order_type']
    ]);

    $order_id = $conn->lastInsertId();

    // Insert order items and update inventory
    foreach ($input['items'] as $item) {
        // Insert order item
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, size, quantity, price, subtotal) 
            VALUES (:order_id, :product_id, :size, :quantity, :price, :subtotal)
        ");

        $subtotal = $item['price'] * $item['quantity'];

        $stmt->execute([
            'order_id' => $order_id,
            'product_id' => $item['id'],
            'size' => $item['size'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'subtotal' => $subtotal
        ]);

        // Update inventory
        $stmt = $conn->prepare("
            UPDATE inventory 
            SET quantity = quantity - :quantity 
            WHERE product_id = :product_id
        ");

        $stmt->execute([
            'quantity' => $item['quantity'],
            'product_id' => $item['id']
        ]);

        // Log inventory transaction
        $stmt = $conn->prepare("
            INSERT INTO inventory_transactions (product_id, transaction_type, quantity, user_id) 
            VALUES (:product_id, 'sale', :quantity, :user_id)
        ");

        $stmt->execute([
            'product_id' => $item['id'],
            'quantity' => -$item['quantity'],
            'user_id' => $_SESSION['user_id']
        ]);
    }

    // Update sales summary for today
    $today = date('Y-m-d');
    $total_items = array_sum(array_column($input['items'], 'quantity'));

    // Check if record exists for today
    $stmt = $conn->prepare("SELECT id FROM sales_summary WHERE date = :date");
    $stmt->execute(['date' => $today]);

    if ($stmt->fetch()) {
        // Update existing record
        $stmt = $conn->prepare("
            UPDATE sales_summary 
            SET total_orders = total_orders + 1,
                total_revenue = total_revenue + :revenue,
                total_items_sold = total_items_sold + :items
            WHERE date = :date
        ");

        $stmt->execute([
            'revenue' => $input['total'],
            'items' => $total_items,
            'date' => $today
        ]);
    } else {
        // Insert new record
        $stmt = $conn->prepare("
            INSERT INTO sales_summary (date, total_orders, total_revenue, total_items_sold) 
            VALUES (:date, 1, :revenue, :items)
        ");

        $stmt->execute([
            'date' => $today,
            'revenue' => $input['total'],
            'items' => $total_items
        ]);
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order processed successfully',
        'order_id' => $order_id
    ]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error processing order: ' . $e->getMessage()
    ]);
}
