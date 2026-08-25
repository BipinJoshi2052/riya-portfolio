<?php

declare(strict_types=1);

/** Escape a value for safe HTML output. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Collapses a raw User-Agent string into a short "Browser N · OS" summary. */
function simplify_user_agent(?string $ua): string
{
    if (!$ua) {
        return 'Unknown';
    }

    $browser = 'Unknown browser';
    if (preg_match('/Edg\/([\d.]+)/', $ua, $m)) {
        $browser = 'Edge ' . explode('.', $m[1])[0];
    } elseif (preg_match('/OPR\/([\d.]+)/', $ua, $m)) {
        $browser = 'Opera ' . explode('.', $m[1])[0];
    } elseif (preg_match('/Firefox\/([\d.]+)/', $ua, $m)) {
        $browser = 'Firefox ' . explode('.', $m[1])[0];
    } elseif (preg_match('/Chrome\/([\d.]+)/', $ua, $m)) {
        $browser = 'Chrome ' . explode('.', $m[1])[0];
    } elseif (preg_match('/Version\/([\d.]+).*Safari/', $ua, $m)) {
        $browser = 'Safari ' . explode('.', $m[1])[0];
    } elseif (preg_match('/MSIE ([\d.]+)/', $ua, $m) || preg_match('/Trident.*rv:([\d.]+)/', $ua, $m)) {
        $browser = 'IE ' . explode('.', $m[1])[0];
    }

    $os = 'Unknown OS';
    if (preg_match('/Windows NT ([\d.]+)/', $ua, $m)) {
        $map = ['10.0' => '10/11', '6.3' => '8.1', '6.2' => '8', '6.1' => '7'];
        $os = 'Windows ' . ($map[$m[1]] ?? $m[1]);
    } elseif (preg_match('/Mac OS X ([\d_]+)/', $ua, $m)) {
        $os = 'macOS ' . str_replace('_', '.', $m[1]);
    } elseif (stripos($ua, 'Android') !== false) {
        preg_match('/Android ([\d.]+)/', $ua, $m);
        $os = 'Android ' . ($m[1] ?? '');
    } elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) {
        preg_match('/OS ([\d_]+)/', $ua, $m);
        $os = 'iOS ' . str_replace('_', '.', $m[1] ?? '');
    } elseif (stripos($ua, 'CrOS') !== false) {
        $os = 'Chrome OS';
    } elseif (stripos($ua, 'Linux') !== false) {
        $os = 'Linux';
    }

    return trim($browser . ' · ' . $os);
}

/** Best-effort real client IP, aware of common reverse-proxy / CDN headers. */
function client_ip(): string
{
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP'];

    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ips = explode(',', $_SERVER[$header]);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function is_private_ip(string $ip): bool
{
    return filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    ) === false;
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? '';

    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Appends the asset file's mtime as a cache-busting query string, so a
 * redeploy invalidates browsers' Cache-Control-driven caching immediately
 * instead of visitors needing to hard-refresh.
 */
function asset_url(string $path): string
{
    $file = PUBLIC_PATH . $path;
    $version = is_file($file) ? filemtime($file) : time();

    return $path . '?v=' . $version;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);

    return $value;
}
