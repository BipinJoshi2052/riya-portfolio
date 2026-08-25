<?php

declare(strict_types=1);

namespace App;

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

final class Mailer
{
    public static function sendContactNotification(array $message, ?int $messageId = null): bool
    {
        require_once __DIR__ . '/../config/config.php';
        $mail = app_config()['mail'];

        $recipients = self::parseRecipients($mail['send_to']);
        $subject = 'New contact form message: ' . ($message['subject'] ?: 'No subject');

        if ($mail['host'] === '' || !$recipients) {
            $error = 'SMTP host or SEND_MAIL_TO is not configured in .env';
            error_log('Mailer: ' . $error . ', skipping send.');
            self::logAttempt($messageId, $recipients, $subject, 'failed', $error);
            return false;
        }

        $mailer = new PHPMailer(true);

        try {
            $mailer->isSMTP();
            $mailer->Host = $mail['host'];
            $mailer->Port = $mail['port'];
            $mailer->SMTPAuth = true;
            $mailer->Username = $mail['username'];
            $mailer->Password = $mail['password'];
            $mailer->SMTPSecure = $mail['secure'];
            $mailer->CharSet = 'UTF-8';

            $mailer->setFrom($mail['from_address'], $mail['from_name']);
            foreach ($recipients as $recipient) {
                $mailer->addAddress($recipient);
            }
            $mailer->addReplyTo($message['email'], $message['name']);

            $mailer->Subject = $subject;
            $mailer->isHTML(true);
            $mailer->Body = self::buildHtmlBody($message);
            $mailer->AltBody = self::buildPlainBody($message);

            $mailer->send();

            self::logAttempt($messageId, $recipients, $subject, 'success', null);
            return true;
        } catch (PHPMailerException $e) {
            $error = $mailer->ErrorInfo !== '' ? $mailer->ErrorInfo : $e->getMessage();
            error_log('Mailer send failed: ' . $error);
            self::logAttempt($messageId, $recipients, $subject, 'failed', $error);
            return false;
        }
    }

    /** @param string[] $recipients */
    private static function logAttempt(?int $messageId, array $recipients, string $subject, string $status, ?string $error): void
    {
        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO email_logs (message_id, recipients, subject, status, error_message)
                 VALUES (:message_id, :recipients, :subject, :status, :error_message)'
            );
            $stmt->execute([
                'message_id' => $messageId,
                'recipients' => implode(', ', $recipients) ?: '(none)',
                'subject' => $subject,
                'status' => $status,
                'error_message' => $error,
            ]);
        } catch (\Throwable $e) {
            error_log('Failed to write email_logs row: ' . $e->getMessage());
        }
    }

    /** @return string[] */
    private static function parseRecipients(string $sendTo): array
    {
        $emails = array_map('trim', explode(',', $sendTo));

        return array_values(array_filter(
            $emails,
            fn ($email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        ));
    }

    private static function buildHtmlBody(array $m): string
    {
        return '<h2>New contact form submission</h2>'
            . '<p><strong>Name:</strong> ' . e($m['name']) . '</p>'
            . '<p><strong>Email:</strong> ' . e($m['email']) . '</p>'
            . '<p><strong>Subject:</strong> ' . e($m['subject'] ?: '(none)') . '</p>'
            . '<p><strong>Message:</strong><br>' . nl2br(e($m['message'])) . '</p>'
            . '<hr><p style="color:#888;font-size:12px;">IP: ' . e($m['ip_address'] ?? '') . '</p>';
    }

    private static function buildPlainBody(array $m): string
    {
        return "New contact form submission\n\n"
            . "Name: {$m['name']}\n"
            . "Email: {$m['email']}\n"
            . 'Subject: ' . ($m['subject'] ?: '(none)') . "\n\n"
            . "Message:\n{$m['message']}\n";
    }
}
