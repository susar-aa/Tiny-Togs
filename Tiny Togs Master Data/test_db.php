<?php
$host = 'localhost';
$db = 'tiny_togs';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully\n";
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC LIMIT 5");
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($cats);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
