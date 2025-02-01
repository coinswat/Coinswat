<?php
session_start();
require_once '../backend/config.php';

// Prüfen, ob der Benutzer eingeloggt ist und welche Anmeldeart genutzt wurde
function checkLogin() {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['wallet_address'])) {
        die(json_encode(["loggedIn" => false]));
    } else {
        global $pdo;
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("SELECT email, wallet_address FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                echo json_encode([
                    "loggedIn" => true, 
                    "email" => $user['email'] ?? null, 
                    "wallet" => formatWallet($user['wallet_address'])
                ]);
                return;
            }
        }
        
        if (isset($_SESSION['wallet_address'])) {
            echo json_encode([
                "loggedIn" => true, 
                "wallet" => formatWallet($_SESSION['wallet_address'])
            ]);
            return;
        }
        
        echo json_encode(["loggedIn" => false]);
    }
}

// Wallet-Authentifizierung mit Speicherung in der Datenbank
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wallet_address'])) {
    $wallet_address = $_POST['wallet_address'];
    
    // Prüfen, ob Wallet bereits existiert
    $stmt = $pdo->prepare("SELECT id FROM users WHERE wallet_address = ?");
    $stmt->execute([$wallet_address]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Falls Wallet nicht existiert, neuen Benutzer anlegen
        $stmt = $pdo->prepare("INSERT INTO users (wallet_address, password_hash) VALUES (?, ?)");
        $stmt->execute([$wallet_address, 'wallet_connect']);
        $user_id = $pdo->lastInsertId();
    } else {
        $user_id = $user['id'];
    }
    
    $_SESSION['user_id'] = $user_id;
    $_SESSION['wallet_address'] = $wallet_address;
    echo json_encode(["status" => "success", "wallet" => formatWallet($wallet_address)]);
    exit();
}

// Registrierung mit E-Mail & Passwort vervollständigen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    if (!isset($_SESSION['user_id'])) {
        die(json_encode(["status" => "error", "message" => "Keine Wallet verbunden."]));
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("UPDATE users SET email = ?, password_hash = ? WHERE id = ?");
    $stmt->execute([$email, $password, $user_id]);
    
    echo json_encode(["status" => "success", "message" => "Registrierung abgeschlossen."]);
    exit();
}

// Benutzer ausloggen
if (isset($_GET['logout'])) {
    session_destroy();
    echo json_encode(["loggedIn" => false]);
    exit();
}

function formatWallet($wallet) {
    return substr($wallet, 0, 5) . '***' . substr($wallet, -5);
}

checkLogin();
?>
