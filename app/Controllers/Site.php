<?php

namespace App\Controllers;

use App\Libraries\ProductQuery;

class Site extends BaseController
{
    /** Shared data for the site layout (header nav + footer categories). */
    private function layoutData(): array
    {
        return [
            'navCategories' => ProductQuery::navCategories(),
        ];
    }

    public function home()
    {
        $db = db_connect();

        $banners = $db->table('banners')->where('isActive', 1)->orderBy('sortOrder', 'ASC')->get()->getResultArray();

        $categories = $db->table('categories')
            ->select('categories.*')
            ->select('(SELECT COUNT(*) FROM products WHERE products.categoryId = categories.id) AS productCount', false)
            ->select('(SELECT COUNT(*) FROM products JOIN categories AS ch ON ch.id = products.categoryId WHERE ch.parentId = categories.id) AS childProductCount', false)
            ->where('isActive', 1)
            ->orderBy('parentId IS NOT NULL, parentId', 'ASC', false)
            ->orderBy('sortOrder', 'ASC')
            ->get()->getResultArray();

        // Mirrors the category-card assembly on the home page: parents (with
        // child product counts rolled up) then up to 2 subcategories, max 12.
        $parents = array_values(array_filter($categories, static fn ($c) => $c['parentId'] === null));
        $childCards = array_slice(array_values(array_filter($categories, static fn ($c) => $c['parentId'] !== null)), 0, 2);

        $categoryCards = [];
        foreach ($parents as $c) {
            $categoryCards[] = [
                'name'         => $c['name'],
                'slug'         => $c['slug'],
                'image'        => $c['image'],
                'productCount' => (int) $c['productCount'] + (int) $c['childProductCount'],
            ];
        }
        foreach ($childCards as $c) {
            $categoryCards[] = [
                'name'         => $c['name'],
                'slug'         => $c['slug'],
                'image'        => $c['image'],
                'productCount' => (int) $c['productCount'],
            ];
        }
        $categoryCards = array_slice($categoryCards, 0, 12);

        $featured    = ProductQuery::cards(static fn ($b) => $b->where('products.isFeatured', 1), 'products.createdAt DESC', 8);
        $popular     = ProductQuery::cards(static fn ($b) => $b->where('products.isPopular', 1), null, 8);
        $trending    = ProductQuery::cards(null, 'products.views DESC', 8);
        $newArrivals = ProductQuery::cards(static fn ($b) => $b->where('products.isNewArrival', 1), 'products.createdAt DESC', 8);
        $bestSellers = ProductQuery::cards(static fn ($b) => $b->where('products.isBestSeller', 1), null, 8);
        $seasonal    = ProductQuery::cards(static fn ($b) => $b->where('products.isSeasonal', 1), null, 8);

        $testimonials = $db->table('testimonials')->where('isActive', 1)->orderBy('sortOrder', 'ASC')->limit(4)->get()->getResultArray();

        $tabs = [
            ['key' => 'popular', 'label' => 'Popular', 'products' => array_map('product_card_data', $popular)],
            ['key' => 'trending', 'label' => 'Trending', 'products' => array_map('product_card_data', $trending)],
            ['key' => 'new', 'label' => 'New Arrivals', 'products' => array_map('product_card_data', $newArrivals)],
            ['key' => 'best', 'label' => 'Best Sellers', 'products' => array_map('product_card_data', $bestSellers)],
            ['key' => 'seasonal', 'label' => 'Seasonal', 'products' => array_map('product_card_data', $seasonal)],
        ];

        return view('site/home', $this->layoutData() + [
            'banners'       => $banners,
            'categoryCards' => $categoryCards,
            'featured'      => array_map('product_card_data', $featured),
            'tabs'          => $tabs,
            'testimonials'  => $testimonials,
            'meta'          => [], // root defaults
        ]);
    }

    public function products()
    {
        $params = $this->request->getGet();
        $result = ProductQuery::listing($params);

        return view('site/products', $this->layoutData() + [
            'params' => $params,
            'result' => $result,
            'meta'   => [
                'title'       => 'All Products — Fresh Vegetables, Fruits, Meat & More | FreshMart',
                'description' => 'Browse our full range of farm-fresh vegetables, fruits, premium meat, seafood, dairy and pantry staples. Filter by category, price and availability. Wholesale & retail.',
                'canonical'   => base_url('products'),
            ],
        ]);
    }

