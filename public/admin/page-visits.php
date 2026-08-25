<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Auth;
use App\Database;
use App\GeoLocation;

Auth::requireLogin();

// Resolve a handful of not-yet-located IPs on each admin page load, so the
// locations fill in progressively without needing a cron job or slowing
// down real visitors (this is the only place geolocation lookups happen).
try {
    GeoLocation::resolvePending(5);
} catch (\Throwable $e) {
    error_log('GeoLocation::resolvePending failed: ' . $e->getMessage());
}

$perPage = 50;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$pdo = Database::connection();

$total = (int) $pdo->query('SELECT COUNT(*) FROM page_views')->fetchColumn();

$stmt = $pdo->prepare(
    'SELECT pv.id, pv.ip_address, pv.page, pv.user_agent, pv.created_at,
            l.display_name, l.city, l.region, l.country, l.latitude, l.longitude, l.status AS location_status
     FROM page_views pv
     LEFT JOIN ip_locations l ON l.ip_address = pv.ip_address
     ORDER BY pv.created_at DESC
     LIMIT :limit OFFSET :offset'
);
$stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$visits = $stmt->fetchAll();

$totalPages = max(1, (int) ceil($total / $perPage));
$activePage = 'page-visits';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Page Visits — Admin</title>
    <link rel="stylesheet" href="<?= e(asset_url('/assets/css/style.css')) ?>">
</head>
<body class="admin-body">
    <?php require __DIR__ . '/_header.php'; ?>

    <main class="container">
        <h1>Page Visits</h1>
        <p class="muted"><?= number_format($total) ?> total view<?= $total === 1 ? '' : 's' ?></p>

        <div class="table-wrap">
            <table>
                <colgroup>
                    <col style="width:16%">
                    <col style="width:12%">
                    <col style="width:10%">
                    <col style="width:34%">
                    <col style="width:28%">
                </colgroup>
                <thead>
                    <tr>
                        <th>When</th>
                        <th>IP address</th>
                        <th>Page</th>
                        <th>Location</th>
                        <th>User agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$visits): ?>
                        <tr><td colspan="5" class="empty">No page views recorded yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($visits as $v): ?>
                        <tr>
                            <td><?= e(date('M j, Y g:i A', strtotime($v['created_at']))) ?></td>
                            <td><?= e($v['ip_address']) ?></td>
                            <td><?= e($v['page']) ?></td>
                            <td>
                                <?php if ($v['location_status'] === 'resolved'): ?>
                                    <?= e($v['display_name'] ?: trim(($v['city'] ?? '') . ', ' . ($v['country'] ?? ''), ', ')) ?>
                                    <?php if ($v['latitude'] !== null && $v['longitude'] !== null): ?>
                                        <br>
                                        <span class="muted" style="font-size:0.8em">
                                            <?= e($v['latitude']) ?>, <?= e($v['longitude']) ?>
                                            &middot;
                                            <a href="https://www.openstreetmap.org/?mlat=<?= e($v['latitude']) ?>&mlon=<?= e($v['longitude']) ?>#map=14/<?= e($v['latitude']) ?>/<?= e($v['longitude']) ?>" target="_blank" rel="noopener">view on map</a>
                                        </span>
                                    <?php endif; ?>
                                <?php elseif ($v['location_status'] === 'private'): ?>
                                    <span class="muted">Local / private IP</span>
                                <?php elseif ($v['location_status'] === 'failed'): ?>
                                    <span class="muted">Unavailable</span>
                                <?php else: ?>
                                    <span class="muted">Resolving…</span>
                                <?php endif; ?>
                            </td>
                            <td class="ua-cell" title="<?= e($v['user_agent']) ?>"><?= e(simplify_user_agent($v['user_agent'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?page=<?= $p ?>" class="<?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </main>
</body>
</html>
