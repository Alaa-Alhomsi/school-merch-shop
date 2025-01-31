<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once 'db.php';

// Pagination-Parameter holen
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

// Gesamtanzahl der Bestellungen ermitteln
$totalQuery = "SELECT COUNT(DISTINCT o.id) AS total FROM orders o";
$totalStmt = $pdo->query($totalQuery);
$totalOrders = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalOrders / $limit);

// Bestellungen mit Details abrufen (paginiert)
$query = "SELECT o.id AS order_id, o.user_id, o.created_at, o.total_price, o.status_id,
                 u.email, u.class_name, 
                 oi.product_id, oi.quantity, oi.size_name,
                 p.name AS product_name, p.price AS product_price,
                 os.name AS status_name, os.color AS status_color
          FROM orders o
          JOIN order_items oi ON o.id = oi.order_id
          JOIN users u ON o.user_id = u.id
          JOIN products p ON oi.product_id = p.id AND p.deleted_at IS NULL
          JOIN order_status os ON o.status_id = os.id
          ORDER BY o.created_at DESC
          LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gruppierungs-Arrays initialisieren
$groupedByUser = [];
$groupedByProduct = [];
$groupedByClass = [];

foreach ($orders as $order) {
    $productKey = $order['product_id'] . '_' . ($order['size_name'] ?? 'no_size');
    $className = $order['class_name'] === 'teacher' ? 'Lehrer' : $order['class_name'];

    // Nach Benutzer gruppieren
    $groupedByUser[$order['user_id']]['email'] = $order['email'];
    $groupedByUser[$order['user_id']]['class_name'] = $className;
    $groupedByUser[$order['user_id']]['total_spent'] = ($groupedByUser[$order['user_id']]['total_spent'] ?? 0) + ($order['product_price'] * $order['quantity']);
    $groupedByUser[$order['user_id']]['products'][$productKey]['name'] = $order['product_name'];
    $groupedByUser[$order['user_id']]['products'][$productKey]['size'] = $order['size_name'] ?? 'N/A';
    $groupedByUser[$order['user_id']]['products'][$productKey]['quantity'] = ($groupedByUser[$order['user_id']]['products'][$productKey]['quantity'] ?? 0) + $order['quantity'];
    $groupedByUser[$order['user_id']]['orders'][$order['order_id']]['date'] = $order['created_at'];
    $groupedByUser[$order['user_id']]['orders'][$order['order_id']]['status_name'] = $order['status_name'];
    $groupedByUser[$order['user_id']]['orders'][$order['order_id']]['status_color'] = $order['status_color'];

    // Nach Produkt gruppieren
    $groupedByProduct[$productKey]['name'] = $order['product_name'];
    $groupedByProduct[$productKey]['size'] = $order['size_name'] ?? 'N/A';
    $groupedByProduct[$productKey]['total_quantity'] = ($groupedByProduct[$productKey]['total_quantity'] ?? 0) + $order['quantity'];

    // Nach Klasse gruppieren
    $groupedByClass[$className]['total_spent'] = ($groupedByClass[$className]['total_spent'] ?? 0) + ($order['product_price'] * $order['quantity']);
    $groupedByClass[$className]['users'][$order['user_id']]['email'] = $order['email'];
    $groupedByClass[$className]['users'][$order['user_id']]['total_spent'] = ($groupedByClass[$className]['users'][$order['user_id']]['total_spent'] ?? 0) + ($order['product_price'] * $order['quantity']);
    $groupedByClass[$className]['products'][$productKey]['name'] = $order['product_name'];
    $groupedByClass[$className]['products'][$productKey]['size'] = $order['size_name'] ?? 'N/A';
    $groupedByClass[$className]['products'][$productKey]['quantity'] = ($groupedByClass[$className]['products'][$productKey]['quantity'] ?? 0) + $order['quantity'];
}

// JSON zurückgeben
header('Content-Type: application/json');
echo json_encode([
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_orders' => $totalOrders,
        'limit' => $limit
    ],
    'groupedByUser' => $groupedByUser,
    'groupedByProduct' => $groupedByProduct,
    'groupedByClass' => $groupedByClass
]);
