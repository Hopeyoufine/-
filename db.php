<?php
$host = 'localhost';
$db   = 'mp3_converter_db';
$user = 'root';      // เปลี่ยนเป็น username ของคุณ
$pass = '';          // เปลี่ยนเป็น password ของคุณ
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // ใน production ไม่ควร echo error ออกมาตรงๆ ควรเก็บ log แทน
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>