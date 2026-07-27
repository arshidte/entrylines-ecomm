<?php /** Port of search-bar.tsx — suggestions handled by site.js. */ ?>
<div data-search-box class="relative w-full <?= esc($class ?? '', 'attr') ?>">
    <form action="<?= site_url('products') ?>" method="get" role="search" data-search-form>
        <div class="relative">
            <?= lucide('search', 'pointer-events-none absolute left-4 top-1/2 size-4.5 -translate-y-1/2 text-black/35') ?>
            <input type="search" name="q" data-search-input
                   placeholder="Search fresh vegetables, fruits, meat…"
                   aria-label="Search products"
                   autocomplete="off"
                   class="h-11 w-full rounded-full border border-black/10 bg-white/80 pl-11 pr-11 text-sm shadow-sm backdrop-blur transition-all placeholder:text-black/35 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            <?= lucide('loader-circle', 'absolute right-4 top-1/2 size-4 -translate-y-1/2 animate-spin text-brand-600 hidden') ?>
        </div>
    </form>
    <div data-search-panel class="absolute left-0 right-0 top-full z-50 mt-2 hidden overflow-hidden rounded-2xl border border-black/5 bg-white shadow-2xl fm-pop-sm"></div>
</div>
