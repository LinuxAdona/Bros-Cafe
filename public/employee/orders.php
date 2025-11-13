<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireEmployee();

// madami pa need ayusin here yan muna rereview lang thankies! 

$db = new Database();
$conn = $db->getConnection();

$statuses = ['pending', 'completed', 'cancelled'];
$ordersByStatus = [];

foreach ($statuses as $status) {
    $stmt = $conn->prepare("
        SELECT o.*, u.full_name AS employee_name 
        FROM orders o
        JOIN users u ON o.employee_id = u.id
        WHERE o.status = :status
        ORDER BY o.id DESC
    ");
    $stmt->execute(['status' => $status]);
    $ordersByStatus[$status] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Management | Bro's Cafe</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    * {
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        margin: 0;
        display: flex;
        background-color: #F3F4F6;
        color: #222;
    }

    .sidebar {
        width: 240px;
        height: 100vh;
        background-color: #0B1622;
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: fixed;
        left: 0;
        top: 0;
    }

    .sidebar-top {
        padding: 25px 20px;
    }

    .sidebar h2 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 40px;
    }

    .nav-links a {
        display: flex;
        align-items: center;
        color: #d1d5db;
        text-decoration: none;
        padding: 12px 16px;
        margin-bottom: 8px;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .nav-links a.active, .nav-links a:hover {
        background-color: #FF8C00;
        color: #fff;
    }

    .sidebar-bottom {
        background-color: #09131E;
        padding: 20px;
        text-align: center;
        font-size: 14px;
    }

    /* Main Content */
    .main-content {
        margin-left: 240px;
        padding: 30px;
        width: calc(100% - 240px);
        overflow-y: auto;
    }

    h1 {
        font-size: 26px;
        font-weight: 600;
        color: #222;
        margin-bottom: 25px;
    }

    .order-section {
        background-color: #fff;
        padding: 20px 25px;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }

    .order-section h2 {
        color: #333;
        font-size: 20px;
        margin-bottom: 15px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background-color: #0B1622;
        color: #fff;
        padding: 12px;
        text-align: center;
        font-weight: 500;
        font-size: 14px;
    }

    td {
        padding: 10px;
        text-align: center;
        font-size: 14px;
        border-bottom: 1px solid #e5e7eb;
    }

    tr:hover {
        background-color: #f9fafb;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 12px;
        text-transform: capitalize;
    }

    .status-pending { background-color: #FFE58A; color: #7A5E00; }
    .status-completed { background-color: #A7F3D0; color: #065F46; }
    .status-cancelled { background-color: #FCA5A5; color: #7F1D1D; }

    .btn {
        border: none;
        border-radius: 8px;
        padding: 6px 14px;
        cursor: pointer;
        font-weight: 500;
        font-size: 13px;
        color: #fff;
        transition: all 0.3s;
    }

    .btn.view { background-color: #2563EB; }
    .btn.complete { background-color: #22C55E; }
    .btn.cancel { background-color: #EF4444; }
    .btn:hover { opacity: 0.85; }

</style>
</head>

<body>
    <div class="sidebar">
        <div class="sidebar-top">
            <h2>Bro's Cafe<br><span style="font-weight:400; font-size:13px; color:#FF8C00;">POS System</span></h2>
            <div class="nav-links">
                <a href="../dashboard.php">Dashboard</a>
                <a href="../process_order.php">POS</a>
                <a href="order_management.php" class="active">Orders</a>
                <a href="../inventory.php">Inventory</a>
            </div>
        </div>
        <div class="sidebar-bottom">
            <?= htmlspecialchars($_SESSION['full_name'] ?? 'Employee') ?><br>
            <span style="color:#9ca3af; font-size:13px;">Employee</span>
        </div>
    </div>

    <!-- Main -->
    <div class="main-content">
        <h1>Order Management</h1>

        <?php foreach ($ordersByStatus as $status => $orders): ?>
        <div class="order-section">
            <h2><?= ucfirst($status) ?> Orders</h2>
            <?php if (count($orders) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Employee</th>
                        <th>Total (₱)</th>
                        <th>Payment</th>
                        <th>Order Type</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['order_number']) ?></td>
                        <td><?= htmlspecialchars($order['employee_name']) ?></td>
                        <td><?= number_format($order['total_amount'], 2) ?></td>
                        <td><?= ucfirst($order['payment_method']) ?></td>
                        <td><?= ucfirst($order['order_type']) ?></td>
                        <td><span class="status-badge status-<?= $status ?>"><?= $status ?></span></td>
                        <td><?= htmlspecialchars($order['created_at']) ?></td>
                        <td>
                            <button class="btn view" onclick="viewOrder(<?= $order['id'] ?>)">View</button>
                            <?php if ($status === 'pending'): ?>
                                <button class="btn complete" onclick="updateStatus(<?= $order['id'] ?>, 'completed')">Complete</button>
                                <button class="btn cancel" onclick="updateStatus(<?= $order['id'] ?>, 'cancelled')">Cancel</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="text-align:center; color:#6b7280;">No <?= $status ?> orders found.</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

<script>
function updateStatus(orderId, status) {
    if (!confirm('Are you sure to mark this order as ' + status + '?')) return;
    fetch('update_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: orderId, status: status})
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
}

function viewOrder(id) {
    window.location.href = 'view_order.php?id=' + id;
}
</script>
</body>
</html>
