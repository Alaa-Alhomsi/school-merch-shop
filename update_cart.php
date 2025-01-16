<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 0;
    $size_name = isset($data['size_name']) ? $data['size_name'] : null;

    if ($product_id > 0 && $quantity >= 0) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $cart_item_key = $product_id . ($size_name ? '_' . $size_name : '');

        if ($quantity > 0) {
            if (isset($_SESSION['cart'][$cart_item_key])) {
                $_SESSION['cart'][$cart_item_key]['quantity'] = $quantity;
            }
        } else {
            unset($_SESSION['cart'][$cart_item_key]);
        }

        $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));

        // Überprüfen, ob das Produkt ausverkauft ist
        $stmt = $pdo->prepare("SELECT is_sold_out FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product && $product['is_sold_out']) {
            echo json_encode([
                'success' => false,
                'message' => 'Dieses Produkt ist ausverkauft und kann nicht aktualisiert werden.'
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Warenkorb wurde aktualisiert',
            'cartCount' => $cartCount
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Ungültige Produkt-ID oder Menge'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Ungültige Anfrage'
    ]);
}