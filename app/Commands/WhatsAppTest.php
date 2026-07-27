<?php

namespace App\Commands;

use App\Libraries\WhatsApp;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Sends a sample order alert so the WhatsApp configuration can be
 * verified without placing a real enquiry:
 *
 *   php spark whatsapp:test
 */
class WhatsAppTest extends BaseCommand
{
    protected $group       = 'FreshMart';
    protected $name        = 'whatsapp:test';
    protected $description = 'Sends a test WhatsApp order alert using the current configuration.';

    public function run(array $params)
    {
        $config = WhatsApp::config();
        CLI::write('Configuration in effect (Admin → Settings overrides .env):', 'yellow');
        CLI::write('  Phone Number ID : ' . ($config['phoneNumberId'] ?: '(missing)'));
        CLI::write('  Notify Number   : ' . ($config['to'] ?: '(missing)'));
        CLI::write('  Template        : ' . ($config['template'] !== '' ? $config['template'] . ' (' . $config['templateLang'] . ')' : '(none — plain text mode)'));
        CLI::write('  Access Token    : ' . ($config['token'] !== '' ? 'set (' . strlen($config['token']) . ' chars)' : '(missing)'));
        CLI::newLine();

        if (! WhatsApp::isConfigured()) {
            CLI::error('Not configured — set the access token, phone number id and notify number first.');

            return;
        }

        CLI::write('Sending test alert…');
        $result = WhatsApp::sendOrderAlert([
            'customerName'    => 'Test Customer',
            'phone'           => '+1 555 000 1234',
            'productName'     => 'Vine-Ripened Tomatoes (Test)',
            'quantity'        => '2',
            'preferredUnit'   => 'Kg',
            'location'        => 'Test City',
            'deliveryAddress' => '42 Test Street, Test City',
            'preferredDate'   => date('Y-m-d'),
            'notes'           => 'This is a configuration test — please ignore.',
        ]);

        if ($result['sent']) {
            CLI::write('Sent! Message id: ' . $result['id'], 'green');
        } else {
            CLI::error('Failed: ' . ($result['reason'] ?? 'unknown error'));
            if (isset($result['response']['error'])) {
                CLI::write(json_encode($result['response']['error'], JSON_PRETTY_PRINT));
            }
        }
    }
}
