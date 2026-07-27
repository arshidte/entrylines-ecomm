<?php /** Port of src/app/admin/login/page.tsx. */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | FreshMart</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/img/favicon-32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/img/favicon-16.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/apple-touch-icon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="antialiased">
<div class="flex min-h-dvh items-center justify-center bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 px-4">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <span class="mx-auto mb-5 inline-flex items-center rounded-2xl bg-white px-5 py-4 shadow-2xl">
                <img src="<?= base_url('assets/img/logo.png') ?>" alt="EntryLines Holdings" class="h-10 w-auto">
            </span>
            <h1 class="text-2xl font-extrabold text-white">Admin Panel</h1>
            <p class="mt-1 text-sm text-white/50">Sign in to manage your store</p>
        </div>

        <form action="<?= site_url('admin/login') ?>" method="post" class="space-y-4 rounded-3xl bg-white p-8 shadow-2xl">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-ink">Email Address</label>
                <input id="email" name="email" type="email" required autocomplete="username" placeholder="admin@freshmart.com" class="fm-input">
            </div>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-ink">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••" class="fm-input">
            </div>

            <?php if (! empty($error)): ?>
                <p role="alert" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-danger-600"><?= esc($error) ?></p>
            <?php endif ?>

            <button type="submit" class="<?= btn('primary', 'lg', 'w-full') ?>">
                <?= lucide('lock', 'size-5') ?>
                Sign In
            </button>

            <p class="text-center text-xs text-black/40">Protected area — authorized staff only.</p>
        </form>
    </div>
</div>
</body>
</html>
