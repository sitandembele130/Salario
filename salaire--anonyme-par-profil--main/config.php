<?php
declare(strict_types=1);

session_start();

$host = '127.0.0.1';
$dbname = 'salaire_anonyme';
$username = 'root';
$password = ''; // Valeur par défaut de XAMPP local. Définissez un mot de passe en production.

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Connexion à la base de données impossible. Vérifiez config.php et importez database.sql.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
