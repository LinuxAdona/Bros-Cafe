<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bros Cafe</title>
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="min-h-screen flex flex-col bg-gray-100 font-['Montserrat']">
        <!-- Navigation Bar -->
        <nav class="sticky top-0 z-50 flex items-center h-16 shadow-md bg-gray-50/80 backdrop-blur-md">
            <div class="container flex items-center justify-between mx-auto">
                <a href="home.php" class="flex items-center">
                    <img src="../assets/images/logo.png" alt="Bro's Cafe Logo" class="w-10 h-10 rounded-full">
                    <span class="ml-3 text-xl font-bold">BROS CAFE</span>
                </a>
                <ul class="flex space-x-2 font-medium">
                    <li class="transition ease-out hover:-translate-y-0.5"><a href="home.php"
                            class="px-4 py-2 text-white transition-all rounded-lg bg-amber-500 hover:bg-amber-600 hover:shadow-md">Home</a>
                    </li>
                    <li class="transition ease-out hover:-translate-y-0.5"><a href="menu.php"
                            class="px-4 py-2 transition-all rounded-lg bg-transparent hover:bg-white hover:shadow-md">Menu</a>
                    </li>
                    <li class="transition ease-out hover:-translate-y-0.5"><a href="about.php"
                            class="px-4 py-2 transition-all rounded-lg bg-transparent hover:bg-white hover:shadow-md">About</a>
                    </li>
                    <li class="transition ease-out hover:-translate-y-0.5"><a href="contact.php"
                            class="px-4 py-2 transition-all rounded-lg bg-transparent hover:bg-white hover:shadow-md">Contact</a>
                    </li>
                </ul>
                <div class="flex items-center space-x-4 font-medium">
                    <a href="login.php" class="transition ease-out hover:-translate-x-0.5">Log in</a>
                    <a href="signup.php"
                        class="px-4 py-2 text-white transition-all rounded-lg bg-amber-500 hover:bg-amber-600 hover:shadow-md">Sign
                        up</a>
                </div>
            </div>
        </nav>
        <!-- Hero Section -->
        <header class="relative flex-1 overflow-hidden bg-center bg-cover"
            style="background-image: url('../assets/images/hero-img.jpg');">
            <!-- Animated overlay with gradient -->
            <div
                class="absolute inset-0 bg-linear-to-br from-black/60 via-amber-900/40 to-black/60 backdrop-blur-[2px]">
            </div>

            <!-- Decorative floating elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute w-20 h-20 rounded-full top-20 left-10 bg-amber-500/10 blur-xl animate-pulse"></div>
                <div
                    class="absolute w-32 h-32 delay-1000 rounded-full top-40 right-20 bg-orange-500/10 blur-2xl animate-pulse">
                </div>
                <div
                    class="absolute w-24 h-24 delay-500 rounded-full bottom-20 left-1/4 bg-yellow-500/10 blur-xl animate-pulse">
                </div>
            </div>

            <!-- Content -->
            <div class="relative flex items-center justify-center" style="height: calc(100vh - 4rem);">
                <div class="max-w-5xl px-4 text-center">
                    <!-- Main Heading with gradient text -->
                    <h1
                        class="mb-6 text-5xl font-black text-transparent md:text-7xl lg:text-8xl bg-clip-text bg-linear-to-r from-amber-200 via-yellow-100 to-amber-200 drop-shadow-2xl animate-fade-in">
                        BROS CAFE
                    </h1>

                    <!-- Tagline -->
                    <p
                        class="mb-4 text-2xl font-bold text-white md:text-3xl lg:text-4xl drop-shadow-lg animate-slide-up">
                        Fuel Up Your Day With Good Coffee
                    </p>

                    <!-- Description -->
                    <p
                        class="max-w-3xl mx-auto mb-10 delay-200 text-md md:text-lg lg:text-xl text-amber-100/90 drop-shadow-md animate-slide-up">
                        Handcrafted beverages, cozy atmosphere, and unforgettable moments.<br />
                        Experience the perfect blend of taste, comfort, and community.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col items-center justify-center gap-4 delay-300 sm:flex-row animate-slide-up">
                        <a href="menu.php"
                            class="relative inline-flex items-center px-10 py-4 overflow-hidden font-bold text-white transition-all duration-300 transform rounded-full shadow-2xl group bg-linear-to-r from-amber-600 to-orange-600 hover:shadow-amber-500/50 hover:scale-110 hover:from-amber-500 hover:to-orange-500">
                            <span class="relative z-10 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Explore Our Menu
                            </span>
                            <div
                                class="absolute inset-0 transition-transform origin-left transform scale-x-0 bg-white/20 group-hover:scale-x-100">
                            </div>
                        </a>

                        <a href="about.php"
                            class="inline-flex items-center px-10 py-4 font-bold text-white transition-all duration-300 transform border-2 rounded-full shadow-lg border-white/80 hover:bg-white hover:text-amber-700 hover:scale-110 backdrop-blur-sm">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Our Story
                            </span>
                        </a>
                    </div>

                    <!-- Scroll indicator -->
                    <div class="absolute transform -translate-x-1/2 bottom-8 left-1/2 animate-bounce">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white/70" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </div>
                </div>
            </div>
        </header>
        <!-- Product Sections -->
        <section class="py-16 bg-gray-100">
            <div class="container px-4 mx-auto">
                <!-- Section Header -->
                <div class="mb-12 text-center">
                    <h2 class="mb-4 text-3xl font-bold text-gray-800 md:text-4xl lg:text-5xl">Our Coffee Favorites</h2>
                    <p class="text-lg text-gray-600 md:text-xl">Discover our signature coffee creations</p>
                </div>

                <!-- Product Grid -->
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Product Card 1: Sea Salt Latte -->
                    <div
                        class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105 hover:shadow-xl">
                        <div class="flex items-center justify-center h-48 bg-linear-to-br from-amber-100 to-amber-200">
                            <span class="text-6xl">☕</span>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xl font-bold text-gray-800">Sea Salt Latte</h3>
                                <span class="text-yellow-500">★</span>
                            </div>
                            <p class="mb-4 text-gray-600">Espresso and milk topped with sea salt cream</p>
                            <div class="flex items-center justify-between">
                                <div class="space-y-1">
                                    <p class="text-sm text-gray-500">Dodici: <span
                                            class="font-semibold text-amber-600">₱120</span></p>
                                    <p class="text-sm text-gray-500">Sedici: <span
                                            class="font-semibold text-amber-600">₱150</span></p>
                                </div>
                                <button
                                    class="px-4 py-2 text-sm font-semibold text-white transition-colors rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Order Now
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Product Card 2: Spanish Latte -->
                    <div
                        class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105 hover:shadow-xl">
                        <div
                            class="flex items-center justify-center h-48 bg-linear-to-br from-orange-100 to-orange-200">
                            <span class="text-6xl">☕</span>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xl font-bold text-gray-800">Spanish Latte</h3>
                                <span class="text-yellow-500">★</span>
                            </div>
                            <p class="mb-4 text-gray-600">Sweet twist on a classic iced cafe latte</p>
                            <div class="flex items-center justify-between">
                                <div class="space-y-1">
                                    <p class="text-sm text-gray-500">Dodici: <span
                                            class="font-semibold text-amber-600">₱120</span></p>
                                    <p class="text-sm text-gray-500">Sedici: <span
                                            class="font-semibold text-amber-600">₱140</span></p>
                                </div>
                                <button
                                    class="px-4 py-2 text-sm font-semibold text-white transition-colors rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Order Now
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Product Card 3: White Chocolate Mocha -->
                    <div
                        class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105 hover:shadow-xl">
                        <div class="flex items-center justify-center h-48 bg-linear-to-br from-pink-100 to-pink-200">
                            <span class="text-6xl">☕</span>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xl font-bold text-gray-800">White Chocolate Mocha</h3>
                                <span class="text-yellow-500">★</span>
                            </div>
                            <p class="mb-4 text-gray-600">Espresso, white chocolate sauce, milk and ice</p>
                            <div class="flex items-center justify-between">
                                <div class="space-y-1">
                                    <p class="text-sm text-gray-500">Dodici: <span
                                            class="font-semibold text-amber-600">₱120</span></p>
                                    <p class="text-sm text-gray-500">Sedici: <span
                                            class="font-semibold text-amber-600">₱140</span></p>
                                </div>
                                <button
                                    class="px-4 py-2 text-sm font-semibold text-white transition-colors rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Order Now
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Product Card 4: Caramel Macchiato -->
                    <div
                        class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105 hover:shadow-xl">
                        <div
                            class="flex items-center justify-center h-48 bg-linear-to-br from-yellow-100 to-yellow-200">
                            <span class="text-6xl">☕</span>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xl font-bold text-gray-800">Caramel Macchiato</h3>
                                <span class="text-yellow-500">★</span>
                            </div>
                            <p class="mb-4 text-gray-600">Espresso shot with vanilla, caramel sauce, milk and ice</p>
                            <div class="flex items-center justify-between">
                                <div class="space-y-1">
                                    <p class="text-sm text-gray-500">Dodici: <span
                                            class="font-semibold text-amber-600">₱130</span></p>
                                    <p class="text-sm text-gray-500">Sedici: <span
                                            class="font-semibold text-amber-600">₱150</span></p>
                                </div>
                                <button
                                    class="px-4 py-2 text-sm font-semibold text-white transition-colors rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Order Now
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Product Card 5: Matcha Latte -->
                    <div
                        class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105 hover:shadow-xl">
                        <div class="flex items-center justify-center h-48 bg-linear-to-br from-green-100 to-green-200">
                            <span class="text-6xl">☕</span>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xl font-bold text-gray-800">Matcha Latte</h3>
                                <span class="text-yellow-500">★</span>
                            </div>
                            <p class="mb-4 text-gray-600">Creamy matcha with outside milk</p>
                            <div class="flex items-center justify-between">
                                <div class="space-y-1">
                                    <p class="text-sm text-gray-500">Price: <span
                                            class="font-semibold text-amber-600">₱140</span></p>
                                </div>
                                <button
                                    class="px-4 py-2 text-sm font-semibold text-white transition-colors rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Order Now
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Product Card 6: Banana Pudding Matcha Latte -->
                    <div
                        class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105 hover:shadow-xl">
                        <div class="flex items-center justify-center h-48 bg-linear-to-br from-lime-100 to-lime-200">
                            <span class="text-6xl">☕</span>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xl font-bold text-gray-800">Banana Pudding Matcha</h3>
                                <span class="text-yellow-500">★</span>
                            </div>
                            <p class="mb-4 text-gray-600">Matcha latte topped with banana pudding</p>
                            <div class="flex items-center justify-between">
                                <div class="space-y-1">
                                    <p class="text-sm text-gray-500">Price: <span
                                            class="font-semibold text-amber-600">₱180</span></p>
                                </div>
                                <button
                                    class="px-4 py-2 text-sm font-semibold text-white transition-colors rounded-lg bg-amber-600 hover:bg-amber-700">
                                    Order Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- View Full Menu Button -->
                <div class="mt-12 text-center">
                    <a href="menu.php"
                        class="inline-block px-10 py-4 text-lg font-semibold text-white transition-all transform rounded-lg shadow-lg bg-amber-600 hover:bg-amber-700 hover:scale-105">
                        View Full Menu
                    </a>
                </div>
            </div>
        </section>

        <!-- Promos & Special Offers Section -->
        <section class="py-16 bg-white">
            <div class="container px-4 mx-auto">
                <!-- Section Header -->
                <div class="mb-12 text-center">
                    <h2 class="mb-4 text-3xl font-bold text-gray-800 md:text-4xl lg:text-5xl">Special Offers & Promos
                    </h2>
                    <p class="text-lg text-gray-600 md:text-xl">Don't miss out on our exclusive deals!</p>
                </div>

                <!-- Promos Grid -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Promo Card 1: Happy Hour -->
                    <div
                        class="relative overflow-hidden bg-linear-to-br from-amber-500 to-orange-600 rounded-2xl shadow-xl group">
                        <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-20 -mt-20"></div>
                        <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/10 rounded-full -ml-16 -mb-16"></div>
                        <div class="relative p-8 text-white">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <span
                                        class="inline-block px-3 py-1 mb-3 text-xs font-bold bg-white/20 rounded-full backdrop-blur-sm">LIMITED
                                        TIME</span>
                                    <h3 class="text-3xl font-bold mb-2">Happy Hour Special</h3>
                                    <p class="text-xl font-semibold text-amber-100">20% OFF All Beverages</p>
                                </div>
                                <div class="text-5xl">☕</div>
                            </div>
                            <p class="mb-4 text-white/90">Monday to Friday, 2PM - 5PM</p>
                            <div class="flex items-center justify-between pt-4 border-t border-white/20">
                                <span class="text-sm">Valid until: Dec 31, 2025</span>
                                <a href="menu.php"
                                    class="px-6 py-2 font-semibold text-amber-600 transition-all bg-white rounded-lg hover:bg-amber-50 hover:shadow-lg">
                                    Order Now
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Promo Card 2: Student Discount -->
                    <div
                        class="relative overflow-hidden bg-linear-to-br from-blue-500 to-purple-600 rounded-2xl shadow-xl group">
                        <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-20 -mt-20"></div>
                        <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/10 rounded-full -ml-16 -mb-16"></div>
                        <div class="relative p-8 text-white">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <span
                                        class="inline-block px-3 py-1 mb-3 text-xs font-bold bg-white/20 rounded-full backdrop-blur-sm">EVERYDAY</span>
                                    <h3 class="text-3xl font-bold mb-2">Student Discount</h3>
                                    <p class="text-xl font-semibold text-blue-100">15% OFF with Valid ID</p>
                                </div>
                                <div class="text-5xl">🎓</div>
                            </div>
                            <p class="mb-4 text-white/90">Show your student ID at checkout</p>
                            <div class="flex items-center justify-between pt-4 border-t border-white/20">
                                <span class="text-sm">All day, every day</span>
                                <a href="menu.php"
                                    class="px-6 py-2 font-semibold text-blue-600 transition-all bg-white rounded-lg hover:bg-blue-50 hover:shadow-lg">
                                    Learn More
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Promo Card 3: Buy 1 Get 1 -->
                    <div
                        class="relative overflow-hidden bg-linear-to-br from-green-500 to-teal-600 rounded-2xl shadow-xl group">
                        <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-20 -mt-20"></div>
                        <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/10 rounded-full -ml-16 -mb-16"></div>
                        <div class="relative p-8 text-white">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <span
                                        class="inline-block px-3 py-1 mb-3 text-xs font-bold bg-white/20 rounded-full backdrop-blur-sm">WEEKENDS
                                        ONLY</span>
                                    <h3 class="text-3xl font-bold mb-2">Buy 1 Get 1 Free</h3>
                                    <p class="text-xl font-semibold text-green-100">On Selected Drinks</p>
                                </div>
                                <div class="text-5xl">🎉</div>
                            </div>
                            <p class="mb-4 text-white/90">Every Saturday & Sunday</p>
                            <div class="flex items-center justify-between pt-4 border-t border-white/20">
                                <span class="text-sm">Check menu for eligible items</span>
                                <a href="menu.php"
                                    class="px-6 py-2 font-semibold text-green-600 transition-all bg-white rounded-lg hover:bg-green-50 hover:shadow-lg">
                                    View Menu
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Promo Card 4: Loyalty Rewards -->
                    <div
                        class="relative overflow-hidden bg-linear-to-br from-pink-500 to-rose-600 rounded-2xl shadow-xl group">
                        <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-20 -mt-20"></div>
                        <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/10 rounded-full -ml-16 -mb-16"></div>
                        <div class="relative p-8 text-white">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <span
                                        class="inline-block px-3 py-1 mb-3 text-xs font-bold bg-white/20 rounded-full backdrop-blur-sm">MEMBERS
                                        ONLY</span>
                                    <h3 class="text-3xl font-bold mb-2">Loyalty Rewards</h3>
                                    <p class="text-xl font-semibold text-pink-100">Earn Points Every Purchase</p>
                                </div>
                                <div class="text-5xl">⭐</div>
                            </div>
                            <p class="mb-4 text-white/90">1 Point = ₱1 | Redeem for free items</p>
                            <div class="flex items-center justify-between pt-4 border-t border-white/20">
                                <span class="text-sm">Sign up now, it's free!</span>
                                <a href="signup.php"
                                    class="px-6 py-2 font-semibold text-pink-600 transition-all bg-white rounded-lg hover:bg-pink-50 hover:shadow-lg">
                                    Join Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Announcements & Updates Section -->
        <section class="py-16 bg-gradient-to-br from-gray-50 to-amber-50">
            <div class="container px-4 mx-auto">
                <!-- Section Header -->
                <div class="mb-12 text-center">
                    <h2 class="mb-4 text-3xl font-bold text-gray-800 md:text-4xl lg:text-5xl">Latest Updates</h2>
                    <p class="text-lg text-gray-600 md:text-xl">Stay informed with what's happening at Bro's Cafe</p>
                </div>

                <!-- Updates Grid -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Update Card 1 -->
                    <div class="overflow-hidden transition-all bg-white shadow-lg rounded-xl hover:shadow-2xl group">
                        <div class="h-2 bg-linear-to-r from-amber-500 to-orange-500"></div>
                        <div class="p-6">
                            <div class="flex items-center mb-3 text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>October 28, 2025</span>
                            </div>
                            <h3 class="mb-3 text-xl font-bold text-gray-800">New Seasonal Menu Launch</h3>
                            <p class="mb-4 text-gray-600">Introducing our Fall Collection with 5 new signature drinks
                                featuring pumpkin spice, cinnamon, and caramel flavors!</p>
                            <a href="#"
                                class="inline-flex items-center font-semibold text-amber-600 hover:text-amber-700">
                                Read More
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Update Card 2 -->
                    <div class="overflow-hidden transition-all bg-white shadow-lg rounded-xl hover:shadow-2xl group">
                        <div class="h-2 bg-linear-to-r from-blue-500 to-purple-500"></div>
                        <div class="p-6">
                            <div class="flex items-center mb-3 text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>October 25, 2025</span>
                            </div>
                            <h3 class="mb-3 text-xl font-bold text-gray-800">Extended Hours This Weekend</h3>
                            <p class="mb-4 text-gray-600">We're staying open until midnight on Saturday & Sunday to
                                serve you better. Perfect for late-night study sessions!</p>
                            <a href="#"
                                class="inline-flex items-center font-semibold text-amber-600 hover:text-amber-700">
                                Read More
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Update Card 3 -->
                    <div class="overflow-hidden transition-all bg-white shadow-lg rounded-xl hover:shadow-2xl group">
                        <div class="h-2 bg-linear-to-r from-green-500 to-teal-500"></div>
                        <div class="p-6">
                            <div class="flex items-center mb-3 text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>October 20, 2025</span>
                            </div>
                            <h3 class="mb-3 text-xl font-bold text-gray-800">Mobile Ordering Now Available</h3>
                            <p class="mb-4 text-gray-600">Skip the line! Order ahead through our new mobile app and pick
                                up at your convenience. Download now on iOS & Android.</p>
                            <a href="#"
                                class="inline-flex items-center font-semibold text-amber-600 hover:text-amber-700">
                                Read More
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Newsletter Signup -->
                <div class="max-w-3xl p-8 mx-auto mt-12 bg-white shadow-xl rounded-2xl">
                    <div class="text-center">
                        <h3 class="mb-3 text-2xl font-bold text-gray-800">Stay Updated!</h3>
                        <p class="mb-6 text-gray-600">Subscribe to our newsletter for exclusive offers, new menu items,
                            and upcoming events.</p>
                        <form class="flex flex-col gap-3 sm:flex-row">
                            <input type="email" placeholder="Enter your email address"
                                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500"
                                required>
                            <button type="submit"
                                class="px-8 py-3 font-semibold text-white transition-all rounded-lg bg-amber-600 hover:bg-amber-700 hover:shadow-lg">
                                Subscribe
                            </button>
                        </form>
                        <p class="mt-3 text-xs text-gray-500">We respect your privacy. Unsubscribe anytime.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer Section -->
        <footer class="bg-gray-900 text-white">
            <div class="container mx-auto px-4 py-12">
                <!-- Main Footer Content -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
                    <!-- About Section -->
                    <div>
                        <div class="flex items-center mb-4">
                            <img src="../assets/images/logo.png" alt="Bro's Cafe Logo" class="w-12 h-12 rounded-full">
                            <h3 class="ml-3 text-2xl font-bold text-amber-500">Bro's Cafe</h3>
                        </div>
                        <p class="text-gray-400 mb-4">
                            Where every cup tells a story. Handcrafted beverages, cozy atmosphere, and unforgettable
                            moments.
                        </p>
                        <!-- Social Media Icons -->
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-amber-500 transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-amber-500 transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-amber-500 transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-xl font-bold mb-4 text-amber-500">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="home.php" class="text-gray-400 hover:text-amber-500 transition-colors">Home</a>
                            </li>
                            <li><a href="menu.php" class="text-gray-400 hover:text-amber-500 transition-colors">Menu</a>
                            </li>
                            <li><a href="about.php" class="text-gray-400 hover:text-amber-500 transition-colors">About
                                    Us</a></li>
                            <li><a href="contact.php"
                                    class="text-gray-400 hover:text-amber-500 transition-colors">Contact</a></li>
                            <li><a href="signin.php" class="text-gray-400 hover:text-amber-500 transition-colors">Sign
                                    In</a></li>
                        </ul>
                    </div>

                    <!-- Hours & Location -->
                    <div>
                        <h3 class="text-xl font-bold mb-4 text-amber-500">Visit Us</h3>
                        <ul class="space-y-3 text-gray-400">
                            <li class="flex items-start">
                                <svg class="w-6 h-6 mr-2 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>123 Coffee Street, Cafe District, City</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-6 h-6 mr-2 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p>Mon - Fri: 7:00 AM - 9:00 PM</p>
                                    <p>Sat - Sun: 8:00 AM - 10:00 PM</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Contact Info -->
                    <div>
                        <h3 class="text-xl font-bold mb-4 text-amber-500">Contact Us</h3>
                        <ul class="space-y-3 text-gray-400">
                            <li class="flex items-center">
                                <svg class="w-6 h-6 mr-2 text-amber-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span>+63 123 456 7890</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-6 h-6 mr-2 text-amber-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span>info@broscafe.com</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-6 h-6 mr-2 text-amber-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                <span>www.broscafe.com</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-800 my-8"></div>

                <!-- Bottom Footer -->
                <div class="flex flex-col md:flex-row justify-between items-center text-gray-400 text-sm">
                    <p>&copy; 2025 Bro's Cafe. All rights reserved.</p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="hover:text-amber-500 transition-colors">Privacy Policy</a>
                        <a href="#" class="hover:text-amber-500 transition-colors">Terms of Service</a>
                        <a href="#" class="hover:text-amber-500 transition-colors">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </footer>

    </div>
</body>

</html>