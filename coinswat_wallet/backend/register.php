<?php
require_once '../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Ungültige E-Mail-Adresse");
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Prüfen, ob die E-Mail bereits existiert
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            die("Diese E-Mail ist bereits registriert");
        }

        // Benutzer registrieren
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
        $stmt->execute([$email, $hashedPassword]);
        echo "Registrierung erfolgreich!";
    } catch (PDOException $e) {
        die("Fehler bei der Registrierung: " . $e->getMessage());
    }
}
?>
