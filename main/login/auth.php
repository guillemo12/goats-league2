<?php
session_set_cookie_params(['lifetime' => 86400 * 30, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
session_start();
require_once __DIR__ . '/../db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Session-based rate limiting for brute-force prevention
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_last_attempt'] = time();
    }

    // Reset attempts if 5 minutes have passed
    if (time() - $_SESSION['login_last_attempt'] > 300) {
        $_SESSION['login_attempts'] = 0;
    }

    $_SESSION['login_last_attempt'] = time();

    if ($_SESSION['login_attempts'] >= 5) {
        $error = 'Demasiados intentos de inicio de sesión. Por favor, inténtelo de nuevo en 5 minutos.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

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
            // Prevent session fixation
            session_regenerate_id(true);

            // Reset login attempts on successful login
            unset($_SESSION['login_attempts']);
            unset($_SESSION['login_last_attempt']);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['team'] = $user['team_name'];
            $_SESSION['profile_picture'] = $user['profile_picture'];
            header('Location: ../index.php');
            exit;
        } else {
            $_SESSION['login_attempts']++;
            $error = '⚠ Usuario o contraseña incorrectos';
        }
    } else {
        $_SESSION['login_attempts']++;
        $error = '⚠ Usuario o contraseña incorrectos';
    }
    }
}
?>