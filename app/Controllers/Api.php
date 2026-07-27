<?php

namespace App\Controllers;

use App\Libraries\Mailer;
use App\Libraries\ProductQuery;
use App\Libraries\Settings;
use App\Libraries\WhatsApp;

/**
 * Ports of the original API routes and server actions:
 * - GET  /api/search              (src/app/api/search/route.ts)
 * - GET  /api/products/by-slugs   (src/app/api/products/by-slugs/route.ts)
 * - POST /api/enquiry             (src/actions/enquiry.ts)
 * - POST /api/contact             (src/actions/contact.ts)
 * - POST /api/newsletter          (src/actions/newsletter.ts)
 */
class Api extends BaseController
{
    public function search()
    {
        $q = trim((string) $this->request->getGet('q'));
        if (mb_strlen($q) < 2) {
            return $this->response->setJSON(['products' => [], 'categories' => []]);
        }

        $db   = db_connect();
        $like = $db->escapeLikeString($q);

        $products = $db->table('products')
            ->select('products.name, products.slug, products.price, products.discountPrice, products.unit')
            ->select('categories.name AS categoryName')
            ->select('(SELECT url FROM product_images WHERE product_images.productId = products.id ORDER BY sortOrder ASC LIMIT 1) AS image', false)
            ->join('categories', 'categories.id = products.categoryId')
            ->groupStart()
                ->like('products.name', $like, 'both', null, true)
                ->orLike('products.shortDescription', $like, 'both', null, true)
                ->orLike('products.metaKeywords', $like, 'both', null, true)
                ->orLike('categories.name', $like, 'both', null, true)
            ->groupEnd()
            ->limit(6)
            ->get()->getResultArray();

        $categories = $db->table('categories')
            ->select('name, slug')
            ->like('name', $like, 'both', null, true)
            ->where('isActive', 1)
            ->limit(4)
            ->get()->getResultArray();

        return $this->response->setJSON([
            'products' => array_map(static fn ($p) => [
                'name'     => $p['name'],
                'slug'     => $p['slug'],
                'price'    => $p['discountPrice'] !== null ? (float) $p['discountPrice'] : (float) $p['price'],
                'unit'     => $p['unit'],
                'image'    => $p['image'] ?? '',
                'category' => $p['categoryName'],
            ], $products),
            'categories' => $categories,
        ]);
    }

    public function bySlugs()
    {
        $slugs = array_slice(array_values(array_filter(array_map(
            'trim',
            explode(',', (string) $this->request->getGet('slugs'))
        ))), 0, 8);

        if (count($slugs) === 0) {
            return $this->response->setJSON(['products' => []]);
        }

        $products = ProductQuery::cards(static fn ($b) => $b->whereIn('products.slug', $slugs), null);

        // Preserve the order the slugs were requested in (most recent first).
        $bySlug = [];
        foreach ($products as $p) {
            $bySlug[$p['slug']] = product_card_data($p);
        }
        $ordered = [];
        foreach ($slugs as $slug) {
            if (isset($bySlug[$slug])) {
                $ordered[] = $bySlug[$slug];
            }
        }

        return $this->response->setJSON(['products' => $ordered]);
    }

