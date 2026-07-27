<?php
/**
 * Site layout — port of src/app/layout.tsx + src/app/(site)/layout.tsx
 * (Header, Footer, EnquiryProvider modal).
 *
 * Expects: $navCategories, $meta (optional overrides), and section content
 * via renderSection('content').
 */
$meta        = $meta ?? [];
$title       = $meta['title'] ?? 'FreshMart — Premium Fresh Produce, Wholesale & Retail';
$description = $meta['description'] ?? 'Premium fresh vegetables, fruits, meat, seafood and dairy delivered daily. Wholesale and retail supplier with 100% quality-checked produce direct from farms.';
$keywords    = $meta['keywords'] ?? 'fresh vegetables, fruits, meat, seafood, dairy, wholesale grocery, organic produce, farm fresh delivery';
$canonical   = $meta['canonical'] ?? null;
$ogImage     = $meta['ogImage'] ?? null;

$staticLinks = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Offers', 'href' => '/offers'],
    ['label' => 'Track Order', 'href' => '/track-order'],
    ['label' => 'About', 'href' => '/about'],
    ['label' => 'Contact', 'href' => '/contact'],
];
$quickSlugs = ['vegetables', 'fruits', 'fresh-meat', 'seafood', 'dairy'];
$quick      = [];
foreach ($quickSlugs as $qs) {
    foreach ($navCategories as $nc) {
        if ($nc['slug'] === $qs) {
            $quick[] = $nc;
        }
    }
}
$currentPath = '/' . trim(service('uri')->getPath(), '/');
$isProductsActive = str_starts_with($currentPath, '/products') || str_starts_with($currentPath, '/category');

