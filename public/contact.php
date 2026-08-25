<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Database;
use App\Mailer;

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== '';

function respond(bool $isAjax, bool $success, string $message): never
{
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }

    flash($success ? 'contact_success' : 'contact_error', $message);
    redirect('/#contact');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/');
}

if (!csrf_verify()) {
    respond($isAjax, false, 'Your session expired, please refresh the page and try again.');
}

// Honeypot: bots fill hidden fields, humans never see them.
if (!empty($_POST['hp_check'])) {
    respond($isAjax, true, 'Thanks for reaching out!');
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

$_SESSION['old_input'] = compact('name', 'email', 'subject', 'message');

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond($isAjax, false, 'Please fill in your name, a valid email, and a message.');
}

$ip = client_ip();

try {
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
    $messageId = (int) Database::connection()->lastInsertId();
} catch (\Throwable $e) {
    error_log('Contact form DB insert failed: ' . $e->getMessage());
    respond($isAjax, false, 'Sorry, something went wrong on our end. Please try again shortly.');
}

try {
    Mailer::sendContactNotification([
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message,
        'ip_address' => $ip,
    ], $messageId);
} catch (\Throwable $e) {
    error_log('Contact mail send failed: ' . $e->getMessage());
    // The message is already saved, so we don't fail the request over email delivery.
}

unset($_SESSION['old_input']);
respond($isAjax, true, "Thanks, {$name}! Your message has been sent. Expect a reply within a couple of working days.");
