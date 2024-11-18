<?php
session_start();
require_once 'db.php';

// Hole die neuesten Produkte
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 6");
$latestProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hole Kategorien
$stmt = $pdo->query("SELECT * FROM categories LIMIT 4");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Warenkorb-Zähler
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>

<!DOCTYPE html>
<html lang="de" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schul-Merchandise Shop</title>
    <link href="/css/output.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="h-full bg-gray-100">
    <?php include 'navbar.php'; ?>
    
    <main class="container mx-auto px-4 py-12">
        <section id="categories" class="mb-24">
            <h2 class="text-3xl font-bold text-center mb-12">Unsere Kategorien</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($categories as $category): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                        <img src="images/<?php echo htmlspecialchars($category['image']); ?>" 
                             alt="<?php echo htmlspecialchars($category['name']); ?>" 
                             class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </h3>
                            <a href="shop.php?category=<?php echo $category['id']; ?>" 
                               class="text-indigo-600 hover:text-indigo-800">
                                Entdecken →
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="products" class="mb-24">
            <h2 class="text-3xl font-bold text-center mb-12">Neueste Produkte</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($latestProducts as $product): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                        <img src="images/<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="w-full h-64 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h3>
                            <p class="text-gray-600 mb-4">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold">
                                    €<?php echo number_format($product['price'], 2); ?>
                                </span>
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" 
                                   class="bg-indigo-600 text-white px-4 py-2 rounded-full hover:bg-indigo-700 transition duration-300">
                                    Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    
    <?php include 'footer.php'; ?>
    <script src="js/script.js"></script>
</body>
</html>
