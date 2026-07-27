<?php
/**
 * Admin layout — port of src/app/admin/(dashboard)/layout.tsx +
 * src/components/admin/sidebar.tsx.
 * Expects: $title, $newEnquiriesCount.
 */
$links = [
    ['href' => 'admin', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
    ['href' => 'admin/products', 'label' => 'Products', 'icon' => 'package'],
    ['href' => 'admin/categories', 'label' => 'Categories', 'icon' => 'folder-tree'],
    ['href' => 'admin/banners', 'label' => 'Banners', 'icon' => 'images'],
    ['href' => 'admin/enquiries', 'label' => 'Enquiries', 'icon' => 'message-square-text'],
    ['href' => 'admin/subscribers', 'label' => 'Subscribers', 'icon' => 'mail'],
    ['href' => 'admin/settings', 'label' => 'Settings', 'icon' => 'settings'],
];
$currentPath = '/' . trim(service('uri')->getPath(), '/');
$isActive = static fn (string $href): bool => $href === 'admin'
    ? $currentPath === '/admin'
    : str_starts_with($currentPath, '/' . $href);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Admin | FreshMart Admin') ?></title>
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
<div class="min-h-dvh bg-[#f4f6f2]">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-60 flex-col bg-brand-950 text-white lg:flex">
        <div class="flex h-16 items-center gap-2 border-b border-white/10 px-4">
            <span class="inline-flex items-center rounded-lg bg-white px-2.5 py-1.5">
                <img src="<?= base_url('assets/img/logo.png') ?>" alt="EntryLines Holdings" class="h-6 w-auto">
            </span>
            <span class="rounded bg-white/10 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-white/60">Admin</span>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto p-3">
            <?php foreach ($links as $l): ?>
                <a href="<?= site_url($l['href']) ?>"
                   class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition-colors <?= $isActive($l['href']) ? 'bg-brand-600 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' ?>">
                    <?= lucide($l['icon'], 'size-4.5') ?>
                    <?= esc($l['label']) ?>
                    <?php if ($l['label'] === 'Enquiries' && ($newEnquiriesCount ?? 0) > 0): ?>
                        <span class="ml-auto flex h-5 min-w-5 items-center justify-center rounded-full bg-accent-500 px-1.5 text-[10px] font-bold"><?= $newEnquiriesCount ?></span>
                    <?php endif ?>
                </a>
            <?php endforeach ?>
        </nav>

        <div class="border-t border-white/10 p-3">
            <a href="<?= site_url('/') ?>" target="_blank" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-white/60 transition-colors hover:bg-white/5 hover:text-white">
                <?= lucide('external-link', 'size-4.5') ?>
                View Website
            </a>
        </div>
    </aside>

    <!-- Mobile bottom nav -->
    <nav class="fixed inset-x-0 bottom-0 z-30 flex justify-around border-t border-black/5 bg-white py-2 shadow-2xl lg:hidden">
        <?php foreach (array_slice($links, 0, 5) as $l): ?>
            <a href="<?= site_url($l['href']) ?>"
               class="relative flex flex-col items-center gap-0.5 rounded-lg px-3 py-1 text-[10px] font-semibold <?= $isActive($l['href']) ? 'text-brand-700' : 'text-black/40' ?>">
                <?= lucide($l['icon'], 'size-5') ?>
                <?= esc($l['label']) ?>
                <?php if ($l['label'] === 'Enquiries' && ($newEnquiriesCount ?? 0) > 0): ?>
                    <span class="absolute -top-0.5 right-1 flex size-4 items-center justify-center rounded-full bg-accent-500 text-[9px] font-bold text-white"><?= $newEnquiriesCount ?></span>
                <?php endif ?>
            </a>
        <?php endforeach ?>
    </nav>

    <div class="lg:pl-60">
        <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-black/5 bg-white/80 px-5 backdrop-blur">
            <p class="text-sm text-ink-soft">
                Welcome back, <span class="font-bold text-ink"><?= esc(session()->get('admin_name') ?? 'Admin') ?></span>
            </p>
            <form action="<?= site_url('admin/logout') ?>" method="post">
                <button type="submit" class="flex cursor-pointer items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold text-ink-soft transition-colors hover:bg-red-50 hover:text-danger-600">
                    <?= lucide('log-out', 'size-4') ?> Sign Out
                </button>
            </form>
        </header>
        <main class="p-5 pb-24 sm:p-8 lg:pb-8">
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>
<script>window.FM_BASE = '<?= rtrim(base_url(), '/') ?>';</script>
<script src="<?= base_url('assets/js/admin.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
