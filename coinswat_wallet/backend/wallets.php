<?php
require_once '../backend/config.php';
require_once '../backend/session.php';
checkLogin();

// Wallet hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $chain = $_POST['chain'];
    $address = $_POST['address'];
    $private_key = openssl_encrypt($_POST['private_key'], 'AES-256-CBC', 'DEIN_GEHEIMNIS', 0, '1234567890123456');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO wallets (user_id, chain, address, private_key) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $chain, $address, $private_key]);
        echo "Wallet erfolgreich gespeichert!";
    } catch (PDOException $e) {
        die("Fehler beim Speichern des Wallets: " . $e->getMessage());
    }
}

// Wallets abrufen
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("SELECT id, chain, address FROM wallets WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($wallets);
    } catch (PDOException $e) {
        die("Fehler beim Abrufen der Wallets: " . $e->getMessage());
    }
}
?>
