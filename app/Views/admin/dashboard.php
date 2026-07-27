<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-extrabold text-ink">Dashboard</h1>
        <p class="text-sm text-ink-soft">Store overview and recent activity.</p>
    </div>

    <!-- Stat cards -->
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <?php foreach ($stats as $stat): ?>
            <a href="<?= esc($stat['href'], 'attr') ?>" class="group rounded-3xl bg-white p-5 shadow-card transition-all hover:-translate-y-0.5 hover:shadow-card-hover">
                <span class="mb-4 flex size-11 items-center justify-center rounded-2xl <?= $stat['color'] ?> text-white">
                    <?= lucide($stat['icon'], 'size-5') ?>
                </span>
                <p class="text-3xl font-extrabold text-ink"><?= $stat['value'] ?></p>
                <p class="mt-0.5 flex items-center gap-2 text-sm text-ink-soft">
                    <?= esc($stat['label']) ?>
                    <?php if (! empty($stat['sub'])): ?>
                        <span class="rounded-full bg-accent-50 px-2 py-0.5 text-[10px] font-bold text-accent-600"><?= esc($stat['sub']) ?></span>
                    <?php endif ?>
                </p>
            </a>
        <?php endforeach ?>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <!-- Enquiry chart -->
        <div class="rounded-3xl bg-white p-6 shadow-card xl:col-span-2">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-ink">Enquiries — Last 14 Days</h2>
                    <p class="text-xs text-ink-soft"><?= $chartTotal ?> total in this period</p>
                </div>
                <?= lucide('trending-up', 'size-5 text-brand-600') ?>
            </div>
            <div class="flex h-44 items-end gap-1.5 sm:gap-2">
                <?php foreach ($buckets as $b): ?>
                    <div class="group flex flex-1 flex-col items-center gap-1.5">
                        <span class="text-[10px] font-bold text-brand-700 opacity-0 transition-opacity group-hover:opacity-100"><?= $b['count'] ?></span>
                        <div class="w-full rounded-t-lg bg-brand-200 transition-colors group-hover:bg-brand-600"
                             style="height: <?= max(4, ($b['count'] / $maxCount) * 130) ?>px"
                             title="<?= esc($b['label'], 'attr') ?>: <?= $b['count'] ?> enquiries"></div>
                        <span class="hidden rotate-0 text-[9px] text-black/35 sm:block"><?= esc(explode(' ', $b['label'])[0]) ?></span>
                    </div>
                <?php endforeach ?>
            </div>
        </div>

        <!-- Top products -->
        <div class="rounded-3xl bg-white p-6 shadow-card">
            <h2 class="mb-4 font-bold text-ink">Most Viewed Products</h2>
            <ul class="space-y-3">
                <?php foreach ($topProducts as $i => $p): ?>
                    <li>
                        <a href="<?= site_url('products/' . $p['slug']) ?>" target="_blank" class="flex items-center gap-3 rounded-xl p-2 transition-colors hover:bg-brand-50">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-xs font-bold text-brand-700"><?= $i + 1 ?></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-ink"><?= esc($p['name']) ?></span>
                                <span class="text-xs text-black/40"><?= esc($p['categoryName']) ?></span>
                            </span>
                            <span class="text-xs font-bold text-ink-soft"><?= $p['views'] ?> views</span>
                        </a>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
    </div>

    <!-- Recent enquiries -->
    <div class="rounded-3xl bg-white p-6 shadow-card">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-bold text-ink">Recent Enquiries</h2>
            <a href="<?= site_url('admin/enquiries') ?>" class="flex items-center gap-1 text-sm font-bold text-brand-700 hover:text-brand-800">
                View all <?= lucide('arrow-right', 'size-3.5') ?>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-black/5 text-xs uppercase tracking-wider text-black/40">
                        <th class="pb-3 pr-4 font-semibold">Customer</th>
                        <th class="pb-3 pr-4 font-semibold">Product</th>
                        <th class="pb-3 pr-4 font-semibold">Quantity</th>
                        <th class="pb-3 pr-4 font-semibold">Date</th>
                        <th class="pb-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentEnquiries as $e): ?>
                        <tr class="border-b border-black/5 last:border-0">
                            <td class="py-3 pr-4">
                                <p class="font-semibold text-ink"><?= esc($e['customerName']) ?></p>
                                <p class="text-xs text-black/40"><?= esc($e['email']) ?></p>
                            </td>
                            <td class="py-3 pr-4"><?= esc($e['productName']) ?></td>
                            <td class="py-3 pr-4"><?= esc($e['quantity']) ?> <?= esc($e['preferredUnit']) ?></td>
                            <td class="py-3 pr-4 text-black/50"><?= format_date($e['createdAt']) ?></td>
                            <td class="py-3">
                                <?= badge($e['status'] === 'NEW' ? 'new' : ($e['status'] === 'CONTACTED' ? 'stock' : 'muted'), esc($e['status'])) ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    <?php if (count($recentEnquiries) === 0): ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-ink-soft">
                                No enquiries yet — they'll appear here as customers submit them.
                            </td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
