<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseBuilder;

/**
 * Port of src/lib/products.ts + the card include from src/lib/types.ts.
 */
class ProductQuery
{
    public const PAGE_SIZE = 12;

    /**
     * Products joined with their category and cover image, shaped for
     * product_card_data(). $where customises the builder before fetching.
     */
    public static function cards(?callable $where = null, ?string $orderBy = 'products.createdAt DESC', ?int $limit = null, ?int $offset = null): array
    {
        $builder = self::cardBuilder();
        if ($where !== null) {
            $where($builder);
        }
        if ($orderBy !== null) {
            $builder->orderBy($orderBy, '', false);
        }
        if ($limit !== null) {
            $builder->limit($limit, $offset ?? 0);
        }

        return $builder->get()->getResultArray();
    }

    public static function cardBuilder(): BaseBuilder
    {
        $db = db_connect();

        return $db->table('products')
            ->select('products.*, categories.name AS categoryName, categories.slug AS categorySlug')
            ->select('(SELECT url FROM product_images WHERE product_images.productId = products.id ORDER BY sortOrder ASC LIMIT 1) AS image', false)
            ->select('(SELECT alt FROM product_images WHERE product_images.productId = products.id ORDER BY sortOrder ASC LIMIT 1) AS imageAlt', false)
            ->join('categories', 'categories.id = products.categoryId');
    }

    /** Port of queryProducts() — listing with filters, sort and pagination. */
    public static function listing(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));

        $applyWhere = static function (BaseBuilder $builder) use ($params): void {
            $db = db_connect();

            if (! empty($params['q'])) {
                $q = $db->escapeLikeString($params['q']);
                $builder->groupStart()
                    ->like('products.name', $q, 'both', null, true)
                    ->orLike('products.shortDescription', $q, 'both', null, true)
                    ->orLike('products.description', $q, 'both', null, true)
                    ->orLike('products.metaKeywords', $q, 'both', null, true)
                    ->orLike('categories.name', $q, 'both', null, true)
                    ->groupEnd();
            }

            if (! empty($params['category'])) {
                // Match the category itself or any of its children.
                $slug = $db->escape($params['category']);
                $builder->groupStart()
                    ->where("categories.slug = {$slug}", null, false)
                    ->orWhere("categories.parentId IN (SELECT id FROM (SELECT id FROM categories WHERE slug = {$slug}) AS parentCat)", null, false)
                    ->groupEnd();
            }

            $min = isset($params['minPrice']) && $params['minPrice'] !== '' ? (float) $params['minPrice'] : null;
            $max = isset($params['maxPrice']) && $params['maxPrice'] !== '' ? (float) $params['maxPrice'] : null;
            if ($min !== null) {
                $builder->where('products.price >=', $min);
            }
            if ($max !== null) {
                $builder->where('products.price <=', $max);
            }

            if (($params['availability'] ?? '') === 'in-stock') {
                $builder->where('products.stockStatus', 'IN_STOCK');
            }
            if (($params['availability'] ?? '') === 'low-stock') {
                $builder->where('products.stockStatus', 'LOW_STOCK');
            }
            if (($params['organic'] ?? '') === '1') {
                $builder->where('products.isOrganic', 1);
            }
            if (($params['fresh'] ?? '') === '1') {
                $builder->where('products.isFresh', 1);
            }
            if (($params['offers'] ?? '') === '1') {
                $builder->where('products.onOffer', 1);
            }
        };

        $orderBy = match ($params['sort'] ?? '') {
            'price-asc'  => 'products.price ASC',
            'price-desc' => 'products.price DESC',
            'popular'    => 'products.views DESC',
            'alpha'      => 'products.name ASC',
            default      => 'products.createdAt DESC',
        };

        // Count (join needed for q/category filters).
        $countBuilder = db_connect()->table('products')
            ->join('categories', 'categories.id = products.categoryId');
        $applyWhere($countBuilder);
        $total = $countBuilder->countAllResults();

        $products = self::cards($applyWhere, $orderBy, self::PAGE_SIZE, ($page - 1) * self::PAGE_SIZE);

        return [
            'products'   => $products,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => max(1, (int) ceil($total / self::PAGE_SIZE)),
        ];
    }

    /** Active top-level categories with active children — used by nav, footer and filters. */
    public static function navCategories(): array
    {
        $db      = db_connect();
        $parents = $db->table('categories')
            ->where('isActive', 1)->where('parentId IS NULL')
            ->orderBy('sortOrder', 'ASC')->get()->getResultArray();
        $children = $db->table('categories')
            ->where('isActive', 1)->where('parentId IS NOT NULL')
            ->orderBy('sortOrder', 'ASC')->get()->getResultArray();

        foreach ($parents as &$parent) {
            $parent['children'] = array_values(array_filter(
                $children,
                static fn ($c) => (int) $c['parentId'] === (int) $parent['id']
            ));
        }

        return $parents;
    }

    /** Port of buildEnquiryWhere() (src/lib/enquiry-filters.ts). */
    public static function applyEnquiryFilters(BaseBuilder $builder, array $filters): void
    {
        if (! empty($filters['q'])) {
            $q = db_connect()->escapeLikeString($filters['q']);
            $builder->groupStart()
                ->like('customerName', $q, 'both', null, true)
                ->orLike('email', $q, 'both', null, true)
                ->orLike('phone', $q)
                ->orLike('companyName', $q, 'both', null, true)
                ->orLike('productName', $q, 'both', null, true)
                ->groupEnd();
        }
        if (! empty($filters['product'])) {
            $builder->where('productName', $filters['product']);
        }
        if (! empty($filters['status']) && in_array($filters['status'], ['NEW', 'CONTACTED', 'CLOSED'], true)) {
            $builder->where('status', $filters['status']);
        }
        if (! empty($filters['from'])) {
            $builder->where('createdAt >=', date('Y-m-d H:i:s', strtotime($filters['from'])));
        }
        if (! empty($filters['to'])) {
            $builder->where('createdAt <=', $filters['to'] . ' 23:59:59');
        }
    }
}
