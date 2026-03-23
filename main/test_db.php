<?php
// Script temporal para comprobar que el archivo de base de datos existe
$file = '/var/www/html/data/goats-league.sqlite'; // Usamos la misma ruta de subir.php

// Función auxiliar para convertir bytes a un formato legible
function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow]; 
}

echo "<div style='font-family: sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; border: 1px solid #ccc; border-radius: 8px; margin-top: 40px;'>";
echo "<h2>Test de Volumen SQLite</h2>";

if (file_exists($file)) {
    echo "<div style='color: green; background: #e8f5e9; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
    echo "✅ <strong>¡El archivo existe!</strong><br><br>";
    echo "<strong>Ruta:</strong> " . htmlspecialchars($file) . "<br>";
    echo "<strong>Tamaño:</strong> " . formatBytes(filesize($file));
    
    // Comprobar si PHP puede además leer y escribir el archivo
    if (is_readable($file) && is_writable($file)) {
        echo "<br><strong>Permisos (PHP):</strong> ✅ Lectura y Escritura permitidas";
    } else {
        echo "<br><strong>Permisos (PHP):</strong> ❌ NO se puede leer/escribir. Revisa la propiedad del archivo/volumen.";
    }
    
    echo "</div>";
    
    echo "<p style='color: #666; font-size: 14px;'>⚠️ Recuerda borrar <code>test_db.php</code> y <code>subir.php</code> por seguridad una vez modifiquemos la conexión (db.php).</p>";
} else {
    echo "<div style='color: red; background: #ffebee; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
    echo "❌ <strong>No se encuentra el archivo SQLite.</strong><br><br>";
    echo "Se ha buscado en: <code>" . htmlspecialchars($file) . "</code><br>";
    echo "Asegúrate de haber configurado el Mount Path del volumen en Railway a <code>/var/www/html/data</code> y haber subido el archivo correctamente con subir.php.";
    echo "</div>";
}
echo "</div>";
?>
