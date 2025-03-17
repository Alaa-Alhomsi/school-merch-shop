<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    header('Location: shop.php');
    exit;
}

require_once 'db.php';

// Produkte abrufen
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.deleted_at IS NULL";
$products = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Kategorien abrufen
$categories = $pdo->query("SELECT * FROM categories WHERE deleted_at = NULL")->fetchAll(PDO::FETCH_ASSOC);

// Produkt hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $is_sold_out = isset($_POST['is_sold_out']) ? 1 : 0;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image'];
        $image_name = uniqid() . '-' . basename($image['name']);
        $target_path = 'images/' . $image_name;

        if (move_uploaded_file($image['tmp_name'], $target_path)) {
            $insert_query = "INSERT INTO products (name, description, price, image, category_id, is_sold_out) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($insert_query);
            $stmt->execute([$name, $description, $price, $image_name, $category_id, $is_sold_out]);
            header('Location: manage_products.php');
            exit;
        } else {
            echo "Fehler beim Hochladen des Bildes.";
        }
    }
}

// Produkt bearbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $product_id = (int)$_POST['product_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $is_sold_out = isset($_POST['is_sold_out']) ? 1 : 0;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image'];
        $image_name = uniqid() . '-' . basename($image['name']);
        $target_path = 'images/' . $image_name;
        if (move_uploaded_file($image['tmp_name'], $target_path)) {
            $update_query = "UPDATE products SET name = ?, description = ?, price = ?, image = ?, category_id = ?, is_sold_out = ? WHERE id = ?";
            $stmt = $pdo->prepare($update_query);
            $stmt->execute([$name, $description, $price, $image_name, $category_id, $is_sold_out, $product_id]);
        } else {
            echo "Fehler beim Hochladen des Bildes.";
        }
    } else {
        $update_query = "UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, is_sold_out = ? WHERE id = ?";
        $stmt = $pdo->prepare($update_query);
        $stmt->execute([$name, $description, $price, $category_id, $is_sold_out, $product_id]);
    }

    header('Location: manage_products.php');
    exit;
}

// Produkt löschen
if (isset($_GET['delete_product'])) {
    $product_id = (int)$_GET['delete_product'];

    // Zuerst das Bild des Produkts abrufen
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Bildpfad definieren
        $image_path = 'images/' . $product['image'];
        $deleted_path = 'images/deleted/' . $product['image'];

        // Bild in den 'deleted' Ordner verschieben
        if (file_exists($image_path)) {
            rename($image_path, $deleted_path);
        }

        // Produkt als gelöscht markieren
        $delete_query = "UPDATE products SET deleted_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($delete_query);
        $stmt->execute([$product_id]);
    }

    header('Location: manage_products.php');
    exit;
}

// Kategorie hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['category_name']);
    $allows_sizes = isset($_POST['allows_sizes']) ? 1 : 0;
    $insert_query = "INSERT INTO categories (name, allows_sizes) VALUES (?, ?)";
    $stmt = $pdo->prepare($insert_query);
    $stmt->execute([$name, $allows_sizes]);
    header('Location: manage_products.php');
    exit;
}

// Kategorie bearbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['category_name']);
    $allows_sizes = isset($_POST['allows_sizes']) ? 1 : 0;
    $update_query = "UPDATE categories SET name = ?, allows_sizes = ? WHERE id = ?";
    $stmt = $pdo->prepare($update_query);
    $stmt->execute([$name, $allows_sizes, $category_id]);
    header('Location: manage_products.php');
    exit;
}

// Kategorie löschen
if (isset($_GET['delete_category'])) {
    $category_id = (int)$_GET['delete_category'];

    // Überprüfen, ob aktive Produkte in dieser Kategorie vorhanden sind
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND deleted_at IS NULL");
    $stmt->execute([$category_id]);
    $active_products_count = $stmt->fetchColumn();

    if ($active_products_count > 0) {
        // Warnung anzeigen, wenn aktive Produkte vorhanden sind
        echo "<script>alert('Diese Kategorie kann nicht gelöscht werden, da aktive Produkte vorhanden sind.');</script>";
    } else {
        // Kategorie als gelöscht markieren
        $delete_query = "UPDATE categories SET deleted_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($delete_query);
        $stmt->execute([$category_id]);
    }

    header('Location: manage_products.php');
    exit;
}

// Produkt oder Kategorie zum Bearbeiten auswählen
$edit_product = null;
$edit_category = null;

