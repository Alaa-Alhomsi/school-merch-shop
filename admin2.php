<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    header('Location: shop.php');
    exit;
}

require_once 'db.php';

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
    <script src="https://unpkg.com/htmx.org@1.9.6"></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <?php include 'navbar.php'; ?>
    
    <main class="flex-grow container mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Bestellungen verwalten</h1>
        
        <div class="flex flex-wrap gap-4 mb-6">
            <select id="grouping" class="form-select">
                <option value="user" selected>Nach Benutzer</option>
                <option value="product">Nach Produkt</option>
                <option value="class">Nach Klasse</option>
            </select>
            
            <select id="statusFilter" class="form-select">
                <option value="">Alle Status</option>
                <?php foreach ($orderStatuses as $status): ?>
                    <option value="<?= $status['id'] ?>"> <?= htmlspecialchars($status['name']) ?> </option>
                <?php endforeach; ?>
            </select>
            
            <input type="text" id="search" placeholder="Suchen..." class="form-input">
            
            <button id="downloadExcel" class="btn bg-green-500">Excel-Bericht</button>
        </div>
        
        <div id="results" class="bg-white shadow overflow-hidden sm:rounded-lg mb-8"
             hx-get="admin_orders.php?page=1" 
             hx-trigger="revealed" 
             hx-swap="beforeend">
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>
