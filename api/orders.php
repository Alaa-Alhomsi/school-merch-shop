<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../db.php';

// Pagination-Parameter
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
$offset = ($page - 1) * $limit;
$groupBy = isset($_GET['groupBy']) ? $_GET['groupBy'] : null;

// Gesamtanzahl der Bestellungen ermitteln
$totalQuery = "SELECT COUNT(DISTINCT id) AS total FROM orders";
$totalStmt = $pdo->query($totalQuery);
$totalOrders = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalOrders / $limit);

// Bestellungen abrufen
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

// Gruppierung je nach Parameter
$result = [];

if ($groupBy === 'user') {
    foreach ($orders as $order) {
        $userId = $order['user_id'];
        $result[$userId]['email'] = $order['email'];
        $result[$userId]['class_name'] = $order['class_name'];
        $result[$userId]['total_spent'] = ($result[$userId]['total_spent'] ?? 0) + ($order['product_price'] * $order['quantity']);
    }
} elseif ($groupBy === 'product') {
    foreach ($orders as $order) {
        $productKey = $order['product_id'] . '_' . ($order['size_name'] ?? 'no_size');
        $result[$productKey]['name'] = $order['product_name'];
        $result[$productKey]['size'] = $order['size_name'] ?? 'N/A';
        $result[$productKey]['total_quantity'] = ($result[$productKey]['total_quantity'] ?? 0) + $order['quantity'];
    }
} elseif ($groupBy === 'class') {
    foreach ($orders as $order) {
        $className = $order['class_name'] === 'teacher' ? 'Lehrer' : $order['class_name'];
        $result[$className]['total_spent'] = ($result[$className]['total_spent'] ?? 0) + ($order['product_price'] * $order['quantity']);
    }
} else {
    $result = $orders; // Falls keine Gruppierung, rohe Bestellungen ausgeben
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
    'data' => $result
]);
