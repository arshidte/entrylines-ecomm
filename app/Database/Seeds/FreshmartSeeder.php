<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Port of prisma/seed.ts. Seeds the admin user, 10 categories (+children),
 * 42 products with images, 3 hero banners and 4 testimonials. The raw data
 * lives in seed-data.json — extracted verbatim from the original seed script.
 */
class FreshmartSeeder extends Seeder
{
    public function run()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/seed-data.json'), true);
        $now  = date('Y-m-d H:i:s');

        // Admin user
        $adminEmail    = (string) env('ADMIN_EMAIL', 'admin@freshmart.com');
        $adminPassword = (string) env('ADMIN_PASSWORD', 'Admin@123');
        if ($this->db->table('admin_users')->where('email', $adminEmail)->countAllResults() === 0) {
            $this->db->table('admin_users')->insert([
                'name'         => 'FreshMart Admin',
                'email'        => $adminEmail,
                'passwordHash' => password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 10]),
                'createdAt'    => $now,
            ]);
        }

        // Categories (parents, then children)
        $catIds = [];
        $sort   = 0;

        foreach ($data['categories'] as $cat) {
            $existing = $this->db->table('categories')->where('slug', $cat['slug'])->get()->getRowArray();
            if ($existing) {
                $this->db->table('categories')->where('id', $existing['id'])->update([
                    'image'       => $cat['image'],
                    'description' => $cat['description'],
                    'isFeatured'  => ! empty($cat['isFeatured']) ? 1 : 0,
                    'updatedAt'   => $now,
                ]);
                $parentId = (int) $existing['id'];
            } else {
                $this->db->table('categories')->insert([
                    'name'        => $cat['name'],
                    'slug'        => $cat['slug'],
                    'description' => $cat['description'],
                    'image'       => $cat['image'],
                    'isFeatured'  => ! empty($cat['isFeatured']) ? 1 : 0,
                    'sortOrder'   => $sort++,
                    'createdAt'   => $now,
                    'updatedAt'   => $now,
                ]);
                $parentId = (int) $this->db->insertID();
            }
            $catIds[$cat['slug']] = $parentId;

            foreach ($cat['children'] ?? [] as $child) {
                $existingChild = $this->db->table('categories')->where('slug', $child['slug'])->get()->getRowArray();
                if ($existingChild) {
                    $this->db->table('categories')->where('id', $existingChild['id'])->update([
                        'image'       => $child['image'],
                        'description' => $child['description'],
                        'parentId'    => $parentId,
                        'updatedAt'   => $now,
                    ]);
                    $catIds[$child['slug']] = (int) $existingChild['id'];
                } else {
                    $this->db->table('categories')->insert([
                        'name'        => $child['name'],
                        'slug'        => $child['slug'],
                        'description' => $child['description'],
                        'image'       => $child['image'],
                        'parentId'    => $parentId,
                        'sortOrder'   => $sort++,
                        'createdAt'   => $now,
                        'updatedAt'   => $now,
                    ]);
                    $catIds[$child['slug']] = (int) $this->db->insertID();
                }
            }
        }

        // Products
        foreach ($data['products'] as $p) {
            $categoryId = $catIds[$p['category']] ?? null;
            if ($categoryId === null) {
                throw new \RuntimeException("Unknown category slug: {$p['category']}");
            }
            if ($this->db->table('products')->where('slug', $p['slug'])->countAllResults() > 0) {
                continue;
            }

            $this->db->table('products')->insert([
                'name'                => $p['name'],
                'slug'                => $p['slug'],
                'categoryId'          => $categoryId,
                'description'         => $p['description'],
                'shortDescription'    => $p['shortDescription'],
                'price'               => $p['price'],
                'discountPrice'       => $p['discountPrice'] ?? null,
                'unit'                => $p['unit'],
                'weightOptions'       => json_encode($p['weightOptions'] ?? []),
                'stockStatus'         => $p['stockStatus'] ?? 'IN_STOCK',
                'isFeatured'          => ! empty($p['isFeatured']) ? 1 : 0,
                'isPopular'           => ! empty($p['isPopular']) ? 1 : 0,
                'isNewArrival'        => ! empty($p['isNewArrival']) ? 1 : 0,
                'isBestSeller'        => ! empty($p['isBestSeller']) ? 1 : 0,
                'isSeasonal'          => ! empty($p['isSeasonal']) ? 1 : 0,
                'isFresh'             => array_key_exists('isFresh', $p) ? (int) (bool) $p['isFresh'] : 1,
                'isOrganic'           => ! empty($p['isOrganic']) ? 1 : 0,
                'onOffer'             => ! empty($p['onOffer']) ? 1 : 0,
                'nutrition'           => $p['nutrition'] ?? null,
                'storageInstructions' => $p['storageInstructions'] ?? null,
                'origin'              => $p['origin'] ?? null,
                'brand'               => $p['brand'] ?? null,
                'seoTitle'            => "{$p['name']} — Fresh {$p['unit']} Price | FreshMart",
                'seoDescription'      => $p['shortDescription'],
                'metaKeywords'        => strtolower($p['name']) . ', fresh ' . str_replace('-', ' ', $p['category']) . ', wholesale, retail',
                'views'               => random_int(100, 999),
                'createdAt'           => $now,
                'updatedAt'           => $now,
            ]);
            $productId = (int) $this->db->insertID();

            foreach ($p['images'] as $i => $url) {
                $this->db->table('product_images')->insert([
                    'url'       => $url,
                    'alt'       => "{$p['name']} — fresh, premium quality",
                    'sortOrder' => $i,
                    'productId' => $productId,
                ]);
            }
        }

        // Banners
        $this->db->table('banners')->truncate();
        foreach ($data['banners'] as $b) {
            $this->db->table('banners')->insert([
                'title'     => $b['title'],
                'subtitle'  => $b['subtitle'],
                'image'     => $b['image'],
                'ctaText'   => $b['ctaText'],
                'ctaLink'   => $b['ctaLink'],
                'sortOrder' => $b['sortOrder'],
                'createdAt' => $now,
            ]);
        }

        // Testimonials
        $this->db->table('testimonials')->truncate();
        foreach ($data['testimonials'] as $t) {
            $this->db->table('testimonials')->insert([
                'name'      => $t['name'],
                'role'      => $t['role'],
                'avatar'    => $t['avatar'],
                'rating'    => $t['rating'],
                'content'   => $t['content'],
                'sortOrder' => $t['sortOrder'],
                'createdAt' => $now,
            ]);
        }

        echo 'Seeded ' . $this->db->table('categories')->countAllResults() . ' categories, '
            . $this->db->table('products')->countAllResults() . ' products, '
            . $this->db->table('banners')->countAllResults() . " banners.\n";
    }
}
