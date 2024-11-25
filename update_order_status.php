<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    echo json_encode(['success' => false, 'message' => 'Nicht autorisiert']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['orderId'] ?? null;
    $statusId = $_POST['statusId'] ?? null;

    if ($orderId && $statusId) {
        try {
            // Update der Bestellung
            $stmt = $pdo->prepare("UPDATE orders SET status_id = ? WHERE id = ?");
            $success = $stmt->execute([$statusId, $orderId]);

            if ($success) {
                // Hole den neuen Status für die Antwort
                $stmt = $pdo->prepare("SELECT os.name, os.color 
                                     FROM orders o 
                                     JOIN order_status os ON o.status_id = os.id 
                                     WHERE o.id = ?");
                $stmt->execute([$orderId]);
                $newStatus = $stmt->fetch(PDO::FETCH_ASSOC);

                echo json_encode([
                    'success' => true,
                    'newStatus' => $newStatus
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Datenbankfehler beim Update'
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Datenbankfehler: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Fehlende Parameter'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Ungültige Anfragemethode'
    ]);
} 