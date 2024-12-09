<?php
session_start();
require_once 'db.php';

// Hole die neuesten Produkte
$stmt = $pdo->query("SELECT * FROM products WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 3");
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
<html lang="de" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schul-Merchandise Shop - Dein Style, Deine Schule</title>
    <link href="/css/output.css" rel="stylesheet">
    <style>
        .slideshow-container {
            position: relative;
            max-width: 100%;
            margin: auto;
            overflow: hidden;
        }

        .slides {
            display: none;
            position: absolute;
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .slideshow-container img {
            width: 100%;
            height: auto;
        }

        .fade {
            animation: fade 1.5s ease-in-out;
        }

        @keyframes fade {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .slogan {
            text-align: center;
            font-size: 2rem;
            margin: 20px 0;
            color: #333;
        }
    </style>
</head>
<body class="h-full overflow-x-hidden bg-gray-100">
    <?php include 'navbar.php'; ?>

    <div class="slideshow-container">
        <?php foreach ($latestProducts as $index => $product): ?>
            <div class="slides fade">
                <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <div class="slogan"><?php echo htmlspecialchars($product['name']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="slogan">
        <h1><?php echo $randomSlogan; ?></h1>
    </div>

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
            slides[slideIndex - 1].style.display = "block";  
            setTimeout(showSlides, 3000); // Wechselt alle 3 Sekunden
        }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
