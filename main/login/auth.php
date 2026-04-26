<?php
session_set_cookie_params(['lifetime' => 86400 * 30, 'path' => '/']);
session_start();
require_once __DIR__ . '/../db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate Limiting Logic: lock out after 5 attempts for 5 minutes (300 seconds)
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }

    if ($_SESSION['login_attempts'] >= 5) {
        $time_passed = time() - $_SESSION['last_attempt_time'];
        if ($time_passed < 300) {
            $remaining = ceil((300 - $time_passed) / 60);
            $error = "⚠ Demasiados intentos fallidos. Inténtalo de nuevo en $remaining minuto(s).";
        } else {
            // Reset attempts after timeout
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt_time'] = time();
        }
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($error)) {
        $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.password, u.role, u.profile_picture, t.name as team_name 
        FROM users u 
        LEFT JOIN teams t ON u.team_id = t.id 
        WHERE u.username = ?
    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $stored = $user['password'];
        $isValid = false;
        
        // Check if stored password is bcrypt or plain text
        if (password_get_info($stored)['algo'] !== null && password_get_info($stored)['algo'] !== 0) {
            $isValid = password_verify($password, $stored);
        } else {
            // Legacy plain text comparison
            $isValid = ($password === $stored);
            // Auto-upgrade to bcrypt on successful login
            if ($isValid) {
                $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
                    ->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
            }
        }
        
        if ($isValid) {
            // Reset login attempts on successful login
            $_SESSION['login_attempts'] = 0;

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['team'] = $user['team_name'];
            $_SESSION['profile_picture'] = $user['profile_picture'];
            header('Location: ../index.php');
            exit;
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            $error = '⚠ Usuario o contraseña incorrectos';
        }
    } else {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        $error = '⚠ Usuario o contraseña incorrectos';
    }
    }
}
?>