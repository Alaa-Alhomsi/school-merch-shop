<?php
session_start();
require_once 'db.php';

$error = '';
$success = '';

// Klassen aus der Datenbank abrufen
$stmt = $pdo->query("SELECT class_name FROM classes");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $class_name = isset($_POST['class_name']) ? $_POST['class_name'] : null;
    $is_teacher = isset($_POST['is_teacher']) ? 1 : 0;

    // Validierung: Überprüfung der E-Mail-Domain
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@s\.hakmistelbach\.ac\.at$/", $email)) {
        $error = "Nur E-Mail-Adressen mit @s.hakmistelbach.ac.at sind erlaubt.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwörter stimmen nicht überein.";
    } elseif (!$is_teacher && !$class_name) {
        $error = "Bitte wählen Sie eine Klasse aus oder markieren Sie, dass Sie ein Lehrer sind.";
    } else {
        // Passwort verschlüsseln
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prüfen, ob die E-Mail bereits existiert
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Diese E-Mail ist bereits registriert.";
        } else {
            // Neuen Benutzer einfügen
            $verification_code = md5(uniqid("email_verification", true));  // E-Mail-Verifizierungs-Token
            $stmt = $pdo->prepare("INSERT INTO users (email, password, class_name, is_teacher, email_verified, verification_code) VALUES (?, ?, ?, ?, 0, ?)");
            if ($stmt->execute([$email, $hashed_password, $is_teacher ? null : $class_name, $is_teacher, $verification_code])) {
                // E-Mail-Bestätigung senden (PHPMailer empfohlen)
                $verification_link = "https://shop.digbizmistelbach.info/verify.php?email=$email&code=$verification_code";
                $subject = "E-Mail-Bestätigung";
                $message = "Klicken Sie auf diesen Link, um Ihre E-Mail zu bestätigen: $verification_link";
                $headers = "From: noreply@merch.hakmistelbach.ac.at";
                
                mail($email, $subject, $message, $headers);
                
                $success = "Registrierung erfolgreich. Bitte überprüfen Sie Ihre E-Mail zur Bestätigung. Die E-Mail kann bis zu 5 Minuten benötigen, um anzukommen. Bitte prüfen Sie auch Ihren Spam-Ordner. Gesendet wurde eine E-Mail an: $email";
            } else {
                $error = "Fehler bei der Registrierung.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrierung - Schul-Merchandise Shop</title>
    <link href="/css/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body class="h-full">
    <?php include 'navbar.php'; ?>
    
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-md">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Registrieren
                </h2>
            </div>
            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p><?php echo $error; ?></p>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p><?php echo $success; ?></p>
                </div>
            <?php endif; ?>
            <form class="mt-8 space-y-6" action="register.php" method="POST">
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">E-Mail-Adresse</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input id="email" name="email" type="email" autocomplete="email" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm pl-10" placeholder="name@s.hakmistelbach.ac.at">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Passwort</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="password" name="password" type="password" autocomplete="new-password" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm pl-10" placeholder="Passwort">
                        </div>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Passwort bestätigen</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm pl-10" placeholder="Passwort bestätigen">
                        </div>
                    </div>

                    <div>
                        <label for="class_name" class="block text-sm font-medium text-gray-700">Klasse</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-graduation-cap text-gray-400"></i>
                            </div>
                            <select id="class_name" name="class_name" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm pl-10">
                                <option value="">--- Wähle deine Klasse ---</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo htmlspecialchars($class['class_name']); ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="is_teacher" name="is_teacher" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_teacher" class="ml-2 block text-sm text-gray-900">
                            Ich bin Lehrer
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-user-plus text-indigo-500 group-hover:text-indigo-400"></i>
                        </span>
                        Registrieren
                    </button>
                </div>
            </form>
            <div class="text-sm text-center">
                <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500 transition duration-150 ease-in-out">
                    Schon ein Konto? Hier einloggen
                </a>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>