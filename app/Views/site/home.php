<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
/** Port of src/app/(site)/page.tsx. */
$heroFeatures = [
    ['icon' => 'truck', 'label' => 'Same Day Delivery'],
    ['icon' => 'sprout', 'label' => 'Fresh from Farms'],
    ['icon' => 'badge-check', 'label' => '100% Quality Checked'],
    ['icon' => 'store', 'label' => 'Wholesale & Retail'],
    ['icon' => 'tag', 'label' => 'Best Prices'],
];
$whyChooseUs = [
    ['icon' => 'sprout', 'title' => 'Fresh Daily', 'text' => 'Harvested at dawn and on shelves the same morning — never warehoused.'],
    ['icon' => 'wallet', 'title' => 'Affordable Pricing', 'text' => 'Direct farm sourcing cuts out middlemen, for retail and wholesale alike.'],
    ['icon' => 'award', 'title' => 'Premium Quality', 'text' => 'Every batch is graded and quality checked before it reaches you.'],
    ['icon' => 'zap', 'title' => 'Fast Delivery', 'text' => 'Cold-chain vans deliver same-day across the city, on your schedule.'],
    ['icon' => 'headphones', 'title' => 'Customer Support', 'text' => 'A real human answers within the hour, seven days a week.'],
    ['icon' => 'shield-check', 'title' => 'Trusted Supplier', 'text' => 'Restaurants, hotels and thousands of families rely on us weekly.'],
];
$stats = [
    ['value' => 120, 'suffix' => '+', 'label' => 'Partner Farms'],
    ['value' => 8500, 'suffix' => '+', 'label' => 'Happy Customers'],
    ['value' => 300, 'suffix' => '+', 'label' => 'Fresh Products'],
    ['value' => 15, 'suffix' => ' yrs', 'label' => 'In Business'],
];
$visibleTabs = array_values(array_filter($tabs, static fn ($t) => count($t['products']) > 0));
?>

<?php if (count($banners) > 0): ?>
<!-- Hero slider — port of hero-slider.tsx -->
<section aria-label="Featured offers" class="relative mx-auto mt-4 max-w-7xl px-4 sm:px-6" data-hero-slider>
    <div class="relative h-[440px] overflow-hidden rounded-4xl sm:h-[500px] lg:h-[560px]">
        <?php foreach ($banners as $i => $banner): ?>
            <div data-hero-slide="<?= $i ?>" class="absolute inset-0 fm-hero-slide<?= $i === 0 ? ' is-active' : '' ?>">
                <img src="<?= esc($banner['image'], 'attr') ?>" alt="<?= esc($banner['title'], 'attr') ?>"
                     <?= $i === 0 ? '' : 'loading="lazy"' ?> class="absolute inset-0 size-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-brand-950/80 via-brand-950/45 to-transparent"></div>
            </div>
        <?php endforeach ?>

        <!-- Copy -->
        <div class="relative z-10 flex h-full max-w-2xl flex-col justify-center gap-5 p-8 sm:p-12 lg:p-16">
            <?php foreach ($banners as $i => $banner): ?>
                <div data-hero-copy="<?= $i ?>" class="flex flex-col items-start gap-5 fm-hero-copy<?= $i === 0 ? ' is-active' : ' hidden' ?>">
                    <span class="glass-dark rounded-full px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-brand-100">
                        100% Quality Checked
                    </span>
                    <h1 class="text-4xl font-extrabold leading-[1.05] text-white sm:text-5xl lg:text-6xl"><?= esc($banner['title']) ?></h1>
                    <?php if ($banner['subtitle']): ?>
                        <p class="max-w-lg text-base leading-relaxed text-white/85 sm:text-lg"><?= esc($banner['subtitle']) ?></p>
                    <?php endif ?>
                    <div class="mt-2 flex flex-wrap gap-3">
                        <a href="<?= esc(site_url(ltrim($banner['ctaLink'], '/')), 'attr') ?>" class="<?= btn('accent', 'lg') ?>">
                            <?= esc($banner['ctaText']) ?> <?= lucide('arrow-right', 'size-5') ?>
                        </a>
                        <a href="<?= site_url('products') ?>" class="<?= btn('white', 'lg') ?>">Browse All</a>
                    </div>
                </div>
            <?php endforeach ?>
        </div>

        <?php if (count($banners) > 1): ?>
            <div class="absolute bottom-6 left-1/2 z-10 flex -translate-x-1/2 gap-2">
                <?php foreach ($banners as $i => $banner): ?>
                    <button data-hero-dot="<?= $i ?>" aria-label="Go to slide <?= $i + 1 ?>"
                            class="h-2 cursor-pointer rounded-full transition-all duration-300 <?= $i === 0 ? 'w-8 bg-white' : 'w-2 bg-white/40 hover:bg-white/70' ?>"></button>
                <?php endforeach ?>
            </div>
            <div class="absolute bottom-6 right-6 z-10 hidden gap-2 sm:flex">
                <button data-hero-prev aria-label="Previous slide" class="glass-dark flex size-11 cursor-pointer items-center justify-center rounded-full text-white transition-all hover:bg-white/20">
                    <?= lucide('chevron-left', 'size-5') ?>
                </button>
                <button data-hero-next aria-label="Next slide" class="glass-dark flex size-11 cursor-pointer items-center justify-center rounded-full text-white transition-all hover:bg-white/20">
                    <?= lucide('chevron-right', 'size-5') ?>
                </button>
            </div>
        <?php endif ?>
    </div>
