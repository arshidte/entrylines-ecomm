<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/** Port of the banners admin page + saveBanner/deleteBanner actions. */
class Banners extends BaseController
{
    public function index()
    {
        return view('admin/banners', [
            'title'             => 'Banners | FreshMart Admin',
            'banners'           => db_connect()->table('banners')->orderBy('sortOrder', 'ASC')->get()->getResultArray(),
            'newEnquiriesCount' => db_connect()->table('enquiries')->where('status', 'NEW')->countAllResults(),
        ]);
    }

    public function save()
    {
        $data = $this->request->getJSON(true) ?? [];

        if (empty($data['title']) || empty($data['image'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Title and image are required.']);
        }

        $fields = [
            'title'     => $data['title'],
            'subtitle'  => ($data['subtitle'] ?? '') !== '' ? $data['subtitle'] : null,
            'image'     => $data['image'],
            'ctaText'   => ($data['ctaText'] ?? '') !== '' ? $data['ctaText'] : 'Shop Now',
            'ctaLink'   => ($data['ctaLink'] ?? '') !== '' ? $data['ctaLink'] : '/products',
            'sortOrder' => (int) ($data['sortOrder'] ?? 0),
            'isActive'  => ! empty($data['isActive']) ? 1 : 0,
        ];

        $db = db_connect();
        if (! empty($data['id'])) {
            $db->table('banners')->where('id', (int) $data['id'])->update($fields);
        } else {
            $fields['createdAt'] = date('Y-m-d H:i:s');
            $db->table('banners')->insert($fields);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Banner saved.']);
    }

    public function delete(int $id)
    {
        db_connect()->table('banners')->where('id', $id)->delete();

        return $this->response->setJSON(['success' => true]);
    }
}
