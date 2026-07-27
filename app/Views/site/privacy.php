<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
$sections = [
    ['title' => 'Information We Collect', 'body' => 'When you submit an enquiry or subscribe to our newsletter, we collect the details you provide: your name, company name (if given), email address, phone number, location, delivery address and any notes you include. We also collect basic usage analytics to improve the website.'],
    ['title' => 'How We Use Your Information', 'body' => 'Your details are used solely to respond to your enquiries, arrange deliveries, and — if you have subscribed — send you our newsletter. We never sell or rent your personal information to third parties.'],
    ['title' => 'Email Communications', 'body' => 'Enquiry submissions trigger two emails: a notification to our team and an acknowledgement to you. Newsletter emails include an unsubscribe option; you can also ask us to remove you at any time.'],
    ['title' => 'Data Storage & Security', 'body' => 'Your data is stored securely in our database with access restricted to authorised staff. We retain enquiry records only as long as needed to serve you and meet legal obligations.'],
    ['title' => 'Cookies', 'body' => 'We use only essential cookies required for the website to function (such as admin session cookies). Your wishlist and recently viewed products are stored locally in your own browser and never leave your device.'],
    ['title' => 'Your Rights', 'body' => 'You may request a copy of the personal data we hold about you, ask for corrections, or request deletion at any time by emailing info@entrylinesholdings.com.'],
];
?>
<div class="mx-auto max-w-3xl px-4 py-14 sm:px-6">
    <h1 class="text-4xl font-extrabold text-ink">Privacy Policy</h1>
    <p class="mt-3 text-sm text-ink-soft">Last updated: July 2026</p>
    <div class="mt-10 space-y-8">
        <?php foreach ($sections as $s): ?>
            <section>
                <h2 class="mb-2 text-xl font-bold text-ink"><?= esc($s['title']) ?></h2>
                <p class="leading-relaxed text-ink-soft"><?= esc($s['body']) ?></p>
            </section>
        <?php endforeach ?>
    </div>
</div>
<?= $this->endSection() ?>
