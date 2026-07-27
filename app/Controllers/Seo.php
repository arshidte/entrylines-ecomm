<?php

namespace App\Controllers;

/**
 * Ports of src/app/sitemap.ts and src/app/robots.ts.
 */
class Seo extends BaseController
{
    public function sitemap()
    {
        $db      = db_connect();
        $siteUrl = rtrim(base_url(), '/');

        $products   = $db->table('products')->select('slug, updatedAt')->get()->getResultArray();
        $categories = $db->table('categories')->select('slug, updatedAt')->where('isActive', 1)->get()->getResultArray();

        $urls = [
            ['loc' => $siteUrl, 'changefreq' => 'daily', 'priority' => '1'],
            ['loc' => $siteUrl . '/products', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => $siteUrl . '/offers', 'changefreq' => 'daily', 'priority' => '0.8'],
            ['loc' => $siteUrl . '/about', 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => $siteUrl . '/contact', 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => $siteUrl . '/privacy', 'changefreq' => 'yearly', 'priority' => '0.2'],
            ['loc' => $siteUrl . '/terms', 'changefreq' => 'yearly', 'priority' => '0.2'],
        ];

        foreach ($categories as $c) {
            $urls[] = [
                'loc'        => $siteUrl . '/category/' . $c['slug'],
                'lastmod'    => date('c', strtotime($c['updatedAt'] ?? 'now')),
                'changefreq' => 'daily',
                'priority'   => '0.8',
            ];
        }
        foreach ($products as $p) {
            $urls[] = [
                'loc'        => $siteUrl . '/products/' . $p['slug'],
                'lastmod'    => date('c', strtotime($p['updatedAt'] ?? 'now')),
                'changefreq' => 'weekly',
                'priority'   => '0.7',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n    <loc>" . esc($u['loc'], 'html') . "</loc>\n";
            if (isset($u['lastmod'])) {
                $xml .= '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
            }
            $xml .= '    <changefreq>' . $u['changefreq'] . "</changefreq>\n"
                . '    <priority>' . $u['priority'] . "</priority>\n  </url>\n";
        }
        $xml .= '</urlset>';

        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    public function robots()
    {
        $siteUrl = rtrim(base_url(), '/');
        $body    = "User-Agent: *\nAllow: /\nDisallow: /admin\nDisallow: /api\n\nSitemap: {$siteUrl}/sitemap.xml\n";

        return $this->response->setContentType('text/plain')->setBody($body);
    }
}
