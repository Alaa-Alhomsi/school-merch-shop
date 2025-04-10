<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Überprüfe Admin-Berechtigung
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    echo json_encode(['success' => false, 'message' => 'Nicht autorisiert']);
    exit;
}

// Hole die Produkt-ID
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;

if ($product_id) {
    try {
        // Beginne Transaktion
        $pdo->beginTransaction();

        // Lösche das Produkt, aber behalte die Bestellungen
        $stmt = $pdo->prepare("UPDATE products SET deleted_at = NOW() WHERE id = ?");
        $success = $stmt->execute([$product_id]);

        if ($success) {
            $pdo->commit();
            echo json_encode(['success' => true]);
        } else {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Produkt konnte nicht gelöscht werden']);
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Datenbankfehler: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Keine Produkt-ID angegeben']);
} 