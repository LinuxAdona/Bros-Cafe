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

<!-- Order Info -->
<div class="p-4 rounded-lg bg-gray-50">
    <div class="grid grid-cols-2 gap-3 text-sm">
        <div>
            <p class="text-xs text-gray-500">Employee</p>
            <p class="font-semibold text-gray-800"><?php echo $order['employee_name'] ?? 'N/A'; ?></p>
        </div>
        <div>
            <p class="text-xs text-gray-500">Status</p>
            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full 
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
            <p class="text-xs text-gray-500">Order Type</p>
            <p class="font-semibold text-gray-800"><?php echo ucfirst($order['order_type']); ?></p>
        </div>
        <div>
            <p class="text-xs text-gray-500">Payment Method</p>
            <p class="font-semibold text-gray-800"><?php echo strtoupper($order['payment_method']); ?></p>
        </div>
        <?php if ($order['payment_method'] === 'gcash' && !empty($order['reference_number'])): ?>
            <div class="col-span-2">
                <p class="text-xs text-gray-500">Reference Number</p>
                <p class="font-mono text-sm font-medium text-purple-700"><?php echo htmlspecialchars($order['reference_number']); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Customer Information -->
<?php if ($order['customer_name']): ?>
    <div class="p-4 rounded-lg bg-blue-50">
        <h4 class="mb-2 text-sm font-bold text-gray-700 uppercase">Customer Information</h4>
        <div class="space-y-1 text-sm">
            <p class="text-gray-700"><span class="font-semibold">Name:</span> <?php echo $order['customer_name']; ?></p>
            <?php if ($order['customer_email']): ?>
                <p class="text-gray-700"><span class="font-semibold">Email:</span> <?php echo $order['customer_email']; ?></p>
            <?php endif; ?>
            <?php if ($order['customer_phone']): ?>
                <p class="text-gray-700"><span class="font-semibold">Phone:</span> <?php echo $order['customer_phone']; ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Order Items -->
<div>
    <h4 class="mb-3 text-sm font-bold text-gray-700 uppercase">Order Items</h4>
    <div class="space-y-2">
        <?php foreach ($items as $item): ?>
            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                <div class="flex items-center flex-1">
                    <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 text-sm font-bold rounded-full bg-amber-100 text-amber-600">
                        <?php echo $item['quantity']; ?>x
                    </div>
                    <div class="ml-3">
                        <p class="font-semibold text-gray-800"><?php echo $item['product_name']; ?></p>
                        <p class="text-xs text-gray-500">Size: <span class="font-medium"><?php echo ucfirst($item['size']); ?></span></p>
                    </div>
                </div>
                <p class="font-semibold text-gray-700">₱<?php echo number_format($item['subtotal'], 2); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Total -->
<div class="pt-4 border-t-2 border-gray-300">
    <div class="flex items-center justify-between">
        <span class="text-lg font-bold text-gray-800">Total Amount</span>
        <span class="text-2xl font-bold text-amber-600">₱<?php echo number_format($order['total_amount'], 2); ?></span>
    </div>
</div>

<script>
    // Update modal header with order info
    document.getElementById('modalOrderNumber').textContent = '<?php echo $order['order_number']; ?>';
    document.getElementById('modalDateTime').textContent = '<?php echo date('M d, Y • h:i A', strtotime($order['created_at'])); ?>';
</script>