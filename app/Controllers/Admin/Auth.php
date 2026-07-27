<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Session-based admin auth — replaces NextAuth credentials login
 * (src/lib/auth.ts + src/actions/admin-auth.ts).
 */
class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('admin_id')) {
            return redirect()->to('/admin');
        }

        return view('admin/login', ['error' => null]);
    }

    public function attempt()
    {
        $email    = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');

        $user = db_connect()->table('admin_users')->where('email', $email)->get()->getRowArray();

        if (! $user || ! password_verify($password, $user['passwordHash'])) {
            return view('admin/login', ['error' => 'Invalid email or password.']);
        }

        session()->regenerate();
        session()->set([
            'admin_id'    => (int) $user['id'],
            'admin_name'  => $user['name'],
            'admin_email' => $user['email'],
        ]);

        return redirect()->to('/admin');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/admin/login');
    }
}
