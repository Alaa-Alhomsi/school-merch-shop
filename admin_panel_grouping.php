<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once 'db.php';

// Bestellungen mit Details abrufen
$query = "SELECT o.id AS order_id, o.user_id, o.created_at, o.total_price, o.status_id,
                 u.email, u.class_name, 
                 oi.product_id, oi.quantity, oi.size_name,
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
    $product_key = $order['product_id'] . '_' . ($order['size_name'] ?? 'no_size');
    $class_name = $order['class_name'] === 'teacher' ? 'Lehrer' : $order['class_name'];
    
    // Nach Benutzer gruppieren
    if (!isset($groupedByUser[$order['user_id']])) {
        $groupedByUser[$order['user_id']] = [
            'email' => $order['email'],
            'class_name' => $class_name,
            'total_spent' => 0,
            'products' => [],
            'orders' => []
        ];
    }
    $groupedByUser[$order['user_id']]['total_spent'] += $order['product_price'] * $order['quantity'];
    if (!isset($groupedByUser[$order['user_id']]['products'][$product_key])) {
        $groupedByUser[$order['user_id']]['products'][$product_key] = [
            'name' => $order['product_name'],
            'size' => $order['size_name'] ?? 'N/A',
            'quantity' => 0,
            'orders' => []
        ];
    }
    $groupedByUser[$order['user_id']]['products'][$product_key]['quantity'] += $order['quantity'];
    $groupedByUser[$order['user_id']]['products'][$product_key]['orders'][] = [
        'order_id' => $order['order_id'],
        'date' => $order['created_at'],
        'quantity' => $order['quantity']
    ];
    $groupedByUser[$order['user_id']]['orders'][] = [
        'order_id' => $order['order_id'],
        'date' => $order['created_at'],
        'status_id' => $order['status_id'],
        'status_name' => $order['status_name'],
        'status_color' => $order['status_color']
    ];

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

// Daten als JSON zurückgeben
header('Content-Type: application/json');
echo json_encode([
    'groupedByUser' => $groupedByUser,
    'groupedByProduct' => $groupedByProduct,
    'groupedByClass' => $groupedByClass
]);

// Am Ende der Datei, nach dem JSON-Echo
if (isset($_GET['excel'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'groupedByUser' => $groupedByUser,
        'groupedByProduct' => $groupedByProduct,
        'groupedByClass' => $groupedByClass
    ]);
    exit;
}
