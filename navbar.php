<?php
// Start session
session_start();

// Function to check if the user is logged in
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Function to check if the logged-in user is an admin
function isAdmin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] == true;
}

// Fügen Sie diese Zeilen am Anfang der Datei hinzu, um den Admin-Status zu überprüfen
/*var_dump($_SESSION);
echo "Is Admin: " . (isAdmin() ? "Yes" : "No");*/

// Funktion zum Zählen der Artikel im Warenkorb
function getCartItemCount() {
    if (isset($_SESSION['cart'])) {
        return array_sum(array_column($_SESSION['cart'], 'quantity'));
    }
    return 0;
}

$cartItemCount = getCartItemCount();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="js/script.js" defer></script>
    <style>

        .cart-count {
            display: inline-block;
            background-color: #EF4444;
            color: white;
            border-radius: 9999px;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: 0.25rem;
            vertical-align: middle;
        }

        .dropdown-menu {
            z-index: 1000; /* Erhöhen Sie diesen Wert bei Bedarf */
        }
    </style>
</head>
<body>
    <nav class="bg-gray-800" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="index.php" class="text-white font-bold text-xl">SchulMerch</a>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Home</a>
                            <a href="shop.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Shop</a>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                     <a href="cart.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                                <?php include 'svg/cart.svg'; ?>
                                <span class="ml-1">Warenkorb</span>
                                <span class="cart-count bg-red-500 text-white rounded-full px-2 py-1 text-xs ml-1" style="display: none;">0</span>
                            </a>
                        <?php if (isLoggedIn()): ?>
                            <div class="relative group">
                                <button class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                                    <?php include 'svg/user.svg'; ?>
                                    Profil
                                </button>
                                <div class="dropdown-menu absolute right-0 mt-0 w-48 bg-white rounded-md shadow-lg hidden group-hover:block">
                                    <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                                    <?php if (isAdmin()): ?>
                                        <a href="admin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admin Panel</a>
                                        <a href="manage_products.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Produkte verwalten</a>
                                    <?php endif; ?>
                                    <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="register.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Registrieren</a>
                            <a href="login.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium ml-2">Login</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="-mr-2 flex md:hidden">
                    <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div :class="{'block': open, 'hidden': !open}" class="hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Home</a>
                <a href="shop.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Shop</a>
                <a href="cart.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Warenkorb</a>
                <?php if (isLoggedIn()): ?>
                <a href="profile.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Profil</a>
                    <?php if (isAdmin()): ?>
                        <a href="admin.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Admin Panel</a>
                        <a href="manage_products.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Produkte verwalten</a>
                    <?php endif; ?>
                    <a href="logout.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Logout</a>
                <?php else: ?>
                    <a href="register.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Registrieren</a>
                    <a href="login.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</body>
</html>