<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Auth;
use App\Settings;

Auth::requireLogin();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Your session expired, please try again.';
    } else {
        $color = trim((string) ($_POST['primary_color'] ?? ''));
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $error = 'Please choose a valid color.';
        } else {
            Settings::set('theme_primary_color', strtolower($color));
            flash('theme_success', 'Theme color updated.');
            redirect('/admin/theme');
        }
    }
}

$success = flash('theme_success');
$currentColor = theme_primary_color();
$activePage = 'theme';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Theme — Admin</title>
    <link rel="stylesheet" href="<?= e(asset_url('/assets/css/style.css')) ?>">
    <?= theme_style_tag() ?>
    <style>
        .rp-theme-form { max-width: 420px; }
        .rp-color-row { display: flex; align-items: center; flex-wrap: wrap; gap: 16px; margin: 1.25rem 0; }
        .rp-color-row input[type="color"] {
            width: 64px; height: 64px; padding: 0; border: 1px solid rgba(245,245,247,0.16);
            border-radius: 12px; background: none; cursor: pointer;
        }
        .rp-color-row input[type="text"] {
            background: #0d0d0d; border: 1px solid rgba(245,245,247,0.16); color: #f5f5f7;
            padding: 0.65rem 0.85rem; border-radius: 10px; font: inherit; width: 140px;
            text-transform: uppercase;
        }
        .rp-swatches { display: flex; flex-wrap: wrap; gap: 10px; margin: 1rem 0 1.5rem; }
        .rp-swatch {
            width: 34px; height: 34px; border-radius: 8px; border: 2px solid rgba(245,245,247,0.16);
            cursor: pointer; padding: 0;
        }
        .rp-swatch:hover { border-color: rgba(245,245,247,0.4); }
        .rp-preview {
            margin-top: 1.5rem; padding: 1.25rem; border-radius: 12px; background: #0d0d0d;
            border: 1px solid rgba(245,245,247,0.14);
        }
        .rp-preview-btn {
            display: inline-flex; align-items: center; gap: 8px; background: var(--rp-primary);
            color: #0a0a0a; padding: 12px 22px; border-radius: 999px; font-weight: 700; font-size: 14px;
        }
        .rp-preview-tag {
            display: inline-block; margin-left: 12px; font-family: 'JetBrains Mono', monospace; font-size: 11px;
            text-transform: uppercase; letter-spacing: 0.06em; border: 1px solid var(--rp-primary); color: var(--rp-primary);
            border-radius: 999px; padding: 6px 12px;
        }
    </style>
</head>
<body class="admin-body">
    <?php require __DIR__ . '/_header.php'; ?>

    <main class="container">
        <h1>Theme</h1>
        <p class="muted">Choose the site's primary accent color — used across buttons, links, highlights, and the visitor map markers.</p>

        <?php if ($success): ?>
            <p class="alert alert-success"><?= e($success) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="alert alert-error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post" class="rp-theme-form" id="rp-theme-form">
            <?= csrf_field() ?>

            <div class="rp-color-row">
                <input type="color" id="rp-color-picker" value="<?= e($currentColor) ?>" aria-label="Pick a color">
                <input type="text" id="rp-color-text" name="primary_color" value="<?= e($currentColor) ?>" maxlength="7" pattern="#[0-9a-fA-F]{6}" required>
            </div>

            <div class="rp-swatches">
                <?php foreach (['#ccff00', '#5b8def', '#ff6b6b', '#3fb27f', '#f5a623', '#a06bff', '#ff4fa3', '#22d3ee'] as $swatch): ?>
                    <button type="button" class="rp-swatch" data-color="<?= e($swatch) ?>" style="background:<?= e($swatch) ?>" aria-label="<?= e($swatch) ?>"></button>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="rp-submit" style="width:auto;padding:0.75rem 1.5rem">Save theme color</button>
        </form>

        <div class="rp-preview">
            <span class="rp-preview-btn">Preview button</span>
            <span class="rp-preview-tag">Preview tag</span>
        </div>
    </main>

    <script>
        (function () {
            var picker = document.getElementById('rp-color-picker');
            var text = document.getElementById('rp-color-text');
            var preview = document.querySelector('.rp-preview');

            function apply(color) {
                if (/^#[0-9a-fA-F]{6}$/.test(color)) {
                    document.documentElement.style.setProperty('--rp-primary', color);
                }
            }

            picker.addEventListener('input', function () {
                text.value = picker.value;
                apply(picker.value);
            });
            text.addEventListener('input', function () {
                if (/^#[0-9a-fA-F]{6}$/.test(text.value)) {
                    picker.value = text.value;
                    apply(text.value);
                }
            });
            document.querySelectorAll('.rp-swatch').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var color = btn.getAttribute('data-color');
                    picker.value = color;
                    text.value = color;
                    apply(color);
                });
            });
        })();
    </script>
</body>
</html>
