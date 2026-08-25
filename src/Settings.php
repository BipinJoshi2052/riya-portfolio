<?php

declare(strict_types=1);

namespace App;

/** Generic key/value settings store, cached for the lifetime of the request. */
final class Settings
{
    private static ?array $cache = null;

    public static function get(string $key, ?string $default = null): ?string
    {
        self::loadAll();

        return self::$cache[$key] ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $stmt->execute(['key' => $key, 'value' => $value]);

        self::loadAll();
        self::$cache[$key] = $value;
    }

    private static function loadAll(): void
    {
        if (self::$cache !== null) {
            return;
        }

        self::$cache = [];

        try {
            $rows = Database::connection()
                ->query('SELECT setting_key, setting_value FROM settings')
                ->fetchAll();

            foreach ($rows as $row) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (\Throwable $e) {
            error_log('Settings::loadAll failed: ' . $e->getMessage());
        }
    }
}