if (isset($_GET['edit_product'])) {
    $product_id = (int)$_GET['edit_product'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_GET['edit_category'])) {
    $category_id = (int)$_GET['edit_category'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produkte und Kategorien verwalten</title>
    <link href="/css/output.css" rel="stylesheet">
    <link rel="stylesheet" href="css/footerConf.css">
</head>
<body class="flex flex-col min-h-screen font-sans">
    <?php include 'navbar.php'; ?>
    <main class="flex-grow">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-8 text-center">Produkte und Kategorien verwalten</h1>
            
            <!-- Produkte verwalten -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-2xl font-semibold mb-4">Produkte</h2>
                
                <!-- Produkt hinzufügen/bearbeiten Formular -->
                <form action="manage_products.php" method="post" enctype="multipart/form-data" class="space-y-4 mb-8">
                    <?php if ($edit_product): ?>
                        <input type="hidden" name="product_id" value="<?= $edit_product['id'] ?>">
                    <?php endif; ?>
                    <input type="text" name="name" placeholder="Produktname" value="<?= $edit_product ? htmlspecialchars($edit_product['name']) : '' ?>" required class="w-full px-3 py-2 border rounded-md">
                    <textarea name="description" placeholder="Beschreibung" required class="w-full px-3 py-2 border rounded-md"><?= $edit_product ? htmlspecialchars($edit_product['description']) : '' ?></textarea>
                    <input type="number" name="price" placeholder="Preis" step="0.01" value="<?= $edit_product ? htmlspecialchars($edit_product['price']) : '' ?>" required class="w-full px-3 py-2 border rounded-md">
                    <select name="category_id" required class="w-full px-3 py-2 border rounded-md">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= ($edit_product && $edit_product['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <!-- Kontrollkästchen für Ausverkauft -->
                    <div>
                        <input type="checkbox" name="is_sold_out" id="is_sold_out" <?= ($edit_product && $edit_product['is_sold_out']) ? 'checked' : '' ?>>
                        <label for="is_sold_out" class="ml-2">Ausverkauft</label>
                    </div>

                    <input type="file" name="image" <?= $edit_product ? '' : 'required' ?> class="w-full">
                    <button type="submit" name="<?= $edit_product ? 'update_product' : 'add_product' ?>" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
                        <?= $edit_product ? 'Produkt aktualisieren' : 'Produkt hinzufügen' ?>
                    </button>
                </form>

                <!-- Produktliste -->
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">Preis</th>
                                <th class="px-4 py-2">Kategorie</th>
                                <th class="px-4 py-2">Bild</th>
                                <th class="px-4 py-2">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($product['name']); ?></td>
                                    <td class="border px-4 py-2">€<?= number_format($product['price'], 2); ?></td>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($product['category_name']); ?></td>
                                    <td class="border px-4 py-2"><img src="images/<?= htmlspecialchars($product['image']); ?>" alt="Produktbild" class="w-24"></td>
                                    <td class="border px-4 py-2">
                                        <a href="manage_products.php?edit_product=<?= $product['id']; ?>" class="text-blue-500 hover:underline">Bearbeiten</a>
                                        <a href="manage_products.php?delete_product=<?= $product['id']; ?>" onclick="return confirm('Sind Sie sicher, dass Sie dieses Produkt löschen möchten?');" class="text-red-500 hover:underline ml-2">Löschen</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Kategorien verwalten -->
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-semibold mb-4">Kategorien</h2>
                
                <!-- Kategorie hinzufügen/bearbeiten Formular -->
                <form action="manage_products.php" method="post" class="space-y-4 mb-8">
                    <?php if ($edit_category): ?>
                        <input type="hidden" name="category_id" value="<?= $edit_category['id'] ?>">
                    <?php endif; ?>
                    <input type="text" name="category_name" placeholder="Kategoriename" value="<?= $edit_category ? htmlspecialchars($edit_category['name']) : '' ?>" required class="w-full px-3 py-2 border rounded-md">
                    <div class="flex items-center">
                        <input type="checkbox" name="allows_sizes" id="allows_sizes" <?= ($edit_category && $edit_category['allows_sizes']) ? 'checked' : '' ?> class="mr-2">
                        <label for="allows_sizes">Größen für diese Kategorie erlauben</label>
                    </div>
                    <button type="submit" name="<?= $edit_category ? 'update_category' : 'add_category' ?>" class="w-full bg-green-500 text-white py-2 px-4 rounded-md hover:bg-green-600">
                        <?= $edit_category ? 'Kategorie aktualisieren' : 'Kategorie hinzufügen' ?>
                    </button>
                </form>

                <!-- Kategorieliste -->
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">Größen erlaubt</th>
                                <th class="px-4 py-2">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($category['name']); ?></td>
                                    <td class="border px-4 py-2"><?= $category['allows_sizes'] ? 'Ja' : 'Nein'; ?></td>
                                    <td class="border px-4 py-2">
                                        <a href="manage_products.php?edit_category=<?= $category['id']; ?>" class="text-blue-500 hover:underline">Bearbeiten</a>
                                        <a href="manage_products.php?delete_category=<?= $category['id']; ?>" onclick="return confirm('Sind Sie sicher, dass Sie diese Kategorie löschen möchten?');" class="text-red-500 hover:underline ml-2">Löschen</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
