<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

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
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, role) VALUES (:username, :email, :password, :full_name, :phone, 'customer')");

            if ($stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashed_password,
                'full_name' => $full_name,
                'phone' => $phone
            ])) {
                $success = 'Registration successful! You can now login.';
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
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
</head>

<body class="bg-gray-100 font-['Montserrat']">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo and Title -->
            <div class="text-center">
                <a href="home.php" class="inline-block">
                    <img src="../assets/images/logo.png" alt="Bro's Cafe Logo" class="w-20 h-20 rounded-full mx-auto">
                </a>
                <h2 class="mt-6 text-3xl font-bold text-gray-900">Create Account</h2>
                <p class="mt-2 text-sm text-gray-600">Join Bro's Cafe today</p>
            </div>

            <!-- Signup Form -->
            <form class="mt-8 space-y-6 bg-white p-8 rounded-xl shadow-lg" method="POST">
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline"><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <div class="space-y-4">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username *</label>
                        <input type="text" id="username" name="username" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" id="email" name="email" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
                        <input type="password" id="password" name="password" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                        <p class="mt-1 text-xs text-gray-500">At least 6 characters</p>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                    </div>
                </div>

                <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
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
</body>

</html>