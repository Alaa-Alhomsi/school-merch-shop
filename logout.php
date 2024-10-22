<?php
session_start();

// Alle Session-Daten löschen
session_unset();

// Session beenden
session_destroy();

// Umleitung zur Startseite oder zur Login-Seite
header('Location: index.php');
exit;
?>
