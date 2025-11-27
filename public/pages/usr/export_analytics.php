<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

$format = isset($_GET['format']) ? $_GET['format'] : 'pdf';
$period = isset($_GET['period']) ? $_GET['period'] : '7days';

// Get date range filter
$start_date = '';
$end_date = date('Y-m-d');
$period_label = '';

switch ($period) {
    case 'today':
        $start_date = date('Y-m-d');
        $period_label = 'Today';
        break;
    case '7days':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $period_label = 'Last 7 Days';
        break;
    case '30days':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $period_label = 'Last 30 Days';
        break;
    case 'thismonth':
        $start_date = date('Y-m-01');
        $period_label = 'This Month';
        break;
    case 'lastmonth':
        $start_date = date('Y-m-01', strtotime('-1 month'));
        $end_date = date('Y-m-t', strtotime('-1 month'));
        $period_label = 'Last Month';
        break;
}

// Total Revenue
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(total_amount), 0) as revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN :start AND :end
    AND status != 'cancelled'
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$total_revenue = $stmt->fetch()['revenue'];

// Total Orders
$stmt = $conn->prepare("
    SELECT COUNT(*) as count
    FROM orders
    WHERE DATE(created_at) BETWEEN :start AND :end
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$total_orders = $stmt->fetch()['count'];

// Average Order Value
$avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;

// Total Items Sold
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(oi.quantity), 0) as total
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN :start AND :end
    AND o.status != 'cancelled'
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$total_items = $stmt->fetch()['total'];

// Top selling products
$stmt = $conn->prepare("
    SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN :start AND :end
    AND o.status != 'cancelled'
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 10
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sales by category
$stmt = $conn->prepare("
    SELECT c.name, SUM(oi.subtotal) as revenue, SUM(oi.quantity) as items_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN :start AND :end
    AND o.status != 'cancelled'
    GROUP BY c.id
    ORDER BY revenue DESC
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$category_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Order Types
$stmt = $conn->prepare("
    SELECT order_type, COUNT(*) as count
    FROM orders
    WHERE DATE(created_at) BETWEEN :start AND :end
    AND status != 'cancelled'
    GROUP BY order_type
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$order_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Employee Performance
$stmt = $conn->prepare("
    SELECT u.full_name,
           COUNT(o.id) as orders_processed,
           SUM(o.total_amount) as revenue_generated
    FROM orders o
    JOIN users u ON o.employee_id = u.id
    WHERE DATE(o.created_at) BETWEEN :start AND :end
    AND o.status != 'cancelled'
    GROUP BY o.employee_id
    ORDER BY revenue_generated DESC
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$employee_performance = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($format === 'pdf') {
    // PDF Export
    define('FPDF_FONTPATH', '../../../src/fpdf_fonts/');
    require_once '../../../src/fpdf.php';

    $pdf = new FPDF('P', 'mm', 'A4'); // Portrait orientation
    $pdf->AddPage();

    // Header
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->Cell(0, 10, "Bro's Cafe - Analytics Report", 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 6, $period_label, 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, 'Generated on: ' . date('F d, Y h:i A'), 0, 1, 'C');
    $pdf->Cell(0, 5, 'Period: ' . date('M d, Y', strtotime($start_date)) . ' - ' . date('M d, Y', strtotime($end_date)), 0, 1, 'C');
    $pdf->Ln(5);

    // Key Metrics Section
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor(59, 130, 246);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 8, 'Key Metrics', 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(2);

    $pdf->SetFont('Arial', '', 11);
    $metrics = [
        ['Total Revenue', 'PHP ' . number_format($total_revenue, 2)],
        ['Total Orders', number_format($total_orders)],
        ['Average Order Value', 'PHP ' . number_format($avg_order_value, 2)],
        ['Total Items Sold', number_format($total_items)]
    ];

    foreach ($metrics as $metric) {
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(95, 7, $metric[0], 1, 0, 'L', true);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(95, 7, $metric[1], 1, 1, 'R', true);
        $pdf->SetFont('Arial', '', 11);
    }
    $pdf->Ln(5);

    // Top Selling Products
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor(59, 130, 246);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 8, 'Top Selling Products', 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(2);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(10, 7, '#', 1, 0, 'C', true);
    $pdf->Cell(100, 7, 'Product Name', 1, 0, 'L', true);
    $pdf->Cell(40, 7, 'Qty Sold', 1, 0, 'C', true);
    $pdf->Cell(40, 7, 'Revenue', 1, 1, 'R', true);

    $pdf->SetFont('Arial', '', 10);
    foreach ($top_products as $index => $product) {
        $pdf->Cell(10, 6, ($index + 1), 1, 0, 'C');
        $productName = strlen($product['name']) > 45 ? substr($product['name'], 0, 42) . '...' : $product['name'];
        $pdf->Cell(100, 6, $productName, 1, 0, 'L');
        $pdf->Cell(40, 6, number_format($product['total_sold']), 1, 0, 'C');
        $pdf->Cell(40, 6, 'PHP ' . number_format($product['revenue'], 2), 1, 1, 'R');
    }
    $pdf->Ln(5);

    // Sales by Category
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor(59, 130, 246);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 8, 'Sales by Category', 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(2);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(80, 7, 'Category', 1, 0, 'L', true);
    $pdf->Cell(50, 7, 'Items Sold', 1, 0, 'C', true);
    $pdf->Cell(60, 7, 'Revenue', 1, 1, 'R', true);

    $pdf->SetFont('Arial', '', 10);
    foreach ($category_sales as $category) {
        $pdf->Cell(80, 6, $category['name'], 1, 0, 'L');
        $pdf->Cell(50, 6, number_format($category['items_sold']), 1, 0, 'C');
        $pdf->Cell(60, 6, 'PHP ' . number_format($category['revenue'], 2), 1, 1, 'R');
    }
    $pdf->Ln(5);

    // Add new page for more content
    $pdf->AddPage();

    // Order Types
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor(59, 130, 246);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 8, 'Order Types', 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(2);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(95, 7, 'Order Type', 1, 0, 'L', true);
    $pdf->Cell(95, 7, 'Count', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 10);
    foreach ($order_types as $type) {
        $pdf->Cell(95, 6, ucfirst($type['order_type']), 1, 0, 'L');
        $pdf->Cell(95, 6, number_format($type['count']), 1, 1, 'C');
    }
    $pdf->Ln(5);

    // Employee Performance
    if (count($employee_performance) > 0) {
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 8, 'Employee Performance', 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(80, 7, 'Employee', 1, 0, 'L', true);
        $pdf->Cell(50, 7, 'Orders', 1, 0, 'C', true);
        $pdf->Cell(60, 7, 'Revenue', 1, 1, 'R', true);

        $pdf->SetFont('Arial', '', 10);
        foreach ($employee_performance as $employee) {
            $pdf->Cell(80, 6, $employee['full_name'], 1, 0, 'L');
            $pdf->Cell(50, 6, number_format($employee['orders_processed']), 1, 0, 'C');
            $pdf->Cell(60, 6, 'PHP ' . number_format($employee['revenue_generated'], 2), 1, 1, 'R');
        }
    }

    // Footer
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 5, 'This report was automatically generated by Bro\'s Cafe Management System', 0, 1, 'C');

    // Output PDF
    $pdf->Output('D', 'analytics_report_' . $period . '_' . date('Y-m-d_His') . '.pdf');
    exit;
}

// If format not supported
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unsupported format']);
