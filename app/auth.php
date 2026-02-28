<?php

declare(strict_types=1);

const AUTH_USER_SESSION_KEY = 'user_email';

function validate_registration_input(string $email, string $password): array
{
    $errors = [];
    $trimmedEmail = trim($email);

    if ($trimmedEmail === '' || !filter_var($trimmedEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    return $errors;
}

function login_user(string $email): void
{
    session_regenerate_id(true);
    $_SESSION[AUTH_USER_SESSION_KEY] = normalize_email($email);
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'] ?? '/',
            $params['domain'] ?? '',
            (bool) ($params['secure'] ?? false),
            (bool) ($params['httponly'] ?? true),
        );
    }

    session_destroy();
    start_secure_session();
    session_regenerate_id(true);
}

function is_authenticated(): bool
{
    $email = $_SESSION[AUTH_USER_SESSION_KEY] ?? null;

    return is_string($email) && $email !== '';
}

function current_user_email(): ?string
{
    if (!is_authenticated()) {
        return null;
    }

    return (string) $_SESSION[AUTH_USER_SESSION_KEY];
}

function require_authentication(): void
{
    if (!is_authenticated()) {
        set_flash('error', 'Please sign in to continue.');
        redirect('/login');
    }
}

