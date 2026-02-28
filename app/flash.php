<?php

declare(strict_types=1);

const FLASH_KEY = '_flash';

function set_flash(string $type, string $message): void
{
    $_SESSION[FLASH_KEY] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pull_flash(): ?array
{
    $flash = $_SESSION[FLASH_KEY] ?? null;

    if (!is_array($flash)) {
        return null;
    }

    unset($_SESSION[FLASH_KEY]);

    return $flash;
}

