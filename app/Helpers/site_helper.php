<?php

/**
 * Site-wide helpers — PHP ports of src/lib/utils.ts plus view utilities.
 */

if (! function_exists('format_price')) {
    /** Mirrors formatPrice(): USD with 2 decimals, e.g. "$2.49". */
    function format_price(float $price): string
    {
        return '$' . number_format($price, 2);
    }
}

if (! function_exists('slugify')) {
    function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s_]+/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);

        return $text;
    }
}

if (! function_exists('format_date')) {
    /** Mirrors formatDate(): "Jul 15, 2026". */
    function format_date($date): string
    {
        $ts = is_numeric($date) ? (int) $date : strtotime((string) $date);

        return date('M j, Y', $ts);
    }
}

if (! function_exists('stock_label')) {
    function stock_label(string $status): string
    {
        return match ($status) {
            'IN_STOCK'     => 'In Stock',
            'LOW_STOCK'    => 'Limited Stock',
            'OUT_OF_STOCK' => 'Out of Stock',
            default        => $status,
        };
    }
}

if (! function_exists('units')) {
    /** Mirrors UNITS in src/lib/utils.ts. */
    function units(): array
    {
        return ['Kg', 'Gram', 'Piece', 'Box', 'Dozen', 'Bundle', 'Litre', 'Pack'];
    }
}

if (! function_exists('lucide')) {
    /**
     * Renders a lucide icon as inline SVG — visually identical to the
     * lucide-react components used by the original app.
     */
    function lucide(string $name, string $class = ''): string
    {
        static $icons = null;
        if ($icons === null) {
            $icons = require APPPATH . 'Config/LucideIcons.php';
        }
        $inner = $icons[$name] ?? '';
        $classAttr = $class !== '' ? ' class="' . esc($class, 'attr') . '"' : '';

        return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"'
            . ' stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"'
            . $classAttr . ' aria-hidden="true">' . $inner . '</svg>';
    }
}

if (! function_exists('badge')) {
    /** Mirrors the Badge component variants (src/components/ui/badge.tsx). */
    function badge(string $variant, string $content, string $extra = ''): string
    {
        $variants = [
            'fresh'      => 'bg-brand-600 text-white',
            'organic'    => 'bg-brand-100 text-brand-800',
            'offer'      => 'bg-danger-500 text-white',
            'new'        => 'bg-accent-500 text-white',
            'bestseller' => 'bg-ink text-white',
            'outline'    => 'border border-black/15 text-ink-soft',
            'stock'      => 'bg-brand-50 text-brand-700',
            'lowstock'   => 'bg-accent-50 text-accent-600',
            'outofstock' => 'bg-red-50 text-danger-600',
            'muted'      => 'bg-black/5 text-ink-soft',
        ];
        $class = 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ' . ($variants[$variant] ?? $variants['fresh']);
        if ($extra !== '') {
            $class .= ' ' . $extra;
        }

        return '<span class="' . $class . '">' . $content . '</span>';
    }
}

if (! function_exists('stock_badge')) {
    function stock_badge(string $status): string
    {
        $variant = $status === 'IN_STOCK' ? 'stock' : ($status === 'LOW_STOCK' ? 'lowstock' : 'outofstock');

        return badge($variant, esc(stock_label($status)));
    }
}

if (! function_exists('btn')) {
    /** Mirrors buttonVariants (src/components/ui/button.tsx) — returns the class list. */
    function btn(string $variant = 'primary', string $size = 'md', string $extra = ''): string
    {
        $base = 'relative inline-flex cursor-pointer items-center justify-center gap-2 overflow-hidden whitespace-nowrap rounded-full font-semibold transition-all duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:pointer-events-none disabled:opacity-50 active:scale-[0.97] [&_svg]:pointer-events-none [&_svg]:shrink-0';

        $variants = [
            'primary' => 'bg-brand-600 text-white shadow-md shadow-brand-600/25 hover:bg-brand-700 hover:shadow-lg hover:shadow-brand-600/30',
            'accent'  => 'bg-accent-500 text-white shadow-md shadow-accent-500/25 hover:bg-accent-600 hover:shadow-lg hover:shadow-accent-500/30',
            'outline' => 'border-2 border-brand-600 bg-transparent text-brand-700 hover:bg-brand-50',
            'ghost'   => 'text-ink hover:bg-brand-50 hover:text-brand-700',
            'white'   => 'bg-white text-brand-700 shadow-md hover:bg-brand-50 hover:shadow-lg',
            'danger'  => 'bg-danger-500 text-white shadow-md shadow-danger-500/25 hover:bg-danger-600',
            'subtle'  => 'bg-brand-50 text-brand-700 hover:bg-brand-100',
        ];
        $sizes = [
            'sm'     => 'h-9 px-4 text-sm [&_svg]:size-4',
            'md'     => 'h-11 px-6 text-sm [&_svg]:size-4',
            'lg'     => 'h-13 px-8 text-base [&_svg]:size-5',
            'icon'   => 'size-11 [&_svg]:size-5',
            'iconSm' => 'size-9 [&_svg]:size-4',
        ];

        $class = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);

        return $extra !== '' ? $class . ' ' . $extra : $class;
    }
}

if (! function_exists('settings')) {
    /** All site settings (DB overrides merged over defaults). */
    function settings(): array
    {
        return \App\Libraries\Settings::all();
    }
}

if (! function_exists('setting_value')) {
    function setting_value(string $key): string
    {
        return \App\Libraries\Settings::get($key);
    }
}

if (! function_exists('weight_options')) {
    /** Decodes the JSON weightOptions column into a PHP array. */
    function weight_options(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }
}

if (! function_exists('product_card_data')) {
    /**
     * Mirrors toCardData() — the serializable product shape used by cards,
     * quick-view and the enquiry modal. Expects a product row joined with
     * category name/slug and its first image.
     */
    function product_card_data(array $p): array
    {
        return [
            'id'               => (string) $p['id'],
            'name'             => $p['name'],
            'slug'             => $p['slug'],
            'shortDescription' => $p['shortDescription'],
            'price'            => (float) $p['price'],
            'discountPrice'    => $p['discountPrice'] !== null ? (float) $p['discountPrice'] : null,
            'unit'             => $p['unit'],
            'weightOptions'    => weight_options($p['weightOptions'] ?? null),
            'stockStatus'      => $p['stockStatus'],
            'isFresh'          => (bool) $p['isFresh'],
            'isOrganic'        => (bool) $p['isOrganic'],
            'onOffer'          => (bool) $p['onOffer'],
            'isNewArrival'     => (bool) $p['isNewArrival'],
            'isBestSeller'     => (bool) $p['isBestSeller'],
            'image'            => $p['image'] ?? '',
            'imageAlt'         => ($p['imageAlt'] ?? '') !== '' ? $p['imageAlt'] : $p['name'],
            'categoryName'     => $p['categoryName'] ?? '',
            'categorySlug'     => $p['categorySlug'] ?? '',
        ];
    }
}
