<?php
require_once '../../../config/database.php';
require_once '../../../src/services/functions.php';

requireRole('admin'); // Only admins can generate QR codes

$db = new Database();
$conn = $db->getConnection();

// Get current admin user info
$userId = $_SESSION['user_id'];

$sql = "SELECT id, username, email, role FROM users WHERE id = :id AND role = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $userId);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Generate QR code
$timestamp = time();
$hash = md5($admin['id'] . '_' . $timestamp . '_' . $admin['email']);
$qrCode = 'ADMIN_' . $admin['id'] . '_' . $timestamp . '_' . $hash;

// QR code will expire in 5 minutes
$expiryTime = date('h:i A', $timestamp + 300);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin QR Code Generator - Bros Cafe</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl shadow-xl border border-gray-200">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-indigo-600 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-qrcode text-3xl text-white"></i>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Admin Verification QR
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Scan this QR code for quick admin verification
                </p>
            </div>

            <!-- Admin Info -->
            <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Admin:</span>
                    <span
                        class="text-sm font-semibold text-indigo-900"><?php echo htmlspecialchars($admin['username']); ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Email:</span>
                    <span class="text-sm text-gray-600"><?php echo htmlspecialchars($admin['email']); ?></span>
                </div>
            </div>

            <!-- QR Code Display -->
            <div class="bg-white border-2 border-gray-200 rounded-lg p-6">
                <div class="flex justify-center mb-4">
                    <canvas id="qrcode" style="display:none;"></canvas>
                    <img id="qrImage" alt="QR Code" class="mx-auto" />
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 mb-2">QR Code Value:</p>
                    <div class="bg-gray-50 rounded p-2 break-all text-xs font-mono text-gray-700" id="qrCodeText">
                        <?php echo $qrCode; ?>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button onclick="copyToClipboard()"
                    class="flex-1 flex items-center justify-center px-4 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fa-solid fa-copy mr-2"></i>
                    Copy Code
                </button>
                <button onclick="downloadQR()"
                    class="flex-1 flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fa-solid fa-download mr-2"></i>
                    Download
                </button>
            </div>

            <!-- Refresh Button -->
            <div class="text-center">
                <button onclick="location.reload()" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    <i class="fa-solid fa-rotate-right mr-1"></i>
                    Generate New QR Code
                </button>
            </div>

            <!-- Back to Inventory -->
            <div class="text-center pt-4 border-t border-gray-200">
                <a href="inventory.php" class="text-sm text-gray-600 hover:text-gray-900">
                    <i class="fa-solid fa-arrow-left mr-1"></i>
                    Back to Inventory
                </a>
            </div>
        </div>
    </div>

    <script src="/js/qrcode.min.js"></script>
    <script>
        const qrCodeValue = '<?php echo $qrCode; ?>';

        document.addEventListener("DOMContentLoaded", function() {
            var canvas = document.getElementById('qrcode');
            QRCode.toCanvas(canvas, qrCodeValue, {
                width: 250,
                margin: 2,
                color: {
                    dark: '#4F46E5', // Indigo
                    light: '#FFFFFF'
                }
            }, function(error) {
                if (error) console.error(error);
                // Set image src to canvas data URL
                document.getElementById('qrImage').src = canvas.toDataURL('image/png');
            });
        });

        // Copy to clipboard
        function copyToClipboard() {
            navigator.clipboard.writeText(qrCodeValue).then(() => {
                const btn = event.target.closest('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check mr-2"></i>Copied!';
                btn.classList.add('bg-green-50', 'text-green-700', 'border-green-300');
            }).catch(err => {
                alert('Failed to copy: ' + err);
            });
        }

        // Download QR Code
        function downloadQR() {
            const canvas = document.getElementById('qrcode');
            const url = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.download = 'admin-qr-code-<?php echo $admin['username']; ?>-<?php echo $timestamp; ?>.png';
            link.href = url;
            link.click();
        }
    </script>
</body>

</html>
