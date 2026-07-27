<?php
/**
 * Port of product-listing.tsx + filters-sidebar.tsx + listing-controls.tsx +
 * pagination.tsx. Filter interactions navigate with updated query params
 * (handled by site.js), matching the router.push behaviour of the original.
 *
 * Vars: $result (products/total/page/totalPages), $params, $navCategories,
 *       $basePath, $activeCategory?
 */
$activeCategory = $activeCategory ?? ($params['category'] ?? null);
$view           = ($params['view'] ?? '') === 'list' ? 'list' : 'grid';
$products       = $result['products'];
$total          = $result['total'];
$page           = $result['page'];
$totalPages     = $result['totalPages'];

$activeCount = 0;
foreach (['minPrice', 'maxPrice', 'availability', 'organic', 'fresh', 'offers'] as $k) {
    if (! empty($params[$k])) {
        $activeCount++;
    }
}

$sortOptions = [
    'newest'     => 'Newest',
    'price-asc'  => 'Price: Low to High',
    'price-desc' => 'Price: High to Low',
    'popular'    => 'Popularity',
    'alpha'      => 'Alphabetical',
];
$currentSort = $params['sort'] ?? 'newest';

$checkbox = static function (string $key, string $value, string $label, array $params): string {
    $checked = ($params[$key] ?? '') === $value;

    return '<label class="flex cursor-pointer items-center gap-2.5 py-1 text-sm text-ink-soft transition-colors hover:text-ink">'
        . '<input type="checkbox" data-filter-param="' . $key . '" data-filter-value="' . $value . '"' . ($checked ? ' checked' : '')
        . ' class="size-4 cursor-pointer rounded accent-[#2E7D32]">'
        . esc($label) . '</label>';
};

// The filter panel content (rendered in both the desktop sidebar and the mobile drawer).
ob_start();
?>
<div class="space-y-5">
    <div class="border-b border-black/5 pb-5">
        <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-ink">Category</h3>
        <ul class="space-y-0.5 text-sm">
            <li>
                <a href="<?= site_url('products') ?>" class="block rounded-lg px-2 py-1.5 transition-colors <?= ! $activeCategory ? 'bg-brand-50 font-bold text-brand-700' : 'text-ink-soft hover:text-brand-700' ?>">All Products</a>
            </li>
            <?php foreach ($navCategories as $cat): ?>
                <li>
                    <a href="<?= site_url('category/' . $cat['slug']) ?>" class="block rounded-lg px-2 py-1.5 transition-colors <?= $activeCategory === $cat['slug'] ? 'bg-brand-50 font-bold text-brand-700' : 'text-ink-soft hover:text-brand-700' ?>"><?= esc($cat['name']) ?></a>
                    <?php foreach ($cat['children'] as $sub): ?>
                        <a href="<?= site_url('category/' . $sub['slug']) ?>" class="block rounded-lg py-1 pl-6 pr-2 text-xs transition-colors <?= $activeCategory === $sub['slug'] ? 'font-bold text-brand-700' : 'text-black/45 hover:text-brand-700' ?>"><?= esc($sub['name']) ?></a>
                    <?php endforeach ?>
                </li>
            <?php endforeach ?>
        </ul>
    </div>

    <div class="border-b border-black/5 pb-5">
        <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-ink">Price Range</h3>
        <form data-price-form class="flex items-center gap-2">
            <input type="number" min="0" step="0.5" placeholder="Min" aria-label="Minimum price" name="minPrice" value="<?= esc($params['minPrice'] ?? '', 'attr') ?>" class="fm-input h-10">
            <span class="text-black/30">–</span>
            <input type="number" min="0" step="0.5" placeholder="Max" aria-label="Maximum price" name="maxPrice" value="<?= esc($params['maxPrice'] ?? '', 'attr') ?>" class="fm-input h-10">
            <button type="submit" class="<?= btn('subtle', 'sm') ?>">Go</button>
        </form>
    </div>

    <div class="border-b border-black/5 pb-5">
        <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-ink">Availability</h3>
        <?= $checkbox('availability', 'in-stock', 'In Stock', $params) ?>
        <?= $checkbox('availability', 'low-stock', 'Limited Stock', $params) ?>
    </div>

    <div class="border-b border-black/5 pb-5">
        <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-ink">Highlights</h3>
        <?= $checkbox('fresh', '1', 'Fresh Today', $params) ?>
        <?= $checkbox('organic', '1', 'Organic', $params) ?>
        <?= $checkbox('offers', '1', 'On Offer', $params) ?>
    </div>

    <?php if ($activeCount > 0): ?>
        <button type="button" data-clear-filters class="<?= btn('ghost', 'sm', 'w-full') ?>">
            <?= lucide('x', 'size-4') ?> Clear all filters (<?= $activeCount ?>)
        </button>
    <?php endif ?>
