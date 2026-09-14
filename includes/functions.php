<?php

declare(strict_types=1);

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function flash(string $type, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}

function format_price(float $price): string
{
    return '₱' . number_format($price, 2);
}

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
