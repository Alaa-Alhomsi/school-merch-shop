<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    http_response_code(403);
    exit('Nicht autorisiert');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['orderId'] ?? null;
    $statusId = $_POST['statusId'] ?? null;

    if ($orderId && $statusId) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status_id = ? WHERE id = ?");
            $stmt->execute([$statusId, $orderId]);
            
            $stmt = $pdo->prepare("SELECT name, color FROM order_status WHERE id = ?");
            $stmt->execute([$statusId]);
            $newStatus = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'newStatus' => $newStatus
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Datenbankfehler']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Fehlende Parameter']);
    }
} 