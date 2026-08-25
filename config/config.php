<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

/**
 * Loads .env once per request and returns the app config array.
 * Safe to call from anywhere — repeated calls just return the cached array.
 */
function app_config(): array
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();

    date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

    $config = [
        'app' => [
            'env' => $_ENV['APP_ENV'] ?? 'production',
            'url' => rtrim($_ENV['APP_URL'] ?? '', '/'),
        ],
        'db' => [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'database' => $_ENV['DB_DATABASE'] ?? '',
            'username' => $_ENV['DB_USERNAME'] ?? '',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
        ],
        'mail' => [
            'host' => $_ENV['SMTP_HOST'] ?? '',
            'port' => (int) ($_ENV['SMTP_PORT'] ?? 587),
            'secure' => $_ENV['SMTP_SECURE'] ?? 'tls',
            'username' => $_ENV['SMTP_USERNAME'] ?? '',
            'password' => $_ENV['SMTP_PASSWORD'] ?? '',
            'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? '',
            'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Website',
            'send_to' => $_ENV['SEND_MAIL_TO'] ?? '',
        ],
        'page_view_cooldown_minutes' => (int) ($_ENV['PAGE_VIEW_COOLDOWN_MINUTES'] ?? 10),
    ];

    return $config;
}
