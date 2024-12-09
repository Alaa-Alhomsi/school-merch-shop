<?php
session_start();
header('Content-Type: application/json');

function getCartItemCount() {
    if (isset($_SESSION['cart'])) {
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $stmt = $pdo->prepare("SELECT quantity FROM products WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$item['product_id']]);
            if ($stmt->rowCount() > 0) {
                $count += $item['quantity'];
            }
        }
        return $count;
    }
    return 0;
}

$count = getCartItemCount();

echo json_encode([
    'success' => true,
    'count' => $count
]);
