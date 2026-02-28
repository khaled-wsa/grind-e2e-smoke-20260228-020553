<?php

declare(strict_types=1);

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

function dashboard_page_content(string $email, string $csrfToken): string
{
    return '<div class="card"><p>You are signed in as <strong>' . e($email) . '</strong>.</p>
        <form method="post" action="/logout">
            <input type="hidden" name="_csrf_token" value="' . e($csrfToken) . '">
            <button type="submit">Logout</button>
        </form></div>';
}
