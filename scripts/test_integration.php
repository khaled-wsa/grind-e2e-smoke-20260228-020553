<?php

declare(strict_types=1);

$appRoot = dirname(__DIR__);
$storageDir = $appRoot . '/storage';
$dbFile = $storageDir . '/app.sqlite';
$backupDir = $storageDir . '/.integration-test-backup-' . bin2hex(random_bytes(6));
$dbArtifacts = [
    $dbFile,
    $dbFile . '-shm',
    $dbFile . '-wal',
];

if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}

if (!mkdir($backupDir, 0755, true) && !is_dir($backupDir)) {
    fwrite(STDERR, "Unable to create backup directory.\n");
    exit(1);
}

$backedUpFiles = [];
foreach ($dbArtifacts as $artifact) {
    if (file_exists($artifact)) {
        $backupPath = $backupDir . '/' . basename($artifact);
        if (!rename($artifact, $backupPath)) {
            fwrite(STDERR, "Unable to back up SQLite artifact: {$artifact}\n");
            exit(1);
        }
        $backedUpFiles[$artifact] = $backupPath;
    }
}

register_shutdown_function(
    static function () use ($dbArtifacts, $backedUpFiles, $backupDir): void {
        foreach ($dbArtifacts as $artifact) {
            if (file_exists($artifact)) {
                @unlink($artifact);
            }
        }

        foreach ($backedUpFiles as $originalPath => $backupPath) {
            if (file_exists($backupPath)) {
                @rename($backupPath, $originalPath);
            }
        }

        @rmdir($backupDir);
    }
);

function assert_true(bool $condition, string $message): void
{
    if ($condition) {
        return;
    }

    fwrite(STDERR, $message . "\n");
    exit(1);
}

require_once $appRoot . '/app/bootstrap.php';

$email = 'integration@example.com';
$password = 'password123';

assert_true(!user_exists($email), 'Expected integration test user to be absent before creation.');
assert_true(create_user($email, $password), 'Expected user creation to succeed.');
assert_true(verify_user_credentials($email, $password), 'Expected valid credentials to authenticate.');
assert_true(!verify_user_credentials($email, 'wrong-password'), 'Expected invalid credentials to fail.');

$user = find_user_by_email($email);
assert_true(is_array($user), 'Expected created user to be retrievable.');
$userId = (int) $user['id'];
assert_true($userId > 0, 'Expected created user id to be a positive integer.');

$recordId = create_dashboard_record($userId, 'integration_test', 'Inserted by integration test');
assert_true(is_int($recordId) && $recordId > 0, 'Expected dashboard record creation to return an id.');

$record = find_dashboard_record_by_id($recordId);
assert_true(is_array($record), 'Expected created dashboard record to be retrievable.');
assert_true(($record['event_type'] ?? '') === 'integration_test', 'Expected created record event type to match.');

$records = list_dashboard_records_for_user($userId, 5);
assert_true($records !== [], 'Expected dashboard record list to include the inserted record.');

assert_true(
    update_dashboard_record($recordId, 'integration_test_updated', 'Updated by integration test'),
    'Expected dashboard record update to succeed.'
);

$updatedRecord = find_dashboard_record_by_id($recordId);
assert_true(is_array($updatedRecord), 'Expected updated dashboard record to be retrievable.');
assert_true(
    ($updatedRecord['event_type'] ?? '') === 'integration_test_updated',
    'Expected updated record event type to match.'
);

assert_true(delete_dashboard_record($recordId), 'Expected dashboard record deletion to succeed.');
assert_true(find_dashboard_record_by_id($recordId) === null, 'Expected deleted dashboard record to be absent.');

assert_true(delete_user($userId), 'Expected user deletion to succeed.');
assert_true(find_user_by_email($email) === null, 'Expected deleted user to be absent.');

echo "Integration checks passed.\n";
