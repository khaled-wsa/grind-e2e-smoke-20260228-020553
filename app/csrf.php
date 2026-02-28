<?php

declare(strict_types=1);

const CSRF_TOKEN_SESSION_KEY = '_csrf_token';

function csrf_token(): string
{
    $token = $_SESSION[CSRF_TOKEN_SESSION_KEY] ?? null;

    if (!is_string($token) || $token === '') {
        $token = bin2hex(random_bytes(32));
        $_SESSION[CSRF_TOKEN_SESSION_KEY] = $token;
    }

    return $token;
}

function validate_csrf_token(?string $token): bool
{
    $expected = $_SESSION[CSRF_TOKEN_SESSION_KEY] ?? null;

    if (!is_string($expected) || $expected === '' || !is_string($token) || $token === '') {
        return false;
    }

    return hash_equals($expected, $token);
}

