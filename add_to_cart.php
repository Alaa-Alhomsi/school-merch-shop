<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

define('MAX_CART_VALUE', 75);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $size_name = isset($_POST['size_name']) ? $_POST['size_name'] : null;

    if ($product_id > 0 && $quantity > 0) {
        $stmt = $pdo->prepare("SELECT p.*, c.allows_sizes FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            if ($product['allows_sizes'] && $size_name === null) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Bitte wählen Sie eine Größe aus'
                ]);
                exit;
            }

            if ($product['is_sold_out']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Dieses Produkt ist ausverkauft und kann nicht in den Warenkorb gelegt werden.'
                ]);
                exit;
            }

            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            $current_cart_value = array_sum(array_map(function($item) {
                return $item['price'] * $item['quantity'];
            }, $_SESSION['cart']));

            $new_cart_value = $current_cart_value + ($product['price'] * $quantity);

            if ($new_cart_value <= MAX_CART_VALUE) {
                $cart_item_key = $product_id . ($size_name ? '_' . $size_name : '');
                if (isset($_SESSION['cart'][$cart_item_key])) {
                    $_SESSION['cart'][$cart_item_key]['quantity'] += $quantity;
                } else {
                    $_SESSION['cart'][$cart_item_key] = [
                        'id' => $product_id,
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'quantity' => $quantity,
                        'size_name' => $size_name
                    ];
                }

                $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));

                echo json_encode([
                    'success' => true,
                    'message' => 'Warenkorb wurde aktualisiert',
                    'cartCount' => $cartCount
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Der maximale Warenkorbwert von €' . MAX_CART_VALUE . ' wurde überschritten'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Produkt nicht gefunden'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Ungültige Produkt-ID oder Menge. Produkt-ID: ' . $product_id . ', Menge: ' . $quantity . ', Größe-ID: ' . $size_name
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Ungültige Anfrage'
    ]);
}