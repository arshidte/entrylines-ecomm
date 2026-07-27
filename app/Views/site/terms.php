<?= $this->extend('layouts/site') ?>
<?= $this->section('content') ?>
<?php
$sections = [
    ['title' => 'Enquiry-Based Ordering', 'body' => 'FreshMart operates as an enquiry-based platform. Prices displayed are indicative; final pricing, availability and delivery are confirmed by our team when we respond to your enquiry. Submitting an enquiry does not constitute a binding order until confirmed by us.'],
    ['title' => 'Pricing & Availability', 'body' => 'Fresh produce prices fluctuate with season and market conditions. We reserve the right to adjust quoted prices before order confirmation. Product images are representative; natural produce varies in size, shape and colour.'],
    ['title' => 'Delivery', 'body' => 'Same-day delivery applies to confirmed orders placed before 2 PM within our service area. Delivery times are estimates; perishables are transported under cold-chain conditions.'],
    ['title' => 'Freshness Guarantee', 'body' => 'If you are not satisfied with the freshness or quality of any item, contact us within 24 hours of delivery for a replacement or refund of that item.'],
    ['title' => 'Wholesale Terms', 'body' => 'Wholesale pricing, credit terms and recurring supply agreements are arranged individually. Contact our wholesale desk for a tailored quotation.'],
    ['title' => 'Website Use', 'body' => 'Content on this website, including images and text, may not be reproduced without permission. We may update these terms from time to time; continued use of the site constitutes acceptance of the current terms.'],
];
?>
<div class="mx-auto max-w-3xl px-4 py-14 sm:px-6">
    <h1 class="text-4xl font-extrabold text-ink">Terms &amp; Conditions</h1>
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
