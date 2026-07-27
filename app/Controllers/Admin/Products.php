<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Port of the admin product pages + saveProduct/deleteProduct actions
 * (src/actions/admin.ts).
 */
class Products extends BaseController
{
    public function index()
    {
        $db       = db_connect();
        $q        = (string) $this->request->getGet('q');
        $category = (string) $this->request->getGet('category');

        $builder = $db->table('products')
            ->select('products.*, categories.name AS categoryName, categories.slug AS categorySlug')
            ->select('(SELECT url FROM product_images WHERE product_images.productId = products.id ORDER BY sortOrder ASC LIMIT 1) AS image', false)
            ->select('(SELECT COUNT(*) FROM enquiries WHERE enquiries.productId = products.id) AS enquiryCount', false)
            ->join('categories', 'categories.id = products.categoryId')
            ->orderBy('products.createdAt', 'DESC');

        if ($q !== '') {
            $builder->like('products.name', $db->escapeLikeString($q), 'both', null, true);
        }
        if ($category !== '') {
            $builder->where('categories.slug', $category);
        }

        $products   = $builder->get()->getResultArray();
        $categories = $db->table('categories')->select('name, slug')->orderBy('sortOrder', 'ASC')->get()->getResultArray();

        return view('admin/products/index', [
            'title'      => 'Products | FreshMart Admin',
            'products'   => $products,
            'categories' => $categories,
            'q'          => $q,
            'category'   => $category,
        ] + $this->badge());
    }

    public function create()
    {
        return view('admin/products/form', [
            'title'      => 'Add Product | FreshMart Admin',
            'categories' => $this->categoryOptions(),
            'product'    => null,
            'images'     => [],
        ] + $this->badge());
    }

    public function edit(int $id)
    {
        $db      = db_connect();
        $product = $db->table('products')->where('id', $id)->get()->getRowArray();
        if (! $product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $images = $db->table('product_images')->where('productId', $id)->orderBy('sortOrder', 'ASC')->get()->getResultArray();

        return view('admin/products/form', [
            'title'      => 'Edit Product | FreshMart Admin',
            'categories' => $this->categoryOptions(),
            'product'    => $product,
            'images'     => $images,
        ] + $this->badge());
    }

    public function save()
    {
        $data = $this->request->getJSON(true) ?? [];
        $db   = db_connect();

        $images = array_values(array_filter($data['images'] ?? [], static fn ($i) => trim($i['url'] ?? '') !== ''));

        if (empty($data['name']) || empty($data['categoryId']) || empty($data['price']) || count($images) === 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Name, category, price and at least one image are required.']);
        }

        $slug     = slugify($data['slug'] ?: $data['name']);
        $existing = $db->table('products')->where('slug', $slug)->get()->getRowArray();
        if ($existing && (int) $existing['id'] !== (int) ($data['id'] ?? 0)) {
            return $this->response->setJSON(['success' => false, 'message' => "Slug \"{$slug}\" is already in use."]);
        }

        $now    = date('Y-m-d H:i:s');
        $fields = [
            'name'                => $data['name'],
            'slug'                => $slug,
            'categoryId'          => (int) $data['categoryId'],
            'description'         => $data['description'] ?? '',
            'shortDescription'    => $data['shortDescription'] ?? '',
            'price'               => (float) $data['price'],
            'discountPrice'       => ! empty($data['discountPrice']) ? (float) $data['discountPrice'] : null,
            'unit'                => $data['unit'] ?? 'Kg',
            'weightOptions'       => json_encode(array_values(array_filter(array_map('trim', explode(',', $data['weightOptions'] ?? ''))))),
            'stockStatus'         => in_array($data['stockStatus'] ?? '', ['IN_STOCK', 'LOW_STOCK', 'OUT_OF_STOCK'], true) ? $data['stockStatus'] : 'IN_STOCK',
            'isFeatured'          => ! empty($data['isFeatured']) ? 1 : 0,
            'isPopular'           => ! empty($data['isPopular']) ? 1 : 0,
            'isNewArrival'        => ! empty($data['isNewArrival']) ? 1 : 0,
            'isBestSeller'        => ! empty($data['isBestSeller']) ? 1 : 0,
            'isSeasonal'          => ! empty($data['isSeasonal']) ? 1 : 0,
            'isFresh'             => ! empty($data['isFresh']) ? 1 : 0,
            'isOrganic'           => ! empty($data['isOrganic']) ? 1 : 0,
            'onOffer'             => ! empty($data['onOffer']) ? 1 : 0,
            'nutrition'           => ($data['nutrition'] ?? '') !== '' ? $data['nutrition'] : null,
            'storageInstructions' => ($data['storageInstructions'] ?? '') !== '' ? $data['storageInstructions'] : null,
            'origin'              => ($data['origin'] ?? '') !== '' ? $data['origin'] : null,
            'brand'               => ($data['brand'] ?? '') !== '' ? $data['brand'] : null,
            'seoTitle'            => ($data['seoTitle'] ?? '') !== '' ? $data['seoTitle'] : null,
            'seoDescription'      => ($data['seoDescription'] ?? '') !== '' ? $data['seoDescription'] : null,
            'metaKeywords'        => ($data['metaKeywords'] ?? '') !== '' ? $data['metaKeywords'] : null,
            'updatedAt'           => $now,
        ];

        if (! empty($data['id'])) {
            $productId = (int) $data['id'];
            $db->table('products')->where('id', $productId)->update($fields);
            $db->table('product_images')->where('productId', $productId)->delete();
        } else {
            $fields['createdAt'] = $now;
            $db->table('products')->insert($fields);
            $productId = (int) $db->insertID();
        }

        foreach ($images as $i => $img) {
            $db->table('product_images')->insert([
                'url'       => trim($img['url']),
                'alt'       => ($img['alt'] ?? '') !== '' ? $img['alt'] : $data['name'],
                'sortOrder' => $i,
                'productId' => $productId,
            ]);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Product saved.', 'slug' => $slug]);
    }

    public function delete(int $id)
    {
        db_connect()->table('products')->where('id', $id)->delete();

        return $this->response->setJSON(['success' => true]);
    }

    private function categoryOptions(): array
    {
        return db_connect()->table('categories')
            ->select('categories.id, categories.name, parent.name AS parentName')
            ->join('categories AS parent', 'parent.id = categories.parentId', 'left')
            ->orderBy('categories.sortOrder', 'ASC')
            ->get()->getResultArray();
    }

    private function badge(): array
    {
        return ['newEnquiriesCount' => db_connect()->table('enquiries')->where('status', 'NEW')->countAllResults()];
    }
}
