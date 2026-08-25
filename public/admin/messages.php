<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Auth;
use App\Database;

Auth::requireLogin();

$pdo = Database::connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'mark_read' && $id > 0) {
        $stmt = $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    } elseif (($_POST['action'] ?? '') === 'delete' && $id > 0) {
        $stmt = $pdo->prepare('DELETE FROM messages WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
    redirect('/admin/messages');
}

$perPage = 30;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$total = (int) $pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();

$stmt = $pdo->prepare(
    'SELECT id, name, email, subject, message, ip_address, is_read, created_at
     FROM messages ORDER BY created_at DESC LIMIT :limit OFFSET :offset'
);
$stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll();

$totalPages = max(1, (int) ceil($total / $perPage));
$activePage = 'messages';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages — Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-body">
    <?php require __DIR__ . '/_header.php'; ?>

    <main class="container">
        <h1>Messages</h1>
        <p class="muted"><?= number_format($total) ?> total message<?= $total === 1 ? '' : 's' ?></p>

        <?php if (!$messages): ?>
            <p class="empty">No messages yet.</p>
        <?php endif; ?>

        <div class="message-list">
            <?php foreach ($messages as $m): ?>
                <article class="message-card <?= $m['is_read'] ? '' : 'unread' ?>">
                    <header>
                        <div>
                            <strong><?= e($m['name']) ?></strong>
                            &lt;<a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>&gt;
                            <?php if (!$m['is_read']): ?><span class="badge">New</span><?php endif; ?>
                        </div>
                        <time><?= e($m['created_at']) ?></time>
                    </header>

                    <?php if ($m['subject']): ?>
                        <p class="message-subject"><?= e($m['subject']) ?></p>
                    <?php endif; ?>

                    <p class="message-body"><?= nl2br(e($m['message'])) ?></p>

                    <footer>
                        <span class="muted">IP: <?= e($m['ip_address'] ?? 'unknown') ?></span>
                        <div class="message-actions">
                            <?php if (!$m['is_read']): ?>
                                <form method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                    <input type="hidden" name="action" value="mark_read">
                                    <button type="submit" class="btn-link">Mark as read</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" onsubmit="return confirm('Delete this message?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn-link btn-danger">Delete</button>
                            </form>
                        </div>
                    </footer>
                </article>
            <?php endforeach; ?>
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
