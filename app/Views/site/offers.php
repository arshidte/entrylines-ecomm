<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<div class="bg-gradient-to-r from-danger-500 to-accent-500 text-white">
    <div class="mx-auto flex max-w-7xl flex-col items-start gap-3 px-4 py-14 sm:px-6">
        <span class="flex items-center gap-2 rounded-full bg-white/15 px-4 py-1.5 text-xs font-bold uppercase tracking-widest backdrop-blur">
            <?= lucide('badge-percent', 'size-4') ?> Limited Time
        </span>
        <h1 class="text-4xl font-extrabold sm:text-5xl">Offers &amp; Deals</h1>
        <p class="max-w-xl text-white/85">
            Fresh savings, updated daily. Same premium quality, better prices —
            while stocks last.
        </p>
    </div>
</div>

<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
    <?php if (count($products) === 0): ?>
        <p class="rounded-3xl bg-white py-20 text-center text-ink-soft shadow-card">
            No active offers right now — check back tomorrow!
        </p>
    <?php else: ?>
        <div data-animate="stagger" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($products as $p): ?>
                <div data-animate-item>
                    <?= view('partials/product_card', ['product' => $p, 'layout' => 'grid']) ?>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>
</div>
<?= $this->endSection() ?>
