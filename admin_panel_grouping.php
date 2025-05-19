<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once 'db.php';

// Parameter aus der URL holen
$statusFilter = isset($_GET['status']) ? (int)$_GET['status'] : null;
$grouping = isset($_GET['grouping']) ? $_GET['grouping'] : 'user';

// Basis-Query mit Status-Filter
$query = "SELECT o.id AS order_id, o.user_id, o.created_at, o.total_price, o.status_id,
                 u.email, u.class_name, 
                 oi.product_id, oi.quantity, oi.size_name,
                 p.name AS product_name, p.price AS product_price,
                 os.name AS status_name, os.color AS status_color
          FROM orders o
          JOIN order_items oi ON o.id = oi.order_id
          JOIN users u ON o.user_id = u.id
          JOIN products p ON oi.product_id = p.id AND p.deleted_at IS NULL
          JOIN order_status os ON o.status_id = os.id";

// Status-Filter hinzufügen, wenn vorhanden
if ($statusFilter) {
    $query .= " WHERE o.status_id = :status_id";
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($query);
if ($statusFilter) {
    $stmt->bindParam(':status_id', $statusFilter, PDO::PARAM_INT);
}
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Daten für verschiedene Gruppierungen vorbereiten
$groupedByUser = [];
$groupedByProduct = [];
$groupedByClass = [];
$groupedByOrder = [];

foreach ($orders as $order) {
    $product_key = $order['product_id'] . '_' . ($order['size_name'] ?? 'no_size');
    $class_name = $order['class_name'] === 'teacher' ? 'Lehrer' : $order['class_name'];
    
    // Nach Bestellung gruppieren
    if (!isset($groupedByOrder[$order['order_id']])) {
        $groupedByOrder[$order['order_id']] = [
            'user_id' => $order['user_id'],
            'email' => $order['email'],
            'class_name' => $class_name,
            'created_at' => $order['created_at'],
            'status_id' => $order['status_id'],
            'status_name' => $order['status_name'],
            'status_color' => $order['status_color'],
            'total_price' => 0,
            'products' => []
        ];
    }
    
    // Produkt zur Bestellung hinzufügen
    if (!isset($groupedByOrder[$order['order_id']]['products'][$product_key])) {
        $groupedByOrder[$order['order_id']]['products'][$product_key] = [
            'name' => $order['product_name'],
            'size' => $order['size_name'] ?? 'N/A',
            'quantity' => 0,
            'price_per_unit' => $order['product_price'],
            'total_price' => 0
        ];
    }
    $groupedByOrder[$order['order_id']]['products'][$product_key]['quantity'] += $order['quantity'];
    $product_total = $order['product_price'] * $order['quantity'];
    $groupedByOrder[$order['order_id']]['products'][$product_key]['total_price'] += $product_total;
    $groupedByOrder[$order['order_id']]['total_price'] += $product_total;

    // Nach Benutzer gruppieren (angepasst für Bestellungssummen)
    if (!isset($groupedByUser[$order['user_id']])) {
        $groupedByUser[$order['user_id']] = [
            'email' => $order['email'],
            'class_name' => $class_name,
            'orders' => []
        ];
    }

    // Bestellung mit Status und eigener Summe hinzufügen
    if (!isset($groupedByUser[$order['user_id']]['orders'][$order['order_id']])) {
        $groupedByUser[$order['user_id']]['orders'][$order['order_id']] = [
            'date' => $order['created_at'],
            'status_id' => $order['status_id'],
            'status_name' => $order['status_name'],
            'status_color' => $order['status_color'],
            'total_price' => 0,
            'products' => []
        ];
    }

    // Produkt zur Benutzerbestellung hinzufügen
    $groupedByUser[$order['user_id']]['orders'][$order['order_id']]['products'][] = [
        'product_name' => $order['product_name'],
        'quantity' => $order['quantity'],
        'size' => $order['size_name'] ?? 'N/A',
        'price_per_unit' => $order['product_price'],
        'total_price' => $order['product_price'] * $order['quantity']
    ];
    $groupedByUser[$order['user_id']]['orders'][$order['order_id']]['total_price'] += $order['product_price'] * $order['quantity'];

    // Nach Produkt gruppieren
    if (!isset($groupedByProduct[$product_key])) {
        $groupedByProduct[$product_key] = [
            'name' => $order['product_name'],
            'size' => $order['size_name'] ?? 'N/A',
            'total_quantity' => 0,
            'users' => []
        ];
    }
    $groupedByProduct[$product_key]['total_quantity'] += $order['quantity'];
    if (!isset($groupedByProduct[$product_key]['users'][$order['user_id']])) {
        $groupedByProduct[$product_key]['users'][$order['user_id']] = [
            'email' => $order['email'],
            'class_name' => $order['class_name'],
            'quantity' => 0,
            'orders' => []
        ];
    }
    $groupedByProduct[$product_key]['users'][$order['user_id']]['quantity'] += $order['quantity'];
    $groupedByProduct[$product_key]['users'][$order['user_id']]['orders'][] = [
        'order_id' => $order['order_id'],
        'date' => $order['created_at'],
        'quantity' => $order['quantity']
    ];

    // Nach Klasse gruppieren
    if (!isset($groupedByClass[$class_name])) {
        $groupedByClass[$class_name] = [
            'total_spent' => 0,
            'users' => [],
            'products' => []
        ];
    }
    $groupedByClass[$class_name]['total_spent'] += $order['product_price'] * $order['quantity'];
    if (!isset($groupedByClass[$class_name]['users'][$order['user_id']])) {
        $groupedByClass[$class_name]['users'][$order['user_id']] = [
            'email' => $order['email'],
            'total_spent' => 0
        ];
    }
    $groupedByClass[$class_name]['users'][$order['user_id']]['total_spent'] += $order['product_price'] * $order['quantity'];

    if (!isset($groupedByClass[$class_name]['products'][$product_key])) {
        $groupedByClass[$class_name]['products'][$product_key] = [
            'name' => $order['product_name'],
            'size' => $order['size_name'] ?? 'N/A',
            'quantity' => 0
        ];
    }
    $groupedByClass[$class_name]['products'][$product_key]['quantity'] += $order['quantity'];
}

// Nur die angeforderte Gruppierung zurückgeben
$response = [];
switch ($grouping) {
    case 'user':
        $response['groupedByUser'] = $groupedByUser;
        break;
    case 'product':
        $response['groupedByProduct'] = $groupedByProduct;
        break;
    case 'class':
        $response['groupedByClass'] = $groupedByClass;
        break;
    case 'order':
        $response['groupedByOrder'] = $groupedByOrder;
        break;
    default:
        $response = [
            'groupedByUser' => $groupedByUser,
            'groupedByProduct' => $groupedByProduct,
            'groupedByClass' => $groupedByClass,
            'groupedByOrder' => $groupedByOrder
        ];
}

// Daten als JSON zurückgeben
header('Content-Type: application/json');
echo json_encode($response);

// Am Ende der Datei, nach dem JSON-Echo
if (isset($_GET['excel'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'groupedByUser' => $groupedByUser,
        'groupedByProduct' => $groupedByProduct,
        'groupedByClass' => $groupedByClass,
        'groupedByOrder' => $groupedByOrder
    ]);
    exit;
}
