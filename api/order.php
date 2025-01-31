<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

if (!isset($_SESSION['loggedin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if($_SESSION['admin'] != true){
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getOrder($pdo, $_GET['id']);
        } else {
            echo json_encode(['error' => 'Order ID is required']);
        }
        break;
    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteOrder($pdo, $_GET['id']);
        } else {
            echo json_encode(['error' => 'Order ID is required']);
        }
        break;
    case 'PATCH':
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($_GET['id']) && isset($data['status_id'])) {
            updateOrderStatus($pdo, $_GET['id'], $data['status_id']);
        } else {
            echo json_encode(['error' => 'Order ID and status_id are required']);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
}

function getOrder($pdo, $id) {
    $stmt = $pdo->prepare("SELECT o.id AS order_id, o.user_id, o.created_at, o.total_price, o.status_id,
                                  u.email, u.class_name,
                                  oi.product_id, oi.quantity, oi.size_name,
                                  p.name AS product_name, p.price AS product_price,
                                  os.name AS status_name, os.color AS status_color
                           FROM orders o
                           JOIN order_items oi ON o.id = oi.order_id
                           JOIN users u ON o.user_id = u.id
                           JOIN products p ON oi.product_id = p.id AND p.deleted_at IS NULL
                           JOIN order_status os ON o.status_id = os.id
                           WHERE o.id = ?");
    $stmt->execute([$id]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($orderItems) {
        // Struktur für die Bestellung erstellen
        $order = [
            'order_id' => $orderItems[0]['order_id'],
            'user_id' => $orderItems[0]['user_id'],
            'created_at' => $orderItems[0]['created_at'],
            'total_price' => $orderItems[0]['total_price'],
            'status_id' => $orderItems[0]['status_id'],
            'email' => $orderItems[0]['email'],
            'class_name' => $orderItems[0]['class_name'],
            'status_name' => $orderItems[0]['status_name'],
            'status_color' => $orderItems[0]['status_color'],
            'products' => []
        ];

        // Produkte zur Bestellung hinzufügen
        foreach ($orderItems as $item) {
            $order['products'][] = [
                'product_id' => $item['product_id'],
                'name' => $item['product_name'],
                'size' => $item['size_name'] ?? 'N/A',
                'quantity' => $item['quantity'],
                'price' => $item['product_price']
            ];
        }

        echo json_encode($order);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
    }
}

function deleteOrder($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['message' => 'Order deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete order']);
    }
}

function updateOrderStatus($pdo, $id, $status_id) {
    $stmt = $pdo->prepare("UPDATE orders SET status_id = ? WHERE id = ?");
    if ($stmt->execute([$status_id, $id])) {
        echo json_encode(['message' => 'Order status updated']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update order status']);
    }
}
