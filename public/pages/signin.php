<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in to Bros Cafe</title>
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
</head>

<body>
    <div class="min-h-screen font-['Montserrat']">
        <div class="flex flex-col lg:flex-row w-full">
            <!-- Side Image Section: hidden on mobile, visible on lg+ screens -->
            <div class="hidden lg:block lg:w-7/12">
                <img src="../assets/images/bg-login.jpg" alt="Sign In Image" class="object-cover w-full h-screen">
            </div>
            <!-- Form Section: full-width on mobile, responsive padding and sizing -->
            <div
                class="flex flex-col items-center justify-center w-full lg:w-5/12 min-h-screen p-6 sm:p-8 md:p-12 lg:p-16 bg-gray-100">
                <!-- Logo and Title -->
                <div class="flex flex-col items-center w-full max-w-md">
                    <a href="home.php" class="flex flex-col items-start w-max">
                        <img src="../assets/images/logo.png" alt="Bro's Cafe Logo"
                            class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 rounded-full">
                    </a>
                    <h1 class="mt-6 sm:mt-8 md:mt-10 text-2xl sm:text-3xl font-bold text-gray-800 text-center">Sign in
                        to your account</h1>
                    <p class="mt-3 sm:mt-4 text-sm sm:text-base text-gray-600 text-center">
                        Don't have an account?
                        <a href="signup.php" class="font-medium text-amber-600 hover:underline">Sign up</a>
                    </p>
                </div>
                <!-- Form -->
                <form class="w-full max-w-md mt-8 sm:mt-10 md:mt-12" action="">
                    <div class="flex flex-col">
                        <label for="email" class="mb-2 text-base sm:text-lg font-medium">Email address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email"
                            class="p-2.5 sm:p-3 text-sm sm:text-base transition-all bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div class="flex flex-col mt-4 sm:mt-6">
                        <label for="password" class="mb-2 text-base sm:text-lg font-medium">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password"
                            class="p-2.5 sm:p-3 text-sm sm:text-base transition-all bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full mt-4 sm:mt-6 gap-3 sm:gap-0">
                        <div class="flex items-center">
                            <input type="checkbox" id="remember" name="remember" class="w-4 h-4">
                            <label for="remember" class="ml-2 text-sm sm:text-base font-medium text-gray-700">Remember
                                me</label>
                        </div>
                        <a href="#" class="text-sm sm:text-base font-medium text-amber-600 hover:underline">Forgot
                            password?</a>
                    </div>
                    <button type="submit"
                        class="w-full p-2.5 sm:p-3 mt-6 sm:mt-8 text-sm sm:text-base font-medium text-white transition-all rounded-lg cursor-pointer bg-amber-600 hover:bg-amber-700">
                        Sign in
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>