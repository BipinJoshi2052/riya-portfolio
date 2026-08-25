<?php
/** @var string $activePage */
?>
<header class="admin-header">
    <div class="container admin-header-inner">
        <span class="admin-brand">मेरो बुडिको वेबसाईट</span>
        <nav class="admin-nav">
            <a href="/admin/page-visits" class="<?= $activePage === 'page-visits' ? 'active' : '' ?>">Page Visits</a>
            <a href="/admin/messages" class="<?= $activePage === 'messages' ? 'active' : '' ?>">Messages</a>
            <a href="/logout">Log out</a>
        </nav>
    </div>
</header>
