<?php
require_once '../../config/database.php';
require_once '../../src/services/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = $_SESSION['user_role'];
    if ($role === 'admin') {
        header('Location: ./usr/dashboard.php');
    } else {
        header('Location: ./usr/pos.php');
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $db = new Database();
        $conn = $db->getConnection();

        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);

        if ($stmt->fetch()) {
            $error = 'Username or email already exists';
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, role) VALUES (:username, :email, :password, :full_name, :phone, 'employee')");

            if ($stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashed_password,
                'full_name' => $full_name,
                'phone' => $phone
            ])) {
                $success = 'Registration successful! You can now login.';
                // Don't redirect immediately - let the modal show first
                // JavaScript will handle the redirect after 3 seconds
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Bro's Cafe</title>
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="stylesheet" href="../assets/css/signup.css">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
</head>

<body class="bg-gray-100 font-['Montserrat']">
    <!-- Modal for Success/Error Messages -->
    <?php if ($error || $success): ?>
        <div id="messageModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 modal-overlay" style="background-color: rgba(0, 0, 0, 0.5);">
            <div class="w-full max-w-md p-6 bg-white shadow-2xl modal-content rounded-2xl">
                <div class="text-center">
                    <?php if ($success): ?>
                        <!-- Success Icon -->
                        <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 bg-green-100 rounded-full success-icon">
                            <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="mb-2 text-2xl font-bold text-gray-900">Success!</h3>
                        <p class="mb-6 text-gray-600"><?php echo $success; ?></p>
                        <button onclick="window.location.href='login.php'" class="w-full px-6 py-3 text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            Go to Login
                        </button>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <!-- Error Icon -->
                        <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 bg-red-100 rounded-full success-icon">
                            <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <h3 class="mb-2 text-2xl font-bold text-gray-900">Oops!</h3>
                        <p class="mb-6 text-gray-600"><?php echo $error; ?></p>
                        <button onclick="closeModal()" class="w-full px-6 py-3 text-white transition-colors bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            Try Again
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="flex items-center justify-center min-h-screen px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            <!-- Logo and Title -->
            <div class="text-center">
                <a href="home.php" class="inline-block">
                    <img src="../assets/images/logo.png" alt="Bro's Cafe Logo" class="w-20 h-20 mx-auto rounded-full">
                </a>
                <h2 class="mt-6 text-3xl font-bold text-gray-900">Create Account</h2>
                <p class="mt-2 text-sm text-gray-600">Join Bro's Cafe today</p>
            </div>

            <!-- Signup Form -->
            <form class="p-8 mt-8 space-y-6 bg-white shadow-lg rounded-xl" method="POST">

                <div class="space-y-4">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username *</label>
                        <input type="text" id="username" name="username" required
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" id="email" name="email" required
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
                        <input type="password" id="password" name="password" required
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                        <p class="mt-1 text-xs text-gray-500">At least 6 characters</p>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password
                            *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                    </div>
                </div>

                <button type="submit"
                    class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white transition-colors border border-transparent rounded-lg shadow-sm cursor-pointer bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                    Sign Up
                </button>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account?
                        <a href="login.php" class="font-medium text-amber-600 hover:text-amber-500">Sign in</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        function closeModal() {
            const modal = document.getElementById('messageModal');
            if (modal) {
                modal.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        }

        // Auto redirect for success messages
        <?php if ($success): ?>
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 3000);
        <?php endif; ?>

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Close modal when clicking outside
        document.getElementById('messageModal')?.addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });
    </script>
</body>

</html>