<?php

declare(strict_types=1);

function ensure_user_storage_exists(): void
{
    database_connection();
}

function normalize_email(string $email): string
{
    return strtolower(trim($email));
}

function hydrate_user_row(array $row): array
{
    return [
        'id' => (int) ($row['id'] ?? 0),
        'email' => (string) ($row['email'] ?? ''),
        'password_hash' => (string) ($row['password_hash'] ?? ''),
        'created_at' => (string) ($row['created_at'] ?? ''),
    ];
}

function hydrate_dashboard_record_row(array $row): array
{
    return [
        'id' => (int) ($row['id'] ?? 0),
        'user_id' => (int) ($row['user_id'] ?? 0),
        'event_type' => (string) ($row['event_type'] ?? ''),
        'details' => (string) ($row['details'] ?? ''),
        'created_at' => (string) ($row['created_at'] ?? ''),
    ];
}

function find_user_by_email(string $email): ?array
{
    $statement = database_connection()->prepare(
        'SELECT id, email, password_hash, created_at FROM users WHERE email = :email LIMIT 1'
    );
    $statement->execute([
        ':email' => normalize_email($email),
    ]);

    $user = $statement->fetch();

    if (!is_array($user)) {
        return null;
    }

    return hydrate_user_row($user);
}

function find_user_by_id(int $userId): ?array
{
    $statement = database_connection()->prepare(
        'SELECT id, email, password_hash, created_at FROM users WHERE id = :id LIMIT 1'
    );
    $statement->execute([
        ':id' => $userId,
    ]);

    $user = $statement->fetch();

    if (!is_array($user)) {
        return null;
    }

    return hydrate_user_row($user);
}

function user_exists(string $email): bool
{
    return find_user_by_email($email) !== null;
}

function create_user(string $email, string $password): bool
{
    $normalizedEmail = normalize_email($email);
    if ($normalizedEmail === '' || find_user_by_email($normalizedEmail) !== null) {
        return false;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    if (!is_string($passwordHash) || $passwordHash === '') {
        return false;
    }

    $statement = database_connection()->prepare(
        'INSERT INTO users (email, password_hash) VALUES (:email, :password_hash)'
    );

    try {
        return $statement->execute([
            ':email' => $normalizedEmail,
            ':password_hash' => $passwordHash,
        ]);
    } catch (PDOException) {
        return false;
    }
}

function update_user_password(int $userId, string $password): bool
{
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    if (!is_string($passwordHash) || $passwordHash === '') {
        return false;
    }

    $statement = database_connection()->prepare(
        'UPDATE users SET password_hash = :password_hash WHERE id = :id'
    );

    $statement->execute([
        ':password_hash' => $passwordHash,
        ':id' => $userId,
    ]);

    return $statement->rowCount() > 0;
}

function delete_user(int $userId): bool
{
    $statement = database_connection()->prepare('DELETE FROM users WHERE id = :id');
    $statement->execute([
        ':id' => $userId,
    ]);

    return $statement->rowCount() > 0;
}

function verify_user_credentials(string $email, string $password): bool
{
    $user = find_user_by_email($email);
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
        update_user_password((int) $user['id'], $password);
    }

    return true;
}

function create_dashboard_record(int $userId, string $eventType, string $details = ''): ?int
{
    $normalizedEventType = trim($eventType);
    if ($userId <= 0 || $normalizedEventType === '') {
        return null;
    }

    $statement = database_connection()->prepare(
        'INSERT INTO dashboard_records (user_id, event_type, details)
         VALUES (:user_id, :event_type, :details)'
    );
    $statement->execute([
        ':user_id' => $userId,
        ':event_type' => $normalizedEventType,
        ':details' => $details,
    ]);

    return (int) database_connection()->lastInsertId();
}

function find_dashboard_record_by_id(int $recordId): ?array
{
    $statement = database_connection()->prepare(
        'SELECT id, user_id, event_type, details, created_at
         FROM dashboard_records WHERE id = :id LIMIT 1'
    );
    $statement->execute([
        ':id' => $recordId,
    ]);

    $record = $statement->fetch();
    if (!is_array($record)) {
        return null;
    }

    return hydrate_dashboard_record_row($record);
}

function list_dashboard_records_for_user(int $userId, int $limit = 20): array
{
    if ($userId <= 0) {
        return [];
    }

    $statement = database_connection()->prepare(
        'SELECT id, user_id, event_type, details, created_at
         FROM dashboard_records
         WHERE user_id = :user_id
         ORDER BY datetime(created_at) DESC, id DESC
         LIMIT :limit'
    );
    $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $statement->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
    $statement->execute();

    $records = $statement->fetchAll();
    if (!is_array($records)) {
        return [];
    }

    return array_map(
        static fn (array $record): array => hydrate_dashboard_record_row($record),
        $records,
    );
}

function update_dashboard_record(int $recordId, string $eventType, string $details): bool
{
    $normalizedEventType = trim($eventType);
    if ($recordId <= 0 || $normalizedEventType === '') {
        return false;
    }

    $statement = database_connection()->prepare(
        'UPDATE dashboard_records
         SET event_type = :event_type, details = :details
         WHERE id = :id'
    );
    $statement->execute([
        ':event_type' => $normalizedEventType,
        ':details' => $details,
        ':id' => $recordId,
    ]);

    return $statement->rowCount() > 0;
}

function delete_dashboard_record(int $recordId): bool
{
    $statement = database_connection()->prepare('DELETE FROM dashboard_records WHERE id = :id');
    $statement->execute([
        ':id' => $recordId,
    ]);

    return $statement->rowCount() > 0;
}
