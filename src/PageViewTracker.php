<?php

declare(strict_types=1);

namespace App;

final class PageViewTracker
{
    /**
     * Records a page view for the given IP unless that same IP already has
     * one within the cooldown window (default 10 minutes) — refreshing the
     * page repeatedly does not create duplicate views.
     */
    public static function record(string $ip, string $page, int $cooldownMinutes): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT created_at FROM page_views WHERE ip_address = :ip ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute(['ip' => $ip]);
        $lastSeen = $stmt->fetchColumn();

        if ($lastSeen !== false) {
            $cooldownEndsAt = strtotime($lastSeen) + ($cooldownMinutes * 60);
            if (time() < $cooldownEndsAt) {
                return;
            }
        }

        $insert = $pdo->prepare(
            'INSERT INTO page_views (ip_address, page, user_agent, referrer) VALUES (:ip, :page, :ua, :ref)'
        );
        $insert->execute([
            'ip' => $ip,
            'page' => $page,
            'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
            'ref' => substr($_SERVER['HTTP_REFERER'] ?? '', 0, 512) ?: null,
        ]);

        // Make sure a location cache row exists so the admin panel knows to resolve it later.
        $seed = $pdo->prepare(
            'INSERT IGNORE INTO ip_locations (ip_address, status) VALUES (:ip, :status)'
        );
        $seed->execute([
            'ip' => $ip,
            'status' => is_private_ip($ip) ? 'private' : 'pending',
        ]);
    }
}
