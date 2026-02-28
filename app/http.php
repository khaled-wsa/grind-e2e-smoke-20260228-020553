<?php

declare(strict_types=1);

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    if (!is_string($path) || $path === '') {
        return '/';
    }

    return rtrim($path, '/') === '' ? '/' : rtrim($path, '/');
}

function redirect(string $path, int $statusCode = 302): never
{
    header('Location: ' . $path, true, $statusCode);
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

