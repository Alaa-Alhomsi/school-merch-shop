<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once 'db.php';

// Parameter holen
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10; // Anzahl der Einträge pro Seite
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// SQL-Abfrage anpassen
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
          WHERE (u.email LIKE :search OR p.name LIKE :search)";

if ($statusFilter) {
    $query .= " AND o.status_id = :status";
}

$query .= " ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
if ($statusFilter) {
    $stmt->bindValue(':status', $statusFilter, PDO::PARAM_INT);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// HTML für die Tabelle generieren
$html = '<table class="min-w-full divide-y divide-gray-200"><thead><tr><th>Email</th><th>Klasse</th><th>Produkte</th><th>Gesamt</th><th>Status</th></tr></thead><tbody>';
foreach ($orders as $order) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($order['email']) . '</td>';
    $html .= '<td>' . htmlspecialchars($order['class_name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($order['product_name']) . ' (Menge: ' . $order['quantity'] . ')</td>';
    $html .= '<td>€' . number_format($order['total_price'], 2) . '</td>';
    $html .= '<td><button class="status-button" data-order-id="' . $order['order_id'] . '" style="background-color: ' . $order['status_color'] . '">' . htmlspecialchars($order['status_name']) . '</button></td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';

// JSON zurückgeben
header('Content-Type: application/json');
echo json_encode(['html' => $html]); 