<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

// Get customer's orders
$stmt = $conn->prepare("
    SELECT o.*, COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.id
    WHERE o.customer_id = :customer_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute(['customer_id' => $_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Bro's Cafe</title>
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
</head>

<body class="bg-gray-100 font-['Montserrat']">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="../../pages/home.php" class="flex items-center">
                    <img src="../../assets/images/logo.png" alt="Logo" class="w-10 h-10 rounded-full">
                    <span class="ml-3 text-xl font-bold">Bro's Cafe</span>
                </a>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo $current_user['full_name']; ?></span>
                    <a href="../../pages/logout.php" class="text-red-600 hover:text-red-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">My Orders</h1>
            <p class="text-gray-600">View your order history and track current orders</p>
        </div>

        <?php if (empty($orders)): ?>
            <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">No Orders Yet</h3>
                <p class="text-gray-600 mb-6">You haven't placed any orders yet.</p>
                <a href="../../pages/home.php" class="inline-block bg-amber-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-amber-700">
                    Browse Menu
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800"><?php echo $order['order_number']; ?></h3>
                                <p class="text-sm text-gray-600"><?php echo date('F d, Y - h:i A', strtotime($order['created_at'])); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-amber-600"><?php echo formatCurrency($order['total_amount']); ?></p>
                                <span class="inline-block px-3 py-1 text-sm rounded-full mt-2
                                    <?php
                                    echo $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : ($order['status'] === 'cancelled' ? 'bg-red-100 text-red-800' :
                                        'bg-yellow-100 text-yellow-800');
                                    ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center space-x-4">
                                    <span class="text-gray-600">
                                        <strong>Type:</strong> <?php echo ucfirst($order['order_type']); ?>
                                    </span>
                                    <span class="text-gray-600">
                                        <strong>Payment:</strong> <?php echo ucfirst($order['payment_method']); ?>
                                    </span>
                                    <span class="text-gray-600">
                                        <strong>Items:</strong> <?php echo $order['item_count']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>