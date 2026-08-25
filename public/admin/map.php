<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Auth;
use App\Database;

Auth::requireLogin();

$pdo = Database::connection();

$stmt = $pdo->query(
    "SELECT l.ip_address, l.latitude, l.longitude, l.display_name, l.city, l.region, l.country,
            COUNT(pv.id) AS visits, MAX(pv.created_at) AS last_visit
     FROM ip_locations l
     JOIN page_views pv ON pv.ip_address = l.ip_address
     WHERE l.status = 'resolved' AND l.latitude IS NOT NULL AND l.longitude IS NOT NULL
     GROUP BY l.ip_address, l.latitude, l.longitude, l.display_name, l.city, l.region, l.country
     ORDER BY visits DESC"
);
$locations = $stmt->fetchAll();

$points = array_map(function ($row) {
    return [
        'lat' => (float) $row['latitude'],
        'lon' => (float) $row['longitude'],
        'label' => $row['display_name'] ?: trim(($row['city'] ?? '') . ', ' . ($row['country'] ?? ''), ', '),
        'ip' => $row['ip_address'],
        'visits' => (int) $row['visits'],
        'lastVisit' => $row['last_visit'],
    ];
}, $locations);
$pointsJson = str_replace('</', '<\/', json_encode($points, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

$activePage = 'map';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Visitor Map — Admin</title>
    <link rel="stylesheet" href="<?= e(asset_url('/assets/css/style.css')) ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@10.10.0/ol.css">
    <style>
        #rp-map { width: 100%; height: 70vh; min-height: 420px; border-radius: 12px; border: 1px solid rgba(245,245,247,0.14); background: #0d0d0d; }
        .rp-map-popup {
            position: absolute; left: -50%; bottom: 12px; transform: translateX(-50%);
            background: #0d0d0d; border: 1px solid rgba(204,255,0,0.4); border-radius: 10px;
            padding: 10px 14px; font-size: 13px; min-width: 200px; max-width: 280px;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.6); display: none;
        }
        .rp-map-popup.is-open { display: block; }
        .rp-map-popup .title { font-weight: 700; margin-bottom: 4px; }
        .rp-map-popup .muted { color: #8a8a8a; font-size: 12px; }
        .rp-map-popup-close { position: absolute; top: 4px; right: 8px; color: #8a8a8a; cursor: pointer; font-size: 14px; background: none; border: none; }
        .ol-attribution { font-size: 10px; }
    </style>
</head>
<body class="admin-body">
    <?php require __DIR__ . '/_header.php'; ?>

    <main class="container">
        <h1>Visitor Map</h1>
        <p class="muted"><?= count($points) ?> resolved location<?= count($points) === 1 ? '' : 's' ?> (private/unresolved IPs aren't shown)</p>

        <div id="rp-map"></div>
        <div id="rp-map-popup" class="rp-map-popup">
            <button type="button" class="rp-map-popup-close" id="rp-map-popup-close" aria-label="Close">&times;</button>
            <div id="rp-map-popup-content"></div>
        </div>
    </main>

    <script>window.__MAP_POINTS__ = <?= $pointsJson ?>;</script>
    <script src="https://cdn.jsdelivr.net/npm/ol@10.10.0/dist/ol.js"></script>
    <script src="<?= e(asset_url('/assets/js/admin-map.js')) ?>"></script>
</body>
</html>
