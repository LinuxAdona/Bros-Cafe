<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

$db = new Database();
$conn = $db->getConnection();

$format = isset($_GET['format']) ? $_GET['format'] : 'csv';

// Get all inventory data
$inventory_sql = "
    SELECT 
        i.name AS ingredient_name,
        inv.quantity,
        inv.unit,
        inv.reorder_level,
        inv.last_restocked,
        CASE 
            WHEN inv.quantity = 0 THEN 'Out of Stock'
            WHEN inv.quantity <= inv.reorder_level THEN 'Low Stock'
            ELSE 'In Stock'
        END AS status
    FROM ingredients i
    JOIN inventory inv ON i.id = inv.ingredient_id
    ORDER BY i.name
";
$stmt = $conn->query($inventory_sql);
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all transactions
$transactions_sql = "
    SELECT 
        it.id,
        ig.name AS ingredient_name,
        it.transaction_type,
        it.quantity,
        i.unit,
        it.notes,
        it.created_at,
        u.username AS user_name
    FROM inventory_transactions it
    JOIN ingredients ig ON it.ingredient_id = ig.id
    JOIN inventory i ON ig.id = i.ingredient_id
    LEFT JOIN users u ON it.user_id = u.id
    ORDER BY it.created_at DESC
";
$stmt = $conn->query($transactions_sql);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get stock distribution
$stmt = $conn->query("
    SELECT 
        SUM(CASE WHEN i.quantity > i.reorder_level THEN 1 ELSE 0 END) as in_stock,
        SUM(CASE WHEN i.quantity <= i.reorder_level AND i.quantity > 0 THEN 1 ELSE 0 END) as low_stock,
        SUM(CASE WHEN i.quantity = 0 THEN 1 ELSE 0 END) as out_of_stock
    FROM inventory i
");
$stock_distribution = $stmt->fetch();

if ($format === 'csv') {
    // CSV Export
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="inventory_complete_export_' . date('Y-m-d_His') . '.csv"');

    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 support
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // ===== INVENTORY SECTION =====
    fputcsv($output, ['INVENTORY OVERVIEW']);
    fputcsv($output, []);
    fputcsv($output, ['Stock Status Summary']);
    fputcsv($output, ['In Stock', 'Low Stock', 'Out of Stock']);
    fputcsv($output, [
        $stock_distribution['in_stock'],
        $stock_distribution['low_stock'],
        $stock_distribution['out_of_stock']
    ]);
    fputcsv($output, []);
    fputcsv($output, []);

    // Inventory table headers
    fputcsv($output, ['CURRENT INVENTORY']);
    fputcsv($output, ['Ingredient Name', 'Quantity', 'Unit', 'Reorder Level', 'Status', 'Last Restocked']);

    // Inventory data
    foreach ($inventory as $item) {
        fputcsv($output, [
            $item['ingredient_name'],
            $item['quantity'],
            $item['unit'],
            $item['reorder_level'],
            $item['status'],
            $item['last_restocked'] ? date('M d, Y H:i', strtotime($item['last_restocked'])) : 'Never'
        ]);
    }

    fputcsv($output, []);
    fputcsv($output, []);

    // ===== TRANSACTIONS SECTION =====
    fputcsv($output, ['TRANSACTION HISTORY']);
    fputcsv($output, ['Transaction ID', 'Ingredient', 'Type', 'Quantity', 'Unit', 'User', 'Notes', 'Date']);

    // Transaction data
    foreach ($transactions as $trans) {
        fputcsv($output, [
            $trans['id'],
            $trans['ingredient_name'],
            ucfirst($trans['transaction_type']),
            $trans['quantity'],
            $trans['unit'],
            $trans['user_name'] ?? 'N/A',
            $trans['notes'] ?? '-',
            date('M d, Y h:i A', strtotime($trans['created_at']))
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

    // ===== TITLE =====
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 10, 'Bros Cafe - Complete Inventory Report', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, 'Generated on: ' . date('F d, Y h:i A'), 0, 1, 'C');
    $pdf->Ln(5);

    // ===== STOCK SUMMARY =====
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'Stock Status Summary', 0, 1);
    $pdf->SetFont('Arial', '', 10);

    $pdf->SetFillColor(220, 252, 231); // Green
    $pdf->Cell(60, 7, 'In Stock: ' . $stock_distribution['in_stock'], 1, 0, 'L', true);
    $pdf->SetFillColor(254, 249, 195); // Yellow
    $pdf->Cell(60, 7, 'Low Stock: ' . $stock_distribution['low_stock'], 1, 0, 'L', true);
    $pdf->SetFillColor(254, 202, 202); // Red
    $pdf->Cell(60, 7, 'Out of Stock: ' . $stock_distribution['out_of_stock'], 1, 1, 'L', true);
    $pdf->Ln(5);

    // ===== INVENTORY TABLE =====
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'Current Inventory', 0, 1);
    $pdf->Ln(2);

    // Table header
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(59, 130, 246); // Blue background
    $pdf->SetTextColor(255, 255, 255); // White text
    $pdf->SetDrawColor(59, 130, 246); // Blue border

    // Column widths for inventory
    $invColWidths = [70, 25, 18, 28, 30, 40];

    $pdf->Cell($invColWidths[0], 7, 'Ingredient Name', 1, 0, 'L', true);
    $pdf->Cell($invColWidths[1], 7, 'Quantity', 1, 0, 'C', true);
    $pdf->Cell($invColWidths[2], 7, 'Unit', 1, 0, 'C', true);
    $pdf->Cell($invColWidths[3], 7, 'Reorder Lvl', 1, 0, 'C', true);
    $pdf->Cell($invColWidths[4], 7, 'Status', 1, 0, 'C', true);
    $pdf->Cell($invColWidths[5], 7, 'Last Restocked', 1, 1, 'C', true);

    // Table data
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(0, 0, 0); // Black text

    foreach ($inventory as $item) {
        // Set status color
        if ($item['status'] === 'Out of Stock') {
            $pdf->SetFillColor(254, 202, 202); // Red
        } elseif ($item['status'] === 'Low Stock') {
            $pdf->SetFillColor(254, 243, 199); // Yellow
        } else {
            $pdf->SetFillColor(240, 240, 240); // Light gray
        }

        $lastRestocked = $item['last_restocked'] ? date('M d, Y H:i', strtotime($item['last_restocked'])) : 'Never';

        $pdf->Cell($invColWidths[0], 6, $item['ingredient_name'], 1, 0, 'L', true);
        $pdf->Cell($invColWidths[1], 6, $item['quantity'], 1, 0, 'C', true);
        $pdf->Cell($invColWidths[2], 6, $item['unit'], 1, 0, 'C', true);
        $pdf->Cell($invColWidths[3], 6, $item['reorder_level'], 1, 0, 'C', true);
        $pdf->Cell($invColWidths[4], 6, $item['status'], 1, 0, 'C', true);
        $pdf->Cell($invColWidths[5], 6, $lastRestocked, 1, 1, 'C', true);
    }

    // ===== TRANSACTIONS TABLE =====
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'Transaction History', 0, 1);
    $pdf->Ln(2);

    // Transaction table header
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(139, 92, 246); // Purple background
    $pdf->SetTextColor(255, 255, 255); // White text
    $pdf->SetDrawColor(139, 92, 246); // Purple border

    // Column widths for transactions
    $transColWidths = [18, 50, 25, 22, 18, 30, 40, 45];

    $pdf->Cell($transColWidths[0], 7, 'ID', 1, 0, 'C', true);
    $pdf->Cell($transColWidths[1], 7, 'Ingredient', 1, 0, 'L', true);
    $pdf->Cell($transColWidths[2], 7, 'Type', 1, 0, 'C', true);
    $pdf->Cell($transColWidths[3], 7, 'Quantity', 1, 0, 'C', true);
    $pdf->Cell($transColWidths[4], 7, 'Unit', 1, 0, 'C', true);
    $pdf->Cell($transColWidths[5], 7, 'User', 1, 0, 'L', true);
    $pdf->Cell($transColWidths[6], 7, 'Notes', 1, 1, 'L', true);

    // Transaction data
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(0, 0, 0); // Black text

    $rowCount = 0;
    foreach ($transactions as $trans) {
        // Set alternating row colors
        if ($rowCount % 2 == 0) {
            $pdf->SetFillColor(248, 248, 248);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }

        // Truncate long notes
        $notes = $trans['notes'] ?? '-';
        if (strlen($notes) > 20) {
            $notes = substr($notes, 0, 17) . '...';
        }

        // Truncate long ingredient names
        $ingredientName = $trans['ingredient_name'];
        if (strlen($ingredientName) > 25) {
            $ingredientName = substr($ingredientName, 0, 22) . '...';
        }

        $userName = $trans['user_name'] ?? 'N/A';
        if (strlen($userName) > 15) {
            $userName = substr($userName, 0, 12) . '...';
        }

        $pdf->Cell($transColWidths[0], 6, $trans['id'], 1, 0, 'C', true);
        $pdf->Cell($transColWidths[1], 6, $ingredientName, 1, 0, 'L', true);
        $pdf->Cell($transColWidths[2], 6, ucfirst($trans['transaction_type']), 1, 0, 'C', true);
        $pdf->Cell($transColWidths[3], 6, $trans['quantity'], 1, 0, 'C', true);
        $pdf->Cell($transColWidths[4], 6, $trans['unit'], 1, 0, 'C', true);
        $pdf->Cell($transColWidths[5], 6, $userName, 1, 0, 'L', true);
        $pdf->Cell($transColWidths[6], 6, $notes, 1, 1, 'L', true);

        $rowCount++;

        // Add new page if needed
        if ($pdf->GetY() > 180 && $rowCount < count($transactions)) {
            $pdf->AddPage();
            // Repeat header
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetFillColor(139, 92, 246);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell($transColWidths[0], 7, 'ID', 1, 0, 'C', true);
            $pdf->Cell($transColWidths[1], 7, 'Ingredient', 1, 0, 'L', true);
            $pdf->Cell($transColWidths[2], 7, 'Type', 1, 0, 'C', true);
            $pdf->Cell($transColWidths[3], 7, 'Quantity', 1, 0, 'C', true);
            $pdf->Cell($transColWidths[4], 7, 'Unit', 1, 0, 'C', true);
            $pdf->Cell($transColWidths[5], 7, 'User', 1, 0, 'L', true);
            $pdf->Cell($transColWidths[6], 7, 'Notes', 1, 1, 'L', true);
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(0, 0, 0);
        }
    }

    // Summary
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 6, 'Summary: Total Transactions: ' . count($transactions), 0, 1);

    // Output PDF
    $pdf->Output('D', 'inventory_complete_report_' . date('Y-m-d_His') . '.pdf');
    exit;
}

// If format not supported
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unsupported format']);
