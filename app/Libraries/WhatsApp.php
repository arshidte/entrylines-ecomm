<?php

namespace App\Libraries;

/**
 * WhatsApp Cloud API order alerts.
 *
 * Sends every new product enquiry ("order") to the business owner's
 * WhatsApp number. Configuration follows the same pattern as Mailer:
 * Admin → Settings values take precedence, .env values are the fallback.
 *
 * WhatsApp platform rules:
 * - Business-initiated messages require a pre-approved TEMPLATE. When
 *   whatsapp_template_name is configured, the alert is sent as that
 *   template with 8 body parameters (see README for the template body).
 * - Free-form TEXT messages are only delivered while the recipient has
 *   an open 24-hour customer-service window (i.e. they messaged the
 *   business number within the last 24h). When no template is
 *   configured, a plain-text alert is attempted instead.
 *
 * Sending is best-effort: failures are logged and never break the
 * enquiry flow (same contract as the enquiry emails).
 */
class WhatsApp
{
    private const GRAPH_VERSION = 'v22.0';

    /** Resolved configuration (settings first, .env fallback). */
    public static function config(): array
    {
        $s   = Settings::all();
        $get = static fn (string $key, string $envKey) => $s[$key] !== '' ? $s[$key] : trim((string) env($envKey, ''));

        return [
            'token'         => $get('whatsapp_access_token', 'WHATSAPP_ACCESS_TOKEN'),
            'phoneNumberId' => $get('whatsapp_phone_number_id', 'PHONE_NUMBER_ID'),
            'to'            => preg_replace('/\D+/', '', $get('whatsapp_notify_to', 'WHATSAPP_NOTIFY_TO')),
            'template'      => $get('whatsapp_template_name', 'WHATSAPP_TEMPLATE_NAME'),
            'templateLang'  => $get('whatsapp_template_lang', 'WHATSAPP_TEMPLATE_LANG') ?: 'en',
        ];
    }

    public static function isConfigured(): bool
    {
        $c = self::config();

        return $c['token'] !== '' && $c['phoneNumberId'] !== '' && $c['to'] !== '';
    }

    /**
     * Sends the order alert for an enquiry. Returns a result array and
     * never throws.
     */
    public static function sendOrderAlert(array $e): array
    {
        $c = self::config();

        if (! self::isConfigured()) {
            log_message('info', '[whatsapp] Not configured (access token / phone number id / notify number missing) — skipping order alert.');

            return ['sent' => false, 'reason' => 'not configured'];
        }

        // Template parameters must not contain newlines, tabs or 4+ spaces.
        $p = static function ($value, string $fallback = '—'): string {
            $value = trim(preg_replace('/\s+/', ' ', (string) ($value ?? '')));

            return $value !== '' ? mb_substr($value, 0, 500) : $fallback;
        };

        $fields = [
            $p($e['customerName'] ?? ''),                                   // {{1}} customer
            $p($e['phone'] ?? ''),                                          // {{2}} phone
            $p($e['productName'] ?? ''),                                    // {{3}} product
            $p(($e['quantity'] ?? '') . ' ' . ($e['preferredUnit'] ?? '')), // {{4}} quantity
            $p($e['location'] ?? ''),                                       // {{5}} location
            $p($e['deliveryAddress'] ?? ''),                                // {{6}} address
            $p($e['preferredDate'] ?? '', 'Not specified'),                 // {{7}} preferred date
            $p($e['notes'] ?? '', 'None'),                                  // {{8}} notes
        ];

        if ($c['template'] !== '') {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to'                => $c['to'],
                'type'              => 'template',
                'template'          => [
                    'name'       => $c['template'],
                    'language'   => ['code' => $c['templateLang']],
                    'components' => [[
                        'type'       => 'body',
                        'parameters' => array_map(
                            static fn (string $text) => ['type' => 'text', 'text' => $text],
                            $fields
                        ),
                    ]],
                ],
            ];
        } else {
            $body = "🛒 New FreshMart Order Enquiry\n\n"
                . "Customer: {$fields[0]}\n"
                . "Phone: {$fields[1]}\n"
                . "Product: {$fields[2]}\n"
                . "Quantity: {$fields[3]}\n"
                . "Location: {$fields[4]}\n"
                . "Delivery Address: {$fields[5]}\n"
                . "Preferred Date: {$fields[6]}\n"
                . "Notes: {$fields[7]}";

            $payload = [
                'messaging_product' => 'whatsapp',
                'to'                => $c['to'],
                'type'              => 'text',
                'text'              => ['preview_url' => false, 'body' => $body],
            ];
        }

        return self::post($c, $payload);
    }

    /** Low-level Graph API call. Logs and swallows every failure. */
    private static function post(array $c, array $payload): array
    {
        $url = 'https://graph.facebook.com/' . self::GRAPH_VERSION . "/{$c['phoneNumberId']}/messages";

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $c['token'],
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);
            $response = curl_exec($ch);
            $status   = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                log_message('error', "[whatsapp] Request failed: {$curlErr}");

                return ['sent' => false, 'reason' => $curlErr];
            }

            $decoded = json_decode($response, true) ?? [];

            if ($status >= 200 && $status < 300 && isset($decoded['messages'][0]['id'])) {
                log_message('info', '[whatsapp] Order alert sent, message id ' . $decoded['messages'][0]['id']);

                return ['sent' => true, 'id' => $decoded['messages'][0]['id']];
            }

            $error = $decoded['error']['message'] ?? ('HTTP ' . $status);
            log_message('error', "[whatsapp] API error: {$error} — response: " . mb_substr($response, 0, 1000));

            return ['sent' => false, 'reason' => $error, 'response' => $decoded];
        } catch (\Throwable $ex) {
            log_message('error', '[whatsapp] Exception: ' . $ex->getMessage());

            return ['sent' => false, 'reason' => $ex->getMessage()];
        }
    }
}
