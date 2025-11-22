<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

if (!isset($_GET['id'])) {
    echo '<p class="text-red-500">Invalid order ID</p>';
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$order_id = $_GET['id'];

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, 
           u.full_name as customer_name, 
           u.email as customer_email,
           u.phone as customer_phone,
           e.full_name as employee_name
    FROM orders o
    LEFT JOIN users u ON o.customer_id = u.id
    LEFT JOIN users e ON o.employee_id = e.id
    WHERE o.id = :id
");
$stmt->execute(['id' => $order_id]);
$order = $stmt->fetch();

if (!$order) {
    echo '<p class="text-red-500">Order not found</p>';
    exit;
}

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, p.name as product_name, p.image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = :order_id
");
$stmt->execute(['order_id' => $order_id]);
$items = $stmt->fetchAll();
?>

<div class="space-y-4">
    <!-- Order Info -->
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500">Order Number</p>
            <p class="text-lg font-semibold text-gray-900"><?php echo $order['order_number']; ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500">Order Date</p>
            <p class="text-lg font-semibold text-gray-900">
                <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500">Status</p>
            <span class="px-3 py-1 text-sm font-medium rounded-full 
                <?php
                echo match ($order['status']) {
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'preparing' => 'bg-blue-100 text-blue-800',
                    'ready' => 'bg-purple-100 text-purple-800',
                    'completed' => 'bg-green-100 text-green-800',
                    'cancelled' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800'
                };
                ?>">
                <?php echo ucfirst($order['status']); ?>
            </span>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500">Order Type</p>
            <p class="text-lg font-semibold text-gray-900"><?php echo ucfirst($order['order_type']); ?></p>
        </div>
    </div>

    <hr class="my-4">

    <!-- Customer Info -->
    <div>
        <h4 class="mb-2 font-semibold text-gray-900">Customer Information</h4>
        <div class="space-y-1 text-sm text-gray-600">
            <p><strong>Name:</strong> <?php echo $order['customer_name'] ?: 'Walk-in Customer'; ?></p>
            <?php if ($order['customer_email']): ?>
                <p><strong>Email:</strong> <?php echo $order['customer_email']; ?></p>
            <?php endif; ?>
            <?php if ($order['customer_phone']): ?>
                <p><strong>Phone:</strong> <?php echo $order['customer_phone']; ?></p>
            <?php endif; ?>
        </div>
    </div>

    <hr class="my-4">

    <!-- Order Items -->
    <div>
        <h4 class="mb-3 font-semibold text-gray-900">Order Items</h4>
        <div class="space-y-3">
            <?php foreach ($items as $item): ?>
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        <div class="text-gray-700">
                            <p class="font-medium"><?php echo $item['product_name']; ?></p>
                            <p class="text-sm text-gray-500">
                                <?php echo ucfirst($item['size']); ?>
                                • Qty: <?php echo $item['quantity']; ?>
                                • ₱<?php echo number_format($item['price'], 2); ?> each
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">₱<?php echo number_format($item['subtotal'], 2); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <hr class="my-4">

    <!-- Payment Summary -->
    <div class="p-4 rounded-lg bg-gray-50">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-600">Payment Method:</span>
            <span class="font-medium text-gray-900"><?php echo strtoupper($order['payment_method']); ?></span>
        </div>
        <?php if ($order['payment_method'] === 'gcash' && !empty($order['reference_number'])): ?>
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-600">Reference Number:</span>
                <span class="font-mono text-sm font-medium text-purple-700"><?php echo htmlspecialchars($order['reference_number']); ?></span>
            </div>
        <?php endif; ?>
        <div class="flex items-center justify-between pt-2 border-t border-gray-300">
            <span class="text-lg font-semibold text-gray-900">Total Amount:</span>
            <span
                class="text-2xl font-bold text-amber-600">₱<?php echo number_format($order['total_amount'], 2); ?></span>
        </div>
    </div>

    <!-- Employee Info -->
    <div class="text-sm text-gray-500">
        Processed by: <strong><?php echo $order['employee_name']; ?></strong>
    </div>
</div>