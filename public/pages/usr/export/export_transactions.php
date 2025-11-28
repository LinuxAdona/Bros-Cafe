<?php
require_once '../../../../config/database.php';
require_once '../../../../src/services/functions.php';

requireEmployee();

$db = new Database();
$conn = $db->getConnection();

$format = isset($_GET['format']) ? $_GET['format'] : 'csv';

// Get filter parameters
$trans_search = isset($_GET['trans_search']) ? $_GET['trans_search'] : '';
$trans_type_filter = isset($_GET['trans_type']) ? $_GET['trans_type'] : '';
$trans_date_filter = isset($_GET['trans_date']) ? $_GET['trans_date'] : '';

// Build transaction query
$trans_where = ["1=1"];
$trans_params = [];

if ($trans_search) {
    $trans_where[] = "ig.name LIKE :search";
    $trans_params['search'] = "%$trans_search%";
}

if ($trans_type_filter && $trans_type_filter !== '') {
    $trans_where[] = "it.transaction_type = :type";
    $trans_params['type'] = $trans_type_filter;
}

if ($trans_date_filter && $trans_date_filter !== '') {
    $trans_where[] = "DATE(it.created_at) = :date";
    $trans_params['date'] = $trans_date_filter;
}

$trans_where_clause = implode(' AND ', $trans_where);

// Get all transaction data
$sql = "
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
    WHERE $trans_where_clause
    ORDER BY it.created_at DESC
";

$stmt = $conn->prepare($sql);
foreach ($trans_params as $key => $value) {
    $stmt->bindValue(":$key", $value);
}
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

function convertUnit($stock, $unit)
{
    // Only convert if unit is 'ml' or 'g'
    if (($unit === 'ml' || $unit === 'g') && abs($stock) >= 1000) {
        $converted = $stock / 1000;
        $newUnit = ($unit === 'ml') ? 'L' : 'kg';
        return [$converted, $newUnit];
    }
    // Otherwise, return original value and unit
    return [$stock, $unit];
}

if ($format === 'csv') {
    // CSV Export
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transactions_export_' . date('Y-m-d_His') . '.csv"');

    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 support
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Add headers
    fputcsv($output, ['Date & Time', 'Ingredient', 'Type', 'Quantity', 'Unit', 'User', 'Notes']);

    // Add data
    foreach ($transactions as $transaction) {
        $type = $transaction['transaction_type'];
        $qty = $transaction['quantity'];
        $sign = ($type === 'restock' || ($type === 'adjustment' && $qty > 0)) ? '+' : '';
        list($convertedQty, $convertedUnit) = convertUnit(abs($qty), $transaction['unit']);

        fputcsv($output, [
            date('M d, Y h:i A', strtotime($transaction['created_at'])),
            $transaction['ingredient_name'],
            ucfirst($transaction['transaction_type']),
            $sign . $convertedQty,
            $convertedUnit,
            $transaction['user_name'] ?? 'N/A',
            $transaction['notes'] ?? '-'
        ]);
    }

    fclose($output);
    exit;
}

if ($format === 'pdf') {
    // PDF Export
    define('FPDF_FONTPATH', '../../../../src/fpdf_fonts/');
    require_once '../../../../src/fpdf.php';

    $pdf = new FPDF('L', 'mm', 'A4'); // Landscape orientation
    $pdf->AddPage();

    // Title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Bros Cafe - Transaction History Report', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, 'Generated on: ' . date('F d, Y h:i A'), 0, 1, 'C');
    $pdf->Ln(5);

    // Table header
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(59, 130, 246); // Blue background
    $pdf->SetTextColor(255, 255, 255); // White text
    $pdf->SetDrawColor(59, 130, 246); // Blue border

    // Column widths
    $colWidths = [35, 50, 25, 25, 35, 60];

    $pdf->Cell($colWidths[0], 8, 'Date & Time', 1, 0, 'L', true);
    $pdf->Cell($colWidths[1], 8, 'Ingredient', 1, 0, 'L', true);
    $pdf->Cell($colWidths[2], 8, 'Type', 1, 0, 'C', true);
    $pdf->Cell($colWidths[3], 8, 'Quantity', 1, 0, 'C', true);
    $pdf->Cell($colWidths[4], 8, 'User', 1, 0, 'C', true);
    $pdf->Cell($colWidths[5], 8, 'Notes', 1, 1, 'L', true);

    // Table data
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(0, 0, 0); // Black text

    foreach ($transactions as $transaction) {
        $type = $transaction['transaction_type'];

        // Set type color
        $typeColors = [
            'restock' => [220, 252, 231],  // Green
            'sale' => [219, 234, 254],     // Blue
            'adjustment' => [254, 249, 195], // Yellow
            'waste' => [254, 226, 226]     // Red
        ];
        $color = $typeColors[$type] ?? [240, 240, 240];
        $pdf->SetFillColor($color[0], $color[1], $color[2]);

        $dateTime = date('M d, Y h:i A', strtotime($transaction['created_at']));
        $qty = $transaction['quantity'];
        $sign = ($type === 'restock' || ($type === 'adjustment' && $qty > 0)) ? '+' : '';
        list($convertedQty, $convertedUnit) = convertUnit(abs($qty), $transaction['unit']);
        $quantityStr = $sign . $convertedQty . ' ' . $convertedUnit;

        // Truncate long text to fit
        $ingredientName = strlen($transaction['ingredient_name']) > 30
            ? substr($transaction['ingredient_name'], 0, 27) . '...'
            : $transaction['ingredient_name'];
        $notes = strlen($transaction['notes'] ?? '-') > 40
            ? substr($transaction['notes'], 0, 37) . '...'
            : ($transaction['notes'] ?? '-');

        $pdf->Cell($colWidths[0], 7, $dateTime, 1, 0, 'L', true);
        $pdf->Cell($colWidths[1], 7, $ingredientName, 1, 0, 'L', true);
        $pdf->Cell($colWidths[2], 7, ucfirst($type), 1, 0, 'C', true);
        $pdf->Cell($colWidths[3], 7, $quantityStr, 1, 0, 'C', true);
        $pdf->Cell($colWidths[4], 7, $transaction['user_name'] ?? 'N/A', 1, 0, 'C', true);
        $pdf->Cell($colWidths[5], 7, $notes, 1, 1, 'L', true);
    }

    // Summary
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);
    $totalTransactions = count($transactions);

    $restockCount = count(array_filter($transactions, fn($t) => $t['transaction_type'] === 'restock'));
    $saleCount = count(array_filter($transactions, fn($t) => $t['transaction_type'] === 'sale'));
    $adjustmentCount = count(array_filter($transactions, fn($t) => $t['transaction_type'] === 'adjustment'));
    $wasteCount = count(array_filter($transactions, fn($t) => $t['transaction_type'] === 'waste'));

    $pdf->Cell(0, 6, 'Summary: Total: ' . $totalTransactions . ' | Restock: ' . $restockCount . ' | Sale: ' . $saleCount . ' | Adjustment: ' . $adjustmentCount . ' | Waste: ' . $wasteCount, 0, 1);

    // Output PDF
    $pdf->Output('D', 'transactions_report_' . date('Y-m-d_His') . '.pdf');
    exit;
}

// If format not supported
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unsupported format']);