$navLink = static function (string $href, string $label, bool $active): string {
    $class = $active
        ? 'relative rounded-full px-3.5 py-2 text-sm font-semibold transition-colors text-brand-700'
        : 'relative rounded-full px-3.5 py-2 text-sm font-semibold transition-colors text-ink-soft hover:text-brand-700';
    $pill = $active ? '<span class="absolute inset-0 -z-10 rounded-full bg-brand-50"></span>' : '';

    return '<a href="' . site_url(ltrim($href, '/')) . '" class="' . $class . '">' . esc($label) . $pill . '</a>';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?></title>
    <meta name="description" content="<?= esc($description, 'attr') ?>">
    <meta name="keywords" content="<?= esc($keywords, 'attr') ?>">
    <meta name="robots" content="<?= esc($meta['robots'] ?? 'index, follow', 'attr') ?>">
    <?php if ($canonical): ?><link rel="canonical" href="<?= esc($canonical, 'attr') ?>"><?php endif ?>
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="FreshMart">
    <meta property="og:url" content="<?= esc($canonical ?? base_url(), 'attr') ?>">
    <meta property="og:title" content="<?= esc($title, 'attr') ?>">
    <meta property="og:description" content="<?= esc($description, 'attr') ?>">
    <meta property="og:image" content="<?= esc($ogImage ?: base_url('assets/img/logo.png'), 'attr') ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= esc($meta['twitterTitle'] ?? 'FreshMart — Premium Fresh Produce', 'attr') ?>">
    <meta name="twitter:description" content="<?= esc($meta['twitterDesc'] ?? 'Farm-fresh groceries delivered daily. Wholesale & retail.', 'attr') ?>">
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/img/favicon-32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/img/favicon-16.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/apple-touch-icon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <?= $this->renderSection('head') ?>
</head>
<body class="antialiased">
<div class="flex min-h-dvh flex-col">
    <header class="sticky top-0 z-40">
        <!-- Announcement strip -->
        <div class="bg-brand-800 text-white">
            <div class="mx-auto flex h-9 max-w-7xl items-center justify-between px-4 text-xs sm:px-6">
                <p class="flex items-center gap-1.5 font-medium">
                    <?= lucide('truck', 'size-3.5 text-accent-400') ?>
                    <span class="hidden sm:inline">Same-day delivery on orders before 2 PM —</span>
                    <span>Wholesale &amp; Retail</span>
                </p>
                <a href="tel:+393773330007" class="flex items-center gap-1.5 font-medium transition-colors hover:text-accent-400">
                    <?= lucide('phone-call', 'size-3.5') ?>
                    +39 377 3330007
                </a>
            </div>
        </div>

        <!-- Main glass bar -->
        <div class="glass shadow-glass">
            <div class="mx-auto max-w-7xl px-4 sm:px-6">
                <div class="flex h-16 items-center justify-between gap-4 lg:h-17">
                    <a href="<?= site_url('/') ?>" class="flex shrink-0 items-center" aria-label="EntryLines Holdings home">
                        <img src="<?= base_url('assets/img/logo.png') ?>" alt="EntryLines Holdings" class="h-9 w-auto lg:h-10">
                    </a>

                    <?= view('partials/search_bar', ['class' => 'hidden max-w-xl md:block']) ?>

                    <!-- Mobile menu trigger -->
                    <div class="lg:hidden">
                        <button data-mobile-menu-open aria-label="Open menu" class="flex size-11 cursor-pointer items-center justify-center rounded-full text-ink transition-colors hover:bg-brand-50">
                            <?= lucide('menu', 'size-6') ?>
                        </button>
                    </div>
                </div>

                <!-- Desktop nav row -->
                <div class="hidden justify-center pb-2 lg:flex">
                    <nav aria-label="Primary" class="hidden items-center justify-center gap-0.5 lg:flex">
                        <?= $navLink('/', 'Home', $currentPath === '/') ?>

                        <!-- Products mega menu -->
                        <div class="relative" data-mega-menu>
                            <a href="<?= site_url('products') ?>"
                               class="flex items-center gap-1 rounded-full px-3.5 py-2 text-sm font-semibold transition-colors <?= $isProductsActive ? 'text-brand-700' : 'text-ink-soft hover:text-brand-700' ?>"
                               aria-expanded="false" data-mega-trigger>
                                Products
                                <?= lucide('chevron-down', 'size-3.5 transition-transform duration-200') ?>
                            </a>
                            <div class="absolute left-1/2 top-full z-50 w-[560px] pt-3 hidden" data-mega-panel>
                                <div class="grid grid-cols-2 gap-x-6 gap-y-1 rounded-3xl border border-black/5 bg-white p-6 shadow-2xl">
                                    <?php foreach ($navCategories as $cat): ?>
                                        <div class="py-1.5">
                                            <a href="<?= site_url('category/' . $cat['slug']) ?>" class="flex items-center gap-1.5 text-sm font-bold text-ink transition-colors hover:text-brand-700">
                                                <?= lucide('leaf', 'size-3.5 text-brand-500') ?>
                                                <?= esc($cat['name']) ?>
                                            </a>
                                            <?php if (! empty($cat['children'])): ?>
                                                <div class="mt-1 flex flex-wrap gap-x-3 pl-5">
                                                    <?php foreach ($cat['children'] as $sub): ?>
                                                        <a href="<?= site_url('category/' . $sub['slug']) ?>" class="text-xs text-ink-soft transition-colors hover:text-brand-700"><?= esc($sub['name']) ?></a>
                                                    <?php endforeach ?>
                                                </div>
                                            <?php endif ?>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            </div>
                        </div>

                        <?php foreach ($quick as $c): ?>
                            <?= $navLink('/category/' . $c['slug'], $c['name'], $currentPath === '/category/' . $c['slug']) ?>
                        <?php endforeach ?>

                        <?php foreach (array_slice($staticLinks, 1) as $l): ?>
                            <?= $navLink($l['href'], $l['label'], $currentPath === $l['href']) ?>
                        <?php endforeach ?>
                    </nav>
                </div>

                <!-- Mobile search -->
                <div class="pb-3 md:hidden">
                    <?= view('partials/search_bar', ['class' => '']) ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile menu drawer -->
    <div data-mobile-menu class="hidden">
        <div data-mobile-menu-overlay class="fixed inset-0 z-50 bg-brand-950/50 backdrop-blur-sm fm-fade"></div>
        <aside data-mobile-menu-panel class="fixed inset-y-0 right-0 z-50 flex w-80 max-w-[85vw] flex-col bg-white shadow-2xl fm-slide-right">
            <div class="flex items-center justify-between border-b border-black/5 p-4">
                <img src="<?= base_url('assets/img/logo.png') ?>" alt="EntryLines Holdings" class="h-8 w-auto">
                <button data-mobile-menu-close aria-label="Close menu" class="flex size-10 cursor-pointer items-center justify-center rounded-full bg-black/5 transition-colors hover:bg-black/10">
                    <?= lucide('x', 'size-5') ?>
                </button>
            </div>
            <nav aria-label="Mobile" class="flex-1 overflow-y-auto p-4">
                <a href="<?= site_url('/') ?>" class="block rounded-xl px-4 py-3 font-semibold text-ink transition-colors hover:bg-brand-50">Home</a>
                <a href="<?= site_url('products') ?>" class="block rounded-xl px-4 py-3 font-semibold text-ink transition-colors hover:bg-brand-50">All Products</a>

                <p class="px-4 pb-1 pt-4 text-xs font-bold uppercase tracking-wider text-black/40">Categories</p>
                <?php foreach ($navCategories as $cat): ?>
                    <div>
                        <a href="<?= site_url('category/' . $cat['slug']) ?>" class="block rounded-xl px-4 py-2.5 text-sm font-semibold text-ink transition-colors hover:bg-brand-50"><?= esc($cat['name']) ?></a>
                        <?php foreach ($cat['children'] as $sub): ?>
                            <a href="<?= site_url('category/' . $sub['slug']) ?>" class="block rounded-xl py-2 pl-8 pr-4 text-sm text-ink-soft transition-colors hover:bg-brand-50"><?= esc($sub['name']) ?></a>
                        <?php endforeach ?>
                    </div>
                <?php endforeach ?>

                <p class="px-4 pb-1 pt-4 text-xs font-bold uppercase tracking-wider text-black/40">More</p>
                <?php foreach (array_slice($staticLinks, 1) as $l): ?>
                    <a href="<?= site_url(ltrim($l['href'], '/')) ?>" class="block rounded-xl px-4 py-2.5 text-sm font-semibold text-ink transition-colors hover:bg-brand-50"><?= esc($l['label']) ?></a>
                <?php endforeach ?>
            </nav>
        </aside>
    </div>

    <main class="flex-1">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="bg-brand-950 text-white">
        <!-- Newsletter band -->
        <div class="border-b border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col items-center gap-6 px-4 py-14 text-center sm:px-6 lg:flex-row lg:justify-between lg:text-left">
                <div>
                    <h2 class="text-2xl font-extrabold sm:text-3xl">Get fresh deals in your inbox</h2>
                    <p class="mt-2 text-sm text-white/60">Seasonal arrivals, wholesale offers, and recipes — once a week, no spam.</p>
                </div>
                <form data-newsletter-form class="w-full max-w-md">
                    <div class="flex gap-2 rounded-full bg-white/10 p-1.5 backdrop-blur">
                        <div class="relative flex-1">
                            <?= lucide('mail', 'pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-white/50') ?>
                            <input type="email" required name="email" placeholder="Your email address" aria-label="Email address"
                                   class="h-11 w-full rounded-full bg-transparent pl-11 pr-2 text-sm text-white placeholder:text-white/50 focus:outline-none">
                        </div>
                        <button type="submit" class="<?= btn('accent', 'md') ?>" data-newsletter-submit>Subscribe</button>
                    </div>
                    <p role="status" data-newsletter-message class="mt-2 hidden pl-4 text-sm"></p>
                </form>
            </div>
        </div>

        <!-- Link columns -->
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:grid-cols-2 sm:px-6 lg:grid-cols-4">
            <div>
                <a href="<?= site_url('/') ?>" class="inline-flex items-center rounded-2xl bg-white px-4 py-3 shadow-lg shadow-black/20">
                    <img src="<?= base_url('assets/img/logo.png') ?>" alt="EntryLines Holdings" class="h-9 w-auto">
                </a>
                <p class="mt-4 text-sm leading-relaxed text-white/60">
                    Premium wholesale &amp; retail supplier of farm-fresh vegetables,
                    fruits, meat, seafood and dairy. Quality checked, delivered daily.
                </p>
                <div class="mt-5 flex gap-3">
                    <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="flex size-10 items-center justify-center rounded-full bg-white/10 transition-all hover:-translate-y-0.5 hover:bg-brand-600">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="size-4.5" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="flex size-10 items-center justify-center rounded-full bg-white/10 transition-all hover:-translate-y-0.5 hover:bg-brand-600">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="size-4.5" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" aria-label="Twitter" class="flex size-10 items-center justify-center rounded-full bg-white/10 transition-all hover:-translate-y-0.5 hover:bg-brand-600">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="size-4.5" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" aria-label="YouTube" class="flex size-10 items-center justify-center rounded-full bg-white/10 transition-all hover:-translate-y-0.5 hover:bg-brand-600">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="size-4.5" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                </div>
            </div>

            <nav aria-label="Categories">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-white/80">
                    <?= lucide('leaf', 'size-4 text-brand-400') ?> Categories
                </h3>
                <ul class="space-y-2.5 text-sm">
                    <?php foreach (array_slice($navCategories, 0, 8) as $c): ?>
                        <li><a href="<?= site_url('category/' . $c['slug']) ?>" class="text-white/60 transition-colors hover:text-brand-300"><?= esc($c['name']) ?></a></li>
                    <?php endforeach ?>
                </ul>
            </nav>

            <nav aria-label="Quick links">
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-white/80">Quick Links</h3>
                <ul class="space-y-2.5 text-sm">
                    <?php
                    $quickLinks = [
                        ['label' => 'All Products', 'href' => 'products'],
                        ['label' => 'Offers', 'href' => 'offers'],
                        ['label' => 'Track Order', 'href' => 'track-order'],
                        ['label' => 'About Us', 'href' => 'about'],
                        ['label' => 'Contact', 'href' => 'contact'],
                        ['label' => 'Privacy Policy', 'href' => 'privacy'],
                        ['label' => 'Terms & Conditions', 'href' => 'terms'],
                    ];
                    foreach ($quickLinks as $l): ?>
                        <li><a href="<?= site_url($l['href']) ?>" class="text-white/60 transition-colors hover:text-brand-300"><?= esc($l['label']) ?></a></li>
                    <?php endforeach ?>
                </ul>
            </nav>

            <div>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-white/80">Contact</h3>
                <ul class="space-y-3.5 text-sm text-white/60">
                    <li class="flex gap-3">
                        <?= lucide('map-pin', 'mt-0.5 size-4 shrink-0 text-brand-400') ?>
                        <span>via Spinoza 49<br>Rome, Italy 00137</span>
                    </li>
                    <li>
                        <a href="tel:+393773330007" class="flex gap-3 transition-colors hover:text-brand-300">
                            <?= lucide('phone', 'mt-0.5 size-4 shrink-0 text-brand-400') ?>
                            +39 377 3330007
                        </a>
                    </li>
                    <li>
                        <a href="mailto:info@entrylinesholdings.com" class="flex gap-3 transition-colors hover:text-brand-300">
                            <?= lucide('mail', 'mt-0.5 size-4 shrink-0 text-brand-400') ?>
                            info@entrylinesholdings.com
                        </a>
                    </li>
                </ul>
                <div class="mt-5 rounded-2xl bg-white/5 p-4 text-xs leading-relaxed text-white/50">
                    <strong class="text-white/70">Business hours:</strong> Mon–Sat 6 AM – 9 PM,
                    Sun 7 AM – 5 PM. Wholesale desk open from 5 AM.
                </div>
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-white/40 sm:flex-row sm:px-6">
                <p>© <?= date('Y') ?> FreshMart. All rights reserved.</p>
                <p>Fresh from farms — wholesale &amp; retail, every day.</p>
            </div>
        </div>
    </footer>
</div>

<!-- Enquiry modal (Request a Quote) — port of enquiry-provider.tsx + enquiry-form.tsx -->
<div data-enquiry-modal class="hidden">
    <div data-enquiry-overlay class="fixed inset-0 z-50 bg-brand-950/50 backdrop-blur-sm fm-fade"></div>
    <div data-enquiry-content class="fixed left-1/2 top-1/2 z-50 flex max-h-[92dvh] w-[calc(100vw-2rem)] max-w-xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl outline-none fm-pop" role="dialog" aria-modal="true" aria-labelledby="enquiry-modal-title">
        <button data-enquiry-close class="absolute right-4 top-4 z-10 flex size-9 cursor-pointer items-center justify-center rounded-full bg-black/5 text-ink-soft transition-colors hover:bg-black/10 focus-visible:outline-2 focus-visible:outline-brand-600">
            <?= lucide('x', 'size-4') ?>
            <span class="sr-only">Close</span>
        </button>
        <div class="flex flex-col gap-1.5 p-6 pb-2">
            <h2 id="enquiry-modal-title" class="text-xl font-bold text-ink">Request a Quote</h2>
            <p class="text-sm text-ink-soft">
                No online payment needed — send us your requirements and our team
                will confirm price and delivery personally.
            </p>
        </div>
        <div class="overflow-y-auto px-6 pb-6">
            <!-- Success state -->
            <div data-enquiry-success class="hidden flex-col items-center gap-4 py-10 text-center fm-pop">
                <span class="flex size-16 items-center justify-center rounded-full bg-brand-50">
                    <?= lucide('circle-check-big', 'size-9 text-brand-600') ?>
                </span>
                <h3 class="text-xl font-bold text-ink">Thank you!</h3>
                <p class="max-w-sm text-sm leading-relaxed text-ink-soft">
                    Your enquiry has been received. Our team will contact you shortly. A
                    confirmation email is on its way to your inbox.
                </p>
                <button type="button" data-enquiry-close class="<?= btn('subtle', 'md') ?>">Continue Browsing</button>
            </div>

            <form data-enquiry-form class="space-y-4" novalidate>
                <input type="hidden" name="productId" value="">
                <input type="hidden" name="productName" value="">
                <div class="rounded-2xl bg-brand-50 px-4 py-3 text-sm">
                    <span class="text-ink-soft">Enquiring about</span>
                    <span class="font-semibold text-brand-700" data-enquiry-product-name></span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="enq-name" class="mb-1.5 block text-sm font-medium text-ink">Full Name *</label>
                        <input id="enq-name" name="customerName" placeholder="Jane Smith" autocomplete="name" class="fm-input">
                        <p role="alert" class="mt-1 hidden text-xs text-danger-600" data-error-for="customerName"></p>
                    </div>
                    <div>
                        <label for="enq-company" class="mb-1.5 block text-sm font-medium text-ink">Company Name</label>
                        <input id="enq-company" name="companyName" placeholder="Optional" autocomplete="organization" class="fm-input">
                    </div>
                    <div>
                        <label for="enq-email" class="mb-1.5 block text-sm font-medium text-ink">Email Address *</label>
                        <input id="enq-email" name="email" type="email" placeholder="you@example.com" autocomplete="email" class="fm-input">
                        <p role="alert" class="mt-1 hidden text-xs text-danger-600" data-error-for="email"></p>
                    </div>
                    <div>
                        <label for="enq-phone" class="mb-1.5 block text-sm font-medium text-ink">Phone Number *</label>
                        <input id="enq-phone" name="phone" type="tel" placeholder="+1 555 000 1234" autocomplete="tel" class="fm-input">
                        <p role="alert" class="mt-1 hidden text-xs text-danger-600" data-error-for="phone"></p>
                    </div>
                    <div>
                        <label for="enq-location" class="mb-1.5 block text-sm font-medium text-ink">Location / City *</label>
                        <input id="enq-location" name="location" placeholder="Your city" class="fm-input">
                        <p role="alert" class="mt-1 hidden text-xs text-danger-600" data-error-for="location"></p>
                    </div>
                    <div>
                        <label for="enq-date" class="mb-1.5 block text-sm font-medium text-ink">Preferred Delivery Date</label>
                        <input id="enq-date" name="preferredDate" type="date" min="<?= date('Y-m-d') ?>" class="fm-input">
                    </div>
                </div>

                <div>
                    <label for="enq-address" class="mb-1.5 block text-sm font-medium text-ink">Delivery Address *</label>
                    <textarea id="enq-address" name="deliveryAddress" rows="2" placeholder="Street, area, landmark…" autocomplete="street-address" class="fm-textarea"></textarea>
                    <p role="alert" class="mt-1 hidden text-xs text-danger-600" data-error-for="deliveryAddress"></p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="enq-qty" class="mb-1.5 block text-sm font-medium text-ink">Quantity *</label>
                        <input id="enq-qty" name="quantity" type="number" min="1" step="any" inputmode="decimal" value="1" class="fm-input">
                        <p role="alert" class="mt-1 hidden text-xs text-danger-600" data-error-for="quantity"></p>
                    </div>
                    <div>
                        <label for="enq-unit" class="mb-1.5 block text-sm font-medium text-ink">Preferred Unit *</label>
                        <div class="relative">
                            <select id="enq-unit" name="preferredUnit" class="fm-select"></select>
                            <?= lucide('chevron-down', 'pointer-events-none absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-black/40') ?>
                        </div>
                        <p role="alert" class="mt-1 hidden text-xs text-danger-600" data-error-for="preferredUnit"></p>
                    </div>
                </div>

                <div>
                    <label for="enq-notes" class="mb-1.5 block text-sm font-medium text-ink">Additional Notes</label>
                    <textarea id="enq-notes" name="notes" rows="3" placeholder="Cleaning preferences, cut style, recurring order, anything else…" class="fm-textarea"></textarea>
                </div>

                <p role="alert" data-enquiry-server-error class="hidden rounded-xl bg-red-50 px-4 py-3 text-sm text-danger-600"></p>

                <button type="submit" class="<?= btn('primary', 'lg', 'w-full') ?>" data-enquiry-submit>
                    <span data-enquiry-submit-idle class="inline-flex items-center gap-2"><?= lucide('send', 'size-5') ?> Send Enquiry</span>
                    <span data-enquiry-submit-busy class="hidden items-center gap-2"><?= lucide('loader-circle', 'size-5 animate-spin') ?> Sending…</span>
                </button>
                <p class="text-center text-xs text-black/40">We'll email you a confirmation and never share your details.</p>
            </form>
        </div>
    </div>
</div>

<!-- Quick view modal — port of the product-card quick view dialog -->
<div data-quickview-modal class="hidden">
    <div data-quickview-overlay class="fixed inset-0 z-50 bg-brand-950/50 backdrop-blur-sm fm-fade"></div>
    <div data-quickview-content class="fixed left-1/2 top-1/2 z-50 flex max-h-[92dvh] w-[calc(100vw-2rem)] max-w-3xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl outline-none p-0 fm-pop" role="dialog" aria-modal="true">
        <button data-quickview-close class="absolute right-4 top-4 z-10 flex size-9 cursor-pointer items-center justify-center rounded-full bg-black/5 text-ink-soft transition-colors hover:bg-black/10 focus-visible:outline-2 focus-visible:outline-brand-600">
            <?= lucide('x', 'size-4') ?>
            <span class="sr-only">Close</span>
        </button>
        <div class="grid max-h-[92dvh] overflow-y-auto sm:grid-cols-2">
            <div class="relative aspect-square bg-cream-dark">
                <img data-qv-image src="" alt="" class="absolute inset-0 size-full object-cover">
                <div class="pointer-events-none absolute left-3 top-3 z-10 flex flex-col items-start gap-1.5" data-qv-badges></div>
            </div>
            <div class="flex flex-col gap-3 p-6 sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-600" data-qv-category></p>
                <h2 class="text-2xl font-extrabold leading-tight text-ink" data-qv-name></h2>
                <p class="flex items-baseline gap-2" data-qv-price></p>
                <span data-qv-stock></span>
                <p class="text-sm text-ink-soft leading-relaxed" data-qv-desc></p>
                <div class="flex flex-wrap gap-1.5 hidden" data-qv-weights></div>
                <div class="mt-auto flex flex-col gap-2 pt-4">
                    <button type="button" class="<?= btn('primary', 'lg') ?>" data-qv-buy>
                        <?= lucide('shopping-basket', 'size-5') ?> <span data-qv-buy-label>Buy Now</span>
                    </button>
                    <a href="#" class="<?= btn('outline', 'lg') ?>" data-qv-link>View Full Details</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>window.FM_BASE = '<?= rtrim(base_url(), '/') ?>';</script>
<script src="<?= base_url('assets/js/site.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
