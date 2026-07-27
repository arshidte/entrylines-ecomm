<?php

namespace App\Libraries;

use Config\Services;

/**
 * Port of src/lib/mail.ts — SMTP via admin Settings (DB) with .env fallback.
 * When no SMTP host is configured, the message is logged instead of thrown,
 * so the enquiry flow never breaks.
 */
class Mailer
{
    public static function send(string $to, string $subject, string $html): array
    {
        $settings = Settings::all();

        $host = $settings['smtp_host'] !== '' ? $settings['smtp_host'] : (string) env('SMTP_HOST', '');
        $port = (int) ($settings['smtp_port'] !== '' ? $settings['smtp_port'] : env('SMTP_PORT', '587'));
        $user = $settings['smtp_user'] !== '' ? $settings['smtp_user'] : (string) env('SMTP_USER', '');
        $pass = $settings['smtp_pass'] !== '' ? $settings['smtp_pass'] : (string) env('SMTP_PASS', '');
        $from = $settings['smtp_from'] !== '' ? $settings['smtp_from'] : (string) env('SMTP_FROM', 'EntryLines Holdings <no-reply@entrylinesholdings.com>');

        if ($host === '') {
            log_message('info', "[mail:dev] SMTP not configured — would send to {$to}: \"{$subject}\"");

            return ['sent' => false, 'reason' => 'SMTP not configured'];
        }

        // Parse "Name <email>" from addresses.
        $fromEmail = $from;
        $fromName  = '';
        if (preg_match('/^(.*)<([^>]+)>$/', $from, $m)) {
            $fromName  = trim($m[1], " \"'");
            $fromEmail = trim($m[2]);
        }

        $email = Services::email(null, false);
        $email->initialize([
            'protocol'   => 'smtp',
            'SMTPHost'   => $host,
            'SMTPPort'   => $port,
            'SMTPUser'   => $user,
            'SMTPPass'   => $pass,
            'SMTPCrypto' => $port === 465 ? 'ssl' : 'tls',
            'mailType'   => 'html',
            'charset'    => 'utf-8',
            'newline'    => "\r\n",
        ]);
        $email->setFrom($fromEmail, $fromName);
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($html);

        if (! $email->send()) {
            throw new \RuntimeException('Failed to send email: ' . $email->printDebugger(['headers']));
        }

        return ['sent' => true];
    }

    private static function wrap(string $body): string
    {
        return <<<HTML
  <div style="font-family:'Segoe UI',Helvetica,Arial,sans-serif;background:#FFFDF7;padding:32px">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #eee">
      <div style="background:#2E7D32;padding:20px 32px">
        <span style="color:#fff;font-size:22px;font-weight:700;letter-spacing:-0.5px">Fresh<span style="color:#FF9800">Mart</span></span>
      </div>
      <div style="padding:32px;color:#222;font-size:15px;line-height:1.6">{$body}</div>
      <div style="padding:16px 32px;background:#fafaf5;color:#888;font-size:12px">
        FreshMart — Farm-Fresh Groceries, Wholesale &amp; Retail
      </div>
    </div>
  </div>
HTML;
    }

    public static function adminEnquiryEmail(array $e): string
    {
        $row = static fn (string $label, string $value): string => '<tr><td style="padding:8px 12px;background:#f6f8f6;font-weight:600;width:40%;border-radius:6px">'
            . $label . '</td><td style="padding:8px 12px">' . $value . '</td></tr>';

        $rows = $row('Customer', esc($e['customerName']));
        if (! empty($e['companyName'])) {
            $rows .= $row('Company', esc($e['companyName']));
        }
        $rows .= $row('Email', esc($e['email']));
        $rows .= $row('Phone', esc($e['phone']));
        $rows .= $row('Location', esc($e['location']));
        $rows .= $row('Delivery Address', esc($e['deliveryAddress']));
        $rows .= $row('Product', esc($e['productName']));
        $rows .= $row('Quantity', esc($e['quantity'] . ' ' . $e['preferredUnit']));
        if (! empty($e['preferredDate'])) {
            $rows .= $row('Preferred Delivery', esc($e['preferredDate']));
        }
        if (! empty($e['notes'])) {
            $rows .= $row('Notes', esc($e['notes']));
        }

        $productName = esc($e['productName']);

        return self::wrap(<<<HTML
    <h2 style="margin:0 0 8px;color:#2E7D32">New Product Enquiry</h2>
    <p style="margin:0 0 20px">A customer has submitted an enquiry for <strong>{$productName}</strong>.</p>
    <table style="width:100%;border-collapse:separate;border-spacing:0 6px;font-size:14px">
      {$rows}
    </table>
HTML);
    }

    public static function customerAckEmail(array $e): string
    {
        $name     = esc($e['customerName']);
        $product  = esc($e['productName']);
        $quantity = esc($e['quantity'] . ' ' . $e['preferredUnit']);

        return self::wrap(<<<HTML
    <h2 style="margin:0 0 8px;color:#2E7D32">Thank you, {$name}!</h2>
    <p>Your enquiry has been received. Our team will contact you shortly.</p>
    <div style="margin:20px 0;padding:16px 20px;background:#f0f7f0;border-radius:12px">
      <strong>Enquiry summary</strong><br/>
      Product: {$product}<br/>
      Quantity: {$quantity}
    </div>
    <p style="color:#666">If you have any questions in the meantime, simply reply to this email.</p>
HTML);
    }
}
