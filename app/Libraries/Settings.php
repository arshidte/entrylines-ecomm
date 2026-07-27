<?php

namespace App\Libraries;

/**
 * Port of src/lib/settings.ts — DB-stored key/value settings merged over
 * hard-coded defaults, cached per request.
 */
class Settings
{
    private static ?array $cache = null;

    public const DEFAULTS = [
        'site_name'          => 'FreshMart',
        'site_tagline'       => 'Farm-Fresh Groceries, Wholesale & Retail',
        'contact_email'      => 'info@entrylinesholdings.com',
        'contact_phone'      => '+39 377 3330007',
        'contact_address'    => 'via Spinoza 49, Rome, Italy 00137',
        'seo_title'          => 'FreshMart — Premium Fresh Produce, Wholesale & Retail',
        'seo_description'    => 'Premium fresh vegetables, fruits, meat, seafood and dairy delivered daily. Wholesale and retail supplier with 100% quality-checked produce direct from farms.',
        'seo_keywords'       => 'fresh vegetables, fruits, meat, seafood, dairy, wholesale grocery, retail grocery, organic produce, farm fresh',
        'social_facebook'    => 'https://facebook.com',
        'social_instagram'   => 'https://instagram.com',
        'social_twitter'     => 'https://twitter.com',
        'social_youtube'     => 'https://youtube.com',
        'smtp_host'          => '',
        'smtp_port'          => '587',
        'smtp_user'          => '',
        'smtp_pass'          => '',
        'smtp_from'          => 'EntryLines Holdings <no-reply@entrylinesholdings.com>',
        'admin_notify_email' => 'info@entrylinesholdings.com',
        'whatsapp_access_token'    => '',
        'whatsapp_phone_number_id' => '',
        'whatsapp_notify_to'       => '',
        'whatsapp_template_name'   => '',
        'whatsapp_template_lang'   => '',
    ];

    public static function all(): array
    {
        if (self::$cache === null) {
            $fromDb = [];

            try {
                $rows = db_connect()->table('settings')->get()->getResultArray();
                foreach ($rows as $row) {
                    $fromDb[$row['key']] = $row['value'];
                }
            } catch (\Throwable) {
                // Table missing (fresh install) — fall back to defaults.
            }

            self::$cache = array_merge(self::DEFAULTS, $fromDb);
        }

        return self::$cache;
    }

    public static function get(string $key): string
    {
        return self::all()[$key] ?? '';
    }

    public static function setMany(array $entries): void
    {
        $db = db_connect();

        foreach ($entries as $key => $value) {
            $exists = $db->table('settings')->where('key', $key)->countAllResults() > 0;
            if ($exists) {
                $db->table('settings')->where('key', $key)->update(['value' => $value]);
            } else {
                $db->table('settings')->insert(['key' => $key, 'value' => $value]);
            }
        }

        self::$cache = null;
    }
}
