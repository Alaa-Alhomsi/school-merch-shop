<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    header('Location: shop.php');
    exit;
}

require_once 'db.php';

// Status aus der Datenbank abrufen
$statusQuery = "SELECT * FROM order_status ORDER BY id";
$statusStmt = $pdo->query($statusQuery);
$orderStatuses = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Bestellungen V2</title>
    <link href="/css/output.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@1.6.1"></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <?php include 'navbar.php'; ?>
    
    <main class="flex-grow container mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Bestellungen verwalten</h1>

        <div class="flex flex-wrap gap-4 mb-6">
            <div class="w-full md:w-auto">
                <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-2">Status Filter:</label>
                <select id="statusFilter" class="w-full md:w-64 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" hx-get="admin_panel_orders.php" hx-target="#results" hx-trigger="change">
                    <option value="">Alle Status</option>
                    <?php foreach ($orderStatuses as $status): ?>
                        <option value="<?= $status['id'] ?>"><?= htmlspecialchars($status['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="w-full md:w-auto">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Suche:</label>
                <input type="text" id="search" class="w-full md:w-64 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" placeholder="Suchen..." hx-get="admin_panel_orders.php" hx-target="#results" hx-trigger="keyup changed delay:500ms">
            </div>
        </div>

        <div id="results" class="bg-white shadow overflow-hidden sm:rounded-lg mb-8" hx-get="admin_panel_orders.php" hx-target="#results" hx-trigger="load">
            <!-- Ergebnisse werden hier dynamisch eingefügt -->
        </div>

        <div class="flex justify-center">
            <button id="loadMore" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600" hx-get="admin_panel_orders.php" hx-target="#results" hx-trigger="click" hx-params="{'offset': document.querySelectorAll('#results .order-row').length, 'search': document.getElementById('search').value, 'status': document.getElementById('statusFilter').value}">
                Mehr laden
            </button>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
