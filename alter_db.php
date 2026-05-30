<?php
$dsn = "mysql:host=127.0.0.1;dbname=prueba;charset=utf8mb4";
$pdo = new PDO($dsn, 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
try {
    $pdo->exec("ALTER TABLE admins ADD COLUMN nombre VARCHAR(100) DEFAULT 'Administrador'");
} catch (Exception $e) {}
try {
    $pdo->exec("ALTER TABLE admins ADD COLUMN foto VARCHAR(255) DEFAULT NULL");
} catch (Exception $e) {}
echo "Done";
