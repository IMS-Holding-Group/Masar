<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'masar';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die('فشل الاتصال بقاعدة البيانات: ' . $e->getMessage());
}
