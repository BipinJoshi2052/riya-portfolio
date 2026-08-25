<?php

declare(strict_types=1);

namespace App;

/**
 * Resolves an IP address to a location.
 *
 * Nominatim (OpenStreetMap) only geocodes addresses/coordinates — it has no
 * concept of "look up this IP". So this does it in two hops, each cached in
 * the ip_locations table so any given IP is only ever looked up once:
 *   1. ip-api.com  : IP -> latitude/longitude (free, no key)
 *   2. Nominatim    : latitude/longitude -> human-readable address
 */
final class GeoLocation
{
    private const USER_AGENT = 'RiyaPortfolio/1.0 (contact form admin panel)';

    /** Resolve (and cache) location for every ip_locations row still pending. */
    public static function resolvePending(int $limit = 5): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT ip_address FROM ip_locations WHERE status = :status LIMIT :limit'
        );
        $stmt->bindValue('status', 'pending');
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $ip) {
            self::resolve((string) $ip);
        }
    }

    public static function resolve(string $ip): void
    {
        if (is_private_ip($ip)) {
            self::save($ip, ['status' => 'private']);
            return;
        }

        $coords = self::lookupCoordinates($ip);
        if ($coords === null) {
            self::save($ip, ['status' => 'failed']);
            return;
        }

        $address = self::reverseGeocode($coords['lat'], $coords['lon']);

        self::save($ip, [
            'status' => 'resolved',
            'latitude' => $coords['lat'],
            'longitude' => $coords['lon'],
            'city' => $coords['city'] ?? $address['city'] ?? null,
            'region' => $coords['region'] ?? $address['region'] ?? null,
            'country' => $coords['country'] ?? $address['country'] ?? null,
            'display_name' => $address['display_name'] ?? null,
        ]);
    }

    private static function lookupCoordinates(string $ip): ?array
    {
        $json = self::httpGet("http://ip-api.com/json/{$ip}?fields=status,lat,lon,city,regionName,country");
        if ($json === null) {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data) || ($data['status'] ?? '') !== 'success') {
            return null;
        }

        return [
            'lat' => (float) $data['lat'],
            'lon' => (float) $data['lon'],
            'city' => $data['city'] ?? null,
            'region' => $data['regionName'] ?? null,
            'country' => $data['country'] ?? null,
        ];
    }

    private static function reverseGeocode(float $lat, float $lon): ?array
    {
        $url = sprintf(
            'https://nominatim.openstreetmap.org/reverse?format=json&lat=%s&lon=%s&zoom=10&addressdetails=1',
            urlencode((string) $lat),
            urlencode((string) $lon)
        );

        $json = self::httpGet($url);
        if ($json === null) {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return null;
        }

        $address = $data['address'] ?? [];

        return [
            'display_name' => $data['display_name'] ?? null,
            'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? null,
            'region' => $address['state'] ?? null,
            'country' => $address['country'] ?? null,
        ];
    }

    private static function httpGet(string $url): ?string
    {
        if (!function_exists('curl_init')) {
            error_log('GeoLocation: the curl PHP extension is not enabled, cannot resolve locations.');
            return null;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_HTTPHEADER => ['User-Agent: ' . self::USER_AGENT],
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($ch);
        $ok = $response !== false && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
        curl_close($ch);

        return $ok ? $response : null;
    }

    private static function save(string $ip, array $fields): void
    {
        $fields['resolved_at'] = date('Y-m-d H:i:s');

        $columns = array_keys($fields);
        $set = implode(', ', array_map(fn ($c) => "$c = :$c", $columns));

        // Native (non-emulated) MySQL prepares reject reusing the same named
        // placeholder twice, so the WHERE clause gets its own distinct name
        // rather than sharing :ip_address with a SET column.
        $fields['where_ip'] = $ip;

        $sql = 'UPDATE ip_locations SET ' . $set . ' WHERE ip_address = :where_ip';
        Database::connection()->prepare($sql)->execute($fields);
    }
}
