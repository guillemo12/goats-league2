<?php
// ¡ADVERTENCIA! BORRA ESTE ARCHIVO INMEDIATAMENTE DESPUÉS DE USARLO.
// CUALQUIER PERSONA QUE CONOZCA ESTA RUTA PODRÍA SUBIR ARCHIVOS A TU SERVIDOR.

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['db'])) {
    // El servidor Apache corre bajo /var/www/html. Es más seguro montar el volumen aquí.
    $targetDir = '/var/www/html/data/';
    if (!file_exists($targetDir)) {
        @mkdir($targetDir, 0777, true);
    }
    
    // Guardamos el archivo con el nombre real 'goats-league.sqlite'
    $targetFile = $targetDir . 'goats-league.sqlite'; 
    
    if (move_uploaded_file($_FILES['db']['tmp_name'], $targetFile)) {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px;'>";
        echo "<strong>✅ ¡Éxito!</strong> Archivo subido con éxito al Volumen en <em>$targetFile</em>.";
        echo "<br><br><strong>⚠️ CRÍTICO: POR FAVOR BORRA ESTE ARCHIVO (subir.php) DE TU PROYECTO INMEDIATAMENTE.</strong>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
        echo "<strong>❌ Error al subir.</strong> Comprueba que el directorio /app/data/ existe y tiene permisos de escritura en Railway.";
        echo "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir SQLite a Railway</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f4f4f5; color: #333; padding: 40px; text-align: center; }
        .card { background: white; max-width: 500px; margin: 0 auto; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn { background: #000; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 16px; margin-top: 15px; width: 100%; }
        .btn:hover { background: #333; }
        input[type="file"] { margin: 20px 0; width: 100%; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Subir Base de Datos SQLite</h2>
        <p style="color: #666; font-size: 14px;">Selecciona tu archivo <code>goats-league.sqlite</code> y súbelo al volumen persistente de Railway.</p>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="db" accept=".sqlite,.db" required>
            <button type="submit" class="btn">Subir SQLite al Volumen</button>
        </form>
    </div>
</body>
</html>
