<?php

declare(strict_types=1);

namespace App;

final class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, password_hash FROM admins WHERE username = :username LIMIT 1'
        );
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_username'] = $username;

        return true;
    }

    public static function check(): bool
    {
        return !empty($_SESSION['admin_id']);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('/login');
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie('PHPSESSID', '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function adminCount(): int
    {
        return (int) Database::connection()
            ->query('SELECT COUNT(*) FROM admins')
            ->fetchColumn();
    }

    public static function createAdmin(string $username, string $password): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO admins (username, password_hash) VALUES (:username, :hash)'
        );
        $stmt->execute([
            'username' => $username,
            'hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }
}
