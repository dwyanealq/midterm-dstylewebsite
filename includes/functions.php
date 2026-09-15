<?php

declare(strict_types=1);


/* Escape output before showing database/user data inside HTML. */
function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* Store a one-time message in the user's session. */
function flash(string $type, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/* Retrieve and immediately remove the one-time session message. */
function get_flash(): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}

/* Format prices consistently throughout the site. */
function format_price(float $price): string
{
    return '₱' . number_format($price, 2);
}

/* Convert the JSON list of product sizes into a usable PHP array. */
function product_sizes(?string $sizesJson): array
{
    if (!$sizesJson) {
        return [];
    }

    $sizes = json_decode($sizesJson, true);
    return is_array($sizes) ? array_values(array_filter($sizes, 'is_string')) : [];
}

function redirect_to(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/*    PASSWORD VALIDATION */

function validate_password_strength(string $password): array
{
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character.';
    }

    return $errors;
}