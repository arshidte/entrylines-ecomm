<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
/** Port of src/components/admin/product-form.tsx — logic in admin.js. */
$isEdit     = $product !== null;
$flagFields = [
    ['isFresh', 'Fresh Badge'],
    ['isOrganic', 'Organic Badge'],
    ['isFeatured', 'Featured Product'],
    ['isPopular', 'Popular Product'],
    ['isNewArrival', 'New Arrival'],
    ['isBestSeller', 'Best Seller'],
    ['isSeasonal', 'Seasonal'],
    ['onOffer', 'On Offer'],
];
$flagDefault = static fn (string $key) => $isEdit ? (bool) $product[$key] : ($key === 'isFresh');
$initialImages = $isEdit && count($images) > 0
    ? array_map(static fn ($i) => ['url' => $i['url'], 'alt' => $i['alt']], $images)
    : [['url' => '', 'alt' => '']];
$weightOptionsStr = $isEdit ? implode(', ', weight_options($product['weightOptions'])) : '';
?>
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-ink"><?= $isEdit ? 'Edit Product' : 'Add Product' ?></h1>
        <p class="text-sm text-ink-soft"><?= $isEdit ? esc($product['name']) : 'Create a new product listing.' ?></p>
    </div>

    <form data-product-form data-product-id="<?= $isEdit ? (int) $product['id'] : '' ?>"
          data-save-url="<?= site_url('admin/products/save') ?>"
          data-redirect="<?= site_url('admin/products') ?>"
          data-initial-images="<?= esc(json_encode($initialImages, JSON_UNESCAPED_SLASHES), 'attr') ?>"
          class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <!-- Basics -->
            <div class="rounded-3xl bg-white p-6 shadow-card space-y-4">
                <h2 class="font-bold text-ink">Basic Information</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="p-name" class="mb-1.5 block text-sm font-medium text-ink">Product Name *</label>
                        <input id="p-name" name="name" required value="<?= esc($product['name'] ?? '', 'attr') ?>" class="fm-input">
                    </div>
                    <div>
                        <label for="p-slug" class="mb-1.5 block text-sm font-medium text-ink">Slug (URL)</label>
                        <input id="p-slug" name="slug" placeholder="auto-generated from name" value="<?= esc($product['slug'] ?? '', 'attr') ?>" class="fm-input">
                    </div>
                    <div>
                        <label for="p-category" class="mb-1.5 block text-sm font-medium text-ink">Category *</label>
                        <div class="relative">
                            <select id="p-category" name="categoryId" required class="fm-select">
                                <option value="" disabled<?= $isEdit ? '' : ' selected' ?>>Select category…</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= (int) $c['id'] ?>"<?= $isEdit && (int) $product['categoryId'] === (int) $c['id'] ? ' selected' : '' ?>>
                                        <?= $c['parentName'] ? esc($c['parentName']) . ' → ' : '' ?><?= esc($c['name']) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                            <?= lucide('chevron-down', 'pointer-events-none absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-black/40') ?>
                        </div>
                    </div>
                    <div>
                        <label for="p-brand" class="mb-1.5 block text-sm font-medium text-ink">Brand</label>
                        <input id="p-brand" name="brand" value="<?= esc($product['brand'] ?? '', 'attr') ?>" class="fm-input">
                    </div>
                </div>
                <div>
                    <label for="p-short" class="mb-1.5 block text-sm font-medium text-ink">Short Description *</label>
                    <input id="p-short" name="shortDescription" required value="<?= esc($product['shortDescription'] ?? '', 'attr') ?>" class="fm-input">
                </div>
                <div>
                    <label for="p-desc" class="mb-1.5 block text-sm font-medium text-ink">Full Description *</label>
                    <textarea id="p-desc" name="description" required rows="5" class="fm-textarea"><?= esc($product['description'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Pricing -->
            <div class="rounded-3xl bg-white p-6 shadow-card space-y-4">
                <h2 class="font-bold text-ink">Pricing &amp; Stock</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label for="p-price" class="mb-1.5 block text-sm font-medium text-ink">Price (USD) *</label>
                        <input id="p-price" name="price" type="number" min="0" step="0.01" required value="<?= esc($product['price'] ?? '', 'attr') ?>" class="fm-input">
                    </div>
                    <div>
                        <label for="p-discount" class="mb-1.5 block text-sm font-medium text-ink">Discount Price</label>
                        <input id="p-discount" name="discountPrice" type="number" min="0" step="0.01" value="<?= esc($product['discountPrice'] ?? '', 'attr') ?>" class="fm-input">
                    </div>
                    <div>
                        <label for="p-unit" class="mb-1.5 block text-sm font-medium text-ink">Unit *</label>
                        <div class="relative">
                            <select id="p-unit" name="unit" class="fm-select">
                                <?php foreach (units() as $u): ?>
                                    <option value="<?= $u ?>"<?= ($product['unit'] ?? 'Kg') === $u ? ' selected' : '' ?>><?= $u ?></option>
                                <?php endforeach ?>
                            </select>
                            <?= lucide('chevron-down', 'pointer-events-none absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-black/40') ?>
                        </div>
                    </div>
                    <div>
                        <label for="p-stock" class="mb-1.5 block text-sm font-medium text-ink">Stock Status</label>
                        <div class="relative">
                            <select id="p-stock" name="stockStatus" class="fm-select">
                                <?php foreach (['IN_STOCK' => 'In Stock', 'LOW_STOCK' => 'Limited Stock', 'OUT_OF_STOCK' => 'Out of Stock'] as $value => $label): ?>
                                    <option value="<?= $value ?>"<?= ($product['stockStatus'] ?? 'IN_STOCK') === $value ? ' selected' : '' ?>><?= $label ?></option>
                                <?php endforeach ?>
                            </select>
                            <?= lucide('chevron-down', 'pointer-events-none absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-black/40') ?>
                        </div>
                    </div>
                </div>
                <div>
                    <label for="p-weights" class="mb-1.5 block text-sm font-medium text-ink">Weight Options (comma separated)</label>
                    <input id="p-weights" name="weightOptions" placeholder="250 g, 500 g, 1 Kg, 5 Kg" value="<?= esc($weightOptionsStr, 'attr') ?>" class="fm-input">
                </div>
            </div>

            <!-- Details -->
            <div class="rounded-3xl bg-white p-6 shadow-card space-y-4">
                <h2 class="font-bold text-ink">Product Details</h2>
                <div>
                    <label for="p-nutrition" class="mb-1.5 block text-sm font-medium text-ink">Nutrition (for fruits &amp; vegetables)</label>
                    <textarea id="p-nutrition" name="nutrition" rows="2" class="fm-textarea"><?= esc($product['nutrition'] ?? '') ?></textarea>
                </div>
                <div>
                    <label for="p-storage" class="mb-1.5 block text-sm font-medium text-ink">Storage Instructions</label>
                    <textarea id="p-storage" name="storageInstructions" rows="2" class="fm-textarea"><?= esc($product['storageInstructions'] ?? '') ?></textarea>
                </div>
                <div>
                    <label for="p-origin" class="mb-1.5 block text-sm font-medium text-ink">Origin</label>
                    <input id="p-origin" name="origin" value="<?= esc($product['origin'] ?? '', 'attr') ?>" class="fm-input">
                </div>
            </div>

            <!-- SEO -->
            <div class="rounded-3xl bg-white p-6 shadow-card space-y-4">
                <h2 class="font-bold text-ink">SEO</h2>
                <div>
                    <label for="p-seo-title" class="mb-1.5 block text-sm font-medium text-ink">SEO Title</label>
                    <input id="p-seo-title" name="seoTitle" value="<?= esc($product['seoTitle'] ?? '', 'attr') ?>" class="fm-input">
                </div>
                <div>
                    <label for="p-seo-desc" class="mb-1.5 block text-sm font-medium text-ink">SEO Description</label>
                    <textarea id="p-seo-desc" name="seoDescription" rows="2" class="fm-textarea"><?= esc($product['seoDescription'] ?? '') ?></textarea>
                </div>
                <div>
                    <label for="p-seo-kw" class="mb-1.5 block text-sm font-medium text-ink">Meta Keywords</label>
                    <input id="p-seo-kw" name="metaKeywords" placeholder="comma, separated, keywords" value="<?= esc($product['metaKeywords'] ?? '', 'attr') ?>" class="fm-input">
                </div>
            </div>
        </div>

        <!-- Right rail -->
        <div class="space-y-6">
            <!-- Images -->
            <div class="rounded-3xl bg-white p-6 shadow-card space-y-4">
                <h2 class="font-bold text-ink">Images *</h2>
                <p class="text-xs text-ink-soft">
                    Upload an image (max 2 MB) or paste a URL. The first image is the cover.
                </p>
                <div data-images-list class="space-y-4"></div>
                <button type="button" data-add-image class="<?= btn('subtle', 'sm', 'w-full') ?>">
                    <?= lucide('plus', 'size-4') ?> Add Image
                </button>
            </div>

            <!-- Flags -->
            <div class="rounded-3xl bg-white p-6 shadow-card space-y-4">
                <h2 class="font-bold text-ink">Badges &amp; Placement</h2>
                <div class="grid grid-cols-1 gap-1">
                    <?php foreach ($flagFields as [$key, $label]): ?>
                        <label class="flex cursor-pointer items-center gap-2.5 rounded-xl px-2 py-1.5 text-sm text-ink-soft transition-colors hover:bg-brand-50">
                            <input type="checkbox" name="<?= $key ?>"<?= $flagDefault($key) ? ' checked' : '' ?> class="size-4 cursor-pointer accent-[#2E7D32]">
                            <?= esc($label) ?>
                        </label>
                    <?php endforeach ?>
                </div>
            </div>

            <p role="alert" data-form-message class="hidden rounded-xl px-4 py-3 text-sm"></p>

            <button type="submit" class="<?= btn('primary', 'lg', 'w-full') ?>" data-form-submit>
                <span data-submit-idle class="inline-flex items-center gap-2"><?= lucide('save', 'size-5') ?></span>
                <span data-submit-busy class="hidden items-center gap-2"><?= lucide('loader-circle', 'size-5 animate-spin') ?></span>
                <?= $isEdit ? 'Update Product' : 'Create Product' ?>
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
