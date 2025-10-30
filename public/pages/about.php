<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Bros Cafe</title>
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
                    <a href="menu.php" class="px-4 py-2 transition-all bg-transparent rounded-lg hover:bg-white hover:shadow-md">Menu</a>
                </li>
                <li class="transition ease-out hover:-translate-y-0.5">
                    <a href="about.php" class="px-4 py-2 text-white transition-all rounded-lg bg-amber-500 hover:bg-amber-600 hover:shadow-md">About</a>
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
                <li><a href="menu.php" class="block px-6 py-2 transition hover:bg-amber-50">Menu</a></li>
                <li><a href="about.php" class="block px-6 py-2 transition bg-amber-50 text-amber-600">About</a></li>
                <li><a href="contact.php" class="block px-6 py-2 transition hover:bg-amber-50">Contact</a></li>
                <li class="px-6 pt-4 border-t"><a href="login.php" class="block py-2 transition hover:text-amber-600">Log in</a></li>
                <li class="px-6"><a href="signup.php" class="block px-4 py-2 text-center text-white transition-all rounded-lg bg-amber-500 hover:bg-amber-600">Sign up</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20 text-white bg-linear-to-br from-amber-600 via-orange-500 to-amber-600">
        <div class="container px-4 mx-auto text-center">
            <h1 class="mb-4 text-4xl font-black md:text-6xl">About Bro's Cafe</h1>
            <p class="text-xl md:text-2xl">Our Story, Mission & Values</p>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="py-16 bg-white">
        <div class="container px-4 mx-auto">
            <div class="flex flex-col items-center gap-12 lg:flex-row">
                <div class="lg:w-1/2">
                    <div class="overflow-hidden shadow-2xl rounded-2xl">
                        <div class="flex items-center justify-center bg-linear-to-br from-amber-100 to-orange-200 h-96">
                            <span class="text-9xl">☕</span>
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <h2 class="mb-6 text-4xl font-bold text-gray-800">Our Story</h2>
                    <div class="space-y-4 text-lg text-gray-600">
                        <p>
                            Founded in 2020, Bro's Cafe started with a simple dream: to create a warm, welcoming space where friends could gather over exceptional coffee. What began as a small neighborhood cafe has grown into a beloved community hub.
                        </p>
                        <p>
                            Our founders, three childhood friends who shared a passion for coffee, traveled across the world to learn from master baristas and discover unique blends. They brought back not just coffee knowledge, but a philosophy: every cup should tell a story.
                        </p>
                        <p>
                            Today, we're proud to serve our community with handcrafted beverages made from ethically-sourced beans, locally-sourced ingredients, and a whole lot of love. We believe that great coffee is more than just a drink—it's an experience, a conversation starter, and a moment of joy in your day.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Values Section -->
    <section class="py-16 bg-linear-to-br from-gray-50 to-amber-50">
        <div class="container px-4 mx-auto">
            <div class="mb-12 text-center">
                <h2 class="mb-4 text-4xl font-bold text-gray-800">Our Mission & Values</h2>
                <p class="text-xl text-gray-600">What drives us every single day</p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Value 1 -->
                <div class="p-8 text-center transition-all transform bg-white shadow-lg rounded-2xl hover:scale-105">
                    <div class="inline-flex items-center justify-center w-20 h-20 mb-6 rounded-full bg-amber-100">
                        <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-800">Quality First</h3>
                    <p class="text-gray-600">We never compromise on the quality of our ingredients. Every bean is carefully selected, every recipe perfected.</p>
                </div>

                <!-- Value 2 -->
                <div class="p-8 text-center transition-all transform bg-white shadow-lg rounded-2xl hover:scale-105">
                    <div class="inline-flex items-center justify-center w-20 h-20 mb-6 bg-green-100 rounded-full">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-800">Sustainability</h3>
                    <p class="text-gray-600">We're committed to ethical sourcing, eco-friendly practices, and supporting fair trade coffee farmers worldwide.</p>
                </div>

                <!-- Value 3 -->
                <div class="p-8 text-center transition-all transform bg-white shadow-lg rounded-2xl hover:scale-105">
                    <div class="inline-flex items-center justify-center w-20 h-20 mb-6 bg-blue-100 rounded-full">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-800">Community</h3>
                    <p class="text-gray-600">We're more than a cafe—we're a gathering place. Building connections and fostering friendships is at our core.</p>
                </div>

                <!-- Value 4 -->
                <div class="p-8 text-center transition-all transform bg-white shadow-lg rounded-2xl hover:scale-105">
                    <div class="inline-flex items-center justify-center w-20 h-20 mb-6 bg-purple-100 rounded-full">
                        <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-800">Innovation</h3>
                    <p class="text-gray-600">We constantly experiment with new flavors, brewing methods, and recipes to bring you exciting beverages.</p>
                </div>

                <!-- Value 5 -->
                <div class="p-8 text-center transition-all transform bg-white shadow-lg rounded-2xl hover:scale-105">
                    <div class="inline-flex items-center justify-center w-20 h-20 mb-6 bg-yellow-100 rounded-full">
                        <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-800">Hospitality</h3>
                    <p class="text-gray-600">Every guest is family. We strive to make your experience memorable with warm service and genuine care.</p>
                </div>

                <!-- Value 6 -->
                <div class="p-8 text-center transition-all transform bg-white shadow-lg rounded-2xl hover:scale-105">
                    <div class="inline-flex items-center justify-center w-20 h-20 mb-6 bg-red-100 rounded-full">
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-800">Consistency</h3>
                    <p class="text-gray-600">Whether it's your first visit or your hundredth, you can count on the same great taste and service every time.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-16 bg-white">
        <div class="container px-4 mx-auto">
            <div class="mb-12 text-center">
                <h2 class="mb-4 text-4xl font-bold text-gray-800">Meet Our Team</h2>
                <p class="text-xl text-gray-600">The passionate people behind your perfect cup</p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                <!-- Team Member 1 -->
                <div class="text-center transition-all transform hover:scale-105">
                    <div class="mb-4 overflow-hidden rounded-2xl">
                        <div class="flex items-center justify-center h-64 bg-linear-to-br from-amber-200 to-orange-300">
                            <span class="text-6xl">👨‍💼</span>
                        </div>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-800">Michael Chen</h3>
                    <p class="mb-2 font-semibold text-amber-600">Founder & CEO</p>
                    <p class="text-sm text-gray-600">Coffee enthusiast with 15 years of experience in specialty coffee.</p>
                </div>

                <!-- Team Member 2 -->
                <div class="text-center transition-all transform hover:scale-105">
                    <div class="mb-4 overflow-hidden rounded-2xl">
                        <div class="flex items-center justify-center h-64 bg-linear-to-br from-blue-200 to-blue-300">
                            <span class="text-6xl">👨‍🍳</span>
                        </div>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-800">David Rodriguez</h3>
                    <p class="mb-2 font-semibold text-amber-600">Head Barista</p>
                    <p class="text-sm text-gray-600">Award-winning barista and latte art champion.</p>
                </div>

                <!-- Team Member 3 -->
                <div class="text-center transition-all transform hover:scale-105">
                    <div class="mb-4 overflow-hidden rounded-2xl">
                        <div class="flex items-center justify-center h-64 bg-linear-to-br from-pink-200 to-rose-300">
                            <span class="text-6xl">👩‍💼</span>
                        </div>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-800">Sarah Johnson</h3>
                    <p class="mb-2 font-semibold text-amber-600">Operations Manager</p>
                    <p class="text-sm text-gray-600">Ensures smooth operations and exceptional customer experience.</p>
                </div>

                <!-- Team Member 4 -->
                <div class="text-center transition-all transform hover:scale-105">
                    <div class="mb-4 overflow-hidden rounded-2xl">
                        <div class="flex items-center justify-center h-64 bg-linear-to-br from-green-200 to-emerald-300">
                            <span class="text-6xl">👨‍🍳</span>
                        </div>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-800">James Park</h3>
                    <p class="mb-2 font-semibold text-amber-600">Pastry Chef</p>
                    <p class="text-sm text-gray-600">Creates delicious pastries and desserts to complement our coffee.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 text-white bg-linear-to-br from-amber-600 to-orange-600">
        <div class="container px-4 mx-auto">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                <div class="text-center">
                    <div class="mb-2 text-5xl font-black md:text-6xl">5+</div>
                    <p class="text-xl">Years Serving</p>
                </div>
                <div class="text-center">
                    <div class="mb-2 text-5xl font-black md:text-6xl">50K+</div>
                    <p class="text-xl">Happy Customers</p>
                </div>
                <div class="text-center">
                    <div class="mb-2 text-5xl font-black md:text-6xl">100+</div>
                    <p class="text-xl">Beverages Served Daily</p>
                </div>
                <div class="text-center">
                    <div class="mb-2 text-5xl font-black md:text-6xl">4.9★</div>
                    <p class="text-xl">Average Rating</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-16 bg-gray-100">
        <div class="container px-4 mx-auto">
            <div class="mb-12 text-center">
                <h2 class="mb-4 text-4xl font-bold text-gray-800">What Our Customers Say</h2>
                <p class="text-xl text-gray-600">Real reviews from real coffee lovers</p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Testimonial 1 -->
                <div class="p-6 bg-white shadow-lg rounded-2xl">
                    <div class="flex mb-4 text-yellow-400">
                        <span>★★★★★</span>
                    </div>
                    <p class="mb-4 text-gray-600">"Best coffee in town! The atmosphere is cozy and the staff is always friendly. My go-to place for morning coffee and weekend hangouts."</p>
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-12 h-12 mr-3 text-2xl rounded-full bg-amber-100">😊</div>
                        <div>
                            <h4 class="font-bold text-gray-800">Emily Watson</h4>
                            <p class="text-sm text-gray-500">Regular Customer</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="p-6 bg-white shadow-lg rounded-2xl">
                    <div class="flex mb-4 text-yellow-400">
                        <span>★★★★★</span>
                    </div>
                    <p class="mb-4 text-gray-600">"The Spanish Latte is to die for! I've tried coffee shops all over the city, and Bro's Cafe consistently delivers the best quality."</p>
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-12 h-12 mr-3 text-2xl bg-blue-100 rounded-full">👍</div>
                        <div>
                            <h4 class="font-bold text-gray-800">Mark Thompson</h4>
                            <p class="text-sm text-gray-500">Coffee Enthusiast</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="p-6 bg-white shadow-lg rounded-2xl">
                    <div class="flex mb-4 text-yellow-400">
                        <span>★★★★★</span>
                    </div>
                    <p class="mb-4 text-gray-600">"Perfect place for studying or working. Fast WiFi, comfortable seating, and amazing coffee. The staff remembers my order!"</p>
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-12 h-12 mr-3 text-2xl bg-green-100 rounded-full">🎓</div>
                        <div>
                            <h4 class="font-bold text-gray-800">Lisa Chen</h4>
                            <p class="text-sm text-gray-500">Student</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-16 text-white bg-linear-to-br from-gray-800 to-gray-900">
        <div class="container px-4 mx-auto text-center">
            <h2 class="mb-4 text-4xl font-bold">Visit Us Today!</h2>
            <p class="mb-8 text-xl">Experience the Bro's Cafe difference for yourself</p>
            <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="menu.php" class="px-10 py-4 font-bold text-white transition-all transform rounded-lg shadow-lg bg-amber-600 hover:bg-amber-700 hover:scale-105">
                    View Our Menu
                </a>
                <a href="contact.php" class="px-10 py-4 font-bold transition-all transform border-2 border-white rounded-lg hover:bg-white hover:text-gray-800 hover:scale-105">
                    Get Directions
                </a>
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
    </script>
</body>

</html>