    public function category(string $slug)
    {
        $db       = db_connect();
        $category = $db->table('categories')->where('slug', $slug)->get()->getRowArray();
        if (! $category || ! (int) $category['isActive']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $parent = $category['parentId'] !== null
            ? $db->table('categories')->where('id', $category['parentId'])->get()->getRowArray()
            : null;

        $params             = $this->request->getGet();
        $params['category'] = $slug;
        $result             = ProductQuery::listing($params);

        $descBase = $category['description'] ?? ('Shop premium ' . strtolower($category['name']));

        return view('site/category', $this->layoutData() + [
            'category' => $category,
            'parent'   => $parent,
            'params'   => $params,
            'result'   => $result,
            'meta'     => [
                'title'       => $category['name'] . ' — Fresh & Premium Quality | FreshMart',
                'description' => $descBase . ' Wholesale and retail, delivered same day.',
                'canonical'   => base_url('category/' . $slug),
                'ogImage'     => $category['image'] ?: null,
            ],
        ]);
    }

    public function productDetail(string $slug)
    {
        $db      = db_connect();
        $product = $db->table('products')
            ->select('products.*, categories.name AS categoryName, categories.slug AS categorySlug')
            ->join('categories', 'categories.id = products.categoryId')
            ->where('products.slug', $slug)
            ->get()->getRowArray();
        if (! $product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $images = $db->table('product_images')->where('productId', $product['id'])->orderBy('sortOrder', 'ASC')->get()->getResultArray();

        // Count the view (the original does this after the response; the
        // effect — an incremented counter — is identical).
        $db->table('products')->where('id', $product['id'])->set('views', 'views + 1', false)->update();

        $related = ProductQuery::cards(
            static fn ($b) => $b->where('products.categoryId', $product['categoryId'])->where('products.id !=', $product['id']),
            null,
            4
        );

        $price    = $product['discountPrice'] !== null ? (float) $product['discountPrice'] : (float) $product['price'];
        $discount = $product['discountPrice'] !== null
            ? (int) round((1 - (float) $product['discountPrice'] / (float) $product['price']) * 100)
            : null;
        $outOfStock = $product['stockStatus'] === 'OUT_OF_STOCK';

        $jsonLd = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $product['name'],
            'description' => $product['shortDescription'],
            'image'       => array_map(static fn ($i) => $i['url'], $images),
            'category'    => $product['categoryName'],
        ];
        if (! empty($product['brand'])) {
            $jsonLd['brand'] = ['@type' => 'Brand', 'name' => $product['brand']];
        }
        $jsonLd['offers'] = [
            '@type'         => 'Offer',
            'url'           => base_url('products/' . $product['slug']),
            'priceCurrency' => 'USD',
            'price'         => number_format($price, 2, '.', ''),
            'availability'  => $outOfStock ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock',
            'seller'        => ['@type' => 'Organization', 'name' => 'FreshMart'],
        ];

        return view('site/product_detail', $this->layoutData() + [
            'product'    => $product,
            'images'     => $images,
            'related'    => array_map('product_card_data', $related),
            'price'      => $price,
            'discount'   => $discount,
            'outOfStock' => $outOfStock,
            'jsonLd'     => $jsonLd,
            'meta'       => [
                'title'          => ($product['seoTitle'] ?: $product['name']) . ' | FreshMart',
                'description'    => $product['seoDescription'] ?: $product['shortDescription'],
                'keywords'       => $product['metaKeywords'] ?: null,
                'canonical'      => base_url('products/' . $slug),
                'ogImage'        => $images[0]['url'] ?? null,
                'ogImageAlt'     => ($images[0]['alt'] ?? '') !== '' ? $images[0]['alt'] : $product['name'],
                'twitterTitle'   => $product['name'],
                'twitterDesc'    => $product['shortDescription'],
            ],
        ]);
    }

    public function offers()
    {
        $products = ProductQuery::cards(static fn ($b) => $b->where('products.onOffer', 1), 'products.updatedAt DESC');

        return view('site/offers', $this->layoutData() + [
            'products' => array_map('product_card_data', $products),
            'meta'     => [
                'title'       => "Offers & Deals — Fresh Savings Every Day | FreshMart",
                'description' => "Today's best deals on fresh vegetables, fruits, meat, seafood and pantry staples. Limited-time discounts on premium quality produce.",
                'canonical'   => base_url('offers'),
            ],
        ]);
    }

    public function about()
    {
        return view('site/about', $this->layoutData() + [
            'meta' => [
                'title'       => 'About Us — 15 Years of Farm-Fresh Quality | FreshMart',
                'description' => 'FreshMart is a wholesale and retail supplier of premium fresh produce, meat, seafood and dairy. Learn about our farm partnerships, cold chain and quality promise.',
                'canonical'   => base_url('about'),
            ],
        ]);
    }

    public function contact()
    {
        return view('site/contact', $this->layoutData() + [
            'meta' => [
                'title'       => 'Contact Us — Wholesale & Retail Enquiries | FreshMart',
                'description' => 'Get in touch with FreshMart for wholesale pricing, retail orders, or any questions. Phone, email, or visit our Market Street facility.',
                'canonical'   => base_url('contact'),
            ],
        ]);
    }

    public function privacy()
    {
        return view('site/privacy', $this->layoutData() + [
            'meta' => [
                'title'       => 'Privacy Policy | FreshMart',
                'description' => 'How FreshMart collects, uses and protects your personal information.',
                'canonical'   => base_url('privacy'),
            ],
        ]);
    }

    public function terms()
    {
        return view('site/terms', $this->layoutData() + [
            'meta' => [
                'title'       => 'Terms & Conditions | FreshMart',
                'description' => 'Terms of use for the FreshMart website and enquiry service.',
                'canonical'   => base_url('terms'),
            ],
        ]);
    }

    public function notFound()
    {
        return response()->setStatusCode(404)->setBody(view('site/404'));
    }
}
