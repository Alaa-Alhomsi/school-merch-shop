<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

require_once 'db.php';

// Parameter abrufen
$grouping = isset($_GET['grouping']) ? $_GET['grouping'] : 'user';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$statusFilter = isset($_GET['statusFilter']) ? $_GET['statusFilter'] : '';

// Daten aus der Datenbank abrufen basierend auf Gruppierung
function getGroupedData($pdo, $grouping, $search, $statusFilter) {
    // Hier würden Sie die Daten aus der Datenbank abrufen
    // Dies ist ein Platzhalter für Ihre tatsächliche Datenbankabfrage
    
    // Benutzer-Gruppierung
    if ($grouping === 'user') {
        $params = [];
        $sql = "SELECT o.id as order_id, o.date, u.id as user_id, u.email, c.name as class_name, 
                      os.id as status_id, os.name as status_name, os.color as status_color,
                      SUM(oi.price * oi.quantity) as total_spent
               FROM orders o
               JOIN users u ON o.user_id = u.id
               JOIN order_items oi ON o.id = oi.order_id
               JOIN order_status os ON o.status_id = os.id
               LEFT JOIN classes c ON u.class_id = c.id
               WHERE 1=1";
        
        if (!empty($search)) {
            $sql .= " AND (u.email LIKE ?)";
            $params[] = "%$search%";
        }
        
        if (!empty($statusFilter)) {
            $sql .= " AND o.status_id = ?";
            $params[] = $statusFilter;
        }
        
        $sql .= " GROUP BY o.id
                 ORDER BY u.email, o.date DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Produkte für jede Bestellung abrufen
        $result = [];
        foreach ($orders as $order) {
            // Produkte für diese Bestellung abrufen
            $productSql = "SELECT p.name as product_name, oi.size, oi.quantity
                          FROM order_items oi
                          JOIN products p ON oi.product_id = p.id
                          WHERE oi.order_id = ?";
            $productStmt = $pdo->prepare($productSql);
            $productStmt->execute([$order['order_id']]);
            $products = $productStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $order['products'] = $products;
            
            // Nach Benutzer gruppieren
            $userId = $order['user_id'];
            if (!isset($result[$userId])) {
                $result[$userId] = [
                    'email' => $order['email'],
                    'class_name' => $order['class_name'],
                    'total_spent' => 0,
                    'orders' => []
                ];
            }
            
            $result[$userId]['orders'][] = $order;
            $result[$userId]['total_spent'] += $order['total_spent'];
        }
        
        return $result;
    }
    
    // Produkt-Gruppierung
    else if ($grouping === 'product') {
        // Implementierung für Produkt-Gruppierung...
        // Ähnliche Struktur wie oben, aber gruppiert nach Produkten
        
        // Für dieses Beispiel ein leeres Array zurückgeben
        return [];
    }
    
    // Klassen-Gruppierung
    else if ($grouping === 'class') {
        // Implementierung für Klassen-Gruppierung...
        // Ähnliche Struktur wie oben, aber gruppiert nach Klassen
        
        // Für dieses Beispiel ein leeres Array zurückgeben
        return [];
    }
    
    return [];
}

// Daten abrufen
$groupedData = getGroupedData($pdo, $grouping, $search, $statusFilter);

// HTML basierend auf Gruppierung generieren
switch ($grouping) {
    case 'user':
        // User HTML
        ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Benutzer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klasse</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bestellungen & Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gesamtausgaben</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($groupedData)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Keine Daten gefunden</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($groupedData as $userId => $userData): ?>
                            <tr>
                                <td class="px-6 py-4"><?= htmlspecialchars($userData['email']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($userData['class_name']) ?></td>
                                <td class="px-6 py-4">
                                    <div class="space-y-2">
                                        <?php foreach ($userData['orders'] as $order): ?>
                                            <div class="flex flex-col gap-1">
                                                <div class="flex items-center gap-2">
                                                    <span>Bestellung #<?= $order['order_id'] ?> (<?= date('d.m.Y', strtotime($order['date'])) ?>)</span>
                                                    <button 
                                                        type="button"
                                                        data-order-id="<?= $order['order_id'] ?>"
                                                        class="px-3 py-1 rounded text-white text-sm"
                                                        style="background-color: <?= $order['status_color'] ?>">
                                                        <?= htmlspecialchars($order['status_name']) ?>
                                                    </button>
                                                </div>
                                                <div class="ml-4">
                                                    <strong>Produkte:</strong>
                                                    <ul class="list-disc list-inside">
                                                        <?php foreach ($order['products'] as $product): ?>
                                                            <li><?= htmlspecialchars($product['product_name']) ?> 
                                                                (Größe: <?= htmlspecialchars($product['size']) ?>, 
                                                                Menge: <?= $product['quantity'] ?>)
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">€<?= number_format($userData['total_spent'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
        break;
        
    case 'product':
        // Produkt HTML
        ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produkt</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Größe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gesamtverkäufe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Käufer</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            <!-- Hier Produkt-Daten anzeigen -->
                            <?php if (empty($groupedData)): ?>
                                Keine Daten gefunden
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
        break;
        
    case 'class':
        // Klassen HTML
        ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klasse</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gesamtausgaben</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Benutzer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produkte</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            <!-- Hier Klassen-Daten anzeigen -->
                            <?php if (empty($groupedData)): ?>
                                Keine Daten gefunden
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
        break;
}