<?php
// Railway env vars en producción, fallback a local para desarrollo
$host   = getenv('MYSQLHOST')     ?: 'localhost';
$user   = getenv('MYSQLUSER')     ?: 'root';
$pass   = getenv('MYSQLPASSWORD') ?: '';
$port   = getenv('MYSQLPORT')     ?: '3306';
$dbname = getenv('MYSQLDATABASE') ?: 'goats_league';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>
