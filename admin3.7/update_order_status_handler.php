<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parameter validieren
    $orderId = isset($_POST['orderId']) ? intval($_POST['orderId']) : 0;
    $statusId = isset($_POST['statusId']) ? intval($_POST['statusId']) : 0;
    
    if ($orderId <= 0 || $statusId <= 0) {
        echo '<div id="notification" class="fixed top-4 right-4 p-4 rounded shadow-lg text-white bg-red-500">
                Ungültige Parameter
              </div>';
        exit;
    }
    
    try {
        // Status in der Datenbank aktualisieren
        $stmt = $pdo->prepare("UPDATE orders SET status_id = ? WHERE id = ?");
        $stmt->execute([$statusId, $orderId]);
        
        if ($stmt->rowCount() > 0) {
            // Statusname für die Bestätigung abrufen
            $statusQuery = $pdo->prepare("SELECT name FROM order_status WHERE id = ?");
            $statusQuery->execute([$statusId]);
            $statusName = $statusQuery->fetchColumn();
            
            echo '<div id="notification" class="fixed top-4 right-4 p-4 rounded shadow-lg text-white bg-green-500">
                    Status für Bestellung #' . $orderId . ' auf "' . htmlspecialchars($statusName) . '" geändert
                  </div>';
        } else {
            echo '<div id="notification" class="fixed top-4 right-4 p-4 rounded shadow-lg text-white bg-red-500">
                    Bestellung nicht gefunden oder Status nicht geändert
                  </div>';
        }
    } catch (PDOException $e) {
        echo '<div id="notification" class="fixed top-4 right-4 p-4 rounded shadow-lg text-white bg-red-500">
                Datenbankfehler: ' . htmlspecialchars($e->getMessage()) . '
              </div>';
    }
} else {
    header('HTTP/1.1 405 Method Not Allowed');
    exit;
}