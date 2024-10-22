<?php
session_start();
require_once 'db.php';

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Überprüfen, ob es sich um eine POST-Anfrage handelt
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cart)) {
    $_SESSION['order_error'] = "Ihr Warenkorb ist leer.";
    header('Location: cart.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Neue Bestellung erstellen
    $stmt = $pdo->prepare("INSERT INTO orders (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    $order_id = $pdo->lastInsertId();

    // Bestellpositionen hinzufügen
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, size_name) VALUES (?, ?, ?, ?)");

    foreach ($cart as $cart_item_key => $item) {
        list($product_id, $size_name) = explode('_', $cart_item_key . '_');
        $quantity = $item['quantity'];

        $stmt->execute([$order_id, $product_id, $quantity, $size_name ?: null]);
    }

    // Transaktion abschließen
    $pdo->commit();

    // Warenkorb leeren
    unset($_SESSION['cart']);

    $_SESSION['order_success'] = "Ihre Bestellung wurde erfolgreich aufgegeben.";
    header('Location: order_confirmation.php?order_id=' . $order_id);
    exit;

} catch (Exception $e) {
    // Bei einem Fehler die Transaktion rückgängig machen
    $pdo->rollBack();
    $_SESSION['order_error'] = "Es gab ein Problem bei der Verarbeitung Ihrer Bestellung. Bitte versuchen Sie es später erneut.";
    header('Location: cart.php');
    exit;
}
?>
