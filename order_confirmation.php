<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Bestelldetails abrufen
$stmt = $pdo->prepare("SELECT o.*, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: index.php');
    exit;
}

// Bestellpositionen abrufen
$stmt = $pdo->prepare("SELECT oi.*, p.name AS product_name 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ? AND p.deleted_at IS NULL");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = array_sum(array_map(function($item) {
    return $item['price'] * $item['quantity'];
}, $order_items));
?>

<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestellbestätigung</title>
    <link href="/css/output.css" rel="stylesheet">
</head>
<body class="h-full flex flex-col">
    <?php include 'navbar.php'; ?>
    
    <main class="flex-grow container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6 text-center">Bestellbestätigung</h1>
        
        <?php if (isset($_SESSION['order_success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['order_success']; ?></span>
            </div>
            <?php unset($_SESSION['order_success']); ?>
        <?php endif; ?>
        
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">Bestellnummer: <?php echo $order_id; ?></h2>
                <p class="mb-2"><strong>Datum:</strong> <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></p>
                <p class="mb-4"><strong>E-Mail:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                
                <h3 class="text-lg font-semibold mb-2">Bestellte Artikel:</h3>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($order_items as $item): ?>
                        <li class="py-4 flex justify-between">
                            <div>
                                <h4 class="text-md font-medium"><?php echo htmlspecialchars($item['name']); ?></h4>
                                <?php if ($item['size_name']): ?>
                                    <p class="text-sm text-gray-600">Größe: <?php echo htmlspecialchars($item['size_name']); ?></p>
                                <?php endif; ?>
                                <p class="text-sm text-gray-600">Menge: <?php echo $item['quantity']; ?></p>
                            </div>
                            <p class="text-md font-medium">€<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <div class="mt-6 text-right">
                    <p class="text-xl font-bold">Gesamtsumme: €<?php echo number_format($total, 2); ?></p>
                </div>
            </div>
        </div>
        
        <div class="mt-8 text-center">
            <a href="index.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                Zurück zur Startseite
            </a>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
