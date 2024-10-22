<?php
session_start();
header('Content-Type: application/json');

function getCartItemCount() {
    if (isset($_SESSION['cart'])) {
        return array_sum(array_column($_SESSION['cart'], 'quantity'));
    }
    return 0;
}

$count = getCartItemCount();

echo json_encode([
    'success' => true,
    'count' => $count
]);
