<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
/** Port of src/components/admin/settings-form.tsx. */
$groups = [
    [
        'title'       => 'Website',
        'description' => 'Basic site identity shown across the storefront.',
        'fields'      => [
            ['key' => 'site_name', 'label' => 'Site Name'],
            ['key' => 'site_tagline', 'label' => 'Tagline'],
            ['key' => 'contact_email', 'label' => 'Contact Email'],
            ['key' => 'contact_phone', 'label' => 'Contact Phone'],
            ['key' => 'contact_address', 'label' => 'Address'],
        ],
    ],
    [
        'title'       => 'SEO',
        'description' => 'Default meta tags for search engines and social sharing.',
        'fields'      => [
            ['key' => 'seo_title', 'label' => 'Default SEO Title'],
            ['key' => 'seo_description', 'label' => 'Default SEO Description', 'type' => 'textarea'],
            ['key' => 'seo_keywords', 'label' => 'Meta Keywords', 'type' => 'textarea'],
        ],
    ],
    [
        'title'       => 'Email (SMTP)',
        'description' => 'Used to send enquiry notifications and customer acknowledgements. Leave host empty to disable sending (emails are logged instead).',
        'fields'      => [
            ['key' => 'smtp_host', 'label' => 'SMTP Host', 'hint' => 'e.g. smtp.gmail.com'],
            ['key' => 'smtp_port', 'label' => 'SMTP Port', 'hint' => '587 (TLS) or 465 (SSL)'],
            ['key' => 'smtp_user', 'label' => 'SMTP Username'],
            ['key' => 'smtp_pass', 'label' => 'SMTP Password', 'type' => 'password'],
            ['key' => 'smtp_from', 'label' => 'From Address', 'hint' => 'e.g. "FreshMart <no-reply@yourdomain.com>"'],
            ['key' => 'admin_notify_email', 'label' => 'Admin Notification Email', 'hint' => 'Where new enquiries are sent'],
        ],
    ],
    [
        'title'       => 'WhatsApp Order Alerts',
        'description' => 'Sends every new order enquiry to your WhatsApp via the Meta Cloud API. Values here override the .env fallbacks. Requires a permanent System User access token; business-initiated alerts need an approved template (see README).',
        'fields'      => [
            ['key' => 'whatsapp_access_token', 'label' => 'Access Token', 'type' => 'password', 'hint' => 'Permanent System User token with whatsapp_business_messaging permission'],
            ['key' => 'whatsapp_phone_number_id', 'label' => 'Phone Number ID', 'hint' => 'From Meta App → WhatsApp → API Setup (falls back to PHONE_NUMBER_ID in .env)'],
            ['key' => 'whatsapp_notify_to', 'label' => 'Notify Number', 'hint' => 'Digits only with country code, e.g. 919744241239'],
            ['key' => 'whatsapp_template_name', 'label' => 'Template Name', 'hint' => 'Approved template for alerts, e.g. freshmart_order_alert. Empty = plain text (needs open 24h session)'],
            ['key' => 'whatsapp_template_lang', 'label' => 'Template Language', 'hint' => 'e.g. en or en_US — must match the approved template'],
        ],
    ],
    [
        'title'       => 'Social Media',
        'description' => 'Links shown in the site footer.',
        'fields'      => [
            ['key' => 'social_facebook', 'label' => 'Facebook URL'],
            ['key' => 'social_instagram', 'label' => 'Instagram URL'],
            ['key' => 'social_twitter', 'label' => 'Twitter / X URL'],
            ['key' => 'social_youtube', 'label' => 'YouTube URL'],
        ],
    ],
];
?>
<div class="max-w-4xl space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-ink">Settings</h1>
        <p class="text-sm text-ink-soft">Website identity, SEO defaults, SMTP email and social links.</p>
    </div>

    <form data-settings-form data-save-url="<?= site_url('admin/settings/save') ?>" class="space-y-6">
        <?php foreach ($groups as $group): ?>
            <div class="rounded-3xl bg-white p-6 shadow-card">
                <h2 class="font-bold text-ink"><?= esc($group['title']) ?></h2>
                <p class="mb-5 mt-0.5 text-sm text-ink-soft"><?= esc($group['description']) ?></p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <?php foreach ($group['fields'] as $f): ?>
                        <div class="<?= ($f['type'] ?? '') === 'textarea' ? 'sm:col-span-2' : '' ?>">
                            <label for="s-<?= $f['key'] ?>" class="mb-1.5 block text-sm font-medium text-ink"><?= esc($f['label']) ?></label>
                            <?php if (($f['type'] ?? '') === 'textarea'): ?>
                                <textarea id="s-<?= $f['key'] ?>" name="<?= $f['key'] ?>" rows="2" class="fm-textarea"><?= esc($settings[$f['key']] ?? '') ?></textarea>
                            <?php else: ?>
                                <input id="s-<?= $f['key'] ?>" name="<?= $f['key'] ?>" type="<?= ($f['type'] ?? '') === 'password' ? 'password' : 'text' ?>" value="<?= esc($settings[$f['key']] ?? '', 'attr') ?>" class="fm-input">
                            <?php endif ?>
                            <?php if (! empty($f['hint'])): ?>
                                <p class="mt-1 text-xs text-black/40"><?= esc($f['hint']) ?></p>
                            <?php endif ?>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        <?php endforeach ?>

        <div class="flex items-center gap-4">
            <button type="submit" class="<?= btn('primary', 'lg') ?>" data-form-submit>
                <span data-submit-idle class="inline-flex items-center gap-2"><?= lucide('save', 'size-5') ?></span>
                <span data-submit-busy class="hidden items-center gap-2"><?= lucide('loader-circle', 'size-5 animate-spin') ?></span>
                Save Settings
            </button>
            <p class="hidden text-sm font-semibold text-brand-700" data-settings-message></p>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
