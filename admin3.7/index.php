<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    header('Location: ../shop.php');
    exit;
}

require_once '../db.php';

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
    <!-- HTMX einbinden -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <!-- HTMX Erweiterungen -->
    <script src="https://unpkg.com/htmx.org/dist/ext/json-enc.js"></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <?php include '../navbar.php'; ?>
    
    <main class="flex-grow container mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Bestellungen verwalten</h1>

        <div class="flex flex-wrap gap-4 mb-6">
            <div class="w-full md:w-auto">
                <label for="grouping" class="block text-sm font-medium text-gray-700 mb-2">Gruppierung:</label>
                <select id="grouping" name="grouping" class="w-full md:w-64 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        hx-get="admin_results.php"
                        hx-trigger="change"
                        hx-target="#results"
                        hx-include="#statusFilter, #search">
                    <option value="user" selected>Nach Benutzer</option>
                    <option value="product">Nach Produkt</option>
                    <option value="class">Nach Klasse</option>
                </select>
            </div>

            <div class="w-full md:w-auto">
                <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-2">Status Filter:</label>
                <select id="statusFilter" name="statusFilter" class="w-full md:w-64 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        hx-get="admin_results.php"
                        hx-trigger="change"
                        hx-target="#results"
                        hx-include="#grouping, #search">
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
                <input type="text" id="search" name="search" class="w-full md:w-64 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" 
                       placeholder="Suchen..."
                       hx-get="admin_results.php"
                       hx-trigger="keyup changed delay:500ms"
                       hx-target="#results"
                       hx-include="#grouping, #statusFilter">
            </div>

            <div class="w-full md:w-auto flex items-end">
                <button class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50"
                        hx-get="generate_excel.php"
                        hx-include="#grouping, #statusFilter, #search"
                        hx-trigger="click">
                    Excel-Bericht
                </button>
            </div>
        </div>

        <!-- Initialer Ladeindikator -->
        <div id="loading" class="text-center py-4">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-500 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Daten werden geladen...
        </div>

        <!-- Ergebniscontainer für HTMX -->
        <div id="results" class="bg-white shadow overflow-hidden sm:rounded-lg mb-8"
             hx-get="admin_results.php?grouping=user" 
             hx-trigger="load"
             hx-indicator="#loading">
            <!-- Ergebnisse werden hier dynamisch eingefügt -->
        </div>

        <!-- Benachrichtigungsbox für Erfolgs-/Fehlermeldungen -->
        <div id="notification" class="hidden fixed top-4 right-4 p-4 rounded shadow-lg text-white" 
             hx-swap-oob="true"></div>

        <!-- Status-Modal -->
        <div id="statusModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <h3 class="text-lg font-medium mb-4">Status ändern</h3>
                <form hx-post="update_order_status.php" 
                      hx-target="#notification" 
                      hx-swap="outerHTML"
                      hx-on::after-request="document.getElementById('statusModal').classList.add('hidden'); 
                                          document.getElementById('results').setAttribute('hx-get', 'admin_results.php?' + 
                                          new URLSearchParams({
                                              grouping: document.getElementById('grouping').value,
                                              statusFilter: document.getElementById('statusFilter').value,
                                              search: document.getElementById('search').value
                                          }));
                                          document.getElementById('results').setAttribute('hx-trigger', 'load');
                                          htmx.process(document.getElementById('results'));">
                    <input type="hidden" id="currentOrderId" name="orderId">

                    <select id="newStatus" name="statusId" class="w-full mb-4 px-3 py-2 border rounded-md">
                        <?php foreach ($orderStatuses as $status): ?>
                            <option value="<?= $status['id'] ?>" data-color="<?= $status['color'] ?>">
                                <?= htmlspecialchars($status['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="flex justify-end space-x-2">
                        <button type="button" id="cancelStatusChange" 
                                class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
                                onclick="document.getElementById('statusModal').classList.add('hidden')">
                            Abbrechen
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            Speichern
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    
    <script>
        // Minimaler JavaScript-Code für das Status-Modal
        document.body.addEventListener('click', function(e) {
            const statusButton = e.target.closest('[data-order-id]');
            if (statusButton) {
                const orderId = statusButton.dataset.orderId;
                document.getElementById('currentOrderId').value = orderId;
                document.getElementById('statusModal').classList.remove('hidden');
            }
        });

        // Zeige Benachrichtigung an und blende sie nach 3 Sekunden aus
        htmx.on('htmx:afterSwap', function(event) {
            if (event.detail.target.id === 'notification' && !event.detail.target.classList.contains('hidden')) {
                setTimeout(function() {
                    event.detail.target.classList.add('hidden');
                }, 3000);
            }
        });
    </script>
</body>
</html>