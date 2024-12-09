<?php
session_start();
require_once 'db.php';

// Überprüfen, ob der Benutzer ein Admin ist
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'];

    if (isset($_POST['approve'])) {
        // Stornierung genehmigen
        $stmt = $pdo->prepare("UPDATE orders SET status_id = 4 WHERE id = ?"); // 4 = Storniert
        $stmt->execute([$orderId]);
        header("Location: admin.php?message=Stornierung genehmigt");
    } elseif (isset($_POST['deny'])) {
        // Stornierung ablehnen
        $stmt = $pdo->prepare("UPDATE orders SET cancellation_requested = FALSE, cancellation_approved = FALSE WHERE id = ?");
        $stmt->execute([$orderId]);
        header("Location: admin.php?message=Stornierung abgelehnt");
    }
}
?> 