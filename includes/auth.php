<?php
/**
 * Authentication and Session Management
 */

if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurable Admin Credentials (via environment variables or defaults)
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
        $target = $_SERVER['REQUEST_URI'] ?? 'index.php';
        header("Location: {$redirectUrl}?redirect=" . urlencode($target));
        exit;
    }
}
