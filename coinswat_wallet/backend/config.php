<?php

// MySQL-Datenbankverbindung für COINSWAT Wallet
$host = 'localhost';
$dbname = 'coinswat_wallet';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Fehler bei der Verbindung zur Datenbank: " . $e->getMessage());
}

?>