    public function enquiry()
    {
        $data = $this->request->getJSON(true) ?? [];

        // Same rules and messages as src/lib/validation.ts.
        $error = null;
        if (mb_strlen(trim($data['customerName'] ?? '')) < 2) {
            $error = 'Please enter your full name';
        } elseif (! filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } elseif (mb_strlen(trim($data['phone'] ?? '')) < 6) {
            $error = 'Please enter a valid phone number';
        } elseif (mb_strlen(trim($data['location'] ?? '')) < 2) {
            $error = 'Please enter your city / location';
        } elseif (mb_strlen(trim($data['deliveryAddress'] ?? '')) < 5) {
            $error = 'Please enter your delivery address';
        } elseif (trim($data['productName'] ?? '') === '') {
            $error = 'Please check the form and try again.';
        } elseif (trim($data['quantity'] ?? '') === '') {
            $error = 'Please enter a quantity';
        } elseif (trim($data['preferredUnit'] ?? '') === '') {
            $error = 'Please choose a unit';
        }
        if ($error !== null) {
            return $this->response->setJSON(['success' => false, 'message' => $error]);
        }

        $db = db_connect();

        $productId = null;
        if (! empty($data['productId'])) {
            $exists = $db->table('products')->where('id', (int) $data['productId'])->countAllResults() > 0;
            $productId = $exists ? (int) $data['productId'] : null;
        }

        try {
            $db->table('enquiries')->insert([
                'customerName'    => trim($data['customerName']),
                'companyName'     => trim($data['companyName'] ?? '') ?: null,
                'email'           => trim($data['email']),
                'phone'           => trim($data['phone']),
                'location'        => trim($data['location']),
                'deliveryAddress' => trim($data['deliveryAddress']),
                'productId'       => $productId,
                'productName'     => trim($data['productName']),
                'quantity'        => trim($data['quantity']),
                'preferredUnit'   => trim($data['preferredUnit']),
                'preferredDate'   => ! empty($data['preferredDate']) ? date('Y-m-d H:i:s', strtotime($data['preferredDate'])) : null,
                'notes'           => trim($data['notes'] ?? '') ?: null,
                'createdAt'       => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to save enquiry: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Something went wrong while saving your enquiry. Please try again.',
            ]);
        }

        // Emails are best-effort: the enquiry is already stored for the admin panel.
        try {
            $adminTo = Settings::get('admin_notify_email') ?: (string) env('ADMIN_NOTIFY_EMAIL', '');
            if ($adminTo !== '') {
                Mailer::send(
                    $adminTo,
                    "New enquiry: {$data['productName']} — {$data['customerName']}",
                    Mailer::adminEnquiryEmail($data)
                );
            }
            Mailer::send(
                $data['email'],
                'We received your enquiry — FreshMart',
                Mailer::customerAckEmail($data)
            );
        } catch (\Throwable $e) {
            log_message('error', 'Failed to send enquiry emails: ' . $e->getMessage());
        }

        // WhatsApp order alert to the business owner — best-effort too.
        WhatsApp::sendOrderAlert($data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Thank you. Your enquiry has been received. Our team will contact you shortly.',
        ]);
    }

    /**
     * Order / enquiry status lookup.
     *
     * Customers don't have accounts — they identify their orders with the same
     * email or phone number they gave when submitting the enquiry. We match on
     * either field and return their enquiry history with a friendly status.
     */
    public function track()
    {
        $data = $this->request->getJSON(true) ?? [];
        $id   = trim((string) ($data['identifier'] ?? ''));

        if ($id === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please enter the email or phone number you used for your order.',
            ]);
        }

        $isEmail = filter_var($id, FILTER_VALIDATE_EMAIL) !== false;
        $digits  = preg_replace('/\D+/', '', $id); // phone digits only

        if (! $isEmail && strlen($digits) < 6) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please enter a valid email address or phone number.',
            ]);
        }

        $db      = db_connect();
        $builder = $db->table('enquiries')
            ->select('customerName, productName, quantity, preferredUnit, preferredDate, status, createdAt')
            ->groupStart();

        if ($isEmail) {
            $builder->where('LOWER(email)', mb_strtolower($id));
        }
        if (strlen($digits) >= 6) {
            // Match the phone ignoring spaces, dashes, parentheses and the + sign.
            $builder->orWhere(
                "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') LIKE '%" . $digits . "%'",
                null,
                false
            );
        }
        $builder->groupEnd();

        // Contact-form messages are stored in the same table — exclude them.
        $rows = $builder
            ->notLike('productName', 'Contact:', 'after')
            ->orderBy('createdAt', 'DESC')
            ->limit(50)
            ->get()->getResultArray();

        // Customer-facing labels for the internal enquiry statuses.
        $labels = [
            'NEW'       => 'Order received',
            'CONTACTED' => 'Being processed',
            'CLOSED'    => 'Completed',
        ];

        $orders = array_map(static function ($r) use ($labels) {
            return [
                'productName'   => $r['productName'],
                'quantity'      => $r['quantity'],
                'unit'          => $r['preferredUnit'],
                'status'        => $r['status'],
                'statusLabel'   => $labels[$r['status']] ?? $r['status'],
                'date'          => $r['createdAt'] ? date('M j, Y', strtotime($r['createdAt'])) : '',
                'preferredDate' => $r['preferredDate'] ? date('M j, Y', strtotime($r['preferredDate'])) : null,
            ];
        }, $rows);

        return $this->response->setJSON([
            'success' => true,
            'name'    => $rows[0]['customerName'] ?? '',
            'orders'  => $orders,
        ]);
    }

    public function contact()
    {
        $data = $this->request->getJSON(true) ?? [];

        $error = null;
        if (mb_strlen(trim($data['name'] ?? '')) < 2) {
            $error = 'Please enter your name';
        } elseif (! filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email';
        } elseif (mb_strlen(trim($data['phone'] ?? '')) < 6) {
            $error = 'Please enter a valid phone number';
        } elseif (mb_strlen(trim($data['subject'] ?? '')) < 2) {
            $error = 'Please enter a subject';
        } elseif (mb_strlen(trim($data['message'] ?? '')) < 10) {
            $error = 'Please tell us a little more';
        }
        if ($error !== null) {
            return $this->response->setJSON(['success' => false, 'message' => $error]);
        }

        // Stored alongside product enquiries so everything lands in one admin inbox.
        db_connect()->table('enquiries')->insert([
            'customerName'    => trim($data['name']),
            'email'           => trim($data['email']),
            'phone'           => trim($data['phone']),
            'location'        => '—',
            'deliveryAddress' => '—',
            'productName'     => 'Contact: ' . trim($data['subject']),
            'quantity'        => '—',
            'preferredUnit'   => '—',
            'notes'           => trim($data['message']),
            'createdAt'       => date('Y-m-d H:i:s'),
        ]);

        try {
            $adminTo = Settings::get('admin_notify_email') ?: (string) env('ADMIN_NOTIFY_EMAIL', '');
            if ($adminTo !== '') {
                Mailer::send(
                    $adminTo,
                    "Contact form: {$data['subject']} — {$data['name']}",
                    '<p><strong>' . esc($data['name']) . '</strong> (' . esc($data['email']) . ', ' . esc($data['phone']) . ') wrote:</p><p>' . esc($data['message']) . '</p>'
                );
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to send contact email: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Thank you! Your message has been received — we'll reply shortly.",
        ]);
    }

    public function newsletter()
    {
        $data  = $this->request->getJSON(true) ?? [];
        $email = trim((string) ($data['email'] ?? ''));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Please enter a valid email address.']);
        }

        $db = db_connect();
        if ($db->table('newsletter_subscribers')->where('email', $email)->countAllResults() === 0) {
            $db->table('newsletter_subscribers')->insert([
                'email'     => $email,
                'createdAt' => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "You're subscribed! Fresh deals are on their way.",
        ]);
    }
}
