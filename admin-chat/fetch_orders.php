<?php
include '../db.php';

$groupBy = $_GET['group_by'] ?? 'user';
$search = $_GET['search'] ?? '';

// Grundstruktur
$groupFields = [
    'user' => 'CONCAT(u.first_name, " ", u.last_name)',
    'product' => 'p.name',
    'order' => 'o.id'
];

$groupLabel = [
    'user' => 'Benutzer',
    'product' => 'Produkt',
    'order' => 'Bestellnummer'
];

if (!isset($groupFields[$groupBy])) {
    echo "Ungültige Gruppierung.";
    exit;
}

$groupField = $groupFields[$groupBy];

$sql = "
SELECT 
    $groupField AS group_label,
    SUM(oi.quantity) AS total_quantity,
    SUM(oi.quantity * oi.price) AS total_price
FROM order_items oi
JOIN orders o ON oi.order_id = o.id
JOIN users u ON o.user_id = u.id
JOIN products p ON oi.product_id = p.id
WHERE
    u.first_name LIKE ? OR
    u.last_name LIKE ? OR
    p.name LIKE ? OR
    o.id LIKE ?
GROUP BY group_label
ORDER BY group_label ASC
";

$stmt = $conn->prepare($sql);
$likeSearch = "%$search%";
$stmt->bind_param("ssss", $likeSearch, $likeSearch, $likeSearch, $likeSearch);
$stmt->execute();
$result = $stmt->get_result();

echo "<table>";
echo "<tr><th>{$groupLabel[$groupBy]}</th><th>Menge</th><th>Gesamtpreis (€)</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['group_label']}</td>
        <td>{$row['total_quantity']}</td>
        <td>" . number_format($row['total_price'], 2, ',', '.') . "</td>
    </tr>";
}

echo "</table>";

$conn->close();
?>
