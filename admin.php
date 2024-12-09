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
    <link rel="stylesheet" href="css/footerConf.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <?php include 'navbar.php'; ?>
    
    <main class="flex-grow container mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Bestellungen verwalten</h1>

        <div class="flex flex-wrap gap-4 mb-6">
            <div class="w-full md:w-auto">
                <label for="grouping" class="block text-sm font-medium text-gray-700 mb-2">Gruppierung:</label>
                <select id="grouping" class="w-full md:w-64 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="user" selected>Nach Benutzer</option>
                    <option value="product">Nach Produkt</option>
                    <option value="class">Nach Klasse</option>
                </select>
            </div>

            <div class="w-full md:w-auto">
                <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-2">Status Filter:</label>
                <select id="statusFilter" class="w-full md:w-64 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">Alle Status</option>
                    <?php foreach ($orderStatuses as $status): ?>
                        <option value="<?= $status['id'] ?>" <?= $status['name'] === 'Neu' ? 'selected' : '' ?>>
                            <?= htmlspecialchars($status['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="w-full md:w-auto">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Suche:</label>
                <input type="text" id="search" class="w-full md:w-64 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" placeholder="Suchen...">
            </div>

            <div class="w-full md:w-auto flex items-end">
                <button id="downloadExcel" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                    Excel-Bericht
                </button>
            </div>
        </div>

        <div id="results" class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
            <!-- Ergebnisse werden hier dynamisch eingefügt -->
        </div>

        <!-- Status-Modal -->
        <div id="statusModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <h3 class="text-lg font-medium mb-4">Status ändern</h3>
                <select id="newStatus" class="w-full mb-4 px-3 py-2 border rounded-md">
                    <?php foreach ($orderStatuses as $status): ?>
                        <option value="<?= $status['id'] ?>" data-color="<?= $status['color'] ?>">
                            <?= htmlspecialchars($status['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="flex justify-end space-x-2">
                    <button id="cancelStatusChange" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Abbrechen</button>
                    <button id="confirmStatusChange" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Speichern</button>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    
    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeAdmin();
        });
    </script>
</body>
</html>
