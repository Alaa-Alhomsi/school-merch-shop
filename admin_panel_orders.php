<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once 'db.php';

// Parameter für Pagination
$limit = 10; // Anzahl der Bestellungen pro Anfrage
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// SQL-Abfrage anpassen
$query = "SELECT o.id AS order_id, o.user_id, o.created_at, o.total_price, 
                 u.email, os.name AS status_name, os.color AS status_color
          FROM orders o
          JOIN users u ON o.user_id = u.id
          JOIN order_status os ON o.status_id = os.id
          WHERE (u.email LIKE :search OR o.id LIKE :search)";

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

// HTML für die Bestellungen generieren
$html = '<table class="min-w-full divide-y divide-gray-200"><thead><tr><th>Email</th><th>Bestell-ID</th><th>Gesamt</th><th>Status</th></tr></thead><tbody>';
foreach ($orders as $order) {
    $html .= '<tr class="order-row">';
    $html .= '<td>' . htmlspecialchars($order['email']) . '</td>';
    $html .= '<td>' . htmlspecialchars($order['order_id']) . '</td>';
    $html .= '<td>€' . number_format($order['total_price'], 2) . '</td>';
    $html .= '<td><button class="status-button" data-order-id="' . $order['order_id'] . '" style="background-color: ' . $order['status_color'] . '">' . htmlspecialchars($order['status_name']) . '</button></td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';

// JSON zurückgeben
header('Content-Type: text/html');
echo $html; 