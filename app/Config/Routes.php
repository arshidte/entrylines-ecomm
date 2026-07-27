<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ── Storefront ──────────────────────────────────────────────
$routes->get('/', 'Site::home');
$routes->get('products', 'Site::products');
$routes->get('products/(:segment)', 'Site::productDetail/$1');
$routes->get('category/(:segment)', 'Site::category/$1');
$routes->get('offers', 'Site::offers');
$routes->get('about', 'Site::about');
$routes->get('contact', 'Site::contact');
$routes->get('privacy', 'Site::privacy');
$routes->get('terms', 'Site::terms');

// SEO
$routes->get('sitemap.xml', 'Seo::sitemap');
$routes->get('robots.txt', 'Seo::robots');

// ── APIs (same paths as the original app) ───────────────────
$routes->get('api/search', 'Api::search');
$routes->get('api/products/by-slugs', 'Api::bySlugs');
$routes->post('api/enquiry', 'Api::enquiry');
$routes->post('api/contact', 'Api::contact');
$routes->post('api/newsletter', 'Api::newsletter');
$routes->get('api/admin/enquiries/export', 'Admin\Enquiries::export', ['filter' => 'adminauth']);

// ── Admin ───────────────────────────────────────────────────
$routes->get('admin/login', 'Admin\Auth::login');
$routes->post('admin/login', 'Admin\Auth::attempt');
$routes->post('admin/logout', 'Admin\Auth::logout');

$routes->group('admin', ['filter' => 'adminauth', 'namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'Dashboard::index');

    $routes->post('upload', 'Uploads::image');

    $routes->get('products', 'Products::index');
    $routes->get('products/new', 'Products::create');
    $routes->get('products/(:num)', 'Products::edit/$1');
    $routes->post('products/save', 'Products::save');
    $routes->post('products/delete/(:num)', 'Products::delete/$1');

    $routes->get('categories', 'Categories::index');
    $routes->post('categories/save', 'Categories::save');
    $routes->post('categories/delete/(:num)', 'Categories::delete/$1');

    $routes->get('banners', 'Banners::index');
    $routes->post('banners/save', 'Banners::save');
    $routes->post('banners/delete/(:num)', 'Banners::delete/$1');

    $routes->get('enquiries', 'Enquiries::index');
    $routes->post('enquiries/status/(:num)', 'Enquiries::status/$1');
    $routes->post('enquiries/delete/(:num)', 'Enquiries::delete/$1');

    $routes->get('subscribers', 'Subscribers::index');
    $routes->post('subscribers/delete/(:num)', 'Subscribers::delete/$1');

    $routes->get('settings', 'Settings::index');
    $routes->post('settings/save', 'Settings::save');
});

$routes->set404Override('App\Controllers\Site::notFound');

