<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireEmployee();

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$db = new Database();
$conn = $db->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

$method = $data['method'] ?? '';
$password = $data['password'] ?? '';
$qrCode = $data['qr_code'] ?? '';

try {
    if ($method === 'password') {
        // Verify admin by password - get user first, then verify password
        $sql = "SELECT id, username, password, role FROM users WHERE role = 'admin' AND status = 'active'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($admins)) {
            echo json_encode([
                'success' => false,
                'message' => 'No active admin accounts found'
            ]);
            exit;
        }

        // Try to verify password against any admin account
        $verified = false;
        $adminUsername = '';

        foreach ($admins as $admin) {
            if (password_verify($password, $admin['password'])) {
                $verified = true;
                $adminUsername = $admin['username'];
                break;
            }
        }

        if ($verified) {
            echo json_encode([
                'success' => true,
                'message' => 'Admin verified successfully',
                'admin' => $adminUsername
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid admin password'
            ]);
        }
    } elseif ($method === 'qr') {
        // Verify admin by QR code
        // QR code format: ADMIN_<user_id>_<timestamp>_<hash>
        $parts = explode('_', $qrCode);

        if (count($parts) !== 4 || $parts[0] !== 'ADMIN') {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid QR code format'
            ]);
            exit;
        }

        $userId = $parts[1];
        $timestamp = $parts[2];
        $hash = $parts[3];

        // Check if QR code is not expired (valid for 5 minutes)
        $currentTime = time();
        if ($currentTime - $timestamp > 300) {
            echo json_encode([
                'success' => false,
                'message' => 'QR code has expired'
            ]);
            exit;
        }

        // Verify QR code hash
        $sql = "SELECT id, username, email, role FROM users WHERE id = :id AND role = 'admin' AND status = 'active'";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            // Verify hash (hash should be md5 of: userId_timestamp_email)
            $expectedHash = md5($userId . '_' . $timestamp . '_' . $admin['email']);

            if ($hash === $expectedHash) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin verified successfully via QR code',
                    'admin' => $admin['username']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid QR code'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Admin not found or inactive'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid verification method'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
