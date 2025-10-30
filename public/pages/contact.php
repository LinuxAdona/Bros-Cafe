<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Bros Cafe</title>
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
                    <a href="about.php" class="px-4 py-2 transition-all bg-transparent rounded-lg hover:bg-white hover:shadow-md">About</a>
                </li>
                <li class="transition ease-out hover:-translate-y-0.5">
                    <a href="contact.php" class="px-4 py-2 text-white transition-all rounded-lg bg-amber-500 hover:bg-amber-600 hover:shadow-md">Contact</a>
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
                <li><a href="about.php" class="block px-6 py-2 transition hover:bg-amber-50">About</a></li>
                <li><a href="contact.php" class="block px-6 py-2 transition bg-amber-50 text-amber-600">Contact</a></li>
                <li class="px-6 pt-4 border-t"><a href="login.php" class="block py-2 transition hover:text-amber-600">Log in</a></li>
                <li class="px-6"><a href="signup.php" class="block px-4 py-2 text-center text-white transition-all rounded-lg bg-amber-500 hover:bg-amber-600">Sign up</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20 text-white bg-linear-to-br from-amber-600 via-orange-500 to-amber-600">
        <div class="container px-4 mx-auto text-center">
            <h1 class="mb-4 text-4xl font-black md:text-6xl">Get In Touch</h1>
            <p class="text-xl md:text-2xl">We'd love to hear from you!</p>
        </div>
    </section>

    <!-- Contact Information Cards -->
    <section class="py-16 bg-white">
        <div class="container px-4 mx-auto">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                <!-- Location Card -->
                <div class="p-6 text-center transition-all transform bg-white shadow-lg rounded-2xl hover:scale-105">
                    <div class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-amber-100">
                        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-800">Our Location</h3>
                    <p class="text-gray-600">123 Coffee Street<br>Cafe District<br>City, State 12345</p>
                </div>

                <!-- Phone Card -->
                <div class="p-6 text-center transition-all transform bg-white shadow-lg rounded-2xl hover:scale-105">
                    <div class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-blue-100">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-800">Call Us</h3>
                    <p class="text-gray-600">+63 123 456 7890<br>+63 987 654 3210<br>Mon-Sun: 7AM-10PM</p>
                </div>

                <!-- Email Card -->
                <div class="p-6 text-center transition-all transform bg-white shadow-lg rounded-2xl hover:scale-105">
                    <div class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-green-100">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-800">Email Us</h3>
                    <p class="text-gray-600">info@broscafe.com<br>support@broscafe.com<br>Response within 24hrs</p>
                </div>

                <!-- Social Card -->
                <div class="p-6 text-center transition-all transform bg-white shadow-lg rounded-2xl hover:scale-105">
                    <div class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-purple-100">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-800">Follow Us</h3>
                    <div class="flex justify-center gap-3 mt-4">
                        <a href="#" class="text-gray-600 transition hover:text-amber-600">
                            <i class="text-2xl fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-gray-600 transition hover:text-amber-600">
                            <i class="text-2xl fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-600 transition hover:text-amber-600">
                            <i class="text-2xl fab fa-twitter"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form & Map Section -->
    <section class="py-16 bg-gray-100">
        <div class="container px-4 mx-auto">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">
                <!-- Contact Form -->
                <div class="p-8 bg-white shadow-xl rounded-2xl">
                    <h2 class="mb-6 text-3xl font-bold text-gray-800">Send Us a Message</h2>
                    <form id="contact-form" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="first-name" class="block mb-2 font-semibold text-gray-700">First Name</label>
                                <input type="text" id="first-name" name="first-name" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                    placeholder="John">
                            </div>
                            <div>
                                <label for="last-name" class="block mb-2 font-semibold text-gray-700">Last Name</label>
                                <input type="text" id="last-name" name="last-name" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                    placeholder="Doe">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block mb-2 font-semibold text-gray-700">Email Address</label>
                            <input type="email" id="email" name="email" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                placeholder="john.doe@example.com">
                        </div>

                        <div>
                            <label for="phone" class="block mb-2 font-semibold text-gray-700">Phone Number</label>
                            <input type="tel" id="phone" name="phone"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                placeholder="+63 123 456 7890">
                        </div>

                        <div>
                            <label for="subject" class="block mb-2 font-semibold text-gray-700">Subject</label>
                            <select id="subject" name="subject" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                <option value="">Select a subject</option>
                                <option value="general">General Inquiry</option>
                                <option value="feedback">Feedback</option>
                                <option value="complaint">Complaint</option>
                                <option value="catering">Catering Services</option>
                                <option value="partnership">Partnership Opportunity</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="message" class="block mb-2 font-semibold text-gray-700">Message</label>
                            <textarea id="message" name="message" rows="5" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                placeholder="Tell us how we can help you..."></textarea>
                        </div>

                        <button type="submit"
                            class="w-full px-8 py-4 font-bold text-white transition-all transform rounded-lg shadow-lg bg-amber-600 hover:bg-amber-700 hover:scale-105">
                            Send Message
                        </button>
                    </form>

                    <!-- Success Message -->
                    <div id="success-message" class="hidden p-4 mt-6 text-green-800 bg-green-100 border border-green-300 rounded-lg">
                        <p class="font-semibold">✓ Message sent successfully!</p>
                        <p class="text-sm">We'll get back to you within 24 hours.</p>
                    </div>
                </div>

                <!-- Map & Business Hours -->
                <div class="space-y-6">
                    <!-- Map -->
                    <div class="overflow-hidden bg-white shadow-xl rounded-2xl">
                        <div class="h-64 bg-linear-to-br from-amber-100 to-orange-200">
                            <!-- Placeholder for map - replace with actual map API -->
                            <div class="flex items-center justify-center h-full">
                                <div class="text-center">
                                    <svg class="w-16 h-16 mx-auto mb-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                    <p class="text-gray-600">Interactive Map</p>
                                    <p class="text-sm text-gray-500">123 Coffee Street, Cafe District</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <a href="https://maps.google.com" target="_blank"
                                class="flex items-center justify-center w-full px-6 py-3 font-semibold transition-all border-2 rounded-lg border-amber-600 text-amber-600 hover:bg-amber-600 hover:text-white">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Open in Google Maps
                            </a>
                        </div>
                    </div>

                    <!-- Business Hours -->
                    <div class="p-8 bg-white shadow-xl rounded-2xl">
                        <h3 class="mb-6 text-2xl font-bold text-gray-800">Business Hours</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between pb-3 border-b border-gray-200">
                                <span class="font-semibold text-gray-700">Monday - Friday</span>
                                <span class="text-amber-600">7:00 AM - 9:00 PM</span>
                            </div>
                            <div class="flex justify-between pb-3 border-b border-gray-200">
                                <span class="font-semibold text-gray-700">Saturday</span>
                                <span class="text-amber-600">8:00 AM - 10:00 PM</span>
                            </div>
                            <div class="flex justify-between pb-3 border-b border-gray-200">
                                <span class="font-semibold text-gray-700">Sunday</span>
                                <span class="text-amber-600">8:00 AM - 10:00 PM</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-semibold text-gray-700">Holidays</span>
                                <span class="text-amber-600">9:00 AM - 8:00 PM</span>
                            </div>
                        </div>
                        <div class="p-4 mt-6 border-2 rounded-lg bg-amber-50 border-amber-200">
                            <p class="flex items-center text-sm text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                We're currently <span class="ml-1 font-semibold text-green-600">OPEN</span>
                            </p>
                        </div>
                    </div>

                    <!-- FAQ Link -->
                    <div class="p-6 text-center text-white shadow-xl bg-linear-to-br from-amber-600 to-orange-600 rounded-2xl">
                        <h3 class="mb-2 text-xl font-bold">Have Questions?</h3>
                        <p class="mb-4">Check out our FAQ section for quick answers</p>
                        <a href="#faq" class="inline-block px-6 py-3 font-semibold transition-all bg-white rounded-lg text-amber-600 hover:bg-amber-50">
                            View FAQs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-16 bg-white">
        <div class="container px-4 mx-auto">
            <div class="mb-12 text-center">
                <h2 class="mb-4 text-4xl font-bold text-gray-800">Frequently Asked Questions</h2>
                <p class="text-xl text-gray-600">Find answers to common questions</p>
            </div>

            <div class="max-w-3xl mx-auto space-y-4">
                <!-- FAQ Item 1 -->
                <div class="overflow-hidden bg-gray-50 rounded-xl">
                    <button class="flex items-center justify-between w-full p-6 text-left faq-btn">
                        <span class="text-lg font-semibold text-gray-800">Do you offer WiFi for customers?</span>
                        <svg class="w-6 h-6 transition-transform text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="hidden p-6 pt-0 faq-content">
                        <p class="text-gray-600">Yes! We offer free high-speed WiFi to all our customers. The password is available on request or displayed at the counter.</p>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="overflow-hidden bg-gray-50 rounded-xl">
                    <button class="flex items-center justify-between w-full p-6 text-left faq-btn">
                        <span class="text-lg font-semibold text-gray-800">Do you have vegan or dairy-free options?</span>
                        <svg class="w-6 h-6 transition-transform text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="hidden p-6 pt-0 faq-content">
                        <p class="text-gray-600">Absolutely! We offer oat milk, almond milk, and soy milk as alternatives. Many of our pastries and snacks are also vegan-friendly.</p>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="overflow-hidden bg-gray-50 rounded-xl">
                    <button class="flex items-center justify-between w-full p-6 text-left faq-btn">
                        <span class="text-lg font-semibold text-gray-800">Can I book the cafe for private events?</span>
                        <svg class="w-6 h-6 transition-transform text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="hidden p-6 pt-0 faq-content">
                        <p class="text-gray-600">Yes! We offer private event bookings for groups of 20 or more. Please contact us at least 2 weeks in advance to discuss your requirements.</p>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="overflow-hidden bg-gray-50 rounded-xl">
                    <button class="flex items-center justify-between w-full p-6 text-left faq-btn">
                        <span class="text-lg font-semibold text-gray-800">Do you offer delivery services?</span>
                        <svg class="w-6 h-6 transition-transform text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="hidden p-6 pt-0 faq-content">
                        <p class="text-gray-600">Yes! We partner with major delivery platforms including GrabFood, FoodPanda, and our own mobile app for delivery within a 5km radius.</p>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="overflow-hidden bg-gray-50 rounded-xl">
                    <button class="flex items-center justify-between w-full p-6 text-left faq-btn">
                        <span class="text-lg font-semibold text-gray-800">What payment methods do you accept?</span>
                        <svg class="w-6 h-6 transition-transform text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="hidden p-6 pt-0 faq-content">
                        <p class="text-gray-600">We accept cash, all major credit/debit cards, GCash, PayMaya, and contactless payments. We're a cashless-friendly establishment!</p>
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

        // Contact form submission
        const contactForm = document.getElementById('contact-form');
        const successMessage = document.getElementById('success-message');

        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            // Here you would normally send the form data to your server
            successMessage.classList.remove('hidden');
            contactForm.reset();

            // Hide success message after 5 seconds
            setTimeout(() => {
                successMessage.classList.add('hidden');
            }, 5000);
        });

        // FAQ Accordion
        const faqBtns = document.querySelectorAll('.faq-btn');

        faqBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const content = btn.nextElementSibling;
                const icon = btn.querySelector('svg');

                // Toggle current FAQ
                content.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
            });
        });
    </script>
</body>

</html>