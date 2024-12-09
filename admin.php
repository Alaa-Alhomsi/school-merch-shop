<?php
session_start();
require_once 'db.php';

// Überprüfen, ob der Benutzer ein Admin ist
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Bestellungen abrufen
$stmt = $pdo->prepare("
    SELECT o.id AS order_id, o.created_at AS order_date, o.total_price, os.status_name
    FROM orders o
    JOIN order_status os ON o.status_id = os.id
    ORDER BY o.created_at DESC
");
$stmt->execute();
$orders = $stmt->fetchAll();

// Stornierungsanfragen abrufen
$cancellationRequests = [];
foreach ($orders as $order) {
    if ($order['status_name'] === 'Stornierung beantragt') {
        $cancellationRequests[] = $order;
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Bestellungen</title>
    <link href="/css/output.css" rel="stylesheet">
    <script src="script.js" defer></script>
</head>
<body>
    <h1>Bestellungen</h1>
    <table>
        <thead>
            <tr>
                <th>Bestellnummer</th>
                <th>Datum</th>
                <th>Gesamtpreis</th>
                <th>Status</th>
                <th>Aktion</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo $order['order_id']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td><?php echo number_format($order['total_price'], 2, ',', '.') . ' €'; ?></td>
                    <td><?php echo htmlspecialchars($order['status_name']); ?></td>
                    <td>
                        <?php if ($order['status_name'] === 'Stornierung beantragt'): ?>
                            <form method="POST" action="process_cancellation.php">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <button type="submit" name="approve">Genehmigen</button>
                                <button type="submit" name="deny">Ablehnen</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Stornierungsanfragen</h2>
    <table>
        <thead>
            <tr>
                <th>Bestellnummer</th>
                <th>Datum</th>
                <th>Gesamtpreis</th>
                <th>Status</th>
                <th>Aktion</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cancellationRequests as $request): ?>
                <tr>
                    <td><?php echo $request['order_id']; ?></td>
                    <td><?php echo $request['order_date']; ?></td>
                    <td><?php echo number_format($request['total_price'], 2, ',', '.') . ' €'; ?></td>
                    <td><?php echo htmlspecialchars($request['status_name']); ?></td>
                    <td>
                        <form method="POST" action="process_cancellation.php">
                            <input type="hidden" name="order_id" value="<?php echo $request['order_id']; ?>">
                            <button type="submit" name="approve">Genehmigen</button>
                            <button type="submit" name="deny">Ablehnen</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
