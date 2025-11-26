<?php
// Password Reset Utility for Bros Cafe
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword === $confirmPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $sql = "UPDATE users SET password = :password WHERE role = 'admin'";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':password', $hashedPassword);

        if ($stmt->execute()) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px;'>";
            echo "<h3>✓ Password Reset Successful!</h3>";
            echo "<p>The admin password has been updated to: <strong>$newPassword</strong></p>";
            echo "<p>Please save this password somewhere safe.</p>";
            echo "<p><a href='public/pages/usr/inventory.php' style='color: #155724;'>Go to Inventory Page</a></p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px;'>";
            echo "Error updating password!";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px;'>";
        echo "Passwords do not match!";
        echo "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password - Bros Cafe</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
        }

        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #ffc107;
        }

        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 13px;
            border-left: 4px solid #17a2b8;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔐 Reset Admin Password</h1>
        <p class="subtitle">Bros Cafe Admin Password Reset</p>

        <div class="warning">
            <strong>⚠ Security Notice:</strong> This tool should only be used by authorized personnel. Delete this file after use.
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="text" id="new_password" name="new_password" required
                    placeholder="Enter new admin password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="text" id="confirm_password" name="confirm_password" required
                    placeholder="Confirm new password">
            </div>

            <button type="submit">Reset Password</button>
        </form>

        <div class="info">
            <strong>💡 Tip:</strong> Use a strong password with letters, numbers, and special characters. Recommended: admin123, BrosCafe@2025, or your own custom password.
        </div>
    </div>
</body>

</html>