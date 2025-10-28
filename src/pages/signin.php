<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in to Bro's Cafe</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" type="image/png" href="../../public/assets/images/logo.png">
</head>

<body>
    <div class="min-h-screen font-['Montserrat']">
        <div class="flex w-full">
            <div class="flex flex-col items-center justify-center w-5/12 h-screen p-16 bg-gray-100">
                <div class="flex flex-col items-start w-full">
                    <a href="home.php" class="flex flex-col items-start w-max">
                        <img src="../../public/assets/images/logo.png" alt="Bro's Cafe Logo"
                            class="w-24 h-24 rounded-full">
                    </a>
                    <h1 class="mt-10 text-3xl font-bold text-gray-800">Sign in to your account</h1>
                    <p class="mt-4 text-start text-gray-600">
                        Don't have an account?
                        <a href="signup.php" class="font-medium text-amber-600 hover:underline">Sign up</a>
                    </p>
                </div>
                <form class="w-full mt-12" action="">
                    <div class="flex flex-col">
                        <label for="email" class="mb-2 text-lg font-medium">Email address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email"
                            class="p-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div class="flex flex-col mt-6">
                        <label for="password" class="mb-2 text-lg font-medium">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password"
                            class="p-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div class="flex items-center justify-between w-full mt-6">
                        <div class="flex items-center">
                            <input type="checkbox" id="remember" name="remember" class="w-4 h-4">
                            <label for="remember" class="ml-2 text-gray-700 font-medium">Remember me</label>
                        </div>
                        <a href="#" class="text-amber-600 font-medium hover:underline">Forgot password?</a>
                    </div>
                    <button type="submit"
                        class="w-full p-3 mt-8 font-medium text-white transition-all rounded-lg cursor-pointer bg-amber-600 hover:bg-amber-700">
                        Sign in
                    </button>
                </form>
            </div>
            <!-- Side Image Section -->
            <div class="flex w-7/12">
                <img src="../../public/assets/images/bg-login.jpg" alt="Sign In Image"
                    class="object-cover w-full h-screen">
            </div>
        </div>
    </div>
</body>

</html>