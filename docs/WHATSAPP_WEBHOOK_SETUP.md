# WhatsApp Webhook — Meta App Dashboard Setup

Steps to register the incoming webhook so this app receives WhatsApp messages
and delivery statuses. The endpoint is already implemented at
`POST/GET /api/whatsapp/webhook`; this covers only the dashboard configuration.

## Prerequisites

- A **publicly reachable HTTPS URL** with a valid TLS certificate. Meta will
  not complete the handshake against `http://` or `localhost`.
- These `.env` values already set on the server:
  - `WHATSAPP_WEBHOOK_VERIFY_TOKEN` — the verify token (any string; must match
    what you paste in the dashboard).
  - `META_APP_SECRET` — used to validate the signature on every incoming POST.

## Steps

1. Go to the **[App Dashboard](https://developers.facebook.com/apps)** and open
   your app.

2. In the left menu, open **WhatsApp → Configuration**.
   _(If the app was created with the "Connect with customers through WhatsApp"
   use case, go to **Use cases → Customize → Configuration** instead.)_

3. In the **Webhook** section, click **Edit** and enter:
   - **Callback URL:** `https://<your-domain>/api/whatsapp/webhook`
   - **Verify token:** the exact value of `WHATSAPP_WEBHOOK_VERIFY_TOKEN`
     from `.env`.

4. Click **Verify and save**. Meta sends a GET request; the endpoint echoes the
   challenge back only when the token matches. On success the webhook is saved.

5. Back in the **Webhook** section, click **Manage** and **subscribe to the
   `messages` field**. (Subscribe to any other fields you need — e.g.
   `message_template_status_update`.)

6. Make sure the app is in **Live** mode. Some webhooks are not delivered while
   the app is in **Dev** mode.

## Verify it works

- Send a WhatsApp message to your business number, then check
  `writable/logs/` — you should see an `[whatsapp] Inbound … message from …`
  line.
- You can also use **WhatsApp → Configuration → Send test** in the dashboard.

## Notes

- Every POST is signed; the endpoint validates the `X-Hub-Signature-256` header
  against `META_APP_SECRET` and rejects mismatches with `401`.
- The endpoint always returns `200` for a valid payload so Meta does not retry
  delivery (retries continue for up to 7 days on any non-200 response).
- Incoming messages/statuses are currently only logged. To store or act on
  them, extend `WhatsAppWebhook::process()` in
  `app/Controllers/WhatsAppWebhook.php`.
