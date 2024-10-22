<?php
// Datenbankverbindung herstellen
$host = 'localhost:3306';  // Host, auf dem die Datenbank läuft
$dbname = 'school_merch2';  // Name der Datenbank
$username = 'Alaa_digbiz2';  // Datenbankbenutzer
$password = 'Alaa123._';  // Datenbankpasswort (leer, falls kein Passwort)

try {
    // Erstellen eines PDO-Objekts für die Verbindung
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Fehler-Modus auf Exception setzen
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Fehlernachricht ausgeben, wenn die Verbindung fehlschlägt
    die("Verbindung zur Datenbank fehlgeschlagen: " . $e->getMessage());
}
?>
