<?php

declare(strict_types=1);

const SQLITE_DB_FILE = APP_ROOT . '/storage/app.sqlite';

function ensure_database_storage_exists(): void
{
    $storageDir = dirname(SQLITE_DB_FILE);

    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }
}

function database_connection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    ensure_database_storage_exists();

    $pdo = new PDO('sqlite:' . SQLITE_DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    initialize_database_schema($pdo);

    return $pdo;
}

function initialize_database_schema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS dashboard_records (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            event_type TEXT NOT NULL,
            details TEXT NOT NULL DEFAULT \'\',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )'
    );

    $pdo->exec(
        'CREATE INDEX IF NOT EXISTS idx_dashboard_records_user_created_at
         ON dashboard_records (user_id, created_at DESC)'
    );
}
