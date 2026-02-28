<?php

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/app/http.php';
require_once APP_ROOT . '/app/session.php';
require_once APP_ROOT . '/app/csrf.php';
require_once APP_ROOT . '/app/flash.php';
require_once APP_ROOT . '/app/database.php';
require_once APP_ROOT . '/app/users.php';
require_once APP_ROOT . '/app/dashboard.php';
require_once APP_ROOT . '/app/auth.php';

ensure_user_storage_exists();
start_secure_session();
