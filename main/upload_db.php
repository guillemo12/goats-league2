<?php
session_set_cookie_params(['lifetime' => 86400 * 30, 'path' => '/', 'httponly' => true, 'secure' => true, 'samesite' => 'Lax']);
session_start();
require_once __DIR__ . '/db.php';

// Auth and Admin Check
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$meInfo = $stmt->fetch();
if (!$meInfo || $meInfo['role'] !== 'admin') { header("Location: index.php"); exit; }

$target_dir = "/app/data/";
$target_file = $target_dir . "database.sqlite";

// 1. Intentar crear la carpeta si no existe y dar permisos
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0700, true);
}
chmod($target_dir, 0700);

if (isset($_POST["submit"])) {
    $fileInfo = pathinfo($_FILES["db_file"]["name"]);
    if (isset($fileInfo['extension']) && strtolower($fileInfo['extension']) === 'sqlite') {
        if (move_uploaded_file($_FILES["db_file"]["tmp_name"], $target_file)) {
            chmod($target_file, 0600); // Permiso de lectura/escritura para la DB (solo usuario)
            echo "✅ ¡Éxito! Archivo subido correctamente.";
        }
        else {
            error_log(print_r($_FILES, true));
            echo "❌ Error al subir. Consulte los logs del sistema.";
        }
    } else {
         echo "❌ Error: Solo se permiten archivos con extensión .sqlite";
    }
}
?>

<form action="" method="post" enctype="multipart/form-data" style="margin-top:20px;">
  Selecciona tu archivo .sqlite:
  <input type="file" name="db_file" id="db_file" accept=".sqlite">
  <input type="submit" value="Subir Base de Datos" name="submit">
</form>

<hr>
<h3>Estado del sistema:</h3>
<?php
echo "El sistema está listo para recibir el archivo.<br>";
echo "Directorio de almacenamiento: Configurado correctamente<br>";
?>