<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

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

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? 'index.php'));
        exit;
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        header('Location: ../login.php');
        exit;
    }
}

function redirect_back_or(string $fallback): never
{
    $target = $_POST['redirect'] ?? $_GET['redirect'] ?? $fallback;

    if (!is_string($target) || $target === '' || str_starts_with($target, 'http')) {
        $target = $fallback;
    }

    header('Location: ' . $target);
    exit;
}
