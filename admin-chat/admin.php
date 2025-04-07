<?php include '../db.php'; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Adminbereich – Bestellungen</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/htmx.org@1.9.2"></script>
</head>
<body>
    <div class="container">
        <h1>Adminbereich</h1>

        <div class="controls">
            <label for="group_by">Gruppieren nach:</label>
            <select id="group_by" name="group_by"
                hx-get="fetch_orders.php"
                hx-target="#results"
                hx-trigger="change"
                hx-include="#search_input">
                <option value="user">Benutzer</option>
                <option value="product">Produkt</option>
                <option value="order">Bestellung</option>
            </select>

            <input type="text" id="search_input" name="search"
                placeholder="Suche..."
                hx-get="fetch_orders.php"
                hx-target="#results"
                hx-trigger="keyup changed delay:500ms"
                hx-include="[name=group_by]">
        </div>

        <div id="results" hx-get="fetch_orders.php" hx-trigger="load"></div>
    </div>
</body>
</html>
