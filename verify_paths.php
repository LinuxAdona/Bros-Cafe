<?php
/**
 * Path Verification Script
 * Checks that all include paths are correct
 */

echo "<h1>File Path Verification</h1>";
echo "<style>
    body { font-family: Arial; margin: 20px; }
    .pass { color: green; }
    .fail { color: red; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

$files_to_check = [
    'public/pages/login.php' => [
        'config' => '../../config/database.php',
        'includes' => '../../includes/functions.php',
        'css' => '../../src/output.css'
    ],
    'public/pages/signup.php' => [
        'config' => '../../config/database.php',
        'includes' => '../../includes/functions.php',
        'css' => '../../src/output.css'
    ],
    'public/employee/pos.php' => [
        'config' => '../../config/database.php',
        'includes' => '../../includes/functions.php',
        'css' => '../../src/output.css'
    ],
    'public/employee/process_order.php' => [
        'config' => '../../config/database.php',
        'includes' => '../../includes/functions.php'
    ],
    'public/admin/dashboard.php' => [
        'config' => '../../config/database.php',
        'includes' => '../../includes/functions.php',
        'css' => '../../src/output.css'
    ],
    'public/admin/inventory.php' => [
        'config' => '../../config/database.php',
        'includes' => '../../includes/functions.php',
        'css' => '../../src/output.css'
    ],
    'public/admin/update_inventory.php' => [
        'config' => '../../config/database.php',
        'includes' => '../../includes/functions.php'
    ],
    'public/customer/orders.php' => [
        'config' => '../../config/database.php',
        'includes' => '../../includes/functions.php',
        'css' => '../../src/output.css'
    ]
];

echo "<h2>Expected Paths</h2>";
echo "<table>";
echo "<tr><th>File</th><th>Type</th><th>Expected Path</th><th>Status</th></tr>";

foreach ($files_to_check as $file => $paths) {
    $filepath = __DIR__ . '/' . $file;
    
    if (!file_exists($filepath)) {
        echo "<tr><td colspan='4' class='fail'>File not found: $file</td></tr>";
        continue;
    }
    
    $content = file_get_contents($filepath);
    
    foreach ($paths as $type => $expected_path) {
        $pattern = '';
        if ($type === 'config') {
            $pattern = '/require_once\s+[\'"]([^"\']+database\.php)[\'"]/';
        } elseif ($type === 'includes') {
            $pattern = '/require_once\s+[\'"]([^"\']+functions\.php)[\'"]/';
        } elseif ($type === 'css') {
            $pattern = '/href=[\'"]([^"\']+output\.css)[\'"]/';
        }
        
        if (preg_match($pattern, $content, $matches)) {
            $actual_path = $matches[1];
            $status = ($actual_path === $expected_path) 
                ? "<span class='pass'>✓ Correct</span>" 
                : "<span class='fail'>✗ Wrong: $actual_path</span>";
        } else {
            $status = "<span class='fail'>✗ Not found</span>";
        }
        
        echo "<tr><td>$file</td><td>$type</td><td>$expected_path</td><td>$status</td></tr>";
    }
}

echo "</table>";

echo "<h2>Summary</h2>";
echo "<p><strong>All paths should be:</strong></p>";
echo "<ul>";
echo "<li>From <code>public/pages/</code>: <code>../../</code> to reach root</li>";
echo "<li>From <code>public/employee/</code>: <code>../../</code> to reach root</li>";
echo "<li>From <code>public/admin/</code>: <code>../../</code> to reach root</li>";
echo "<li>From <code>public/customer/</code>: <code>../../</code> to reach root</li>";
echo "</ul>";

echo "<p><strong>Common files:</strong></p>";
echo "<ul>";
echo "<li>Database config: <code>../../config/database.php</code></li>";
echo "<li>Functions: <code>../../includes/functions.php</code></li>";
echo "<li>CSS: <code>../../src/output.css</code></li>";
echo "<li>Assets: <code>../assets/...</code></li>";
echo "</ul>";
?>