</div>
<?php
$filterContent = ob_get_clean();

$buildHref = static function (int $p) use ($basePath, $params): string {
    $qs = [];
    foreach ($params as $k => $v) {
        if ($v !== '' && $v !== null && $k !== 'page' && $k !== 'category') {
            $qs[$k] = $v;
        }
    }
    if ($p > 1) {
        $qs['page'] = (string) $p;
    }

    return $qs ? $basePath . '?' . http_build_query($qs) : $basePath;
};
?>
<div class="flex gap-8">
    <!-- Mobile filters trigger — floating pill -->
    <div class="lg:hidden">
        <button type="button" data-filters-open class="fixed bottom-6 left-1/2 z-40 flex -translate-x-1/2 cursor-pointer items-center gap-2 rounded-full bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-xl shadow-brand-600/30 transition-transform active:scale-95">
            <?= lucide('sliders-horizontal', 'size-4') ?> Filters
            <?php if ($activeCount > 0): ?>
                <span class="flex size-5 items-center justify-center rounded-full bg-white text-[10px] font-bold text-brand-700"><?= $activeCount ?></span>
            <?php endif ?>
        </button>
        <div data-filters-drawer class="hidden">
            <div data-filters-overlay class="fixed inset-0 z-50 bg-brand-950/50 backdrop-blur-sm fm-fade"></div>
            <aside data-filters-panel class="fixed inset-y-0 left-0 z-50 w-80 max-w-[85vw] overflow-y-auto bg-white p-5 shadow-2xl fm-slide-left">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-bold">Filters</h2>
                    <button type="button" data-filters-close aria-label="Close filters" class="flex size-9 cursor-pointer items-center justify-center rounded-full bg-black/5 hover:bg-black/10">
                        <?= lucide('x', 'size-4') ?>
                    </button>
                </div>
                <?= $filterContent ?>
            </aside>
        </div>
    </div>

    <!-- Desktop sidebar -->
    <aside class="sticky top-32 hidden h-fit w-64 shrink-0 rounded-3xl bg-white p-5 shadow-card lg:block">
        <?= $filterContent ?>
    </aside>

    <div class="min-w-0 flex-1">
        <div class="mb-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-ink-soft">
                    <span class="font-bold text-ink"><?= $total ?></span> product<?= $total === 1 ? '' : 's' ?> found
                </p>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <select aria-label="Sort products" data-sort-select class="fm-select h-10 w-48">
                            <?php foreach ($sortOptions as $value => $label): ?>
                                <option value="<?= $value ?>"<?= $currentSort === $value ? ' selected' : '' ?>><?= $label ?></option>
                            <?php endforeach ?>
                        </select>
                        <?= lucide('chevron-down', 'pointer-events-none absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-black/40') ?>
                    </div>
                    <div class="flex rounded-xl bg-white p-1 shadow-sm">
                        <button type="button" data-view-btn="grid" aria-label="Grid view" aria-pressed="<?= $view === 'grid' ? 'true' : 'false' ?>"
                                class="flex size-8 cursor-pointer items-center justify-center rounded-lg transition-colors <?= $view === 'grid' ? 'bg-brand-600 text-white' : 'text-black/40 hover:text-ink' ?>">
                            <?= lucide('layout-grid', 'size-4') ?>
                        </button>
                        <button type="button" data-view-btn="list" aria-label="List view" aria-pressed="<?= $view === 'list' ? 'true' : 'false' ?>"
                                class="flex size-8 cursor-pointer items-center justify-center rounded-lg transition-colors <?= $view === 'list' ? 'bg-brand-600 text-white' : 'text-black/40 hover:text-ink' ?>">
                            <?= lucide('list', 'size-4') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php if (count($products) === 0): ?>
            <div class="flex flex-col items-center gap-4 rounded-3xl bg-white py-20 text-center shadow-card">
                <span class="flex size-16 items-center justify-center rounded-full bg-brand-50">
                    <?= lucide('package-search', 'size-8 text-brand-600') ?>
                </span>
                <div>
                    <h2 class="text-lg font-bold text-ink">No products found</h2>
                    <p class="mt-1 text-sm text-ink-soft">Try clearing some filters or searching for something else.</p>
                </div>
            </div>
        <?php elseif ($view === 'list'): ?>
            <div class="flex flex-col gap-4">
                <?php foreach ($products as $p): ?>
                    <?= view('partials/product_card', ['product' => product_card_data($p), 'layout' => 'list']) ?>
                <?php endforeach ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($products as $p): ?>
                    <?= view('partials/product_card', ['product' => product_card_data($p), 'layout' => 'grid']) ?>
                <?php endforeach ?>
            </div>
        <?php endif ?>

        <?php if ($totalPages > 1): ?>
            <?php
            $pages = [];
            for ($p = 1; $p <= $totalPages; $p++) {
                if ($p === 1 || $p === $totalPages || abs($p - $page) <= 1) {
                    $pages[] = $p;
                }
            }
            $items = [];
            foreach ($pages as $i => $p) {
                if ($i > 0 && $p - $pages[$i - 1] > 1) {
                    $items[] = '…';
                }
                $items[] = $p;
            }
            ?>
            <nav aria-label="Pagination" class="mt-10 flex items-center justify-center gap-1.5">
                <?php if ($page > 1): ?>
                    <a href="<?= esc($buildHref($page - 1), 'attr') ?>" aria-label="Previous page" class="flex size-10 items-center justify-center rounded-full bg-white text-ink-soft shadow-sm transition-colors hover:bg-brand-50 hover:text-brand-700">
                        <?= lucide('chevron-left', 'size-4') ?>
                    </a>
                <?php endif ?>
                <?php foreach ($items as $item): ?>
                    <?php if ($item === '…'): ?>
                        <span class="px-1 text-black/30">…</span>
                    <?php else: ?>
                        <a href="<?= esc($buildHref($item), 'attr') ?>"<?= $item === $page ? ' aria-current="page"' : '' ?>
                           class="flex size-10 items-center justify-center rounded-full text-sm font-semibold shadow-sm transition-colors <?= $item === $page ? 'bg-brand-600 text-white shadow-md shadow-brand-600/25' : 'bg-white text-ink-soft hover:bg-brand-50 hover:text-brand-700' ?>"><?= $item ?></a>
                    <?php endif ?>
                <?php endforeach ?>
                <?php if ($page < $totalPages): ?>
                    <a href="<?= esc($buildHref($page + 1), 'attr') ?>" aria-label="Next page" class="flex size-10 items-center justify-center rounded-full bg-white text-ink-soft shadow-sm transition-colors hover:bg-brand-50 hover:text-brand-700">
                        <?= lucide('chevron-right', 'size-4') ?>
                    </a>
                <?php endif ?>
            </nav>
        <?php endif ?>
    </div>
</div>
