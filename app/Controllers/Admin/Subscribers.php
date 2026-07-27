<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/** Port of the subscribers admin page + deleteSubscriber action. */
class Subscribers extends BaseController
{
    public function index()
    {
        return view('admin/subscribers', [
            'title'             => 'Subscribers | FreshMart Admin',
            'subscribers'       => db_connect()->table('newsletter_subscribers')->orderBy('createdAt', 'DESC')->get()->getResultArray(),
            'newEnquiriesCount' => db_connect()->table('enquiries')->where('status', 'NEW')->countAllResults(),
        ]);
    }

    public function delete(int $id)
    {
        db_connect()->table('newsletter_subscribers')->where('id', $id)->delete();

        return $this->response->setJSON(['success' => true]);
    }
}
