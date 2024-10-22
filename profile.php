<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Benutzerdaten abrufen
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Verfügbare Klassen abrufen
$stmtClasses = $pdo->query("SELECT class_name FROM classes");
$classes = $stmtClasses->fetchAll(PDO::FETCH_COLUMN);

// Debug-Ausgabe
echo "<!-- Debug: Aktuelle Benutzerklasse: " . htmlspecialchars($user['class_name']) . " -->";

// Formular zur Aktualisierung des Profils verarbeiten
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class = $_POST['class_name'];

    // Überprüfen, ob die ausgewählte Klasse in der Datenbank existiert
    if (in_array($class, $classes)) {
        $stmt = $pdo->prepare("UPDATE users SET class_name = ? WHERE id = ?");
        $stmt->execute([$class, $userId]);

        // Aktualisierte Benutzerdaten abrufen
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        $updateMessage = "Profil erfolgreich aktualisiert.";
    } else {
        $updateMessage = "Fehler: Ungültige Klasse ausgewählt.";
    }
}

// Bestellungen abrufen
$stmt = $pdo->prepare("
    SELECT o.id AS order_id, o.created_at AS order_date, o.total_price,
           p.id AS product_id, p.name AS product_name, oi.quantity, oi.price AS item_price,
           oi.size_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$userId]);
$orderItems = $stmt->fetchAll();

// Bestellungen gruppieren
$orders = [];
foreach ($orderItems as $item) {
    if (!isset($orders[$item['order_id']])) {
        $orders[$item['order_id']] = [
            'id' => $item['order_id'],
            'date' => $item['order_date'],
            'total_price' => $item['total_price'],
            'items' => []
        ];
    }
    $orders[$item['order_id']]['items'][] = [
        'product_id' => $item['product_id'],
        'product_name' => $item['product_name'],
        'quantity' => $item['quantity'],
        'price' => $item['item_price'],
        'size_name' => $item['size_name']
    ];
}

?>

<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mein Profil</title>
    <link href="/css/output.css" rel="stylesheet">
    <link rel="stylesheet" href="css/footerConf.css">
</head>
<body class="flex flex-col min-h-screen">
    <?php include 'navbar.php'; ?>
    
    <main class="flex-grow">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-8">Mein Profil</h1>
            
            <?php if (isset($updateMessage)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $updateMessage; ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-8">
                <h2 class="text-2xl font-bold mb-4">Persönliche Informationen</h2>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                            E-Mail
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="class">
                            Klasse
                        </label>
                        <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="class_name" name="class_name" required>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo htmlspecialchars($class); ?>" <?php echo ($user['class_name'] === $class) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                            Aktualisieren
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
                <h2 class="text-2xl font-bold mb-4">Meine Bestellungen</h2>
                <?php if (empty($orders)): ?>
                    <p>Sie haben noch keine Bestellungen aufgegeben.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Bestellnummer</th>
                                    <th scope="col" class="px-6 py-3">Datum</th>
                                    <th scope="col" class="px-6 py-3">Gesamtpreis</th>
                                    <th scope="col" class="px-6 py-3">Produkte</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr class="bg-white border-b">
                                        <td class="px-6 py-4"><?php echo $order['id']; ?></td>
                                        <td class="px-6 py-4"><?php echo $order['date']; ?></td>
                                        <td class="px-6 py-4"><?php echo number_format($order['total_price'], 2, ',', '.') . ' €'; ?></td>
                                        <td class="px-6 py-4">
                                            <ul>
                                                <?php foreach ($order['items'] as $item): ?>
                                                    <li>
                                                        <a href="product_detail.php?id=<?php echo $item['product_id']; ?>" class="text-blue-600 hover:underline">
                                                            <?php echo htmlspecialchars($item['product_name']); ?>
                                                        </a>
                                                        <?php if (!empty($item['size_name'])): ?>
                                                            (Größe: <?php echo htmlspecialchars($item['size_name']); ?>)
                                                        <?php endif; ?>
                                                        (<?php echo $item['quantity']; ?> x <?php echo number_format($item['price'], 2, ',', '.') . ' €'; ?>)
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>