<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/** Port of the categories admin page + saveCategory/deleteCategory actions. */
class Categories extends BaseController
{
    public function index()
    {
        $categories = db_connect()->table('categories')
            ->select('categories.*, parent.name AS parentName')
            ->select('(SELECT COUNT(*) FROM products WHERE products.categoryId = categories.id) AS productCount', false)
            ->join('categories AS parent', 'parent.id = categories.parentId', 'left')
            ->orderBy('categories.parentId IS NOT NULL, categories.parentId', 'ASC', false)
            ->orderBy('categories.sortOrder', 'ASC')
            ->get()->getResultArray();

        return view('admin/categories', [
            'title'             => 'Categories | FreshMart Admin',
            'categories'        => $categories,
            'newEnquiriesCount' => db_connect()->table('enquiries')->where('status', 'NEW')->countAllResults(),
        ]);
    }

    public function save()
    {
        $data = $this->request->getJSON(true) ?? [];
        $db   = db_connect();

        if (empty($data['name'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Name is required.']);
        }

        $slug     = slugify(($data['slug'] ?? '') !== '' ? $data['slug'] : $data['name']);
        $existing = $db->table('categories')->where('slug', $slug)->get()->getRowArray();
        if ($existing && (int) $existing['id'] !== (int) ($data['id'] ?? 0)) {
            return $this->response->setJSON(['success' => false, 'message' => "Slug \"{$slug}\" is already in use."]);
        }

        $now    = date('Y-m-d H:i:s');
        $fields = [
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => ($data['description'] ?? '') !== '' ? $data['description'] : null,
            'image'       => ($data['image'] ?? '') !== '' ? $data['image'] : null,
            'parentId'    => ! empty($data['parentId']) ? (int) $data['parentId'] : null,
            'isActive'    => ! empty($data['isActive']) ? 1 : 0,
            'isFeatured'  => ! empty($data['isFeatured']) ? 1 : 0,
            'sortOrder'   => (int) ($data['sortOrder'] ?? 0),
            'updatedAt'   => $now,
        ];

        if (! empty($data['id'])) {
            if ((int) ($data['parentId'] ?? 0) === (int) $data['id']) {
                return $this->response->setJSON(['success' => false, 'message' => 'A category cannot be its own parent.']);
            }
            $db->table('categories')->where('id', (int) $data['id'])->update($fields);
        } else {
            $fields['createdAt'] = $now;
            $db->table('categories')->insert($fields);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Category saved.']);
    }

    public function delete(int $id)
    {
        $db           = db_connect();
        $productCount = $db->table('products')->where('categoryId', $id)->countAllResults();
        if ($productCount > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "Cannot delete — {$productCount} product(s) still use this category.",
            ]);
        }
        $db->table('categories')->where('id', $id)->delete();

        return $this->response->setJSON(['success' => true, 'message' => 'Category deleted.']);
    }
}
