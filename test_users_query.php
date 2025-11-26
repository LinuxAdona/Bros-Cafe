<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Test with search parameter
$search = 'Admin';
$where = ["u.role IN ('admin', 'employee')"];
$params = [];

if (!empty($search)) {
    $where[] = "(u.full_name LIKE :search OR u.username LIKE :search OR u.email LIKE :search)";
    $params['search'] = "%$search%";
}

$where_clause = implode(' AND ', $where);

echo "WHERE clause: $where_clause\n";
echo "Params: " . print_r($params, true) . "\n";

try {
    // Count query
    $count_sql = "SELECT COUNT(*) as total FROM users u WHERE $where_clause";
    echo "Count SQL: $count_sql\n";
    $count_stmt = $conn->prepare($count_sql);
    foreach ($params as $key => $value) {
        echo "Binding :$key = $value\n";
        $count_stmt->bindValue(":$key", $value);
    }
    $count_stmt->execute();
    $total = $count_stmt->fetch()['total'];
    echo "Total found: $total\n\n";

    // Main query
    $sql = "
        SELECT u.*, 
               COALESCE((SELECT COUNT(*) FROM orders WHERE employee_id = u.id), 0) as total_orders,
               COALESCE((SELECT SUM(total_amount) FROM orders WHERE employee_id = u.id AND status != 'cancelled'), 0) as total_sales
        FROM users u
        WHERE $where_clause
        ORDER BY u.created_at DESC
        LIMIT 10 OFFSET 0
    ";
    echo "Main SQL: $sql\n";
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        echo "Binding :$key = $value\n";
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(':limit', 10, PDO::PARAM_INT);
    $stmt->bindValue(':offset', 0, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();

    echo "\nUsers found: " . count($users) . "\n";
    foreach ($users as $user) {
        echo "- " . $user['username'] . " (" . $user['full_name'] . ")\n";
    }
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
}
