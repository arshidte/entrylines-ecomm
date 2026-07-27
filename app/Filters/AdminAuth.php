<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Guards every admin page and admin API — mirrors requireAdmin() in the
 * original app.
 */
class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if (! $session->get('admin_id')) {
            if (str_starts_with($request->getUri()->getPath(), '/api/')) {
                return service('response')->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
            }

            return redirect()->to('/admin/login');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
