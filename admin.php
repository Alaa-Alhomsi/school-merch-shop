<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    header('Location: shop.php');
    exit;
}

require_once 'db.php';

// Bestellungen mit Details abrufen
$query = "SELECT o.id AS order_id, o.user_id, o.created_at, o.total_price, o.status_id,
                 u.email, u.class_name, 
                 oi.product_id, oi.quantity, 
                 p.name AS product_name, p.price AS product_price,
                 os.name AS status_name, os.color AS status_color
          FROM orders o
          JOIN users u ON o.user_id = u.id
          JOIN order_items oi ON o.id = oi.order_id
          JOIN products p ON oi.product_id = p.id
          JOIN order_status os ON o.status_id = os.id
          ORDER BY o.created_at DESC";
$stmt = $pdo->query($query);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Daten für verschiedene Gruppierungen vorbereiten
$groupedByUser = [];
$groupedByProduct = [];
$groupedByClass = [];

foreach ($orders as $order) {
    // Nach Benutzer gruppieren
    if (!isset($groupedByUser[$order['user_id']])) {
        $groupedByUser[$order['user_id']] = [
            'email' => $order['email'],
            'class_name' => $order['class_name'],
            'total_spent' => 0,
            'products' => []
        ];
    }
    $groupedByUser[$order['user_id']]['total_spent'] += $order['product_price'] * $order['quantity'];
    if (!isset($groupedByUser[$order['user_id']]['products'][$order['product_id']])) {
        $groupedByUser[$order['user_id']]['products'][$order['product_id']] = [
            'name' => $order['product_name'],
            'quantity' => 0,
            'orders' => []
        ];
    }
    $groupedByUser[$order['user_id']]['products'][$order['product_id']]['quantity'] += $order['quantity'];
    $groupedByUser[$order['user_id']]['products'][$order['product_id']]['orders'][] = [
        'order_id' => $order['order_id'],
        'date' => $order['created_at'],
        'quantity' => $order['quantity']
    ];

    // Nach Produkt gruppieren
    if (!isset($groupedByProduct[$order['product_id']])) {
        $groupedByProduct[$order['product_id']] = [
            'name' => $order['product_name'],
            'total_quantity' => 0,
            'users' => []
        ];
    }
    $groupedByProduct[$order['product_id']]['total_quantity'] += $order['quantity'];
    if (!isset($groupedByProduct[$order['product_id']]['users'][$order['user_id']])) {
        $groupedByProduct[$order['product_id']]['users'][$order['user_id']] = [
            'email' => $order['email'],
            'class_name' => $order['class_name'],
            'quantity' => 0,
            'orders' => []
        ];
    }
    $groupedByProduct[$order['product_id']]['users'][$order['user_id']]['quantity'] += $order['quantity'];
    $groupedByProduct[$order['product_id']]['users'][$order['user_id']]['orders'][] = [
        'order_id' => $order['order_id'],
        'date' => $order['created_at'],
        'quantity' => $order['quantity']
    ];

    // Nach Klasse gruppieren
    if (!isset($groupedByClass[$order['class_name']])) {
        $groupedByClass[$order['class_name']] = [
            'total_spent' => 0,
            'users' => [],
            'products' => []
        ];
    }
    $groupedByClass[$order['class_name']]['total_spent'] += $order['product_price'] * $order['quantity'];
    if (!isset($groupedByClass[$order['class_name']]['users'][$order['user_id']])) {
        $groupedByClass[$order['class_name']]['users'][$order['user_id']] = [
            'email' => $order['email'],
            'total_spent' => 0
        ];
    }
    $groupedByClass[$order['class_name']]['users'][$order['user_id']]['total_spent'] += $order['product_price'] * $order['quantity'];
    if (!isset($groupedByClass[$order['class_name']]['products'][$order['product_id']])) {
        $groupedByClass[$order['class_name']]['products'][$order['product_id']] = [
            'name' => $order['product_name'],
            'quantity' => 0
        ];
    }
    $groupedByClass[$order['class_name']]['products'][$order['product_id']]['quantity'] += $order['quantity'];
}

// Status-Dropdown zum Filtern hinzufügen
$statusQuery = "SELECT * FROM order_status ORDER BY id";
$statusStmt = $pdo->query($statusQuery);
$orderStatuses = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Bestellungen</title>
    <link href="/css/output.css" rel="stylesheet">
    <link rel="stylesheet" href="css/footerConf.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <?php include 'navbar.php'; ?>
    
    <main class="flex-grow container mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Bestellungen verwalten</h1>

        <div class="flex space-x-4 mb-4">
            <div>
                <label for="grouping" class="block text-sm font-medium text-gray-700 mb-2">Gruppierung:</label>
                <select id="grouping" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="user">Nach Benutzer</option>
                    <option value="product">Nach Produkt</option>
                    <option value="class">Nach Klasse</option>
                </select>
            </div>

            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Suche:</label>
                <input type="text" id="search" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" placeholder="Suchen...">
            </div>

            <!-- Fügen Sie den Excel-Download-Button hier hinzu -->
            <div>
                <button id="downloadExcel" class="mt-1 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                    Excel-Bericht herunterladen
                </button>
            </div>
        </div>

        <!-- Fügen Sie dieses Dropdown-Menü nach dem Gruppierungs-Dropdown hinzu -->
        <div id="classSelectContainer" style="display: none;">
            <label for="classSelect" class="block text-sm font-medium text-gray-700 mb-2">Klasse auswählen:</label>
            <select id="classSelect" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="">Alle Klassen</option>
                <?php
                $classes = array_unique(array_column($orders, 'class_name'));
                foreach ($classes as $class) {
                    $displayClass = $class === 'teacher' ? 'Lehrer' : $class;
                    echo "<option value=\"$class\">$displayClass</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-4">
            <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-2">Status Filter:</label>
            <select id="statusFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="1" selected>Neue Bestellungen</option>
                <?php foreach ($orderStatuses as $status): ?>
                    <option value="<?= $status['id'] ?>"><?= htmlspecialchars($status['name']) ?></option>
                <?php endforeach; ?>
                <option value="">Alle Status</option>
            </select>
        </div>

        <div id="results" class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
            <!-- Ergebnisse werden hier dynamisch eingefügt -->
        </div>

        <div id="statusModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <h3 class="text-lg font-medium mb-4">Status ändern</h3>
                <select id="newStatus" class="w-full mb-4 px-3 py-2 border rounded-md">
                    <?php foreach ($orderStatuses as $status): ?>
                        <option value="<?= $status['id'] ?>" data-color="<?= $status['color'] ?>">
                            <?= htmlspecialchars($status['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="flex justify-end space-x-2">
                    <button id="cancelStatusChange" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Abbrechen</button>
                    <button id="confirmStatusChange" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Speichern</button>
                </div>
            </div>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Bestellungsübersicht</h2>
            <canvas id="orderChart"></canvas>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    
    <script type="module" src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeAdmin();
        });
    </script>
</body>
</html>
