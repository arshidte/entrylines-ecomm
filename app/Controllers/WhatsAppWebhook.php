<?php

namespace App\Controllers;

use App\Libraries\WhatsApp;

/**
 * Incoming WhatsApp Cloud API webhook endpoint.
 *
 * Meta calls this endpoint two ways:
 *
 * - GET  — a one-time verification handshake performed when you save the
 *          callback URL in App Dashboard → WhatsApp → Configuration. Meta
 *          sends hub.mode / hub.verify_token / hub.challenge (which PHP maps
 *          to hub_mode / hub_verify_token / hub_challenge). When the token
 *          matches ours, we echo hub.challenge back as plain text.
 *
 * - POST — event notifications (inbound messages, delivery statuses, etc.),
 *          signed with the app secret. We verify the X-Hub-Signature-256
 *          header before doing anything with the body.
 *
 * Processing is best-effort: once a valid, signed payload is accepted we
 * always return HTTP 200, so Meta does not retry the delivery for up to
 * 7 days (see the "Webhook delivery failure" section of the docs).
 */
class WhatsAppWebhook extends BaseController
{
    /** GET — verification handshake. */
    public function verify()
    {
        $mode      = (string) $this->request->getGet('hub_mode');
        $token     = (string) $this->request->getGet('hub_verify_token');
        $challenge = (string) $this->request->getGet('hub_challenge');

        $expected = WhatsApp::webhookConfig()['verifyToken'];

        if ($mode === 'subscribe' && $expected !== '' && hash_equals($expected, $token)) {
            log_message('info', '[whatsapp] Webhook verified.');

            // Meta expects the raw challenge value echoed back verbatim.
            return $this->response
                ->setStatusCode(200)
                ->setContentType('text/plain')
                ->setBody($challenge);
        }

        log_message('warning', '[whatsapp] Webhook verification failed (mode/token mismatch).');

        return $this->response->setStatusCode(403)->setBody('Forbidden');
    }

    /** POST — event notifications. */
    public function receive()
    {
        $raw = (string) $this->request->getBody();
        $sig = $this->request->getHeaderLine('X-Hub-Signature-256') ?: null;

        if (! WhatsApp::verifySignature($raw, $sig)) {
            log_message('warning', '[whatsapp] Webhook signature verification failed — rejecting payload.');

            return $this->response->setStatusCode(401)->setBody('Invalid signature');
        }

        $payload = json_decode($raw, true);

        if (is_array($payload)) {
            try {
                $this->process($payload);
            } catch (\Throwable $e) {
                // Never fail the acknowledgement: Meta retries non-200 responses
                // for up to 7 days, which would flood us with duplicates.
                log_message('error', '[whatsapp] Webhook processing error: ' . $e->getMessage());
            }
        }

        return $this->response->setStatusCode(200)->setBody('EVENT_RECEIVED');
    }

    /**
     * Walks the webhook envelope and logs inbound messages and outbound
     * delivery statuses. This is the hook to extend when you want to store
     * conversations, auto-reply, or update order records.
     */
    private function process(array $payload): void
    {
        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];

                foreach ($value['messages'] ?? [] as $msg) {
                    $from = $msg['from'] ?? '?';
                    $type = $msg['type'] ?? '?';
                    $text = $msg['text']['body'] ?? '';

                    log_message('info', sprintf(
                        '[whatsapp] Inbound %s message from %s%s',
                        $type,
                        $from,
                        $text !== '' ? ': ' . mb_substr($text, 0, 500) : ''
                    ));
                }

                foreach ($value['statuses'] ?? [] as $status) {
                    log_message('info', sprintf(
                        '[whatsapp] Message %s status: %s (recipient %s)',
                        $status['id'] ?? '?',
                        $status['status'] ?? '?',
                        $status['recipient_id'] ?? '?'
                    ));
                }
            }
        }
    }
}
