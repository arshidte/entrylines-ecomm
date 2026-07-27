<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
$details = [
    ['icon' => 'map-pin', 'title' => 'Visit Us', 'lines' => ['via Spinoza 49', 'Rome, Italy 00137']],
    ['icon' => 'phone', 'title' => 'Call Us', 'lines' => ['+39 377 3330007']],
    ['icon' => 'mail', 'title' => 'Email Us', 'lines' => ['info@entrylinesholdings.com']],
    ['icon' => 'clock', 'title' => 'Opening Hours', 'lines' => ['Mon–Sat: 6 AM – 9 PM', 'Sun: 7 AM – 5 PM']],
];
?>
<div class="mx-auto max-w-7xl px-4 py-14 sm:px-6">
    <div data-animate="fade">
        <div class="mb-12 max-w-2xl">
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-brand-600">Contact</p>
            <h1 class="text-4xl font-extrabold text-ink sm:text-5xl">We'd love to hear from you</h1>
            <p class="mt-3 text-ink-soft">
                Wholesale pricing, bulk orders, product questions or feedback — a real
                person will get back to you within the hour during business time.
            </p>
        </div>
    </div>

    <div class="grid gap-10 lg:grid-cols-5">
        <div class="space-y-4 lg:col-span-2">
            <?php foreach ($details as $d): ?>
                <div class="flex gap-4 rounded-3xl bg-white p-5 shadow-card">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                        <?= lucide($d['icon'], 'size-5') ?>
                    </span>
                    <div>
                        <h2 class="font-bold text-ink"><?= esc($d['title']) ?></h2>
                        <?php foreach ($d['lines'] as $l): ?>
                            <p class="text-sm text-ink-soft"><?= esc($l) ?></p>
                        <?php endforeach ?>
                    </div>
                </div>
            <?php endforeach ?>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-card sm:p-8 lg:col-span-3">
            <h2 class="mb-1 text-xl font-bold text-ink">Send us a message</h2>
            <p class="mb-6 text-sm text-ink-soft">
                For product enquiries, you can also use the Buy Now button on any product.
            </p>

            <!-- Contact form — port of contact-form.tsx -->
            <div data-contact-success class="hidden flex-col items-center gap-4 py-12 text-center">
                <span class="flex size-16 items-center justify-center rounded-full bg-brand-50">
                    <?= lucide('circle-check-big', 'size-9 text-brand-600') ?>
                </span>
                <h3 class="text-xl font-bold">Message sent!</h3>
                <p class="max-w-sm text-sm text-ink-soft">
                    Thanks for reaching out — our team will reply to your email shortly.
                </p>
            </div>
            <form data-contact-form class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="ct-name" class="mb-1.5 block text-sm font-medium text-ink">Your Name *</label>
                        <input id="ct-name" name="name" required autocomplete="name" class="fm-input">
                    </div>
                    <div>
                        <label for="ct-email" class="mb-1.5 block text-sm font-medium text-ink">Email *</label>
                        <input id="ct-email" name="email" type="email" required autocomplete="email" class="fm-input">
                    </div>
                    <div>
                        <label for="ct-phone" class="mb-1.5 block text-sm font-medium text-ink">Phone *</label>
                        <input id="ct-phone" name="phone" type="tel" required autocomplete="tel" class="fm-input">
                    </div>
                    <div>
                        <label for="ct-subject" class="mb-1.5 block text-sm font-medium text-ink">Subject *</label>
                        <input id="ct-subject" name="subject" required placeholder="Wholesale pricing, feedback…" class="fm-input">
                    </div>
                </div>
                <div>
                    <label for="ct-message" class="mb-1.5 block text-sm font-medium text-ink">Message *</label>
                    <textarea id="ct-message" name="message" required rows="5" class="fm-textarea"></textarea>
                </div>
                <p role="alert" data-contact-error class="hidden rounded-xl bg-red-50 px-4 py-3 text-sm text-danger-600"></p>
                <button type="submit" class="<?= btn('primary', 'lg') ?>" data-contact-submit>
                    <span data-contact-submit-idle class="inline-flex items-center gap-2"><?= lucide('send', 'size-5') ?></span>
                    <span data-contact-submit-busy class="hidden items-center gap-2"><?= lucide('loader-circle', 'size-5 animate-spin') ?></span>
                    Send Message
                </button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
