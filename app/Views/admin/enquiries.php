<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
/** Port of the enquiries page + enquiry-row.tsx (expand/status/delete in admin.js). */
$statusStyles = [
    'NEW'       => 'bg-accent-50 text-accent-600 border-accent-500/30',
    'CONTACTED' => 'bg-sky-50 text-sky-700 border-sky-300',
    'CLOSED'    => 'bg-black/5 text-ink-soft border-black/10',
];
?>
<div class="space-y-6" data-enquiries
     data-status-url="<?= site_url('admin/enquiries/status') ?>"
     data-delete-url="<?= site_url('admin/enquiries/delete') ?>">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-ink">Enquiries</h1>
            <p class="text-sm text-ink-soft"><?= count($enquiries) ?> enquiries shown</p>
        </div>
        <a href="<?= site_url('api/admin/enquiries/export') . ($exportQs ? '?' . $exportQs : '') ?>" download class="<?= btn('outline', 'md') ?>">
            <?= lucide('download', 'size-4') ?> Export CSV
        </a>
    </div>

    <!-- Filters -->
    <form action="<?= site_url('admin/enquiries') ?>" method="get" class="flex flex-wrap items-end gap-2 rounded-3xl bg-white p-4 shadow-card">
        <div class="relative">
            <?= lucide('search', 'pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-black/35') ?>
            <input type="search" name="q" value="<?= esc($filters['q'], 'attr') ?>" placeholder="Search name, email, phone…"
                   class="h-11 w-60 rounded-xl border border-black/10 pl-10 pr-4 text-sm focus:border-brand-500 focus:outline-none">
        </div>
        <select name="product" aria-label="Filter by product" class="h-11 cursor-pointer rounded-xl border border-black/10 px-3 text-sm focus:outline-none">
            <option value="">All products</option>
            <?php foreach ($productNames as $name): ?>
                <option value="<?= esc($name, 'attr') ?>"<?= $filters['product'] === $name ? ' selected' : '' ?>><?= esc($name) ?></option>
            <?php endforeach ?>
        </select>
        <select name="status" aria-label="Filter by status" class="h-11 cursor-pointer rounded-xl border border-black/10 px-3 text-sm focus:outline-none">
            <option value="">All statuses</option>
            <option value="NEW"<?= $filters['status'] === 'NEW' ? ' selected' : '' ?>>New</option>
            <option value="CONTACTED"<?= $filters['status'] === 'CONTACTED' ? ' selected' : '' ?>>Contacted</option>
            <option value="CLOSED"<?= $filters['status'] === 'CLOSED' ? ' selected' : '' ?>>Closed</option>
        </select>
        <label class="text-xs text-black/45">
            From
            <input type="date" name="from" value="<?= esc($filters['from'], 'attr') ?>" class="ml-1.5 h-11 rounded-xl border border-black/10 px-3 text-sm focus:outline-none">
        </label>
        <label class="text-xs text-black/45">
            To
            <input type="date" name="to" value="<?= esc($filters['to'], 'attr') ?>" class="ml-1.5 h-11 rounded-xl border border-black/10 px-3 text-sm focus:outline-none">
        </label>
        <button type="submit" class="<?= btn('subtle', 'md') ?>">Apply</button>
    </form>

    <div class="space-y-3">
        <?php foreach ($enquiries as $e): ?>
            <div class="rounded-2xl border border-black/5 bg-white transition-shadow hover:shadow-card" data-enquiry-row data-id="<?= (int) $e['id'] ?>">
                <div class="flex flex-wrap items-center gap-3 p-4">
                    <button type="button" data-enquiry-toggle aria-expanded="false" aria-label="Toggle details"
                            class="flex size-8 shrink-0 cursor-pointer items-center justify-center rounded-full bg-black/5 text-ink-soft transition-colors hover:bg-black/10">
                        <span data-icon-closed><?= lucide('chevron-down', 'size-4') ?></span>
                        <span data-icon-open class="hidden"><?= lucide('chevron-up', 'size-4') ?></span>
                    </button>

                    <div class="min-w-40 flex-1">
                        <p class="font-bold text-ink"><?= esc($e['customerName']) ?></p>
                        <p class="text-xs text-black/45">
                            <?= $e['companyName'] ? esc($e['companyName']) . ' · ' : '' ?><?= esc($e['location']) ?>
                        </p>
                    </div>

                    <div class="min-w-40 flex-1">
                        <p class="text-sm font-semibold text-brand-700"><?= esc($e['productName']) ?></p>
                        <p class="text-xs text-black/45"><?= esc($e['quantity']) ?> <?= esc($e['preferredUnit']) ?></p>
                    </div>

                    <p class="w-24 text-xs text-black/45"><?= format_date($e['createdAt']) ?></p>

                    <select data-enquiry-status aria-label="Enquiry status"
                            class="h-9 cursor-pointer rounded-full border px-3 text-xs font-bold focus:outline-none <?= $statusStyles[$e['status']] ?>">
                        <option value="NEW"<?= $e['status'] === 'NEW' ? ' selected' : '' ?>>NEW</option>
                        <option value="CONTACTED"<?= $e['status'] === 'CONTACTED' ? ' selected' : '' ?>>CONTACTED</option>
                        <option value="CLOSED"<?= $e['status'] === 'CLOSED' ? ' selected' : '' ?>>CLOSED</option>
                    </select>

                    <button type="button" class="<?= btn('danger', 'iconSm') ?>" data-enquiry-delete
                            data-confirm="Delete enquiry from <?= esc($e['customerName'], 'attr') ?>?"
                            aria-label="Delete enquiry from <?= esc($e['customerName'], 'attr') ?>">
                        <?= lucide('trash-2', 'size-4') ?>
                    </button>
                </div>

                <div class="hidden grid gap-4 border-t border-black/5 p-4 text-sm sm:grid-cols-2 lg:grid-cols-3" data-enquiry-details>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-black/40">Contact</p>
                        <p class="mt-1">
                            <a href="mailto:<?= esc($e['email'], 'attr') ?>" class="text-brand-700 hover:underline"><?= esc($e['email']) ?></a>
                        </p>
                        <p>
                            <a href="tel:<?= esc($e['phone'], 'attr') ?>" class="text-brand-700 hover:underline"><?= esc($e['phone']) ?></a>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-black/40">Delivery Address</p>
                        <p class="mt-1 text-ink-soft"><?= esc($e['deliveryAddress']) ?></p>
                        <?php if ($e['preferredDate']): ?>
                            <p class="mt-1 text-ink-soft"><span class="font-semibold">Preferred date:</span> <?= format_date($e['preferredDate']) ?></p>
                        <?php endif ?>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-black/40">Notes</p>
                        <p class="mt-1 text-ink-soft"><?= $e['notes'] ? esc($e['notes']) : '—' ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
        <?php if (count($enquiries) === 0): ?>
            <div class="rounded-3xl bg-white p-12 text-center text-ink-soft shadow-card">
                No enquiries match these filters.
            </div>
        <?php endif ?>
    </div>
</div>
<?= $this->endSection() ?>
