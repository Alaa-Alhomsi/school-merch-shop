<?php
session_start();
require_once 'db.php';

// Hole die neuesten Produkte
$stmt = $pdo->query("SELECT * FROM products WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 6");
$latestProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Zufälliger Slogan
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
<html lang="de" class="bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schul-Merchandise Shop - Dein Style, Deine Schule</title>
    <link href="/css/output.css" rel="stylesheet">
    <style>
        .slideshow-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh; /* Vollständige Höhe des Viewports */
            overflow: hidden;
            background-color: rgba(0, 0, 0, 0.5); /* Eingrauen des Hintergrunds */
        }

        .slides {
            display: none;
            flex: 1; /* Nimmt den verfügbaren Platz ein */
            transition: opacity 1s ease-in-out; /* Sanfter Übergang */
        }

        .slideshow-container img {
            width: 100%;
            height: auto;
            object-fit: cover; /* Bild anpassen */
            border-radius: 10px; /* Abgerundete Ecken */
        }

        .details {
            flex: 1; /* Nimmt den verfügbaren Platz ein */
            color: #fff; /* Weißer Text */
            padding: 20px;
            text-align: left;
            z-index: 10; /* Über den Bildern */
            background-color: rgba(0, 0, 0, 0.7); /* Halbtransparenter Hintergrund */
            border-radius: 10px; /* Abgerundete Ecken */
        }

        .slogan {
            font-size: 2rem;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <header class="slideshow-container">
        <?php foreach ($latestProducts as $index => $product): ?>
            <div class="slides">
                <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <div class="details">
                    <h2 class="slogan"><?php echo htmlspecialchars($product['name']); ?></h2>
                    <p class="text-lg mb-4">Kategorie: <?php echo htmlspecialchars($product['category_name']); ?></p>
                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-full hover:bg-blue-700 transition">Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    </header>

    <script>
        let slideIndex = 0;
        showSlides();

        function showSlides() {
            const slides = document.getElementsByClassName("slides");
            for (let i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";  
            }
            slideIndex++;
            if (slideIndex > slides.length) {slideIndex = 1}    
            slides[slideIndex - 1].style.display = "flex";  
            setTimeout(showSlides, 3000); // Wechselt alle 3 Sekunden
        }
    </script>

    <main class="container mx-auto px-4 py-12">
        <h2 class="text-3xl font-bold text-center mb-8">Neueste Produkte</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
            <?php foreach ($latestProducts as $product): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-gray-600 mb-4">Kategorie: <?php echo htmlspecialchars($product['category_name']); ?></p>
                        <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-full hover:bg-blue-700 transition">Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
