<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Settings as SettingsLib;

/** Port of the settings admin page + saveSettingsAction. */
class Settings extends BaseController
{
    public function index()
    {
        return view('admin/settings', [
            'title'             => 'Settings | FreshMart Admin',
            'settings'          => SettingsLib::all(),
            'newEnquiriesCount' => db_connect()->table('enquiries')->where('status', 'NEW')->countAllResults(),
        ]);
    }

    public function save()
    {
        $entries = $this->request->getJSON(true) ?? [];
        // Only accept known settings keys.
        $entries = array_intersect_key($entries, SettingsLib::DEFAULTS);
        SettingsLib::setMany($entries);

        return $this->response->setJSON(['success' => true, 'message' => 'Settings saved.']);
    }
}
