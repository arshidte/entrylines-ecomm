<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-ink">Newsletter Subscribers</h1>
        <p class="text-sm text-ink-soft"><?= count($subscribers) ?> subscribers</p>
    </div>

    <div class="overflow-hidden rounded-3xl bg-white shadow-card">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-black/5 text-xs uppercase tracking-wider text-black/40">
                    <th class="p-4 font-semibold">Email</th>
                    <th class="p-4 font-semibold">Subscribed</th>
                    <th class="p-4 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscribers as $s): ?>
                    <tr class="border-b border-black/5 last:border-0 hover:bg-brand-50/40">
                        <td class="p-4 font-semibold text-ink"><?= esc($s['email']) ?></td>
                        <td class="p-4 text-black/50"><?= format_date($s['createdAt']) ?></td>
                        <td class="p-4">
                            <button type="button" class="<?= btn('danger', 'iconSm') ?>"
                                    data-delete-url="<?= site_url('admin/subscribers/delete/' . $s['id']) ?>"
                                    data-confirm="Remove <?= esc($s['email'], 'attr') ?>?"
                                    aria-label="Remove <?= esc($s['email'], 'attr') ?>">
                                <?= lucide('trash-2', 'size-4') ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach ?>
                <?php if (count($subscribers) === 0): ?>
                    <tr>
                        <td colspan="3" class="p-10 text-center text-ink-soft">No subscribers yet.</td>
                    </tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
