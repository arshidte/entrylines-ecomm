<?php /** Port of src/app/not-found.tsx — standalone page (no site chrome). */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 | FreshMart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="antialiased">
<div class="flex min-h-dvh flex-col items-center justify-center gap-6 bg-cream px-4 text-center">
    <span class="flex size-20 items-center justify-center rounded-full bg-brand-50">
        <?= lucide('sprout', 'size-10 text-brand-600') ?>
    </span>
    <div>
        <h1 class="text-5xl font-extrabold text-ink">404</h1>
        <p class="mt-2 text-lg font-semibold text-ink">This aisle is empty</p>
        <p class="mt-1 max-w-sm text-sm text-ink-soft">
            The page you're looking for doesn't exist or has been moved.
            Let's get you back to the fresh stuff.
        </p>
    </div>
    <div class="flex gap-3">
        <a href="<?= site_url('/') ?>" class="<?= btn('primary', 'md') ?>">Back to Home</a>
        <a href="<?= site_url('products') ?>" class="<?= btn('outline', 'md') ?>">Browse Products</a>
    </div>
</div>
</body>
</html>
