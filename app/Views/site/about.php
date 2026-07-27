<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
$values = [
    ['icon' => 'sprout', 'title' => 'Farm Partnerships', 'text' => 'We work directly with over 120 local farms, agreeing fair prices season by season and planning harvests together.'],
    ['icon' => 'award', 'title' => 'Quality First', 'text' => 'Every crate is inspected twice — once at the farm gate and once at our facility — before it can carry the FreshMart badge.'],
    ['icon' => 'truck', 'title' => 'Unbroken Cold Chain', 'text' => 'From refrigerated pickup to insulated last-mile delivery, temperature is monitored end to end for meat, seafood and dairy.'],
    ['icon' => 'users', 'title' => 'Wholesale & Retail', 'text' => 'The same produce that supplies top restaurants is available to families — no minimums, no compromises.'],
];
$stats = [
    ['value' => 120, 'suffix' => '+', 'label' => 'Partner Farms'],
    ['value' => 8500, 'suffix' => '+', 'label' => 'Happy Customers'],
    ['value' => 40, 'suffix' => '+', 'label' => 'Restaurant Clients'],
    ['value' => 15, 'suffix' => ' yrs', 'label' => 'In Business'],
];
?>
<!-- Hero -->
<div class="relative h-80 overflow-hidden sm:h-96">
    <img src="https://images.unsplash.com/photo-1500937386664-56d1dfef3854?q=80&w=2000&auto=format&fit=crop"
         alt="Farmers harvesting fresh produce at dawn" class="absolute inset-0 size-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-r from-brand-950/85 to-brand-950/40"></div>
    <div class="absolute inset-0 mx-auto flex max-w-7xl flex-col justify-center gap-3 px-4 sm:px-6">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-brand-200">Our Story</p>
        <h1 class="max-w-2xl text-4xl font-extrabold text-white sm:text-5xl">Fifteen years of putting farms first</h1>
    </div>
</div>

<div class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <div class="grid items-center gap-12 lg:grid-cols-2">
        <div data-animate="fade">
            <div class="space-y-5 text-ink-soft">
                <h2 class="text-3xl font-extrabold text-ink">From a single market stall to the city's trusted supplier</h2>
                <p class="leading-relaxed">
                    FreshMart began in 2011 as a single stall at Market Street, selling
                    vegetables picked that same morning from three family farms. The idea
                    was simple and it never changed: <strong class="text-ink">shorten the distance between
                    harvest and home.</strong>
                </p>
                <p class="leading-relaxed">
                    Today we operate a modern cold-chain facility supplying restaurants,
                    hotels and thousands of households — yet every crate still moves the
                    same way it did on day one: picked at dawn, quality checked twice,
                    and delivered before it ever sees a warehouse shelf.
                </p>
                <p class="leading-relaxed">
                    Whether you need two kilos of tomatoes or two hundred, you get the
                    same produce, the same honesty, and the same obsession with freshness.
                </p>
                <a href="<?= site_url('products') ?>" class="<?= btn('primary', 'lg', 'mt-2') ?>">
                    Browse Our Products <?= lucide('arrow-right', 'size-5') ?>
                </a>
            </div>
        </div>
        <div data-animate="fade" data-animate-delay="0.15">
            <div class="relative aspect-[4/5] overflow-hidden rounded-4xl shadow-card-hover">
                <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=1200&auto=format&fit=crop"
                     alt="Fresh produce display at FreshMart" loading="lazy" class="absolute inset-0 size-full object-cover">
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div data-animate="fade">
        <div class="mt-20 grid grid-cols-2 gap-6 rounded-4xl bg-brand-600 px-8 py-10 text-white shadow-xl shadow-brand-600/20 sm:grid-cols-4">
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

    <!-- Values -->
    <div class="mt-20">
        <div data-animate="fade">
            <h2 class="mb-10 text-center text-3xl font-extrabold text-ink">What we stand for</h2>
        </div>
        <div data-animate="stagger" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($values as $v): ?>
                <div data-animate-item>
                    <div class="h-full rounded-3xl bg-white p-7 shadow-card">
                        <span class="mb-4 flex size-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                            <?= lucide($v['icon'], 'size-6') ?>
                        </span>
                        <h3 class="mb-2 font-bold text-ink"><?= esc($v['title']) ?></h3>
                        <p class="text-sm leading-relaxed text-ink-soft"><?= esc($v['text']) ?></p>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
