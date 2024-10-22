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

// Berechne die Anzahl der Produkte im Warenkorb
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;

// Überprüfe, ob der Willkommensbanner angezeigt werden soll
$showWelcome = false;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && !isset($_SESSION['welcome_shown'])) {
    $showWelcome = true;
    $_SESSION['welcome_shown'] = true;
}
?>

<!DOCTYPE html>
<html lang="de" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schul-Merchandise Shop - Interaktive Erfahrung</title>
    <link href="/css/output.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/ScrollTrigger.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.2/vanilla-tilt.min.js"></script>
    <style>
        body {
            overflow-x: hidden;
        }
        .scroll-section {
            min-height: 100vh;
            width: 100vw;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .product-card, .feature-card {
            transition: all 0.3s ease;
            transform-style: preserve-3d;
        }
        .product-card:hover, .feature-card:hover {
            transform: translateZ(20px) rotateX(5deg) rotateY(5deg);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }
        #background-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
        }
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="min-h-screen">
    <?php include 'navbar.php'; ?>

    <div id="background-canvas"></div>

    <main>
        <!-- Hero Section -->
        <section class="scroll-section bg-indigo-900 text-white">
            <div class="text-center">
                <h1 class="text-6xl font-extrabold mb-4 opacity-0 floating" id="hero-title">Schul-Merchandise Shop</h1>
                <p class="text-2xl mb-8 opacity-0" id="hero-slogan"><?php echo $randomSlogan; ?></p>
                <a href="shop.php" class="inline-block bg-white text-indigo-600 px-8 py-3 rounded-md text-lg font-medium hover:bg-indigo-50 transition duration-300 opacity-0 transform hover:scale-110" id="hero-cta">
                    Jetzt einkaufen
                </a>
            </div>
        </section>

        <!-- Latest Products Section -->
        <section class="scroll-section bg-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-4xl font-extrabold text-gray-900 mb-12 text-center opacity-0" id="products-title">Unsere neuesten Produkte</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <?php foreach ($latestProducts as $index => $product): ?>
                        <div class="product-card bg-white shadow-lg rounded-lg overflow-hidden opacity-0" id="product-<?php echo $index; ?>" data-tilt data-tilt-max="5" data-tilt-speed="400" data-tilt-perspective="500">
                            <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover">
                            <div class="p-6">
                                <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                                <div class="flex justify-between items-center">
                                    <span class="text-indigo-600 font-bold">€<?php echo number_format($product['price'], 2); ?></span>
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition duration-300 transform hover:scale-105">Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="scroll-section bg-indigo-900 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-4xl font-extrabold mb-12 text-center opacity-0" id="features-title">Warum unser Schul-Merchandise?</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <div class="feature-card bg-indigo-800 p-8 rounded-lg shadow-md opacity-0" id="feature-1" data-tilt data-tilt-max="10" data-tilt-speed="400" data-tilt-perspective="1000">
                        <h3 class="text-2xl font-semibold mb-4">Qualität</h3>
                        <p>Hochwertige Produkte, die lange halten und gut aussehen.</p>
                    </div>
                    <div class="feature-card bg-indigo-800 p-8 rounded-lg shadow-md opacity-0" id="feature-2" data-tilt data-tilt-max="10" data-tilt-speed="400" data-tilt-perspective="1000">
                        <h3 class="text-2xl font-semibold mb-4">Einzigartigkeit</h3>
                        <p>Exklusive Designs, die deine Schulzugehörigkeit zeigen.</p>
                    </div>
                    <div class="feature-card bg-indigo-800 p-8 rounded-lg shadow-md opacity-0" id="feature-3" data-tilt data-tilt-max="10" data-tilt-speed="400" data-tilt-perspective="1000">
                        <h3 class="text-2xl font-semibold mb-4">Schulgeist</h3>
                        <p>Stärke den Zusammenhalt und zeige deinen Schulstolz.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

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

        gsap.utils.toArray(".product-card").forEach((card, index) => {
            gsap.to(card, {
                scrollTrigger: {
                    trigger: card,
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

        gsap.utils.toArray(".feature-card").forEach((card, index) => {
            gsap.to(card, {
                scrollTrigger: {
                    trigger: card,
                    start: "top 80%",
                },
                opacity: 1,
                y: 0,
                duration: 0.5,
                delay: index * 0.2
            });
        });

        // Three.js Hintergrund
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({alpha: true});
        renderer.setSize(window.innerWidth, window.innerHeight);
        document.getElementById('background-canvas').appendChild(renderer.domElement);

        const geometry = new THREE.TorusKnotGeometry(10, 3, 100, 16);
        const material = new THREE.MeshBasicMaterial({color: 0x6366f1, wireframe: true});
        const torusKnot = new THREE.Mesh(geometry, material);
        scene.add(torusKnot);

        camera.position.z = 30;

        function animate() {
            requestAnimationFrame(animate);
            torusKnot.rotation.x += 0.01;
            torusKnot.rotation.y += 0.01;
            renderer.render(scene, camera);
        }
        animate();

        // Resize-Handler
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        // Initialisiere Vanilla Tilt für 3D-Hover-Effekte
        VanillaTilt.init(document.querySelectorAll(".product-card, .feature-card"), {
            max: 25,
            speed: 400,
            glare: true,
            "max-glare": 0.5,
        });
    </script>
</body>
</html>