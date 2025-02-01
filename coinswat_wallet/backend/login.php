<?php
session_start();
require_once '../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Ungültige E-Mail-Adresse");
    }

    try {
        // Benutzer in der Datenbank suchen
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            die("Falsche E-Mail oder Passwort");
        }

        // Benutzer in die Session speichern
        $_SESSION['user_id'] = $user['id'];
        echo "Login erfolgreich!";
    } catch (PDOException $e) {
        die("Fehler beim Login: " . $e->getMessage());
    }
}
?>
