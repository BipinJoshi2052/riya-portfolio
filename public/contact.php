<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Database;
use App\Mailer;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/');
}

if (!csrf_verify()) {
    flash('contact_error', 'Your session expired, please try again.');
    redirect('/#contact');
}

// Honeypot: bots fill hidden fields, humans never see them.
if (!empty($_POST['company'])) {
    redirect('/#contact');
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

$_SESSION['old_input'] = compact('name', 'email', 'subject', 'message');

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('contact_error', 'Please fill in your name, a valid email, and a message.');
    redirect('/#contact');
}

$ip = client_ip();

$stmt = Database::connection()->prepare(
    'INSERT INTO messages (name, email, subject, message, ip_address) VALUES (:name, :email, :subject, :message, :ip)'
);
$stmt->execute([
    'name' => $name,
    'email' => $email,
    'subject' => $subject ?: null,
    'message' => $message,
    'ip' => $ip,
]);

try {
    Mailer::sendContactNotification([
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message,
        'ip_address' => $ip,
    ]);
} catch (\Throwable $e) {
    error_log('Contact mail send failed: ' . $e->getMessage());
    // The message is already saved, so we don't fail the request over email delivery.
}

unset($_SESSION['old_input']);
flash('contact_success', "Thanks, {$name}! Your message has been sent.");
redirect('/#contact');
