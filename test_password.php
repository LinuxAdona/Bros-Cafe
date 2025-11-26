<?php
// Test password verification
$password = 'admin'; // Try common passwords
$hash = '$2y$10$AeIH0lU6rDYjEI5T3EWgauUk7oFvVCvxV5ZlBWeoMvVgEE17z8R8q';

echo "<h2>Testing password verification:</h2>";
echo "<p>Hash from database: <code>$hash</code></p>";
echo "<hr>";

$testPasswords = ['admin', 'password', '123456', 'admin123', 'Admin@123', 'broscafe', 'cafe123'];

foreach ($testPasswords as $testPass) {
    $result = password_verify($testPass, $hash);
    $status = $result ? '<strong style="color:green">✓ MATCH</strong>' : '<span style="color:red">✗ NO MATCH</span>';
    echo "Password: '<strong>$testPass</strong>' - $status<br>";
}

echo "<hr>";
echo "<h3>Try custom password:</h3>";
echo "<form method='post'>";
echo "<input type='text' name='test_pass' placeholder='Enter password' style='padding: 8px; width: 200px;'>";
echo "<button type='submit' style='padding: 8px 16px; margin-left: 8px;'>Test</button>";
echo "</form>";

if (isset($_POST['test_pass'])) {
    $testResult = password_verify($_POST['test_pass'], $hash);
    $resultText = $testResult ? '<strong style="color:green; font-size: 20px;">✓ MATCH! This is the correct password!</strong>' : '<strong style="color:red; font-size: 20px;">✗ NO MATCH - Try again</strong>';
    echo "<p>Result for '<strong>{$_POST['test_pass']}</strong>': $resultText</p>";
}
