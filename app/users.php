<?php

declare(strict_types=1);

const USERS_FILE = APP_ROOT . '/storage/users.json';

function ensure_user_storage_exists(): void
{
    $storageDir = dirname(USERS_FILE);

    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }

    if (!file_exists(USERS_FILE)) {
        file_put_contents(USERS_FILE, "{}\n");
    }
}

function normalize_email(string $email): string
{
    return strtolower(trim($email));
}

function read_users(): array
{
    $raw = file_get_contents(USERS_FILE);

    if (!is_string($raw) || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);

    return is_array($decoded) ? $decoded : [];
}

function write_users(array $users): bool
{
    $encoded = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if (!is_string($encoded)) {
        return false;
    }

    return file_put_contents(USERS_FILE, $encoded . PHP_EOL, LOCK_EX) !== false;
}

function user_exists(string $email): bool
{
    $users = read_users();

    return array_key_exists(normalize_email($email), $users);
}

function create_user(string $email, string $password): bool
{
    $normalizedEmail = normalize_email($email);
    $users = read_users();

    if (array_key_exists($normalizedEmail, $users)) {
        return false;
    }

    $users[$normalizedEmail] = [
        'email' => $normalizedEmail,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ];

    return write_users($users);
}

function verify_user_credentials(string $email, string $password): bool
{
    $normalizedEmail = normalize_email($email);
    $users = read_users();
    $user = $users[$normalizedEmail] ?? null;

    if (!is_array($user)) {
        return false;
    }

    $hash = $user['password_hash'] ?? null;
    if (!is_string($hash) || $hash === '') {
        return false;
    }

    if (!password_verify($password, $hash)) {
        return false;
    }

    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
        $users[$normalizedEmail]['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        write_users($users);
    }

    return true;
}

