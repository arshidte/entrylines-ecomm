<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<!-- Category hero -->
<div class="relative h-52 overflow-hidden sm:h-64">
    <?php if ($category['image']): ?>
        <img src="<?= esc($category['image'], 'attr') ?>" alt="<?= esc($category['name'], 'attr') ?>" class="absolute inset-0 size-full object-cover">
    <?php endif ?>
    <div class="absolute inset-0 bg-gradient-to-r from-brand-950/85 to-brand-950/30"></div>
    <div class="absolute inset-0 mx-auto flex max-w-7xl flex-col justify-center px-4 sm:px-6">
        <?php if ($parent): ?>
            <p class="mb-1 text-xs font-bold uppercase tracking-widest text-brand-200"><?= esc($parent['name']) ?></p>
        <?php endif ?>
        <h1 class="text-3xl font-extrabold text-white sm:text-5xl"><?= esc($category['name']) ?></h1>
        <?php if ($category['description']): ?>
            <p class="mt-2 max-w-xl text-sm text-white/80 sm:text-base"><?= esc($category['description']) ?></p>
        <?php endif ?>
    </div>
</div>

<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:py-14">
    <?= view('partials/listing', [
        'result'         => $result,
        'params'         => $params,
        'navCategories'  => $navCategories,
        'basePath'       => site_url('category/' . $category['slug']),
        'activeCategory' => $category['slug'],
    ]) ?>
</div>
<?= $this->endSection() ?>
