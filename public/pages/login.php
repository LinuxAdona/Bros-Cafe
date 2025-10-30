<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $db = new Database();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("SELECT * FROM users WHERE (username = :username OR email = :email) AND status = 'active'");
        $stmt->execute(['username' => $username, 'email' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } elseif ($user['role'] === 'employee') {
                header('Location: ../employee/pos.php');
            } else {
                header('Location: ../customer/orders.php');
            }
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bro's Cafe</title>
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
</head>

<body class="bg-gray-100 font-['Montserrat']">
    <div class="flex items-center justify-center min-h-screen px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            <!-- Logo and Title -->
            <div class="text-center">
                <a href="home.php" class="inline-block">
                    <img src="../assets/images/logo.png" alt="Bro's Cafe Logo" class="w-20 h-20 mx-auto rounded-full">
                </a>
                <h2 class="mt-6 text-3xl font-bold text-gray-900">Welcome Back</h2>
                <p class="mt-2 text-sm text-gray-600">Sign in to your account</p>
            </div>

            <!-- Login Form -->
            <form class="p-8 mt-8 space-y-6 bg-white shadow-lg rounded-xl" method="POST">
                <?php if ($error): ?>
                    <div class="relative px-4 py-3 text-red-700 bg-red-100 border border-red-400 rounded" role="alert">
                        <span class="block sm:inline"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <div class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username or Email</label>
                        <input type="text" id="username" name="username" required
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password" name="password" required
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            class="w-4 h-4 border-gray-300 rounded text-amber-600 focus:ring-amber-500">
                        <label for="remember" class="block ml-2 text-sm text-gray-900">Remember me</label>
                    </div>

                    <div class="text-sm">
                        <a href="#" class="font-medium text-amber-600 hover:text-amber-500">Forgot password?</a>
                    </div>
                </div>

                <button type="submit"
                    class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white transition-colors border border-transparent rounded-lg shadow-sm cursor-pointer bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                    Sign In
                </button>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Don't have an account?
                        <a href="signup.php" class="font-medium text-amber-600 hover:text-amber-500">Sign up</a>
                    </p>
                </div>
            </form>

            <!-- Demo Credentials -->
            <div class="p-4 text-sm rounded-lg bg-blue-50">
                <p class="mb-2 font-semibold text-blue-800">Demo Credentials:</p>
                <p class="text-blue-700">Admin: admin / admin123</p>
                <p class="text-blue-700">Employee: employee / employee123</p>
            </div>
        </div>
    </div>
</body>

</html>