</section>
<?php endif ?>

<!-- Hero feature strip -->
<section aria-label="Our promises" class="mx-auto max-w-7xl px-4 sm:px-6">
    <div data-animate="fade">
        <div class="glass mt-6 grid grid-cols-2 gap-y-5 rounded-3xl px-6 py-6 shadow-glass sm:grid-cols-3 lg:grid-cols-5 lg:py-7">
            <?php foreach ($heroFeatures as $f): ?>
                <div class="flex items-center justify-center gap-2.5">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                        <?= lucide($f['icon'], 'size-5') ?>
                    </span>
                    <span class="text-sm font-bold text-ink"><?= esc($f['label']) ?></span>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</section>

<!-- Featured categories -->
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-24">
    <div data-animate="fade">
        <?= view('partials/section_heading', [
            'eyebrow'   => 'Shop by Category',
            'title'     => 'Featured Categories',
            'subtitle'  => 'From leafy greens to ocean-fresh seafood — everything your kitchen needs, in one place.',
            'href'      => site_url('products'),
            'linkLabel' => 'All Products',
            'align'     => 'left',
        ]) ?>
    </div>
    <div data-animate="stagger" class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4">
        <?php foreach ($categoryCards as $i => $cat): ?>
            <div data-animate-item class="<?= $i === 0 ? 'col-span-2 row-span-2' : '' ?>">
                <a href="<?= site_url('category/' . $cat['slug']) ?>"
                   class="group relative block h-full min-h-40 overflow-hidden rounded-3xl bg-cream-dark shadow-card transition-shadow duration-300 hover:shadow-card-hover">
                    <?php if ($cat['image']): ?>
                        <img src="<?= esc($cat['image'], 'attr') ?>" alt="<?= esc($cat['name'], 'attr') ?> — fresh and premium quality"
                             loading="lazy" class="absolute inset-0 size-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">
                    <?php endif ?>
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-950/85 via-brand-950/20 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-2 p-4 sm:p-5">
                        <div>
                            <h3 class="font-extrabold text-white sm:text-lg"><?= esc($cat['name']) ?></h3>
                            <p class="text-xs text-white/70"><?= $cat['productCount'] ?> products</p>
                        </div>
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur transition-all duration-300 group-hover:bg-accent-500 group-hover:rotate-45">
                            <?= lucide('arrow-up-right', 'size-4') ?>
                        </span>
                    </div>
                </a>
            </div>
        <?php endforeach ?>
    </div>
