<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:py-14">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">
            <?= ! empty($params['q']) ? 'Search results for “' . esc($params['q']) . '”' : 'All Products' ?>
        </h1>
        <p class="mt-2 max-w-2xl text-sm text-ink-soft sm:text-base">
            Farm-fresh produce, premium meat and seafood, dairy and pantry staples —
            quality checked daily, available wholesale and retail.
        </p>
    </div>
    <?= view('partials/listing', [
        'result'        => $result,
        'params'        => $params,
        'navCategories' => $navCategories,
        'basePath'      => site_url('products'),
    ]) ?>
</div>
<?= $this->endSection() ?>
