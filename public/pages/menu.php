<?php
require_once '../../config/database.php';
require_once '../../src/services/functions.php';

$db = new Database();
$conn = $db->getConnection();

// Get all categories
$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Get all products with category information
$stmt = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.status = 'available'
    ORDER BY c.name, p.name
");
$products = $stmt->fetchAll();

// Group products by category
$productsByCategory = [];
foreach ($products as $product) {
    $categoryName = $product['category_name'] ?? 'Uncategorized';
    if (!isset($productsByCategory[$categoryName])) {
        $productsByCategory[$categoryName] = [];
    }
    $productsByCategory[$categoryName][] = $product;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Bros Cafe</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-100 font-['Montserrat']">
    <!-- Navigation Bar -->
    <nav class="sticky top-0 z-50 flex items-center h-16 shadow-md bg-gray-50/80 backdrop-blur-md">
        <div class="container flex items-center justify-between px-4 mx-auto">
            <a href="../../index.php" class="flex items-center">
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
                    <a href="../../index.php"
                        class="px-4 py-2 transition-all bg-transparent rounded-lg hover:bg-white hover:shadow-md">Home</a>
                </li>
                <li class="transition ease-out hover:-translate-y-0.5">
                    <a href="menu.php"
                        class="px-4 py-2 text-white transition-all rounded-lg bg-amber-500 hover:bg-amber-600 hover:shadow-md">Menu</a>
                </li>
                <li class="transition ease-out hover:-translate-y-0.5">
                    <a href="about.php"
                        class="px-4 py-2 transition-all bg-transparent rounded-lg hover:bg-white hover:shadow-md">About</a>
                </li>
                <li class="transition ease-out hover:-translate-y-0.5">
                    <a href="contact.php"
                        class="px-4 py-2 transition-all bg-transparent rounded-lg hover:bg-white hover:shadow-md">Contact</a>
                </li>
            </ul>

            <div class="hidden space-x-4 font-medium lg:flex lg:items-center">
                <a href="login.php" class="transition ease-out hover:-translate-x-0.5">Log in</a>
                <a href="signup.php"
                    class="px-4 py-2 text-white transition-all rounded-lg bg-amber-500 hover:bg-amber-600 hover:shadow-md">Sign
                    up</a>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="absolute left-0 right-0 hidden bg-white shadow-lg top-16 lg:hidden">
            <ul class="flex flex-col py-4 space-y-2">
                <li><a href="../../index.php" class="block px-6 py-2 transition hover:bg-amber-50">Home</a></li>
                <li><a href="menu.php" class="block px-6 py-2 transition bg-amber-50 text-amber-600">Menu</a></li>
                <li><a href="about.php" class="block px-6 py-2 transition hover:bg-amber-50">About</a></li>
                <li><a href="contact.php" class="block px-6 py-2 transition hover:bg-amber-50">Contact</a></li>
                <!--
                <li class="px-6 pt-4 border-t"><a href="login.php"
                        class="block py-2 transition hover:text-amber-600">Log in</a></li>
                <li class="px-6"><a href="signup.php"
                        class="block px-4 py-2 text-center text-white transition-all rounded-lg bg-amber-500 hover:bg-amber-600">Sign
                        up</a></li>-->
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
                <button
                    class="px-6 py-2 font-semibold text-white transition-all rounded-full category-btn active bg-amber-500 hover:bg-amber-600"
                    data-category="all">
                    All Items
                </button>
                <?php foreach ($categories as $category): ?>
                <button
                    class="px-6 py-2 font-semibold text-gray-700 transition-all bg-gray-200 rounded-full category-btn hover:bg-amber-500 hover:text-white"
                    data-category="<?php echo strtolower(str_replace(' ', '-', $category['name'])); ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Menu Items -->
    <section class="py-16">
        <div class="container px-4 mx-auto">
            <?php foreach ($productsByCategory as $categoryName => $categoryProducts): ?>
            <!-- <?php echo htmlspecialchars($categoryName); ?> Section -->
            <div class="mb-16" data-category-section="<?php echo strtolower(str_replace(' ', '-', $categoryName)); ?>">
                <h2 class="mb-8 text-3xl font-bold text-center text-gray-800 md:text-4xl">
                    <?php echo htmlspecialchars($categoryName); ?>
                </h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($categoryProducts as $product): ?>
                    <div class="overflow-hidden transition-all transform bg-white shadow-lg rounded-xl hover:scale-105">
                        <div class="flex items-center justify-center h-48 overflow-hidden bg-gray-100">
                            <?php if ($product['image']): ?>
                                <img src="get_image.php?id=<?php echo $product['id']; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="object-cover w-full h-full">
                            <?php else: ?>
                                <span class="text-7xl">☕</span>
                            <?php endif; ?>
                        </div>
                        <div class="p-6">
                            <h3 class="mb-2 text-xl font-bold"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="mb-4 text-gray-600"><?php echo htmlspecialchars($product['description'] ?? 'Delicious item from our menu'); ?></p>
                            <div class="flex items-center justify-between">
                                <div class="w-full">
                                    <?php if ($product['price_dodici']): ?>
                                    <p class="text-sm text-gray-500">Dodici: <span
                                            class="font-semibold text-amber-600"><?php echo formatCurrency($product['price_dodici']); ?></span></p>
                                    <?php endif; ?>
                                    <?php if ($product['price_sedici']): ?>
                                    <p class="text-sm text-gray-500">Sedici: <span
                                            class="font-semibold text-amber-600"><?php echo formatCurrency($product['price_sedici']); ?></span></p>
                                    <?php endif; ?>
                                    <?php if (!$product['price_dodici'] && !$product['price_sedici']): ?>
                                    <p class="text-sm text-gray-500">Price: <span
                                            class="font-semibold text-amber-600">Contact us</span></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
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
                        <li><a href="../../index.php" class="text-gray-400 transition hover:text-amber-500">Home</a>
                        </li>
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