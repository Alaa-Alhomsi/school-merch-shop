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
</head>
<body>
    <?php include 'navbar.php'; ?>

    <header class="bg-blue-600 text-white text-center py-20">
        <h1 class="text-4xl font-bold mb-4">Willkommen im Schul-Merchandise Shop!</h1>
        <p class="text-xl mb-4"><?php echo $randomSlogan; ?></p>
        <a href="#products" class="bg-white text-blue-600 px-6 py-3 rounded-full font-semibold hover:bg-gray-200 transition">Jetzt shoppen</a>
    </header>

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
