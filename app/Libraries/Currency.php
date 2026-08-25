<?php

namespace App\Libraries;

/**
 * Display-only currency conversion. Product prices are stored in USD; the
 * storefront can render them in EUR using the current market rate, fetched
 * from a free no-key API (frankfurter.dev, ECB data) and cached.
 *
 * The selected currency lives in the `fm_currency` cookie so it persists
 * across pages; the actual re-rendering on switch happens client-side.
 */
class Currency
{
    /** Supported display currencies. USD is the stored/base currency. */
    public const CURRENCIES = [
        'USD' => ['symbol' => '$', 'label' => 'USD'],
        'EUR' => ['symbol' => '€', 'label' => 'EUR'],
    ];

    /** Used only if both the live API and the cache are unavailable. */
    public const FALLBACK_RATE = 0.92;

    private const CACHE_KEY = 'usd_eur_rate';
    private const CACHE_TTL = 21600; // 6 hours

    private static ?float $rate = null;

    /** Current USD→EUR market rate (cached, with graceful fallback). */
    public static function rate(): float
    {
        if (self::$rate !== null) {
            return self::$rate;
        }

        $cache  = cache();
        $cached = $cache->get(self::CACHE_KEY);

        if (is_numeric($cached)) {
            return self::$rate = (float) $cached;
        }

        $fetched = self::fetchRate();

        if ($fetched !== null) {
            $cache->save(self::CACHE_KEY, $fetched, self::CACHE_TTL);

            return self::$rate = $fetched;
        }

        return self::$rate = self::FALLBACK_RATE;
    }

    /** Currently selected display currency code (from cookie), validated. */
    public static function current(): string
    {
        $code = (string) (service('request')->getCookie('fm_currency') ?? '');
        $code = strtoupper($code);

        return isset(self::CURRENCIES[$code]) ? $code : 'USD';
    }

    /** Converts a USD amount into the selected display currency. */
    public static function convert(float $usd): float
    {
        return self::current() === 'EUR' ? $usd * self::rate() : $usd;
    }

    /**
     * Renders a price as an HTML span carrying its USD base value, so the
     * client can re-render every price instantly when the currency switches.
     */
    public static function render(float $usd): string
    {
        $code   = self::current();
        $symbol = self::CURRENCIES[$code]['symbol'];
        $amount = number_format(self::convert($usd), 2);

        return '<span class="fm-price" data-usd="' . number_format($usd, 2, '.', '') . '">'
            . $symbol . $amount . '</span>';
    }

    /** Config exposed to the client via window.FM_CURRENCY. */
    public static function jsConfig(): array
    {
        return [
            'code'       => self::current(),
            'rate'       => self::rate(),
            'currencies' => self::CURRENCIES,
        ];
    }

    /** Fetches the live USD→EUR rate; returns null on any failure. */
    private static function fetchRate(): ?float
    {
        try {
            $response = service('curlrequest')->get(
                'https://api.frankfurter.dev/v1/latest?base=USD&symbols=EUR',
                ['timeout' => 3, 'connect_timeout' => 3, 'allow_redirects' => true]
            );

            $data = json_decode($response->getBody(), true);
            $rate = $data['rates']['EUR'] ?? null;

            return is_numeric($rate) ? (float) $rate : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
