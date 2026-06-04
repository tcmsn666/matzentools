<?php
declare(strict_types=1);

function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, username, display_name, role, is_active, must_change_password FROM users WHERE id = ?');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user && (int)$user['is_active'] === 1 ? $user : null;
}

function isLoggedIn(): bool
{
    return currentUser() !== null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect(BASE_PATH . 'login.php');
    }
}

function requireAdmin(): void
{
    $user = currentUser();
    if (!$user || $user['role'] !== 'admin') {
        jsonResponse(['success' => false, 'message' => 'Adminrechte erforderlich.'], 403);
    }
}

function attemptLogin(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM users WHERE username = ? AND is_active = 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    $success = $user && password_verify($password, $user['password_hash']);
    db()->prepare('INSERT INTO login_attempts (username, ip_address, success) VALUES (?, ?, ?)')
        ->execute([$username, $_SERVER['REMOTE_ADDR'] ?? '', $success ? 1 : 0]);
    if (!$success) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    db()->prepare('UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = ?')->execute([(int)$user['id']]);
    return true;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool)$params['secure'], (bool)$params['httponly']);
    }
    session_destroy();
}
