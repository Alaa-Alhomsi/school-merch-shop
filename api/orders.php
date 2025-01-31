<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../db.php';

// Parameter holen
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
$offset = ($page - 1) * $limit;
$groupBy = $_GET['groupBy'] ?? 'none';  // none, user, product, class
$statusFilter = isset($_GET['status']) ? $_GET['status'] : null;
$classFilter = isset($_GET['class']) ? $_GET['class'] : null;

// Grund-Query mit Filtern
$query = "SELECT o.id AS order_id, o.user_id, o.created_at, o.total_price, 
                 u.email, u.class_name, 
                 os.name AS status_name, os.color AS status_color,
                 oi.product_id, oi.quantity, oi.size_name,
                 p.name AS product_name, p.price AS product_price
          FROM orders o
          JOIN order_items oi ON o.id = oi.order_id
          JOIN users u ON o.user_id = u.id
          JOIN products p ON oi.product_id = p.id AND p.deleted_at IS NULL
          JOIN order_status os ON o.status_id = os.id
          WHERE 1=1";

// Status-Filter
if ($statusFilter) {
    $query .= " AND os.name = :status";
}

// Klassen-Filter
if ($classFilter) {
    $query .= " AND u.class_name = :class";
}

// Sortierung nach Bestellnummer, falls keine Gruppierung
if ($groupBy === 'none') {
    $query .= " ORDER BY o.id DESC";
}

// Paginierung
$query .= " LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($query);
if ($statusFilter) $stmt->bindValue(':status', $statusFilter, PDO::PARAM_STR);
if ($classFilter) $stmt->bindValue(':class', $classFilter, PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Umstrukturieren der Daten
$result = [];
foreach ($orders as $order) {
    $orderId = $order['order_id'];
    
    // Falls Bestellung noch nicht existiert, hinzufügen
    if (!isset($result[$orderId])) {
        $result[$orderId] = [
            'order_id' => $orderId,
            'user_id' => $order['user_id'],
            'created_at' => $order['created_at'],
            'total_price' => $order['total_price'],
            'email' => $order['email'],
            'class_name' => $order['class_name'],
            'status_name' => $order['status_name'],
            'status_color' => $order['status_color'],
            'products' => []
        ];
    }

    // Produkt zu Bestellung hinzufügen
    $result[$orderId]['products'][] = [
        'product_id' => $order['product_id'],
        'name' => $order['product_name'],
        'size' => $order['size_name'] ?? 'N/A',
        'quantity' => $order['quantity'],
        'price' => $order['product_price']
    ];
}

// Falls Gruppierung benötigt wird, neu organisieren
$finalResult = [];
if ($groupBy === 'user') {
    foreach ($result as $order) {
        $userId = $order['user_id'];
        if (!isset($finalResult[$userId])) {
            $finalResult[$userId] = [
                'email' => $order['email'],
                'class_name' => $order['class_name'],
                'total_spent' => 0,
                'orders' => []
            ];
        }
        $finalResult[$userId]['total_spent'] += $order['total_price'];
        $finalResult[$userId]['orders'][] = $order;
    }
} elseif ($groupBy === 'class') {
    foreach ($result as $order) {
        $className = $order['class_name'] === 'teacher' ? 'Lehrer' : $order['class_name'];
        if (!isset($finalResult[$className])) {
            $finalResult[$className] = [
                'total_spent' => 0,
                'users' => []
            ];
        }
        $finalResult[$className]['total_spent'] += $order['total_price'];
        $finalResult[$className]['users'][$order['user_id']]['email'] = $order['email'];
        $finalResult[$className]['users'][$order['user_id']]['total_spent'] =
            ($finalResult[$className]['users'][$order['user_id']]['total_spent'] ?? 0) + $order['total_price'];
    }
} else {
    $finalResult = array_values($result);
}

// JSON zurückgeben
header('Content-Type: application/json');
echo json_encode(array_values($finalResult)); // Gebe nur die Daten als Array zurück
?>