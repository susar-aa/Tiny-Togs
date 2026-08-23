<?php
require_once __DIR__ . '/config/bootstrap.php';
use Models\Category;
use Config\Database;

// Connect as root
$pdo = new PDO("mysql:host=localhost;dbname=tiny_togs;charset=utf8mb4", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Reflection to inject PDO into Database
$ref = new ReflectionClass(Database::class);
$prop = $ref->getProperty('conn');
$prop->setAccessible(true);
$prop->setValue(null, $pdo);

$catModel = new Category();
$res = $catModel->importCategory('Test Live Category 2', '', null);
echo "Result of insert: ";
var_dump($res);

$categories = $catModel->getAll();
$found = false;
foreach ($categories as $cat) {
    if ($cat['category_name'] === 'Test Live Category 2') {
        $found = true;
        echo "\nFound in getAll(): ";
        print_r($cat);
    }
}
if (!$found) echo "\nNot found in getAll()!";
