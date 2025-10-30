<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Bros Cafe</title>
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body class="bg-gray-100 font-['Montserrat']">
    <!-- Navigation Bar -->
    <nav class="sticky top-0 z-50 flex items-center h-16 shadow-md bg-gray-50/80 backdrop-blur-md">
        <div class="container flex items-center justify-between px-4 mx-auto">
            <a href="home.php" class="flex items-center">
                <img src="../assets/images/logo.png" alt="Bro's Cafe Logo" class="w-10 h-10 rounded-full">
                <span class="ml-3 text-xl font-bold">BROS CAFE</span>
            </a>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="text-gray-700 lg:hidden focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Desktop Menu -->
            <ul class="hidden space-x-2 font-medium lg:flex">
                <li class="transition ease-out hover:-translate-y-0.5">
                    <a href="home.php" class="px-4 py-2 transition-all bg-transparent rounded-lg hover:bg-white hover:shadow-md">Home</a>
                </li>
                <li class="transition ease-out hover:-translate-y-0.5">
                    <a href="menu.php" class="px-4 py-2 text-white transition-all rounded-lg bg-amber-500 hover:bg-amber-600 hover:shadow-md">Menu</a>
                </li>
                <li class="transition ease-out hover:-translate-y-0.5">
                    <a href="about.php" class="px-4 py-2 transition-all bg-transparent rounded-lg hover:bg-white hover:shadow-md">About</a>
                </li>
                <li class="transition ease-out hover:-translate-y-0.5">
                    <a href="contact.php" class="px-4 py-2 transition-all bg-transparent rounded-lg hover:bg-white hover:shadow-md">Contact</a>
                </li>
            </ul>
            <div class="hidden space-x-4 font-medium lg:flex lg:items-center">
                <a href="login.php" class="transition ease-out hover:-translate-x-0.5">Log in</a>
                <a href="signup.php" class="px-4 py-2 text-white transition-all rounded-lg bg-amber-500 hover:bg-amber-600 hover:shadow-md">Sign up</a>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="absolute left-0 right-0 hidden bg-white shadow-lg top-16 lg:hidden">
            <ul class="flex flex-col py-4 space-y-2">
                <li><a href="home.php" class="block px-6 py-2 transition hover:bg-amber-50">Home</a></li>
                <li><a href="menu.php" class="block px-6 py-2 transition bg-amber-50 text-amber-600">Menu</a></li>
                <li><a href="about.php" class="block px-6 py-2 transition hover:bg-amber-50">About</a></li>
                <li><a href="contact.php" class="block px-6 py-2 transition hover:bg-amber-50">Contact</a></li>
                <li class="px-6 pt-4 border-t"><a href="login.php" class="block py-2 transition hover:text-amber-600">Log in</a></li>
                <li class="px-6"><a href="signup.php" class="block px-4 py-2 text-center text-white transition-all rounded-lg bg-amber-500 hover:bg-amber-600">Sign up</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20 text-white bg-linear-to-br from-amber-600 via-orange-500 to-amber-600">
        <div class="container px-4 mx-auto text-center">
            <h1 class="mb-4 text-4xl font-black md:text-6xl">Our Menu</h1>
            <p class="text-xl md:text-2xl">Discover your next favorite drink</p>
        </div>
    </section>

    <!-- Category Filter -->
    <section class="py-8 bg-white shadow-md">
        <div class="container px-4 mx-auto">
            <div class="flex flex-wrap justify-center gap-3">
                <button class="category-btn active px-6 py-2 font-semibold transition-all rounded-full bg-amber-500 text-white hover:bg-amber-600" data-category="all">
                    All Items
                </button>
                <button class="category-btn px-6 py-2 font-semibold transition-all rounded-full bg-gray-200 text-gray-700 hover:bg-amber-500 hover:text-white" data-category="coffee">
                    Coffee
                </button>
                <button class="category-btn px-6 py-2 font-semibold transition-all rounded-full bg-gray-200 text-gray-700 hover:bg-amber-500 hover:text-white" data-category="non-coffee">
                    Non-Coffee
                </button>
                <button class="category-btn px-6 py-2 font-semibold transition-all rounded-full bg-gray-200 text-gray-700 hover:bg-amber-500 hover:text-white" data-category="frappe">
                    Frappe
                </button>
                <button class="category-btn px-6 py-2 font-semibold transition-all rounded-full bg-gray-200 text-gray-700 hover:bg-amber-500 hover:text-white" data-category="food">
                    Food & Snacks
                </button>
            </div>
        </div>
    </section>

    <!-- Menu Items -->
    <section class="py-16">
        <div class="container px-4 mx-auto">
            <!-- Coffee Section -->
            <div class="mb-16" data-category-section="coffee">
                <h2 class="mb-8 text-3xl font-bold text-center text-gray-800 md:text-4xl">☕ Signature Coffee</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Item 1 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-amber-100 to-amber-200 flex items-center justify-center">
                            <span class="text-7xl">☕</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Sea Salt Latte</h3>
                            <p class="mb-4 text-gray-600">Espresso and milk topped with sea salt cream</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Dodici: <span class="font-semibold text-amber-600">₱120</span></p>
                                    <p class="text-sm text-gray-500">Sedici: <span class="font-semibold text-amber-600">₱150</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-orange-100 to-orange-200 flex items-center justify-center">
                            <span class="text-7xl">☕</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Spanish Latte</h3>
                            <p class="mb-4 text-gray-600">Sweet twist on a classic iced cafe latte</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Dodici: <span class="font-semibold text-amber-600">₱120</span></p>
                                    <p class="text-sm text-gray-500">Sedici: <span class="font-semibold text-amber-600">₱140</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 3 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-pink-100 to-pink-200 flex items-center justify-center">
                            <span class="text-7xl">☕</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">White Chocolate Mocha</h3>
                            <p class="mb-4 text-gray-600">Espresso, white chocolate sauce, milk and ice</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Dodici: <span class="font-semibold text-amber-600">₱120</span></p>
                                    <p class="text-sm text-gray-500">Sedici: <span class="font-semibold text-amber-600">₱140</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 4 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-yellow-100 to-yellow-200 flex items-center justify-center">
                            <span class="text-7xl">☕</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Caramel Macchiato</h3>
                            <p class="mb-4 text-gray-600">Espresso shot with vanilla, caramel sauce, milk and ice</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Dodici: <span class="font-semibold text-amber-600">₱130</span></p>
                                    <p class="text-sm text-gray-500">Sedici: <span class="font-semibold text-amber-600">₱150</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 5 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-amber-200 to-orange-200 flex items-center justify-center">
                            <span class="text-7xl">☕</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Americano</h3>
                            <p class="mb-4 text-gray-600">Pure espresso with hot water</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Dodici: <span class="font-semibold text-amber-600">₱90</span></p>
                                    <p class="text-sm text-gray-500">Sedici: <span class="font-semibold text-amber-600">₱110</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 6 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-brown-100 to-amber-300 flex items-center justify-center">
                            <span class="text-7xl">☕</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Cappuccino</h3>
                            <p class="mb-4 text-gray-600">Espresso with steamed milk foam</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Dodici: <span class="font-semibold text-amber-600">₱110</span></p>
                                    <p class="text-sm text-gray-500">Sedici: <span class="font-semibold text-amber-600">₱130</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Non-Coffee Section -->
            <div class="mb-16" data-category-section="non-coffee">
                <h2 class="mb-8 text-3xl font-bold text-center text-gray-800 md:text-4xl">🍵 Non-Coffee Delights</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Item 1 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-green-100 to-green-200 flex items-center justify-center">
                            <span class="text-7xl">🍵</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Matcha Latte</h3>
                            <p class="mb-4 text-gray-600">Creamy matcha with oat milk</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱140</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-lime-100 to-lime-200 flex items-center justify-center">
                            <span class="text-7xl">🍵</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Banana Pudding Matcha</h3>
                            <p class="mb-4 text-gray-600">Matcha latte topped with banana pudding</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱180</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 3 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-red-100 to-pink-200 flex items-center justify-center">
                            <span class="text-7xl">🍓</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Strawberry Smoothie</h3>
                            <p class="mb-4 text-gray-600">Fresh strawberries blended with yogurt</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱150</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 4 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-purple-100 to-purple-200 flex items-center justify-center">
                            <span class="text-7xl">🫐</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Taro Milk Tea</h3>
                            <p class="mb-4 text-gray-600">Classic taro with fresh milk and pearls</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱130</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 5 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-orange-100 to-orange-200 flex items-center justify-center">
                            <span class="text-7xl">🍊</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Mango Smoothie</h3>
                            <p class="mb-4 text-gray-600">Tropical mango blended to perfection</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱150</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 6 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                            <span class="text-7xl">🧊</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Blueberry Lemonade</h3>
                            <p class="mb-4 text-gray-600">Fresh lemonade with blueberry burst</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱120</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Frappe Section -->
            <div class="mb-16" data-category-section="frappe">
                <h2 class="mb-8 text-3xl font-bold text-center text-gray-800 md:text-4xl">🥤 Frozen Frappes</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Item 1 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-amber-200 to-orange-300 flex items-center justify-center">
                            <span class="text-7xl">🥤</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Caramel Frappe</h3>
                            <p class="mb-4 text-gray-600">Frozen caramel coffee topped with whipped cream</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱160</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-pink-200 to-rose-300 flex items-center justify-center">
                            <span class="text-7xl">🥤</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Mocha Frappe</h3>
                            <p class="mb-4 text-gray-600">Rich chocolate coffee frappe with whipped cream</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱160</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 3 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-green-200 to-emerald-300 flex items-center justify-center">
                            <span class="text-7xl">🥤</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Matcha Frappe</h3>
                            <p class="mb-4 text-gray-600">Frozen matcha green tea with cream</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱170</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 4 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-yellow-200 to-amber-300 flex items-center justify-center">
                            <span class="text-7xl">🥤</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Vanilla Frappe</h3>
                            <p class="mb-4 text-gray-600">Classic vanilla frozen coffee delight</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱150</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Food & Snacks Section -->
            <div class="mb-16" data-category-section="food">
                <h2 class="mb-8 text-3xl font-bold text-center text-gray-800 md:text-4xl">🍰 Food & Snacks</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Item 1 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-yellow-100 to-amber-200 flex items-center justify-center">
                            <span class="text-7xl">🥐</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Butter Croissant</h3>
                            <p class="mb-4 text-gray-600">Freshly baked flaky croissant</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱85</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-orange-100 to-red-200 flex items-center justify-center">
                            <span class="text-7xl">🍰</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Chocolate Cake</h3>
                            <p class="mb-4 text-gray-600">Rich and moist chocolate layer cake</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱120</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 3 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-red-100 to-pink-200 flex items-center justify-center">
                            <span class="text-7xl">🍓</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Strawberry Cheesecake</h3>
                            <p class="mb-4 text-gray-600">Creamy cheesecake with fresh strawberries</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱130</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 4 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-amber-100 to-orange-200 flex items-center justify-center">
                            <span class="text-7xl">🥪</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Club Sandwich</h3>
                            <p class="mb-4 text-gray-600">Triple-decker with chicken, bacon, and veggies</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱180</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 5 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-green-100 to-lime-200 flex items-center justify-center">
                            <span class="text-7xl">🥗</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Caesar Salad</h3>
                            <p class="mb-4 text-gray-600">Fresh greens with caesar dressing and croutons</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱160</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Item 6 -->
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="h-48 bg-linear-to-br from-yellow-100 to-yellow-200 flex items-center justify-center">
                            <span class="text-7xl">🍪</span>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold">Chocolate Chip Cookies</h3>
                            <p class="mb-4 text-gray-600">Freshly baked cookies (3 pieces)</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Price: <span class="font-semibold text-amber-600">₱90</span></p>
                                </div>
                                <button class="px-4 py-2 text-sm font-semibold text-white transition rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-white bg-gray-900">
        <div class="container px-4 py-12 mx-auto">
            <div class="grid grid-cols-1 gap-8 mb-8 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="flex items-center mb-4">
                        <img src="../assets/images/logo.png" alt="Bro's Cafe Logo" class="w-12 h-12 rounded-full">
                        <h3 class="ml-3 text-2xl font-bold text-amber-500">Bro's Cafe</h3>
                    </div>
                    <p class="text-gray-400">Where every cup tells a story.</p>
                </div>
                <div>
                    <h3 class="mb-4 text-xl font-bold text-amber-500">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="home.php" class="text-gray-400 transition hover:text-amber-500">Home</a></li>
                        <li><a href="menu.php" class="text-gray-400 transition hover:text-amber-500">Menu</a></li>
                        <li><a href="about.php" class="text-gray-400 transition hover:text-amber-500">About</a></li>
                        <li><a href="contact.php" class="text-gray-400 transition hover:text-amber-500">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-xl font-bold text-amber-500">Visit Us</h3>
                    <p class="text-gray-400">123 Coffee Street<br>Cafe District, City</p>
                </div>
                <div>
                    <h3 class="mb-4 text-xl font-bold text-amber-500">Contact</h3>
                    <p class="text-gray-400">+63 123 456 7890<br>info@broscafe.com</p>
                </div>
            </div>
            <div class="pt-8 text-center border-t border-gray-800">
                <p class="text-gray-400">&copy; 2025 Bro's Cafe. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Category filter
        const categoryBtns = document.querySelectorAll('.category-btn');
        const categorySections = document.querySelectorAll('[data-category-section]');

        categoryBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const category = btn.getAttribute('data-category');

                // Update active button
                categoryBtns.forEach(b => {
                    b.classList.remove('active', 'bg-amber-500', 'text-white');
                    b.classList.add('bg-gray-200', 'text-gray-700');
                });
                btn.classList.add('active', 'bg-amber-500', 'text-white');
                btn.classList.remove('bg-gray-200', 'text-gray-700');

                // Show/hide sections
                if (category === 'all') {
                    categorySections.forEach(section => {
                        section.style.display = 'block';
                    });
                } else {
                    categorySections.forEach(section => {
                        if (section.getAttribute('data-category-section') === category) {
                            section.style.display = 'block';
                        } else {
                            section.style.display = 'none';
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>