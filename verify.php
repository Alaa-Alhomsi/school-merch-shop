<?php
session_start();
require_once 'db.php';

if (isset($_GET['email']) && isset($_GET['code'])) {
    $email = $_GET['email'];
    $code = $_GET['code'];

    // E-Mail und Code prüfen
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND verification_code = ? AND email_verified = 0");
    $stmt->execute([$email, $code]);

    if ($stmt->rowCount() > 0) {
        // Bestätigen der E-Mail
        $update_stmt = $pdo->prepare("UPDATE users SET email_verified = 1, verification_code = NULL WHERE email = ?");
        $update_stmt->execute([$email]);
        
        $_SESSION['verification_success'] = "E-Mail erfolgreich bestätigt! Sie können sich jetzt einloggen.";
        header('Location: login.php');
        exit;
    } else {
        $_SESSION['verification_error'] = "Ungültiger Bestätigungslink oder E-Mail bereits bestätigt.";
        header('Location: login.php');
        exit;
    }
} else {
    $_SESSION['verification_error'] = "Ungültiger Zugriff.";
    header('Location: login.php');
    exit;
}
?>
