<?php
/**
 * Authentication, Session Security, and CSRF Protection
 */

// Set secure cookie flags before starting session
if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Add baseline HTTP security headers
if (!headers_sent()) {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("Referrer-Policy: strict-origin-when-cross-origin");
}

// Session activity timeout (2 hours)
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
        }
        session_destroy();
    } else {
        $_SESSION['last_activity'] = time();
    }
}

// Configurable Admin Credentials
if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'admin@bincom.net');
}

if (!defined('ADMIN_PASSWORD')) {
    define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'admin123');
}

function isLoggedIn(): bool {
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function getLoggedInUser(): ?string {
    return $_SESSION['admin_email'] ?? null;
}

function requireAuth(string $redirectUrl = 'login.php'): void {
    if (!isLoggedIn()) {
        $target = sanitizeRedirect($_SERVER['REQUEST_URI'] ?? 'index.php', 'new-polling-unit.php');
        header("Location: {$redirectUrl}?redirect=" . urlencode($target));
        exit;
    }
}

// CSRF Token Management
function getCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Safe Redirect Sanitizer to prevent Open Redirect attacks
function sanitizeRedirect(?string $url, string $default = 'new-polling-unit.php'): string {
    if (empty($url)) return $default;
    $url = trim($url);
    if (preg_match('#^https?://#i', $url) || str_starts_with($url, '//') || str_starts_with($url, '\\')) {
        return $default;
    }
    if (!preg_match('#^[a-zA-Z0-9_\-\./\?=&]+$#', $url)) {
        return $default;
    }
    return $url;
}
