<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
/** Port of src/components/admin/category-manager.tsx — editor logic in admin.js. */
$categoriesJson = array_map(static fn ($c) => [
    'id'           => (int) $c['id'],
    'name'         => $c['name'],
    'slug'         => $c['slug'],
    'description'  => $c['description'],
    'image'        => $c['image'],
    'parentId'     => $c['parentId'] !== null ? (int) $c['parentId'] : null,
    'parentName'   => $c['parentName'],
    'isActive'     => (bool) $c['isActive'],
    'isFeatured'   => (bool) $c['isFeatured'],
    'sortOrder'    => (int) $c['sortOrder'],
    'productCount' => (int) $c['productCount'],
], $categories);
?>
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-ink">Categories</h1>
        <p class="text-sm text-ink-soft">
            <?= count($categories) ?> categories — organise your catalogue and control what appears in navigation.
        </p>
    </div>

    <div class="grid gap-6 xl:grid-cols-3"
         data-category-manager
         data-categories="<?= esc(json_encode($categoriesJson, JSON_UNESCAPED_SLASHES), 'attr') ?>"
         data-save-url="<?= site_url('admin/categories/save') ?>"
         data-delete-url="<?= site_url('admin/categories/delete') ?>">
        <!-- List -->
        <div class="overflow-x-auto rounded-3xl bg-white shadow-card xl:col-span-2">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-black/5 text-xs uppercase tracking-wider text-black/40">
                        <th class="p-4 font-semibold">Category</th>
                        <th class="p-4 font-semibold">Parent</th>
                        <th class="p-4 font-semibold">Products</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                        <tr class="border-b border-black/5 last:border-0 hover:bg-brand-50/40">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <span class="relative size-10 shrink-0 overflow-hidden rounded-lg bg-cream-dark">
                                        <?php if ($c['image']): ?>
                                            <img src="<?= esc($c['image'], 'attr') ?>" alt="<?= esc($c['name'], 'attr') ?>" class="absolute inset-0 size-full object-cover">
                                        <?php endif ?>
                                    </span>
                                    <div>
                                        <p class="font-semibold text-ink"><?= esc($c['name']) ?></p>
                                        <p class="text-xs text-black/40">/<?= esc($c['slug']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-ink-soft"><?= $c['parentName'] ? esc($c['parentName']) : '—' ?></td>
                            <td class="p-4"><?= (int) $c['productCount'] ?></td>
                            <td class="p-4">
                                <div class="flex gap-1">
                                    <?= badge($c['isActive'] ? 'stock' : 'muted', $c['isActive'] ? 'Active' : 'Hidden') ?>
                                    <?php if ($c['isFeatured']): ?><?= badge('new', 'Featured') ?><?php endif ?>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex gap-1.5">
                                    <button type="button" data-edit-category="<?= (int) $c['id'] ?>" aria-label="Edit <?= esc($c['name'], 'attr') ?>" class="<?= btn('subtle', 'iconSm') ?>">
                                        <?= lucide('pencil', 'size-4') ?>
                                    </button>
                                    <button type="button" data-delete-category="<?= (int) $c['id'] ?>" data-name="<?= esc($c['name'], 'attr') ?>" aria-label="Delete <?= esc($c['name'], 'attr') ?>" class="<?= btn('danger', 'iconSm') ?>">
                                        <?= lucide('trash-2', 'size-4') ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- Form -->
        <div class="h-fit space-y-4 rounded-3xl bg-white p-6 shadow-card">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-ink" data-editor-title>Category Editor</h2>
                <button type="button" data-editor-cancel aria-label="Cancel" style="display:none" class="<?= btn('ghost', 'iconSm') ?>">
                    <?= lucide('x', 'size-4') ?>
                </button>
                <button type="button" data-editor-new class="<?= btn('primary', 'sm') ?>">
                    <?= lucide('plus', 'size-4') ?> New
                </button>
            </div>

            <p class="text-sm text-ink-soft" data-editor-placeholder>Select a category to edit, or create a new one.</p>

            <form data-category-form class="space-y-3.5 hidden">
                <input type="hidden" name="id" value="">
                <div>
                    <label for="c-name" class="mb-1.5 block text-sm font-medium text-ink">Name *</label>
                    <input id="c-name" name="name" required class="fm-input">
                </div>
                <div>
                    <label for="c-slug" class="mb-1.5 block text-sm font-medium text-ink">Slug</label>
                    <input id="c-slug" name="slug" placeholder="auto-generated" class="fm-input">
                </div>
                <div>
                    <label for="c-desc" class="mb-1.5 block text-sm font-medium text-ink">Description</label>
                    <textarea id="c-desc" name="description" rows="2" class="fm-textarea"></textarea>
                </div>
                <div data-upload-group>
                    <label for="c-image" class="mb-1.5 block text-sm font-medium text-ink">Image</label>
                    <div class="flex gap-2">
                        <input id="c-image" name="image" data-upload-target placeholder="https://… or upload" class="fm-input">
                        <label class="inline-flex shrink-0 cursor-pointer items-center gap-1.5 rounded-full border border-black/10 px-4 text-sm font-semibold text-ink-soft transition-colors hover:bg-brand-50 hover:text-brand-700">
                            <?= lucide('upload', 'size-4') ?> Upload
                            <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-image-upload data-upload-type="categories" class="hidden">
                        </label>
                    </div>
                    <p data-upload-status class="mt-1 hidden text-xs text-ink-soft"></p>
                </div>
                <div>
                    <label for="c-parent" class="mb-1.5 block text-sm font-medium text-ink">Parent Category</label>
                    <div class="relative">
                        <select id="c-parent" name="parentId" class="fm-select"></select>
                        <?= lucide('chevron-down', 'pointer-events-none absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-black/40') ?>
                    </div>
                </div>
                <div>
                    <label for="c-sort" class="mb-1.5 block text-sm font-medium text-ink">Sort Order</label>
                    <input id="c-sort" name="sortOrder" type="number" value="0" class="fm-input">
                </div>
                <div class="flex gap-4">
                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                        <input type="checkbox" name="isActive" class="size-4 accent-[#2E7D32]"> Active
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                        <input type="checkbox" name="isFeatured" class="size-4 accent-[#2E7D32]"> Featured
                    </label>
                </div>

                <p data-editor-message class="hidden rounded-xl px-4 py-2.5 text-sm"></p>

                <button type="submit" class="<?= btn('primary', 'md', 'w-full') ?>" data-editor-submit>
                    <span data-submit-idle class="inline-flex items-center gap-2"><?= lucide('save', 'size-4') ?></span>
                    <span data-submit-busy class="hidden items-center gap-2"><?= lucide('loader-circle', 'size-4 animate-spin') ?></span>
                    Save Category
                </button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
