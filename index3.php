<?php
session_start();
require_once 'db.php';

// Hole die neuesten Produkte basierend auf created_at
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 4");
$latestProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hole einen zufälligen Slogan
$slogans = [
    "Zeig deine Schulliebe mit Stil!",
    "Trag deine Schule mit Stolz!",
    "Dein Schulspirit, dein Style!",
    "Schulmerchandise: Mehr als nur Kleidung!",
    "Einzigartig wie deine Schule!"
];
$randomSlogan = $slogans[array_rand($slogans)];

?>

<!DOCTYPE html>
<html lang="de" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schul-Merchandise Shop - Parallax Erfahrung</title>
    <link href="/css/output.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/ScrollTrigger.min.js"></script>
    <style>
        body {
            overflow-x: hidden;
        }
        .parallax-container {
            height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
            perspective: 1px;
        }
        .parallax-layer {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
        }
        .parallax-layer-back {
            transform: translateZ(-1px) scale(2);
        }
        .parallax-layer-base {
            transform: translateZ(0);
        }
        .content-section {
            position: relative;
            min-height: 100vh;
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 1;
        }
    </style>
</head>
<body class="min-h-screen">
    <?php include 'navbar.php'; ?>

    <div class="parallax-container">
        <div class="parallax-layer parallax-layer-back">
            <img src="src/nike-hoodie.png" alt="Parallax Background" class="w-full h-full object-cover">
        </div>

        <div class="parallax-layer parallax-layer-base">
            <!-- Hero Section -->
            <section class="h-screen flex items-center justify-center text-white">
                <div class="text-center">
                    <h1 class="text-6xl font-extrabold mb-4 opacity-0" id="hero-title">Schul-Merchandise Shop</h1>
                    <p class="text-2xl mb-8 opacity-0" id="hero-slogan"><?php echo $randomSlogan; ?></p>
                    <a href="shop.php" class="inline-block bg-white text-indigo-600 px-8 py-3 rounded-md text-lg font-medium hover:bg-indigo-50 transition duration-300 opacity-0 transform hover:scale-110" id="hero-cta">
                        Jetzt einkaufen
                    </a>
                </div>
            </section>

            <!-- Latest Products Section -->
            <section class="content-section py-24">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-4xl font-extrabold text-gray-900 mb-12 text-center opacity-0" id="products-title">Unsere neuesten Produkte</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        <?php foreach ($latestProducts as $index => $product): ?>
                            <div class="bg-white shadow-lg rounded-lg overflow-hidden opacity-0" id="product-<?php echo $index; ?>">
                                <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover">
                                <div class="p-6">
                                    <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-indigo-600 font-bold">€<?php echo number_format($product['price'], 2); ?></span>
                                        <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition duration-300">Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="content-section py-24 bg-indigo-900 text-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-4xl font-extrabold mb-12 text-center opacity-0" id="features-title">Warum unser Schul-Merchandise?</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                        <div class="bg-indigo-800 p-8 rounded-lg shadow-md opacity-0" id="feature-1">
                            <h3 class="text-2xl font-semibold mb-4">Qualität</h3>
                            <p>Hochwertige Produkte, die lange halten und gut aussehen.</p>
                        </div>
                        <div class="bg-indigo-800 p-8 rounded-lg shadow-md opacity-0" id="feature-2">
                            <h3 class="text-2xl font-semibold mb-4">Einzigartigkeit</h3>
                            <p>Exklusive Designs, die deine Schulzugehörigkeit zeigen.</p>
                        </div>
                        <div class="bg-indigo-800 p-8 rounded-lg shadow-md opacity-0" id="feature-3">
                            <h3 class="text-2xl font-semibold mb-4">Schulgeist</h3>
                            <p>Stärke den Zusammenhalt und zeige deinen Schulstolz.</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // GSAP Animationen
        gsap.registerPlugin(ScrollTrigger);

        // Hero-Animationen
        gsap.to("#hero-title", {opacity: 1, y: 0, duration: 1, delay: 0.5});
        gsap.to("#hero-slogan", {opacity: 1, y: 0, duration: 1, delay: 0.7});
        gsap.to("#hero-cta", {opacity: 1, y: 0, duration: 1, delay: 0.9});

        // Produkt-Animationen
        gsap.to("#products-title", {
            scrollTrigger: {
                trigger: "#products-title",
                start: "top 80%",
            },
            opacity: 1,
            y: 0,
            duration: 1
        });

        gsap.utils.toArray("[id^='product-']").forEach((product, index) => {
            gsap.to(product, {
                scrollTrigger: {
                    trigger: product,
                    start: "top 80%",
                },
                opacity: 1,
                y: 0,
                duration: 0.5,
                delay: index * 0.1
            });
        });

        // Feature-Animationen
        gsap.to("#features-title", {
            scrollTrigger: {
                trigger: "#features-title",
                start: "top 80%",
            },
            opacity: 1,
            y: 0,
            duration: 1
        });

        gsap.utils.toArray("[id^='feature-']").forEach((feature, index) => {
            gsap.to(feature, {
                scrollTrigger: {
                    trigger: feature,
                    start: "top 80%",
                },
                opacity: 1,
                y: 0,
                duration: 0.5,
                delay: index * 0.2
            });
        });

        // Parallax-Effekt
        gsap.to(".parallax-layer-back", {
            yPercent: 50,
            ease: "none",
            scrollTrigger: {
                trigger: ".parallax-container",
                start: "top top",
                end: "bottom top",
                scrub: true
            }
        });
    </script>
</body>
</html>
