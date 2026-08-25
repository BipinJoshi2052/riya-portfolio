<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\PageViewTracker;

$ip = client_ip();
try {
    PageViewTracker::record($ip, '/', $config['page_view_cooldown_minutes']);
} catch (\Throwable $e) {
    error_log('Page view tracking failed: ' . $e->getMessage());
    // Never let analytics failures break the page for a real visitor.
}

$success = flash('contact_success');
$error = flash('contact_error');
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riya Portfolio</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <h1>Riya</h1>
            <p>Portfolio site — placeholder content until the real design is dropped in.</p>
        </div>
    </header>

    <main class="container">
        <section id="contact" class="contact-section">
            <h2>Get in touch</h2>

            <?php if ($success): ?>
                <p class="alert alert-success"><?= e($success) ?></p>
            <?php endif; ?>

            <?php if ($error): ?>
                <p class="alert alert-error"><?= e($error) ?></p>
            <?php endif; ?>

            <form method="post" action="/contact" class="contact-form">
                <?= csrf_field() ?>

                <!-- Honeypot field — real users never fill this in. Name/id are
                     deliberately not "company"/"website"/etc: browser autofill
                     will silently populate hidden fields with common names,
                     which would falsely trigger the bot check. -->
                <div class="hp-field" aria-hidden="true">
                    <label for="hp_check">Leave this field blank</label>
                    <input type="text" id="hp_check" name="hp_check" tabindex="-1" autocomplete="off">
                </div>

                <label for="name">Name</label>
                <input type="text" id="name" name="name" required maxlength="150" value="<?= e($old['name'] ?? '') ?>">

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required maxlength="190" value="<?= e($old['email'] ?? '') ?>">

                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" maxlength="255" value="<?= e($old['subject'] ?? '') ?>">

                <label for="message">Message</label>
                <textarea id="message" name="message" required rows="6"><?= e($old['message'] ?? '') ?></textarea>

                <button type="submit">Send message</button>
            </form>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Riya. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