</section>

<!-- Featured products -->
<section class="bg-white py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div data-animate="fade">
            <?= view('partials/section_heading', [
                'eyebrow'   => 'Hand-picked for you',
                'title'     => 'Featured Products',
                'subtitle'  => "Our produce team's daily selection of the finest arrivals.",
                'href'      => site_url('products'),
                'linkLabel' => 'View All',
                'align'     => 'left',
            ]) ?>
        </div>
        <div data-animate="stagger" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($featured as $p): ?>
                <div data-animate-item>
                    <?= view('partials/product_card', ['product' => $p, 'layout' => 'grid']) ?>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</section>

<!-- Collections tabs — port of product-tabs.tsx -->
<?php if (count($visibleTabs) > 0): ?>
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-24">
    <div data-animate="fade">
        <?= view('partials/section_heading', [
            'eyebrow'   => 'Customer favourites',
            'title'     => 'Explore Our Collections',
            'subtitle'  => "Popular picks, trending now, new arrivals, best sellers and the season's finest.",
            'href'      => null,
            'linkLabel' => 'View All',
            'align'     => 'left',
        ]) ?>
    </div>
    <div data-animate="fade" data-product-tabs>
        <div role="tablist" aria-label="Product collections" class="scrollbar-none mb-8 flex gap-2 overflow-x-auto pb-1">
            <?php foreach ($visibleTabs as $i => $tab): ?>
                <button role="tab" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" data-tab-btn="<?= esc($tab['key'], 'attr') ?>"
                        class="relative shrink-0 cursor-pointer rounded-full px-5 py-2.5 text-sm font-semibold transition-colors duration-200 <?= $i === 0 ? 'text-white' : 'bg-white text-ink-soft shadow-sm hover:text-brand-700' ?>">
                    <span data-tab-pill class="absolute inset-0 rounded-full bg-brand-600 shadow-md shadow-brand-600/25<?= $i === 0 ? '' : ' hidden' ?>"></span>
                    <span class="relative z-10"><?= esc($tab['label']) ?></span>
                </button>
            <?php endforeach ?>
        </div>

        <?php foreach ($visibleTabs as $i => $tab): ?>
            <div data-tab-panel="<?= esc($tab['key'], 'attr') ?>" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 fm-tab-panel<?= $i === 0 ? '' : ' hidden' ?>">
                <?php foreach (array_slice($tab['products'], 0, 8) as $p): ?>
                    <?= view('partials/product_card', ['product' => $p, 'layout' => 'grid']) ?>
                <?php endforeach ?>
            </div>
        <?php endforeach ?>
    </div>
</section>
<?php endif ?>

<!-- Promo banner -->
<section aria-label="Fresh produce direct from farms" class="relative overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-center bg-fixed" style="background-image: url(https://images.unsplash.com/photo-1500937386664-56d1dfef3854?q=80&w=2000&auto=format&fit=crop)"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-brand-950/90 via-brand-900/75 to-brand-950/60"></div>
    <div class="relative mx-auto flex max-w-7xl flex-col items-start gap-6 px-4 py-24 sm:px-6 lg:py-32">
        <div data-animate="fade">
            <span class="glass-dark rounded-full px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-accent-400">Farm to Table</span>
        </div>
        <div data-animate="fade" data-animate-delay="0.1">
            <h2 class="max-w-2xl text-4xl font-extrabold leading-tight text-white sm:text-5xl">Fresh Produce Direct From Farms</h2>
        </div>
        <div data-animate="fade" data-animate-delay="0.2">
            <p class="max-w-xl text-lg text-white/80">
                We partner with over 120 local farms so your produce skips the warehouse
                and lands on your table within hours of harvest.
            </p>
        </div>
        <div data-animate="fade" data-animate-delay="0.3">
            <a href="<?= site_url('products') ?>" class="<?= btn('accent', 'lg') ?>">
                Explore Products <?= lucide('arrow-right', 'size-5') ?>
            </a>
        </div>
    </div>
