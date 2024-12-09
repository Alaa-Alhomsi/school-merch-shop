<?php
session_start();
require_once 'db.php';

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Hole die Bestellungen des Benutzers
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ?");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Beantragung der Stornierung
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_cancellation'])) {
    $orderId = $_POST['order_id'];
    
    // Überprüfen, ob die Stornierung bereits beantragt wurde
    $stmt = $pdo->prepare("SELECT cancellation_approved FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order && $order['cancellation_approved'] === null) {
        // Stornierungsanfrage stellen
        $stmt = $pdo->prepare("UPDATE orders SET cancellation_requested = TRUE WHERE id = ?");
        $stmt->execute([$orderId]);
        $message = "Stornierungsanfrage erfolgreich gestellt.";
    } else {
        $message = "Stornierung kann nicht beantragt werden.";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link href="/css/output.css" rel="stylesheet">
</head>
<body>
    <h1>Mein Profil</h1>
    
    <?php if (isset($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <h2>Bestellungen</h2>
    <table>
        <tr>
            <th>Bestell-ID</th>
            <th>Status</th>
            <th>Aktion</th>
        </tr>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo htmlspecialchars($order['id']); ?></td>
                <td><?php echo htmlspecialchars($order['status']); ?></td>
                <td>
                    <?php if (!$order['cancellation_requested'] && $order['cancellation_approved'] === null): ?>
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <button type="submit" name="request_cancellation">Stornierung beantragen</button>
                        </form>
                    <?php elseif ($order['cancellation_requested']): ?>
                        <span>Stornierung beantragt</span>
                    <?php elseif ($order['cancellation_approved'] === false): ?>
                        <span>Stornierung abgelehnt</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>