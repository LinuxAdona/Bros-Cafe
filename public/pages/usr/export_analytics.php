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

// Daily sales for chart
$stmt = $conn->prepare("
    SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN :start AND :end
    AND status != 'cancelled'
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute(['start' => $start_date, 'end' => $end_date]);
$daily_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($format === 'pdf') {
    // PDF Export
    define('FPDF_FONTPATH', '../../../src/fpdf_fonts/');
    require_once '../../../src/fpdf.php';

    // Extended FPDF class with chart drawing capabilities
    class PDF_Analytics extends FPDF {
        function drawBarChart($data, $title, $xLabel, $yLabel, $chartWidth = 170, $chartHeight = 60) {
            $this->SetFont('Arial', 'B', 11);
            $this->Cell(0, 6, $title, 0, 1, 'L');
            $this->Ln(2);
            
            if (empty($data)) {
                $this->SetFont('Arial', 'I', 10);
                $this->Cell(0, 6, 'No data available', 0, 1, 'C');
                return;
            }
            
            $x = $this->GetX() + 10;
            $y = $this->GetY();
            
            // Find max value for scaling
            $maxValue = max(array_column($data, 'value'));
            if ($maxValue == 0) $maxValue = 1;
            
            // Draw axes
            $this->SetDrawColor(0, 0, 0);
            $this->Line($x, $y, $x, $y + $chartHeight); // Y-axis
            $this->Line($x, $y + $chartHeight, $x + $chartWidth, $y + $chartHeight); // X-axis
            
            // Draw bars
            $barWidth = $chartWidth / count($data);
            $maxBarHeight = $chartHeight - 5;
            
            foreach ($data as $index => $item) {
                $barHeight = ($item['value'] / $maxValue) * $maxBarHeight;
                $barX = $x + ($index * $barWidth) + 2;
                $barY = $y + $chartHeight - $barHeight;
                
                // Draw bar with gradient effect
                $this->SetFillColor(59, 130, 246);
                $this->Rect($barX, $barY, $barWidth - 4, $barHeight, 'F');
                
                // Draw value on top of bar
                $this->SetFont('Arial', '', 7);
                $this->SetXY($barX, $barY - 4);
                $this->Cell($barWidth - 4, 3, number_format($item['value']), 0, 0, 'C');
                
                // Draw label
                $this->SetXY($barX, $y + $chartHeight + 1);
                $label = strlen($item['label']) > 8 ? substr($item['label'], 0, 6) . '..' : $item['label'];
                $this->Cell($barWidth - 4, 3, $label, 0, 0, 'C');
            }
            
            // Y-axis label
            $this->SetFont('Arial', '', 8);
            $this->SetXY($x - 8, $y - 2);
            $this->Cell(8, 4, number_format($maxValue), 0, 0, 'R');
            $this->SetXY($x - 8, $y + $chartHeight - 2);
            $this->Cell(8, 4, '0', 0, 0, 'R');
            
            $this->Ln($chartHeight + 8);
        }
        
        function drawPieChart($data, $title, $chartSize = 50) {
            $this->SetFont('Arial', 'B', 11);
            $this->Cell(0, 6, $title, 0, 1, 'L');
            $this->Ln(2);
            
            if (empty($data)) {
                $this->SetFont('Arial', 'I', 10);
                $this->Cell(0, 6, 'No data available', 0, 1, 'C');
                return;
            }
            
            $total = array_sum(array_column($data, 'value'));
            if ($total == 0) $total = 1;
            
            $centerX = 60;
            $centerY = $this->GetY() + $chartSize + 5;
            
            // Colors for pie slices
            $colors = [
                [59, 130, 246],   // Blue
                [245, 158, 11],   // Amber
                [16, 185, 129],   // Green
                [239, 68, 68],    // Red
                [139, 92, 246],   // Purple
            ];
            
            $startAngle = 0;
            foreach ($data as $index => $item) {
                $angle = ($item['value'] / $total) * 360;
                $color = $colors[$index % count($colors)];
                $this->SetFillColor($color[0], $color[1], $color[2]);
                
                // Draw pie slice
                $this->drawPieSlice($centerX, $centerY, $chartSize, $startAngle, $startAngle + $angle);
                
                $startAngle += $angle;
            }
            
            // Draw legend
            $legendX = $centerX + $chartSize + 15;
            $legendY = $centerY - $chartSize + 5;
            
            $this->SetFont('Arial', '', 9);
            foreach ($data as $index => $item) {
                $color = $colors[$index % count($colors)];
                $this->SetFillColor($color[0], $color[1], $color[2]);
                $this->Rect($legendX, $legendY + ($index * 7), 4, 4, 'F');
                
                $percentage = ($item['value'] / $total) * 100;
                $this->SetXY($legendX + 6, $legendY + ($index * 7) - 0.5);
                $label = strlen($item['label']) > 20 ? substr($item['label'], 0, 18) . '..' : $item['label'];
                $this->Cell(60, 5, $label . ' (' . number_format($percentage, 1) . '%)', 0, 0, 'L');
            }
            
            $this->Ln($chartSize * 2 + 5);
        }
        
        function drawPieSlice($centerX, $centerY, $radius, $startAngle, $endAngle) {
            $startAngle = deg2rad($startAngle - 90);
            $endAngle = deg2rad($endAngle - 90);
            
            // Draw filled triangle fan for smooth pie slice
            $steps = 20;
            $angleStep = ($endAngle - $startAngle) / $steps;
            
            for ($i = 0; $i < $steps; $i++) {
                $a1 = $startAngle + ($i * $angleStep);
                $a2 = $startAngle + (($i + 1) * $angleStep);
                
                $x1 = $centerX + ($radius * cos($a1));
                $y1 = $centerY + ($radius * sin($a1));
                $x2 = $centerX + ($radius * cos($a2));
                $y2 = $centerY + ($radius * sin($a2));
                
                $points = [
                    ['x' => $centerX, 'y' => $centerY],
                    ['x' => $x1, 'y' => $y1],
                    ['x' => $x2, 'y' => $y2]
                ];
                
                $this->Polygon($points);
            }
        }
        
        function Polygon($points) {
            if (count($points) < 3) return;
            
            $this->_out(sprintf('%.2F %.2F m', $points[0]['x'] * $this->k, ($this->h - $points[0]['y']) * $this->k));
            for ($i = 1; $i < count($points); $i++) {
                $this->_out(sprintf('%.2F %.2F l', $points[$i]['x'] * $this->k, ($this->h - $points[$i]['y']) * $this->k));
            }
            $this->_out('h f');
        }
    }

    $pdf = new PDF_Analytics('P', 'mm', 'A4');
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

    // Add new page for charts
    $pdf->AddPage();

    // Sales Trend Chart
    if (count($daily_sales) > 0) {
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 8, 'Visual Analytics', 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(5);

        // Prepare daily sales data for chart
        $salesChartData = [];
        foreach ($daily_sales as $sale) {
            $salesChartData[] = [
                'label' => date('M d', strtotime($sale['date'])),
                'value' => floatval($sale['revenue'])
            ];
        }
        
        // Limit to last 10 days for readability
        if (count($salesChartData) > 10) {
            $salesChartData = array_slice($salesChartData, -10);
        }
        
        $pdf->drawBarChart($salesChartData, 'Daily Revenue Trend', 'Date', 'Revenue (PHP)');
        $pdf->Ln(5);
    }

    // Category Sales Bar Chart
    if (count($category_sales) > 0) {
        $categoryChartData = [];
        foreach ($category_sales as $category) {
            $categoryChartData[] = [
                'label' => $category['name'],
                'value' => floatval($category['revenue'])
            ];
        }
        
        $pdf->drawBarChart($categoryChartData, 'Revenue by Category', 'Category', 'Revenue (PHP)');
        $pdf->Ln(5);
    }

    // Order Types Pie Chart
    if (count($order_types) > 0) {
        $orderTypeChartData = [];
        foreach ($order_types as $type) {
            $orderTypeChartData[] = [
                'label' => ucfirst($type['order_type']),
                'value' => intval($type['count'])
            ];
        }
        
        $pdf->drawPieChart($orderTypeChartData, 'Order Types Distribution');
        $pdf->Ln(5);
    }

    // Top Products Bar Chart
    if (count($top_products) > 0) {
        $topProductsChartData = [];
        $displayCount = min(5, count($top_products)); // Show top 5
        for ($i = 0; $i < $displayCount; $i++) {
            $topProductsChartData[] = [
                'label' => $top_products[$i]['name'],
                'value' => floatval($top_products[$i]['total_sold'])
            ];
        }
        
        $pdf->drawBarChart($topProductsChartData, 'Top 5 Products by Quantity Sold', 'Product', 'Quantity');
    }

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