</section>

<!-- Why choose us + counters -->
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-24">
    <div data-animate="fade">
        <?= view('partials/section_heading', [
            'eyebrow'   => 'Why FreshMart',
            'title'     => 'Why Choose Us',
            'subtitle'  => 'Six reasons thousands of families and food businesses shop with us every week.',
            'href'      => null,
            'linkLabel' => 'View All',
            'align'     => 'center',
        ]) ?>
    </div>
    <div data-animate="stagger" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($whyChooseUs as $w): ?>
            <div data-animate-item>
                <div class="group h-full rounded-3xl bg-white p-7 shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
                    <span class="mb-4 flex size-13 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 transition-all duration-300 group-hover:scale-110 group-hover:bg-brand-600 group-hover:text-white">
                        <?= lucide($w['icon'], 'size-6') ?>
                    </span>
                    <h3 class="mb-1.5 text-lg font-bold text-ink"><?= esc($w['title']) ?></h3>
                    <p class="text-sm leading-relaxed text-ink-soft"><?= esc($w['text']) ?></p>
                </div>
            </div>
        <?php endforeach ?>
    </div>

    <div data-animate="fade" data-animate-delay="0.15">
        <div class="mt-14 grid grid-cols-2 gap-6 rounded-4xl bg-brand-600 px-8 py-10 text-white shadow-xl shadow-brand-600/20 sm:grid-cols-4">
            <?php foreach ($stats as $stat): ?>
                <div class="text-center">
                    <p class="text-3xl font-extrabold sm:text-4xl">
                        <span data-counter="<?= $stat['value'] ?>" data-counter-suffix="<?= esc($stat['suffix'], 'attr') ?>">0<?= esc($stat['suffix']) ?></span>
                    </p>
                    <p class="mt-1 text-sm text-brand-100"><?= esc($stat['label']) ?></p>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="bg-white py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div data-animate="fade">
            <?= view('partials/section_heading', [
                'eyebrow'   => 'Testimonials',
                'title'     => 'Loved by Home Cooks & Head Chefs',
                'subtitle'  => 'Real words from the people who cook with our produce every day.',
                'href'      => null,
                'linkLabel' => 'View All',
                'align'     => 'center',
            ]) ?>
        </div>
        <div data-animate="stagger" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($testimonials as $t): ?>
                <div data-animate-item>
                    <figure class="flex h-full flex-col rounded-3xl bg-cream p-6 shadow-card">
                        <div class="mb-3 flex gap-0.5" aria-label="<?= (int) $t['rating'] ?> out of 5 stars">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <?= lucide('star', 'size-4 ' . ($i < (int) $t['rating'] ? 'fill-accent-500 text-accent-500' : 'text-black/15')) ?>
                            <?php endfor ?>
                        </div>
                        <blockquote class="flex-1 text-sm leading-relaxed text-ink-soft">“<?= esc($t['content']) ?>”</blockquote>
                        <figcaption class="mt-5 flex items-center gap-3">
                            <?php if ($t['avatar']): ?>
                                <img src="<?= esc($t['avatar'], 'attr') ?>" alt="<?= esc($t['name'], 'attr') ?>" width="44" height="44" loading="lazy" class="size-11 rounded-full object-cover">
                            <?php endif ?>
                            <div>
                                <p class="text-sm font-bold text-ink"><?= esc($t['name']) ?></p>
                                <?php if ($t['role']): ?><p class="text-xs text-black/45"><?= esc($t['role']) ?></p><?php endif ?>
                            </div>
                        </figcaption>
                    </figure>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
