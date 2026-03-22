<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = 'user_' . $userId . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($file['name']));
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $oldPic = $stmt->fetchColumn();
            
            if ($oldPic && file_exists(__DIR__ . '/../' . $oldPic)) {
                unlink(__DIR__ . '/../' . $oldPic);
            }
            
            $dbPath = 'uploads/profiles/' . $fileName;
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->execute([$dbPath, $userId]);
            
            $_SESSION['profile_picture'] = $dbPath;
            $message = '<div class="alert alert-success mt-3">Foto de perfil actualizada con éxito.</div>';
        } else {
            $message = '<div class="alert alert-danger mt-3">Error al mover la imagen al directorio destino.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger mt-3">Por favor selecciona una imagen o verifica el tamaño máximo permitido.</div>';
    }
}

// --- CAMBIO DE CONTRASEÑA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    // Fetch current hash (may be plain text or bcrypt)
    $stmtHash = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmtHash->execute([$userId]);
    $storedHash = $stmtHash->fetchColumn();

    // Check password: support both plain text (legacy) and bcrypt
    $isCorrect = false;
    if (password_get_info($storedHash)['algo'] !== null && password_get_info($storedHash)['algo'] !== 0) {
        // It's a proper bcrypt hash
        $isCorrect = password_verify($currentPass, $storedHash);
    } else {
        // Plain text (legacy users not yet migrated)
        $isCorrect = ($currentPass === $storedHash);
    }

    if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
        $message = '<div class="alert alert-danger mt-3">Rellena todos los campos de contraseña.</div>';
    } elseif (!$isCorrect) {
        $message = '<div class="alert alert-danger mt-3">La contraseña actual introducida no es correcta.</div>';
    } elseif ($newPass !== $confirmPass) {
        $message = '<div class="alert alert-danger mt-3">La nueva contraseña y la confirmación no coinciden.</div>';
    } else {
        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
        $stmtUp = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmtUp->execute([$newHash, $userId]);
        $message = '<div class="alert alert-success mt-3"><i class="bi bi-check-circle-fill"></i> ¡Contraseña cambiada correctamente!</div>';
    }
}

// Sin incluir html.php porque ahora está embebido en profile/index.php
?>
