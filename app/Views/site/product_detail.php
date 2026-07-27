<?= $this->extend('layouts/site') ?>

<?= $this->section('head') ?>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES) ?></script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
/** Port of src/app/(site)/products/[slug]/page.tsx. */
$weightOptions = weight_options($product['weightOptions']);
$galleryImages = array_map(static fn ($i) => [
    'url' => $i['url'],
    'alt' => $i['alt'] !== '' ? $i['alt'] : $product['name'],
], $images);

// Payload for the enquiry modal (product actions).
$enquiryPayload = esc(json_encode([
    'id'            => (string) $product['id'],
    'name'          => $product['name'],
    'unit'          => $product['unit'],
    'weightOptions' => $weightOptions,
    'slug'          => $product['slug'],
], JSON_UNESCAPED_SLASHES), 'attr');

$infoBlocks = [];
if ($product['nutrition']) {
    $infoBlocks[] = ['icon' => 'apple', 'title' => 'Nutrition', 'text' => $product['nutrition']];
}
if ($product['storageInstructions']) {
    $infoBlocks[] = ['icon' => 'refrigerator', 'title' => 'Storage Instructions', 'text' => $product['storageInstructions']];
}
if ($product['origin']) {
    $infoBlocks[] = ['icon' => 'map-pin', 'title' => 'Origin', 'text' => $product['origin']];
}
?>
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:py-12">
    <!-- Breadcrumbs -->
    <nav aria-label="Breadcrumb" class="mb-6 flex flex-wrap items-center gap-1.5 text-xs text-black/45">
        <a href="<?= site_url('/') ?>" class="transition-colors hover:text-brand-700">Home</a>
        <?= lucide('chevron-right', 'size-3') ?>
        <a href="<?= site_url('products') ?>" class="transition-colors hover:text-brand-700">Products</a>
        <?= lucide('chevron-right', 'size-3') ?>
        <a href="<?= site_url('category/' . $product['categorySlug']) ?>" class="transition-colors hover:text-brand-700"><?= esc($product['categoryName']) ?></a>
        <?= lucide('chevron-right', 'size-3') ?>
        <span class="font-semibold text-ink"><?= esc($product['name']) ?></span>
    </nav>

    <div class="grid gap-10 lg:grid-cols-2 lg:gap-14">
        <!-- Gallery — port of product-gallery.tsx -->
        <div data-gallery>
            <div class="relative aspect-square cursor-zoom-in overflow-hidden rounded-3xl bg-cream-dark shadow-card" data-gallery-main>
                <?php foreach ($galleryImages as $i => $img): ?>
                    <div data-gallery-image="<?= $i ?>" class="absolute inset-0 fm-gallery-image<?= $i === 0 ? ' is-active' : '' ?>">
                        <img src="<?= esc($img['url'], 'attr') ?>" alt="<?= esc($img['alt'], 'attr') ?>"
                             class="absolute inset-0 size-full object-cover transition-transform duration-300 ease-out" data-gallery-zoom>
                    </div>
                <?php endforeach ?>

                <div class="pointer-events-none absolute left-4 top-4 z-10 flex flex-col items-start gap-1.5">
                    <?php if ($product['isFresh']): ?><?= badge('fresh', 'Fresh') ?><?php endif ?>
                    <?php if ($product['isOrganic']): ?><?= badge('organic', lucide('leaf', 'size-3') . ' Organic') ?><?php endif ?>
                    <?php if ($discount): ?><?= badge('offer', '−' . $discount . '%') ?><?php endif ?>
                </div>
            </div>

            <?php if (count($galleryImages) > 1): ?>
                <div class="mt-4 flex gap-3">
                    <?php foreach ($galleryImages as $i => $img): ?>
                        <button type="button" data-gallery-thumb="<?= $i ?>" aria-label="View image <?= $i + 1 ?>"
                                class="relative size-20 cursor-pointer overflow-hidden rounded-2xl transition-all duration-200 <?= $i === 0 ? 'ring-2 ring-brand-600 ring-offset-2 ring-offset-cream' : 'opacity-60 hover:opacity-100' ?>">
                            <img src="<?= esc($img['url'], 'attr') ?>" alt="<?= esc($img['alt'], 'attr') ?>" class="absolute inset-0 size-full object-cover">
                        </button>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
        </div>

        <div class="flex flex-col gap-5">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="<?= site_url('category/' . $product['categorySlug']) ?>" class="text-xs font-bold uppercase tracking-widest text-brand-600 transition-colors hover:text-brand-800"><?= esc($product['categoryName']) ?></a>
                    <?php if ($product['isNewArrival']): ?><?= badge('new', 'New') ?><?php endif ?>
                    <?php if ($product['isBestSeller']): ?><?= badge('bestseller', 'Best Seller') ?><?php endif ?>
                </div>
                <h1 class="mt-2 text-3xl font-extrabold leading-tight text-ink sm:text-4xl"><?= esc($product['name']) ?></h1>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <p class="flex items-baseline gap-2.5">
                    <span class="text-4xl font-extrabold text-brand-700"><?= format_price($price) ?></span>
                    <?php if ($product['discountPrice'] !== null): ?>
                        <span class="text-xl text-black/35 line-through"><?= format_price((float) $product['price']) ?></span>
                    <?php endif ?>
                    <span class="text-sm font-medium text-black/45">/ <?= esc($product['unit']) ?></span>
                </p>
                <?= stock_badge($product['stockStatus']) ?>
            </div>

            <p class="leading-relaxed text-ink-soft"><?= esc($product['description']) ?></p>

            <!-- Product actions — port of product-actions.tsx -->
            <div class="space-y-5" data-product-actions data-product="<?= $enquiryPayload ?>">
                <?php if (count($weightOptions) > 0): ?>
                    <div>
                        <p class="mb-2 text-sm font-bold text-ink">Weight / Pack Options</p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($weightOptions as $i => $w): ?>
                                <button type="button" data-weight-option="<?= esc($w, 'attr') ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"
                                        class="cursor-pointer rounded-full border px-4 py-2 text-sm font-semibold transition-all duration-200 <?= $i === 0 ? 'border-brand-600 bg-brand-600 text-white shadow-md shadow-brand-600/25' : 'border-black/10 bg-white text-ink-soft hover:border-brand-400 hover:text-brand-700' ?>">
                                    <?= esc($w) ?>
                                </button>
                            <?php endforeach ?>
                        </div>
                    </div>
                <?php endif ?>

                <div class="flex gap-3">
                    <button type="button" data-detail-buy class="<?= btn('primary', 'lg', 'flex-1') ?>"<?= $outOfStock ? ' disabled' : '' ?>>
                        <?= lucide('shopping-basket', 'size-5') ?> <?= $outOfStock ? 'Out of Stock' : 'Buy Now — Send Enquiry' ?>
                    </button>
                    <button type="button" data-wishlist-toggle data-wishlist-detail data-slug="<?= esc($product['slug'], 'attr') ?>"
                            aria-label="Add to wishlist" aria-pressed="false" class="<?= btn('outline', 'icon', 'h-13') ?>">
                        <?= lucide('heart', 'size-5') ?>
                    </button>
                </div>
                <p class="text-xs text-black/45">
                    No online payment required — submit an enquiry and our team confirms
                    price, availability and delivery personally.
                </p>
            </div>

            <!-- Freshness guarantee -->
            <div class="flex gap-4 rounded-3xl bg-brand-50 p-5">
                <?= lucide('shield-check', 'size-9 shrink-0 text-brand-600') ?>
                <div>
                    <p class="font-bold text-brand-800">100% Freshness Guarantee</p>
                    <p class="mt-0.5 text-sm leading-relaxed text-brand-700/80">
                        Not delighted with the quality? We'll replace it or refund you —
                        no questions asked, within 24 hours of delivery.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs font-semibold text-ink-soft">
                <span class="flex items-center gap-2 rounded-2xl bg-white px-4 py-3 shadow-sm">
                    <?= lucide('truck', 'size-4 text-brand-600') ?> Same-day cold-chain delivery
                </span>
                <span class="flex items-center gap-2 rounded-2xl bg-white px-4 py-3 shadow-sm">
                    <?= lucide('badge-check', 'size-4 text-brand-600') ?> Quality checked before dispatch
                </span>
            </div>
        </div>
    </div>

    <!-- Info blocks -->
    <?php if (count($infoBlocks) > 0): ?>
        <div data-animate="fade">
            <div class="mt-14 grid gap-5 sm:grid-cols-3">
                <?php foreach ($infoBlocks as $block): ?>
                    <div class="rounded-3xl bg-white p-6 shadow-card">
                        <span class="mb-3 flex size-11 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                            <?= lucide($block['icon'], 'size-5') ?>
                        </span>
                        <h2 class="mb-1.5 font-bold text-ink"><?= esc($block['title']) ?></h2>
                        <p class="text-sm leading-relaxed text-ink-soft"><?= esc($block['text']) ?></p>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    <?php endif ?>
</div>

<!-- Related products -->
<?php if (count($related) > 0): ?>
    <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6">
        <?= view('partials/section_heading', [
            'eyebrow'   => 'You may also like',
            'title'     => 'Related Products',
            'subtitle'  => null,
            'href'      => site_url('category/' . $product['categorySlug']),
            'linkLabel' => 'More ' . $product['categoryName'],
            'align'     => 'left',
        ]) ?>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($related as $p): ?>
                <?= view('partials/product_card', ['product' => $p, 'layout' => 'grid']) ?>
            <?php endforeach ?>
        </div>
    </section>
<?php endif ?>

<!-- Recently viewed — populated by site.js from localStorage -->
<section data-recently-viewed data-current-slug="<?= esc($product['slug'], 'attr') ?>" class="mx-auto hidden max-w-7xl px-4 pb-16 sm:px-6">
    <?= view('partials/section_heading', ['eyebrow' => 'Pick up where you left off', 'title' => 'Recently Viewed', 'subtitle' => null, 'href' => null, 'linkLabel' => 'View All', 'align' => 'left']) ?>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4" data-recently-viewed-grid></div>
</section>
<?= $this->endSection() ?>
