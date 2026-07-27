<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-ink">Products</h1>
            <p class="text-sm text-ink-soft"><?= count($products) ?> products</p>
        </div>
        <a href="<?= site_url('admin/products/new') ?>" class="<?= btn('primary', 'md') ?>">
            <?= lucide('plus', 'size-4') ?> Add Product
        </a>
    </div>

    <!-- Search + filter -->
    <form class="flex flex-wrap gap-2" action="<?= site_url('admin/products') ?>" method="get">
        <div class="relative">
            <?= lucide('search', 'pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-black/35') ?>
            <input type="search" name="q" value="<?= esc($q, 'attr') ?>" placeholder="Search products…"
                   class="h-11 w-64 rounded-xl border border-black/10 bg-white pl-10 pr-4 text-sm shadow-sm focus:border-brand-500 focus:outline-none">
        </div>
        <select name="category" class="h-11 cursor-pointer rounded-xl border border-black/10 bg-white px-4 text-sm shadow-sm focus:outline-none">
            <option value="">All categories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= esc($c['slug'], 'attr') ?>"<?= $category === $c['slug'] ? ' selected' : '' ?>><?= esc($c['name']) ?></option>
            <?php endforeach ?>
        </select>
        <button type="submit" class="<?= btn('subtle', 'md') ?>">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-3xl bg-white shadow-card">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-black/5 text-xs uppercase tracking-wider text-black/40">
                    <th class="p-4 font-semibold">Product</th>
                    <th class="p-4 font-semibold">Category</th>
                    <th class="p-4 font-semibold">Price</th>
                    <th class="p-4 font-semibold">Stock</th>
                    <th class="p-4 font-semibold">Flags</th>
                    <th class="p-4 font-semibold">Enquiries</th>
                    <th class="p-4 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr class="border-b border-black/5 last:border-0 hover:bg-brand-50/40">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <span class="relative size-12 shrink-0 overflow-hidden rounded-xl bg-cream-dark">
                                    <?php if ($p['image']): ?>
                                        <img src="<?= esc($p['image'], 'attr') ?>" alt="<?= esc($p['name'], 'attr') ?>" class="absolute inset-0 size-full object-cover">
                                    <?php endif ?>
                                </span>
                                <div>
                                    <p class="font-semibold text-ink"><?= esc($p['name']) ?></p>
                                    <p class="text-xs text-black/40">/<?= esc($p['slug']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="p-4"><?= esc($p['categoryName']) ?></td>
                        <td class="p-4">
                            <span class="font-semibold"><?= format_price($p['discountPrice'] !== null ? (float) $p['discountPrice'] : (float) $p['price']) ?></span>
                            <span class="text-xs text-black/40">/<?= esc($p['unit']) ?></span>
                        </td>
                        <td class="p-4">
                            <?= badge($p['stockStatus'] === 'IN_STOCK' ? 'stock' : ($p['stockStatus'] === 'LOW_STOCK' ? 'lowstock' : 'outofstock'), esc(str_replace('_', ' ', $p['stockStatus']))) ?>
                        </td>
                        <td class="p-4">
                            <div class="flex max-w-40 flex-wrap gap-1">
                                <?php if ($p['isFeatured']): ?><?= badge('muted', 'Featured') ?><?php endif ?>
                                <?php if ($p['isOrganic']): ?><?= badge('organic', 'Organic') ?><?php endif ?>
                                <?php if ($p['onOffer']): ?><?= badge('offer', 'Offer') ?><?php endif ?>
                                <?php if ($p['isNewArrival']): ?><?= badge('new', 'New') ?><?php endif ?>
                                <?php if ($p['isBestSeller']): ?><?= badge('bestseller', 'Best') ?><?php endif ?>
                            </div>
                        </td>
                        <td class="p-4 text-center font-semibold"><?= $p['enquiryCount'] ?></td>
                        <td class="p-4">
                            <div class="flex gap-1.5">
                                <a href="<?= site_url('admin/products/' . $p['id']) ?>" aria-label="Edit <?= esc($p['name'], 'attr') ?>" class="<?= btn('subtle', 'iconSm') ?>">
                                    <?= lucide('pencil', 'size-4') ?>
                                </a>
                                <button type="button" class="<?= btn('danger', 'iconSm') ?>"
                                        data-delete-url="<?= site_url('admin/products/delete/' . $p['id']) ?>"
                                        data-confirm="Delete <?= esc($p['name'], 'attr') ?>?"
                                        aria-label="Delete <?= esc($p['name'], 'attr') ?>">
                                    <?= lucide('trash-2', 'size-4') ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
                <?php if (count($products) === 0): ?>
                    <tr>
                        <td colspan="7" class="p-10 text-center text-ink-soft">No products match your search.</td>
                    </tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
