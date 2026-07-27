<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/** Port of src/app/admin/(dashboard)/page.tsx. */
class Dashboard extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $since = new \DateTime('today');
        $since->modify('-13 days');
        $sinceStr = $since->format('Y-m-d 00:00:00');

        $productCount    = $db->table('products')->countAllResults();
        $categoryCount   = $db->table('categories')->countAllResults();
        $enquiryCount    = $db->table('enquiries')->countAllResults();
        $newEnquiries    = $db->table('enquiries')->where('status', 'NEW')->countAllResults();
        $subscriberCount = $db->table('newsletter_subscribers')->countAllResults();

        $recentEnquiries = $db->table('enquiries')->orderBy('createdAt', 'DESC')->limit(6)->get()->getResultArray();

        $topProducts = $db->table('products')
            ->select('products.id, products.name, products.slug, products.views, categories.name AS categoryName')
            ->join('categories', 'categories.id = products.categoryId')
            ->orderBy('views', 'DESC')->limit(5)->get()->getResultArray();

        $enquiriesForChart = $db->table('enquiries')
            ->select('createdAt')
            ->where('createdAt >=', $sinceStr)
            ->get()->getResultArray();

        // Bucket enquiries into the last 14 days.
        $buckets = [];
        for ($i = 0; $i < 14; $i++) {
            $day  = (clone $since)->modify("+{$i} days");
            $next = (clone $day)->modify('+1 day');
            $count = 0;
            foreach ($enquiriesForChart as $e) {
                $t = strtotime($e['createdAt']);
                if ($t >= $day->getTimestamp() && $t < $next->getTimestamp()) {
                    $count++;
                }
            }
            $buckets[] = ['label' => $day->format('M j'), 'count' => $count];
        }
        $maxCount = max(1, max(array_column($buckets, 'count')));

        return view('admin/dashboard', [
            'title'             => 'Dashboard | FreshMart Admin',
            'newEnquiriesCount' => $newEnquiries,
            'stats'             => [
                ['label' => 'Products', 'value' => $productCount, 'icon' => 'package', 'href' => site_url('admin/products'), 'color' => 'bg-brand-600'],
                ['label' => 'Categories', 'value' => $categoryCount, 'icon' => 'folder-tree', 'href' => site_url('admin/categories'), 'color' => 'bg-accent-500'],
                ['label' => 'Enquiries', 'value' => $enquiryCount, 'icon' => 'message-square-text', 'href' => site_url('admin/enquiries'), 'color' => 'bg-sky-600', 'sub' => $newEnquiries . ' new'],
                ['label' => 'Subscribers', 'value' => $subscriberCount, 'icon' => 'mail', 'href' => site_url('admin/subscribers'), 'color' => 'bg-violet-600'],
            ],
            'buckets'           => $buckets,
            'maxCount'          => $maxCount,
            'chartTotal'        => count($enquiriesForChart),
            'topProducts'       => $topProducts,
            'recentEnquiries'   => $recentEnquiries,
        ]);
    }
}
