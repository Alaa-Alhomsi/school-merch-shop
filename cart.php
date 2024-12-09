<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart = &$_SESSION['cart'];
$total = 0;

// Entfernen eines Produkts aus dem Warenkorb
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    if (isset($cart[$remove_id])) {
        unset($cart[$remove_id]);
    }
    header('Location: cart.php');
    exit;
}

// Warenkorb-Daten abrufen
if (!empty($cart)) {
    $product_ids = array_unique(array_map(function($key) {
        return explode('_', $key)[0];
    }, array_keys($cart)));
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    
    $stmt = $pdo->prepare("SELECT p.*, c.allows_sizes 
                           FROM products p 
                           JOIN categories c ON p.category_id = c.id 
                           WHERE p.id IN ($placeholders) AND p.deleted_at IS NULL");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cart as $cart_item_key => $item) {
        list($product_id, $size_name) = explode('_', $cart_item_key . '_');
        $product = array_values(array_filter($products, function($p) use ($product_id) {
            return $p['id'] == $product_id;
        }))[0];

        if ($product) {
            $quantity = $item['quantity'];
            $subtotal = $product['price'] * $quantity;
            $total += $subtotal;
            $cart[$cart_item_key] = array_merge($item, $product, ['subtotal' => $subtotal, 'size_name' => $size_name]);
        }
    }
}

$_SESSION['cart'] = $cart;
?>

<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warenkorb</title>
    <link href="/css/output.css" rel="stylesheet">
    <script type="module" src="js/script.js" defer></script>
</head>
<body class="h-full flex flex-col">
    <?php include 'navbar.php'; ?>
    
    <main class="flex-grow container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Warenkorb</h1>
        
        <?php if (!empty($cart)): ?>
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($cart as $cart_item_key => $item): ?>
                        <li class="p-4 flex flex-col sm:flex-row justify-between items-center" data-product-id="<?= $cart_item_key; ?>" data-price="<?= $item['price']; ?>">
                            <div class="flex-grow mb-2 sm:mb-0">
                                <h3 class="text-lg font-medium"><?= htmlspecialchars($item['name']); ?></h3>
                                <p class="text-gray-500">Preis: €<span class="item-price"><?= number_format($item['price'], 2); ?></span></p>
                                <?php if (isset($item['size_name']) && $item['size_name'] !== ''): ?>
                                    <p class="text-gray-500">Größe: <?= htmlspecialchars($item['size_name']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center">
                                <button class="quantity-decrease bg-gray-200 px-2 py-1 rounded-l">-</button>
                                <input type="number" class="quantity-input w-16 text-center border-t border-b border-gray-200" value="<?= $item['quantity']; ?>" min="1">
                                <button class="quantity-increase bg-gray-200 px-2 py-1 rounded-r">+</button>
                            </div>
                            <div class="text-right ml-4">
                                <p class="font-medium">Gesamt: €<span class="item-subtotal"><?= number_format($item['subtotal'], 2); ?></span></p>
                                <a href="cart.php?remove=<?= $cart_item_key; ?>" class="text-sm text-red-600 hover:text-red-800">Entfernen</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="mt-6 text-right">
                <h2 class="text-xl font-bold">Gesamtsumme: €<span id="cart-total"><?= number_format($total, 2); ?></span></h2>
            </div>
            
            <div class="mt-6">
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']): ?>
                    <form method="post" action="process_order.php">
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                            Bestellung aufgeben
                        </button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="block w-full bg-blue-500 hover:bg-blue-600 text-white text-center font-bold py-2 px-4 rounded">
                        Zum Bestellen einloggen
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-600">Ihr Warenkorb ist leer.</p>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeCart();
        });
    </script>
</body>
</html>