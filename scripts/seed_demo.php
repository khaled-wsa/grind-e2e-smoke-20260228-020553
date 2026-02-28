<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$demoEmail = 'demo@example.com';
$demoPassword = 'password123';

$createdDemoUser = false;
if (!user_exists($demoEmail)) {
    $createdDemoUser = create_user($demoEmail, $demoPassword);
}

$user = find_user_by_email($demoEmail);
if (!is_array($user)) {
    fwrite(STDERR, "Failed to load demo user.\n");
    exit(1);
}

$userId = (int) $user['id'];
$existingRecords = list_dashboard_records_for_user($userId, 1);
$insertedRecordCount = 0;

if ($existingRecords === []) {
    $recordsToSeed = [
        ['account_created', 'Demo account provisioned for local development.'],
        ['first_login', 'First successful login from localhost.'],
        ['profile_viewed', 'Dashboard profile panel opened.'],
    ];

    foreach ($recordsToSeed as [$eventType, $details]) {
        if (create_dashboard_record($userId, $eventType, $details) !== null) {
            $insertedRecordCount++;
        }
    }
}

echo "Seed complete.\n";
echo "Database file: " . SQLITE_DB_FILE . "\n";
echo "Demo user: {$demoEmail}\n";
echo $createdDemoUser ? "Created demo user.\n" : "Demo user already exists.\n";
echo $insertedRecordCount > 0
    ? "Inserted {$insertedRecordCount} activity records.\n"
    : "Activity records already present, no new inserts.\n";
