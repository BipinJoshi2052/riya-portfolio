<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Auth;

// This page only works while there is no admin account yet, so it's safe to
// leave on the server — but feel free to delete it once you've logged in.
if (Auth::adminCount() > 0) {
    redirect('/login');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Your session expired, please try again.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');

        if (strlen($username) < 3) {
            $error = 'Username must be at least 3 characters.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            Auth::createAdmin($username, $password);
            redirect('/login');
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account — Riya Portfolio</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="auth-body">
    <main class="auth-card">
        <h1>Create Admin Account</h1>
        <p class="muted">No admin account exists yet. Create the first one below — this page disables itself afterwards.</p>

        <?php if ($error): ?>
            <p class="alert alert-error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post" action="/setup">
            <?= csrf_field() ?>

            <label for="username">Username</label>
            <input type="text" id="username" name="username" required minlength="3" autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="8">

            <label for="password_confirm">Confirm password</label>
            <input type="password" id="password_confirm" name="password_confirm" required minlength="8">

            <button type="submit">Create account</button>
        </form>
    </main>
</body>
</html>
