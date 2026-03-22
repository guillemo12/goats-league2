<?php
// ARCHIVO TEMPORAL DE DIAGNÓSTICO - BORRARLO DESPUÉS DE USARLO
echo "<pre style='font-family: monospace; background:#111; color:#0f0; padding:20px;'>";
echo "=== DIAGNÓSTICO RAILWAY ===\n\n";

// 1. Mostrar variables de entorno relevantes (SIN mostrar la contraseña completa)
echo "MYSQLHOST:     " . (getenv('MYSQLHOST') ?: '❌ NO ENCONTRADA') . "\n";
echo "MYSQLUSER:     " . (getenv('MYSQLUSER') ?: '❌ NO ENCONTRADA') . "\n";
echo "MYSQLPASSWORD: " . (getenv('MYSQLPASSWORD') ? '✅ DEFINIDA (oculta)' : '❌ NO ENCONTRADA') . "\n";
echo "MYSQLPORT:     " . (getenv('MYSQLPORT') ?: '❌ NO ENCONTRADA') . "\n";
echo "MYSQLDATABASE: " . (getenv('MYSQLDATABASE') ?: '❌ NO ENCONTRADA') . "\n";
echo "DATABASE_URL:  " . (getenv('DATABASE_URL') ? '✅ DEFINIDA (oculta)' : '❌ NO ENCONTRADA') . "\n";
echo "PORT:          " . (getenv('PORT') ?: '❌ NO ENCONTRADA') . "\n";

echo "\n=== PRUEBA DE CONEXIÓN ===\n";

// 2. Intentar conectar
$dbUrl = getenv('DATABASE_URL') ?: getenv('MYSQL_URL') ?: null;
if ($dbUrl) {
    $parts  = parse_url($dbUrl);
    $host   = $parts['host'];
    $user   = $parts['user'];
    $pass   = $parts['pass'] ?? '';
    $port   = $parts['port'] ?? 3306;
    $dbname = ltrim($parts['path'], '/');
} else {
    $host   = getenv('MYSQLHOST')     ?: 'localhost';
    $user   = getenv('MYSQLUSER')     ?: 'root';
    $pass   = getenv('MYSQLPASSWORD') ?: '';
    $port   = getenv('MYSQLPORT')     ?: '3306';
    $dbname = getenv('MYSQLDATABASE') ?: 'goats_league';
}

echo "Intentando conectar a: $host:$port / $dbname ...\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    echo "✅ CONEXIÓN EXITOSA a la base de datos.\n";
} catch (PDOException $e) {
    echo "❌ ERROR DE CONEXIÓN: " . $e->getMessage() . "\n";
}

echo "\n=== PHP INFO ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
echo "</pre>";
?>
