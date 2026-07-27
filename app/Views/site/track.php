<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<div class="mx-auto max-w-3xl px-4 py-14 sm:px-6">
    <div data-animate="fade">
        <div class="mb-8 max-w-2xl">
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-brand-600">Order Status</p>
            <h1 class="text-4xl font-extrabold text-ink sm:text-5xl">Track my order</h1>
            <p class="mt-3 text-ink-soft">
                No account needed. Just enter the email address or phone number you
                gave when placing your order, and we'll show you your enquiry history
                and its current status.
            </p>
        </div>
    </div>

    <!-- Lookup form -->
    <div class="rounded-3xl bg-white p-6 shadow-card sm:p-8">
        <form data-track-form class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="track-id" class="mb-1.5 block text-sm font-medium text-ink">Email or phone number *</label>
                <input id="track-id" name="identifier" required autocomplete="off"
                       placeholder="you@example.com or +39 377 3330007" class="fm-input">
            </div>
            <button type="submit" class="<?= btn('primary', 'lg') ?>" data-track-submit>
                <span data-track-submit-idle class="inline-flex items-center gap-2"><?= lucide('package-search', 'size-5') ?> Track</span>
                <span data-track-submit-busy class="hidden items-center gap-2"><?= lucide('loader-circle', 'size-5 animate-spin') ?> Checking…</span>
            </button>
        </form>
        <p role="alert" data-track-error class="mt-3 hidden rounded-xl bg-red-50 px-4 py-3 text-sm text-danger-600"></p>
    </div>

    <!-- Results (filled in by JS) -->
    <div data-track-results class="mt-8"></div>

    <p class="mt-8 text-center text-sm text-ink-soft">
        Can't find your order?
        <a href="<?= site_url('contact') ?>" class="font-semibold text-brand-700 hover:underline">Contact our team</a>
        and we'll help you out.
    </p>
</div>
<?= $this->endSection() ?>
