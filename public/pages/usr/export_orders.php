<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

$db = new Database();
$conn = $db->getConnection();

$format = isset($_GET['format']) ? $_GET['format'] : 'csv';

// Get filter parameters (same as orders.php)
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query based on filters
$where = ["1=1"];
$params = [];

if ($status_filter !== 'all') {
    $where[] = "o.status = :status";
    $params['status'] = $status_filter;
}

if ($date_filter && $date_filter !== '') {
    $where[] = "DATE(o.created_at) = :date";
    $params['date'] = $date_filter;
}

if ($search) {
    $where[] = "(o.order_number LIKE :search OR u.full_name LIKE :search)";
    $params['search'] = "%$search%";
}

$where_clause = implode(' AND ', $where);

// Get all orders (no pagination for export)
$sql = "
    SELECT 
        o.order_number,
        o.total_amount,
        o.payment_method,
        o.status,
        o.created_at,
        e.full_name as employee_name,
        COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN users e ON o.employee_id = e.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE $where_clause
    GROUP BY o.id
    ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($format === 'csv') {
    // CSV Export
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="orders_export_' . date('Y-m-d_His') . '.csv"');

    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 support
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Add headers
    fputcsv($output, ['Order Number', 'Employee', 'Items', 'Total Amount', 'Payment Method', 'Status', 'Order Date']);

    // Add data
    foreach ($orders as $order) {
        fputcsv($output, [
            $order['order_number'],
            $order['employee_name'] ?? 'N/A',
            $order['item_count'],
            '₱' . number_format($order['total_amount'], 2),
            ucfirst($order['payment_method']),
            ucfirst($order['status']),
            date('M d, Y h:i A', strtotime($order['created_at']))
        ]);
    }

    fclose($output);
    exit;
}

if ($format === 'pdf') {
    // PDF Export
    define('FPDF_FONTPATH', '../../../src/fpdf_fonts/');
    require_once '../../../src/fpdf.php';

    $pdf = new FPDF('L', 'mm', 'A4'); // Landscape orientation
    $pdf->AddPage();

    // Title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Bros Cafe - Orders Report', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, 'Generated on: ' . date('F d, Y h:i A'), 0, 1, 'C');

    // Add filter info
    if ($date_filter) {
        $pdf->Cell(0, 5, 'Date Filter: ' . date('F d, Y', strtotime($date_filter)), 0, 1, 'C');
    }
    if ($status_filter !== 'all') {
        $pdf->Cell(0, 5, 'Status Filter: ' . ucfirst($status_filter), 0, 1, 'C');
    }

    $pdf->Ln(5);

    // Table header
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(245, 158, 11); // Amber background
    $pdf->SetTextColor(255, 255, 255); // White text
    $pdf->SetDrawColor(245, 158, 11); // Amber border

    // Column widths
    $colWidths = [35, 50, 20, 30, 35, 30, 45];

    $pdf->Cell($colWidths[0], 8, 'Order Number', 1, 0, 'L', true);
    $pdf->Cell($colWidths[1], 8, 'Employee', 1, 0, 'L', true);
    $pdf->Cell($colWidths[2], 8, 'Items', 1, 0, 'C', true);
    $pdf->Cell($colWidths[3], 8, 'Total Amount', 1, 0, 'R', true);
    $pdf->Cell($colWidths[4], 8, 'Payment Method', 1, 0, 'C', true);
    $pdf->Cell($colWidths[5], 8, 'Status', 1, 0, 'C', true);
    $pdf->Cell($colWidths[6], 8, 'Order Date', 1, 1, 'C', true);

    // Table data
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(0, 0, 0); // Black text

    foreach ($orders as $order) {
        // Set status color
        switch ($order['status']) {
            case 'completed':
                $pdf->SetFillColor(220, 252, 231); // Green
                break;
            case 'pending':
                $pdf->SetFillColor(254, 249, 195); // Yellow
                break;
            case 'preparing':
                $pdf->SetFillColor(219, 234, 254); // Blue
                break;
            case 'ready':
                $pdf->SetFillColor(233, 213, 255); // Purple
                break;
            case 'cancelled':
                $pdf->SetFillColor(254, 202, 202); // Red
                break;
            default:
                $pdf->SetFillColor(240, 240, 240); // Light gray
        }

        $employeeName = $order['employee_name'] ?? 'N/A';
        if (strlen($employeeName) > 25) {
            $employeeName = substr($employeeName, 0, 22) . '...';
        }

        $pdf->Cell($colWidths[0], 7, $order['order_number'], 1, 0, 'L', true);
        $pdf->Cell($colWidths[1], 7, $employeeName, 1, 0, 'L', true);
        $pdf->Cell($colWidths[2], 7, $order['item_count'], 1, 0, 'C', true);
        $pdf->Cell($colWidths[3], 7, 'P' . number_format($order['total_amount'], 2), 1, 0, 'R', true);
        $pdf->Cell($colWidths[4], 7, ucfirst($order['payment_method']), 1, 0, 'C', true);
        $pdf->Cell($colWidths[5], 7, ucfirst($order['status']), 1, 0, 'C', true);
        $pdf->Cell($colWidths[6], 7, date('M d, Y h:i A', strtotime($order['created_at'])), 1, 1, 'C', true);
    }

    // Summary
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);
    $totalOrders = count($orders);
    $totalRevenue = array_sum(array_column($orders, 'total_amount'));

    $pending = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
    $completed = count(array_filter($orders, fn($o) => $o['status'] === 'completed'));
    $cancelled = count(array_filter($orders, fn($o) => $o['status'] === 'cancelled'));

    $pdf->Cell(0, 6, 'Summary: Total Orders: ' . $totalOrders . ' | Total Revenue: P' . number_format($totalRevenue, 2), 0, 1);
    $pdf->Cell(0, 6, 'Pending: ' . $pending . ' | Completed: ' . $completed . ' | Cancelled: ' . $cancelled, 0, 1);

    // Output PDF
    $pdf->Output('D', 'orders_report_' . date('Y-m-d_His') . '.pdf');
    exit;
}

// If format not supported
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unsupported format']);
