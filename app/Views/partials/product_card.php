<?php
/**
 * Port of product-card.tsx.
 * Vars: $product (product_card_data shape), $layout ('grid'|'list').
 */
$layout     = $layout ?? 'grid';
$outOfStock = $product['stockStatus'] === 'OUT_OF_STOCK';
$detailUrl  = site_url('products/' . $product['slug']);

// JSON payload consumed by quick view + enquiry modal (site.js).
$payload = esc(json_encode($product, JSON_UNESCAPED_SLASHES), 'attr');

$badgesHtml = '';
if ($product['isFresh']) {
    $badgesHtml .= badge('fresh', 'Fresh');
}
if ($product['isOrganic']) {
    $badgesHtml .= badge('organic', lucide('leaf', 'size-3') . ' Organic');
}
if ($product['onOffer'] && $product['discountPrice']) {
    $badgesHtml .= badge('offer', '−' . round((1 - $product['discountPrice'] / $product['price']) * 100) . '%');
}
if ($product['isNewArrival']) {
    $badgesHtml .= badge('new', 'New');
}
if ($product['isBestSeller']) {
    $badgesHtml .= badge('bestseller', 'Best Seller');
}

$priceHtml = '<span class="text-lg font-extrabold text-brand-700">' . format_price($product['discountPrice'] ?? $product['price']) . '</span>'
    . ($product['discountPrice'] ? '<span class="text-sm text-black/35 line-through">' . format_price($product['price']) . '</span>' : '')
    . '<span class="text-xs font-medium text-black/45">/ ' . esc($product['unit']) . '</span>';
?>
<?php if ($layout === 'list'): ?>
<article data-product="<?= $payload ?>" class="group flex gap-5 rounded-3xl bg-white p-4 shadow-card transition-all duration-300 hover:-translate-y-[3px] hover:shadow-card-hover">
    <a href="<?= $detailUrl ?>" class="relative block size-32 shrink-0 overflow-hidden rounded-2xl bg-cream-dark sm:size-40">
        <?php if ($product['image']): ?>
            <img src="<?= esc($product['image'], 'attr') ?>" alt="<?= esc($product['imageAlt'], 'attr') ?>" loading="lazy" class="absolute inset-0 size-full object-cover transition-transform duration-500 group-hover:scale-105">
        <?php endif ?>
        <div class="pointer-events-none absolute left-3 top-3 z-10 flex flex-col items-start gap-1.5"><?= $badgesHtml ?></div>
    </a>
    <div class="flex min-w-0 flex-1 flex-col py-1">
        <p class="text-xs font-semibold uppercase tracking-wider text-brand-600"><?= esc($product['categoryName']) ?></p>
        <a href="<?= $detailUrl ?>">
            <h3 class="mt-0.5 font-bold text-ink transition-colors hover:text-brand-700 sm:text-lg"><?= esc($product['name']) ?></h3>
        </a>
        <p class="mt-1 hidden text-sm text-ink-soft sm:line-clamp-2"><?= esc($product['shortDescription']) ?></p>
        <div class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-2">
            <div class="flex items-center gap-3">
                <p class="flex items-baseline gap-2"><?= $priceHtml ?></p>
                <span class="hidden sm:inline-flex"><?= stock_badge($product['stockStatus']) ?></span>
            </div>
            <div class="flex gap-2">
                <button type="button" data-buy-now class="<?= btn('primary', 'sm') ?>"<?= $outOfStock ? ' disabled' : '' ?>>
                    <?= lucide('shopping-basket', 'size-4') ?> Buy Now
                </button>
                <a href="<?= $detailUrl ?>" class="<?= btn('subtle', 'sm') ?>">Details</a>
            </div>
        </div>
    </div>
</article>
<?php else: ?>
<article data-product="<?= $payload ?>" class="group relative flex h-full flex-col overflow-hidden rounded-3xl bg-white shadow-card transition-all duration-300 hover:-translate-y-1.5 hover:shadow-card-hover">
    <div class="relative aspect-[4/3] overflow-hidden bg-cream-dark">
        <a href="<?= $detailUrl ?>" aria-label="<?= esc($product['name'], 'attr') ?>">
            <?php if ($product['image']): ?>
                <img src="<?= esc($product['image'], 'attr') ?>" alt="<?= esc($product['imageAlt'], 'attr') ?>" loading="lazy" class="absolute inset-0 size-full object-cover transition-transform duration-500 ease-out group-hover:scale-108">
            <?php endif ?>
        </a>
        <div class="pointer-events-none absolute left-3 top-3 z-10 flex flex-col items-start gap-1.5"><?= $badgesHtml ?></div>

        <!-- Hover actions -->
        <div class="absolute right-3 top-3 z-10 flex flex-col gap-2">
            <button type="button" data-wishlist-toggle data-slug="<?= esc($product['slug'], 'attr') ?>"
                    aria-label="Add to wishlist" aria-pressed="false"
                    class="flex size-9 cursor-pointer items-center justify-center rounded-full shadow-md backdrop-blur transition-all duration-200 bg-white/85 text-ink-soft hover:bg-white hover:text-danger-500">
                <?= lucide('heart', 'size-4') ?>
            </button>
            <button type="button" data-quick-view
                    aria-label="Quick view <?= esc($product['name'], 'attr') ?>"
                    class="flex size-9 cursor-pointer items-center justify-center rounded-full bg-white/85 text-ink-soft shadow-md backdrop-blur transition-all duration-200 hover:bg-white hover:text-brand-600 sm:translate-y-1 sm:opacity-0 sm:group-hover:translate-y-0 sm:group-hover:opacity-100">
                <?= lucide('eye', 'size-4') ?>
            </button>
        </div>
    </div>

    <div class="flex flex-1 flex-col p-4">
        <div class="flex items-start justify-between gap-2">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-brand-600"><?= esc($product['categoryName']) ?></p>
            <?= stock_badge($product['stockStatus']) ?>
        </div>
        <a href="<?= $detailUrl ?>">
            <h3 class="mt-1 font-bold leading-snug text-ink transition-colors hover:text-brand-700"><?= esc($product['name']) ?></h3>
        </a>
        <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-ink-soft"><?= esc($product['shortDescription']) ?></p>
        <div class="mt-auto pt-3">
            <p class="flex items-baseline gap-2"><?= $priceHtml ?></p>
            <div class="mt-3 flex gap-2">
                <button type="button" data-buy-now class="<?= btn('primary', 'sm', 'flex-1') ?>"<?= $outOfStock ? ' disabled' : '' ?>>
                    <?= lucide('shopping-basket', 'size-4') ?> <?= $outOfStock ? 'Out of Stock' : 'Buy Now' ?>
                </button>
            </div>
        </div>
    </div>
</article>
<?php endif ?>
