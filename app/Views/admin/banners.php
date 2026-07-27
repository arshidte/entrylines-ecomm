<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
/** Port of src/components/admin/banner-manager.tsx — editor logic in admin.js. */
$bannersJson = array_map(static fn ($b) => [
    'id'        => (int) $b['id'],
    'title'     => $b['title'],
    'subtitle'  => $b['subtitle'],
    'image'     => $b['image'],
    'ctaText'   => $b['ctaText'],
    'ctaLink'   => $b['ctaLink'],
    'sortOrder' => (int) $b['sortOrder'],
    'isActive'  => (bool) $b['isActive'],
], $banners);
?>
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-ink">Banners</h1>
        <p class="text-sm text-ink-soft">Manage the homepage hero slider.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-3"
         data-banner-manager
         data-banners="<?= esc(json_encode($bannersJson, JSON_UNESCAPED_SLASHES), 'attr') ?>"
         data-save-url="<?= site_url('admin/banners/save') ?>"
         data-delete-url="<?= site_url('admin/banners/delete') ?>">
        <div class="space-y-4 xl:col-span-2">
            <?php foreach ($banners as $b): ?>
                <div class="flex gap-4 rounded-3xl bg-white p-4 shadow-card">
                    <span class="relative h-24 w-40 shrink-0 overflow-hidden rounded-2xl bg-cream-dark">
                        <img src="<?= esc($b['image'], 'attr') ?>" alt="<?= esc($b['title'], 'attr') ?>" class="absolute inset-0 size-full object-cover">
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="truncate font-bold text-ink"><?= esc($b['title']) ?></h3>
                            <?= badge($b['isActive'] ? 'stock' : 'muted', $b['isActive'] ? 'Live' : 'Hidden') ?>
                        </div>
                        <?php if ($b['subtitle']): ?>
                            <p class="mt-1 line-clamp-2 text-sm text-ink-soft"><?= esc($b['subtitle']) ?></p>
                        <?php endif ?>
                        <p class="mt-1 text-xs text-black/40">
                            CTA: <?= esc($b['ctaText']) ?> → <?= esc($b['ctaLink']) ?> · Order <?= (int) $b['sortOrder'] ?>
                        </p>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <button type="button" data-edit-banner="<?= (int) $b['id'] ?>" aria-label="Edit <?= esc($b['title'], 'attr') ?>" class="<?= btn('subtle', 'iconSm') ?>">
                            <?= lucide('pencil', 'size-4') ?>
                        </button>
                        <button type="button" data-delete-banner="<?= (int) $b['id'] ?>" data-name="<?= esc($b['title'], 'attr') ?>" aria-label="Delete <?= esc($b['title'], 'attr') ?>" class="<?= btn('danger', 'iconSm') ?>">
                            <?= lucide('trash-2', 'size-4') ?>
                        </button>
                    </div>
                </div>
            <?php endforeach ?>
            <?php if (count($banners) === 0): ?>
                <div class="rounded-3xl bg-white p-10 text-center text-ink-soft shadow-card">
                    No banners yet — create your first hero slide.
                </div>
            <?php endif ?>
        </div>

        <div class="h-fit space-y-4 rounded-3xl bg-white p-6 shadow-card">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-ink" data-editor-title>Banner Editor</h2>
                <button type="button" data-editor-cancel aria-label="Cancel" style="display:none" class="<?= btn('ghost', 'iconSm') ?>">
                    <?= lucide('x', 'size-4') ?>
                </button>
                <button type="button" data-editor-new class="<?= btn('primary', 'sm') ?>">
                    <?= lucide('plus', 'size-4') ?> New
                </button>
            </div>

            <p class="text-sm text-ink-soft" data-editor-placeholder>Select a banner to edit, or create a new one. Banners appear in the homepage hero slider.</p>

            <form data-banner-form class="space-y-3.5 hidden">
                <input type="hidden" name="id" value="">
                <div>
                    <label for="b-title" class="mb-1.5 block text-sm font-medium text-ink">Title *</label>
                    <input id="b-title" name="title" required class="fm-input">
                </div>
                <div>
                    <label for="b-subtitle" class="mb-1.5 block text-sm font-medium text-ink">Subtitle</label>
                    <input id="b-subtitle" name="subtitle" class="fm-input">
                </div>
                <div data-upload-group>
                    <label for="b-image" class="mb-1.5 block text-sm font-medium text-ink">Image * (recommended 2000×1100)</label>
                    <div class="flex gap-2">
                        <input id="b-image" name="image" data-upload-target required placeholder="https://… or upload" class="fm-input">
                        <label class="inline-flex shrink-0 cursor-pointer items-center gap-1.5 rounded-full border border-black/10 px-4 text-sm font-semibold text-ink-soft transition-colors hover:bg-brand-50 hover:text-brand-700">
                            <?= lucide('upload', 'size-4') ?> Upload
                            <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-image-upload data-upload-type="banners" class="hidden">
                        </label>
                    </div>
                    <p data-upload-status class="mt-1 hidden text-xs text-ink-soft"></p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="b-cta-text" class="mb-1.5 block text-sm font-medium text-ink">CTA Text</label>
                        <input id="b-cta-text" name="ctaText" class="fm-input">
                    </div>
                    <div>
                        <label for="b-cta-link" class="mb-1.5 block text-sm font-medium text-ink">CTA Link</label>
                        <input id="b-cta-link" name="ctaLink" class="fm-input">
                    </div>
                </div>
                <div class="grid grid-cols-2 items-end gap-3">
                    <div>
                        <label for="b-sort" class="mb-1.5 block text-sm font-medium text-ink">Sort Order</label>
                        <input id="b-sort" name="sortOrder" type="number" value="0" class="fm-input">
                    </div>
                    <label class="flex cursor-pointer items-center gap-2 pb-3 text-sm">
                        <input type="checkbox" name="isActive" class="size-4 accent-[#2E7D32]"> Active
                    </label>
                </div>

                <p data-editor-message class="hidden rounded-xl px-4 py-2.5 text-sm"></p>

                <button type="submit" class="<?= btn('primary', 'md', 'w-full') ?>" data-editor-submit>
                    <span data-submit-idle class="inline-flex items-center gap-2"><?= lucide('save', 'size-4') ?></span>
                    <span data-submit-busy class="hidden items-center gap-2"><?= lucide('loader-circle', 'size-4 animate-spin') ?></span>
                    Save Banner
                </button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
