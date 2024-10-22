<?php
session_start();
require_once 'db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$query = "SELECT p.*, c.name AS category_name, c.allows_sizes 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          WHERE p.name LIKE :search 
          " . ($category ? "AND c.id = :category " : "") . "
          ORDER BY $sort 
          LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
if ($category) {
    $stmt->bindValue(':category', $category, PDO::PARAM_INT);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_query = "SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id WHERE p.name LIKE :search";
$total_stmt = $pdo->prepare($total_query);
$total_stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
$total_stmt->execute();
$total_products = $total_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);
?>

<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produkte</title>
    <link href="/css/output.css" rel="stylesheet">
    <link rel="stylesheet" href="css/footerConf.css">
</head>
<body class="flex flex-col min-h-screen">
    <?php include 'navbar.php'; ?>
    <main class="flex-grow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-3xl font-bold mb-8 text-center">Produkte</h1>

            <!-- Suchfeld und Sortierung -->
            <form action="shop.php" method="get" class="mb-8 flex flex-col sm:flex-row items-center justify-between">
                <div class="flex-1 w-full sm:w-auto mb-4 sm:mb-0">
                    <input type="text" name="search" placeholder="Produkt suchen..." value="<?= htmlspecialchars($search); ?>" class="w-full px-3 py-2 border rounded-md">
                </div>
                <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <button type="submit" class="w-full sm:w-auto bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">Suchen</button>
                    <div class="w-full sm:w-auto">
                        <label for="sort" class="mr-2">Sortieren nach:</label>
                        <select name="sort" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-2 border rounded-md">
                            <option value="name" <?= $sort == 'name' ? 'selected' : ''; ?>>Name</option>
                            <option value="price" <?= $sort == 'price' ? 'selected' : ''; ?>>Preis</option>
                        </select>
                    </div>
                    <div class="w-full sm:w-auto">
                        <label for="category" class="mr-2">Kategorie:</label>
                        <select name="category" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-2 border rounded-md">
                            <option value="">Alle</option>
                            <?php
                            $cat_query = "SELECT id, name FROM categories";
                            $cat_stmt = $pdo->query($cat_query);
                            while ($cat = $cat_stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value=\"{$cat['id']}\"" . ($category == $cat['id'] ? ' selected' : '') . ">{$cat['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Produktliste -->
            <?php if (count($products) > 0): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <?php foreach ($products as $product): ?>
                        <a href="product_detail.php?id=<?= $product['id']; ?>" class="bg-white shadow-lg rounded-lg overflow-hidden flex flex-col transition-transform duration-300 hover:scale-105">
                            <div class="relative">
                                <img src="images/<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover">
                            </div>
                            <div class="p-4 flex-grow flex flex-col justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold mb-2 text-gray-800"><?= htmlspecialchars($product['name']); ?></h3>
                                    <p class="text-gray-600 mb-4 text-sm"><?= htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?></p>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-800 font-bold">€<?= number_format($product['price'], 2); ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div class="mt-8 flex justify-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i; ?>&search=<?= htmlspecialchars($search); ?>&sort=<?= htmlspecialchars($sort); ?>" 
                           class="mx-1 px-3 py-2 border rounded-md <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-white text-blue-500 hover:bg-blue-100'; ?>">
                            <?= $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-600">Keine Produkte gefunden.</p>
            <?php endif; ?>
        </div>
    </main>

    <div id="notification" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md hidden">
        Produkt wurde zum Warenkorb hinzugefügt
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
