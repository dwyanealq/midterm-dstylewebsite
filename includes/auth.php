<?php

declare(strict_types=1);

/* SESSION / AUTHENTICATION SETUP. */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/* SESSION TIMEOUT */

const SESSION_TIMEOUT = 1800;

if (isset($_SESSION['last_activity'])) {
    if (time() - (int) $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        $_SESSION = [];
        session_destroy();

        header('Location: login.php?timeout=1');
        exit;
    }
}

$_SESSION['last_activity'] = time();

/* AUTHENTICATION HELPERS */


function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function is_admin(): bool
{
    return ($_SESSION['role'] ?? '') === 'admin';
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? '',
        'role' => $_SESSION['role'] ?? 'customer',
    ];
}

/* Require the user to be logged in before accessing protected pages. */

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? 'index.php'));
        exit;
    }
}

/* Require both an authenticated session and the admin role. */

function require_admin(): void
{
    if (!is_admin()) {
        header('Location: ../login.php');
        exit;
    }
}

/* SAFE REDIRECT HELPER */

function redirect_back_or(string $fallback): never
{
    $target = $_POST['redirect'] ?? $_GET['redirect'] ?? $fallback;

    if (!is_string($target) || $target === '' || str_starts_with($target, 'http')) {
        $target = $fallback;
    }

    header('Location: ' . $target);
    exit;
}
