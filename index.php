<?php
session_start();
require_once 'db.php';

//Test
// Hole die neuesten Produkte
$stmt = $pdo->query("SELECT * FROM products WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 6");
$latestProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hole Kategorien
$stmt = $pdo->query("SELECT * FROM categories LIMIT 4");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Zufälliger Slogan
$slogans = [
    "Zeig deine Schulliebe mit Stil!",
    "Trag deine Schule mit Stolz!",
    "Dein Schulspirit, dein Style!",
    "Schulmerchandise: Mehr als nur Kleidung!",
    "Einzigartig wie deine Schule!"
];
$randomSlogan = $slogans[array_rand($slogans)];

// Warenkorb-Zähler
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;

// Willkommensbanner
$showWelcome = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && !isset($_SESSION['welcome_shown']);
if ($showWelcome) {
    $_SESSION['welcome_shown'] = true;
}
?>

<!DOCTYPE html>
<html lang="de" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schul-Merchandise Shop - Dein Style, Deine Schule</title>
    <link href="/css/output.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/ScrollTrigger.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        .float { animation: float 6s ease-in-out infinite; }
        .parallax { transition: transform 0.2s cubic-bezier(0,0,0.2,1); }
        .scroll-section { scroll-snap-align: start; }
    </style>
</head>
<body class="h-full overflow-x-hidden bg-gray-100">
    <?php include 'navbar.php'; ?>

    <div class="relative h-screen overflow-hidden">
        <video autoplay loop muted class="absolute z-0 w-auto min-w-full min-h-full max-w-none">
            <source src="src/video-nike.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-black opacity-50 z-10"></div>
        <div class="relative z-20 flex items-center justify-center h-full text-white text-center px-4">
            <div>
                <h1 class="text-4xl md:text-6xl font-bold mb-4 float"><?php echo $randomSlogan; ?></h1>
                <p class="text-xl md:text-2xl mb-8">Entdecke unsere einzigartige Kollektion</p>
                <a href="#products" class="bg-white text-black px-8 py-3 rounded-full text-lg font-medium hover:bg-opacity-90 transition duration-300 inline-block">Jetzt shoppen</a>
            </div>
        </div>
    </div>

    <main class="container mx-auto px-4 py-12">
        <section id="categories" class="mb-24 scroll-section">
            <h2 class="text-3xl font-bold text-center mb-12">Unsere Kategorien</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($categories as $category): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition duration-300 parallax" data-depth="0.2">
                        <img src="images/<?php echo htmlspecialchars($category['image']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <a href="shop.php?category=<?php echo $category['id']; ?>" class="text-indigo-600 hover:text-indigo-800">Entdecken →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="products" class="mb-24 scroll-section">
            <h2 class="text-3xl font-bold text-center mb-12">Neueste Produkte</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($latestProducts as $product): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition duration-300 parallax" data-depth="0.1">
                        <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-64 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold">€<?php echo number_format($product['price'], 2); ?></span>
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-full hover:bg-indigo-700 transition duration-300">Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="features" class="scroll-section">
            <h2 class="text-3xl font-bold text-center mb-12">Warum unser Merchandise?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-indigo-600 text-white p-8 rounded-lg shadow-lg transform hover:scale-105 transition duration-300 parallax" data-depth="0.3">
                    <h3 class="text-2xl font-semibold mb-4">Qualität</h3>
                    <p>Hochwertige Materialien und sorgfältige Verarbeitung für langlebige Produkte.</p>
                </div>
                <div class="bg-indigo-700 text-white p-8 rounded-lg shadow-lg transform hover:scale-105 transition duration-300 parallax" data-depth="0.3">
                    <h3 class="text-2xl font-semibold mb-4">Design</h3>
                    <p>Einzigartige Designs, die den Geist deiner Schule perfekt einfangen.</p>
                </div>
                <div class="bg-indigo-800 text-white p-8 rounded-lg shadow-lg transform hover:scale-105 transition duration-300 parallax" data-depth="0.3">
                    <h3 class="text-2xl font-semibold mb-4">Gemeinschaft</h3>
                    <p>Stärke das Gemeinschaftsgefühl und zeige deine Schulzugehörigkeit mit Stolz.</p>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        gsap.registerPlugin(ScrollTrigger)
    </script>
    <script type="module" src="js/script.js"></script>
</body>
</html>
