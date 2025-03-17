<?php
require_once 'db.php';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$statusFilter = isset($_GET['status']) ? (int)$_GET['status'] : null;

$query = "SELECT o.*, u.firstname, u.lastname, p.name AS product_name, os.name AS status_name 
          FROM orders o
          JOIN users u ON o.user_id = u.id
          JOIN products p ON o.product_id = p.id
          JOIN order_status os ON o.status_id = os.id";

$params = [];
if ($statusFilter !== null) {
    $query .= " WHERE o.status_id = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$queryCount = "SELECT COUNT(*) FROM orders" . ($statusFilter !== null ? " WHERE status_id = ?" : "");
$stmtCount = $pdo->prepare($queryCount);
$stmtCount->execute($statusFilter !== null ? [$statusFilter] : []);
$totalOrders = $stmtCount->fetchColumn();
$totalPages = ceil($totalOrders / $limit);
?>

<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kunde</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produkt</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Datum</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        <?php foreach ($orders as $order): ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap"> <?= htmlspecialchars($order['firstname'] . ' ' . $order['lastname']) ?> </td>
                <td class="px-6 py-4 whitespace-nowrap"> <?= htmlspecialchars($order['product_name']) ?> </td>
                <td class="px-6 py-4 whitespace-nowrap"> <?= htmlspecialchars($order['status_name']) ?> </td>
                <td class="px-6 py-4 whitespace-nowrap"> <?= htmlspecialchars($order['created_at']) ?> </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if ($totalPages > 1): ?>
    <div class="mt-4 flex justify-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <button hx-get="admin_orders.php?page=<?= $i ?>" hx-target="#results" 
                    class="px-4 py-2 mx-1 border rounded <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-white' ?>">
                <?= $i ?>
            </button>
        <?php endfor; ?>
    </div>
<?php endif; ?>
