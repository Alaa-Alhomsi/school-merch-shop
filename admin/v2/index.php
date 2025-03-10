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
    <title>Admin - Bestellungen</title>
    <link href="/css/output.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@1.6.1"></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <?php include 'navbar.php'; ?>
    
    <main class="flex-grow container mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Bestellungen verwalten</h1>

        <div class="flex flex-wrap gap-4 mb-6">
            <div class="w-full md:w-auto">
                <label for="grouping" class="block text-sm font-medium text-gray-700 mb-2">Gruppierung:</label>
                <select id="grouping" class="w-full md:w-64" hx-get="admin_panel.php" hx-target="#results" hx-trigger="change">
                    <option value="user" selected>Nach Benutzer</option>
                    <option value="product">Nach Produkt</option>
                    <option value="class">Nach Klasse</option>
                </select>
            </div>

            <div class="w-full md:w-auto">
                <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-2">Status Filter:</label>
                <select id="statusFilter" class="w-full md:w-64" hx-get="admin_panel.php" hx-target="#results" hx-trigger="change">
                    <option value="">Alle Status</option>
                    <?php foreach ($orderStatuses as $status): ?>
                        <option value="<?= $status['id'] ?>"><?= htmlspecialchars($status['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="w-full md:w-auto">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Suche:</label>
                <input type="text" id="search" class="w-full md:w-64" placeholder="Suchen..." hx-get="admin_panel.php" hx-target="#results" hx-trigger="keyup changed delay:500ms">
            </div>

            <div class="w-full md:w-auto flex items-end">
                <button id="downloadExcel" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600" hx-get="generate_excel.php" hx-target="body" hx-trigger="click">
                    Excel-Bericht
                </button>
            </div>
        </div>

        <div id="results" class="bg-white shadow overflow-hidden sm:rounded-lg mb-8" hx-get="admin_panel.php" hx-target="#results" hx-trigger="load">
            <!-- Ergebnisse werden hier dynamisch eingefügt -->
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>