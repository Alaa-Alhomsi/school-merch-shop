<?php
session_start();
require_once 'db.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: shop.php');
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.allows_sizes 
                       FROM products p 
                       JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ? AND p.deleted_at IS NULL AND c.deleted_at IS NULL");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: shop.php');
    exit;
}

if ($product['allows_sizes']) {
    $sizes_stmt = $pdo->prepare("SELECT * FROM sizes ORDER BY display_order");
    $sizes_stmt->execute();
    $sizes = $sizes_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produktdetails</title>
    <link href="/css/output.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="css/footerConf.css">
</head>
<body class="font-sans">
    <?php include 'navbar.php'; ?>
    <main>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8 text-center"><?= htmlspecialchars($product['name']); ?></h1>
        <div class="bg-white shadow-md rounded-lg p-6 flex flex-col md:flex-row">
            <img src="images/<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="w-full md:w-1/2 object-cover rounded-lg mb-4 md:mb-0 md:mr-6">
            <div class="flex-1">
                <p class="text-gray-700 mb-4"><?= htmlspecialchars($product['description']); ?></p>
                <p class="text-2xl font-bold text-green-600 mb-4">€<?= number_format($product['price'], 2); ?></p>
                <p class="text-lg font-semibold mb-4">Kategorie: <?= htmlspecialchars($product['category_name']); ?></p>

                <div class="space-y-4">
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Menge:</label>
                        <input type="number" id="quantity" value="1" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <?php if ($product['allows_sizes']): ?>
                        <div>
                            <label for="size" class="block text-sm font-medium text-gray-700">Größe:</label>
                            <select id="size" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <?php foreach ($sizes as $size): ?>
                                    <option value="<?= $size['name']; ?>"><?= htmlspecialchars($size['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <button onclick="addToCart(<?= $product['id']; ?>, <?= $product['allows_sizes'] ? 'true' : 'false'; ?>)" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">In den Warenkorb</button>
                </div>
            </div>
        </div>
    </div>

    <div id="notification" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md hidden">
        Produkt wurde zum Warenkorb hinzugefügt
    </div>

    </main>
    <?php include 'footer.php'; ?>
    <script type="module" src="js/script.js" ></script>
</body>
</html>
