<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ProductQuery;

/**
 * Port of the enquiries admin page, status/delete actions and the CSV
 * export route (src/app/api/admin/enquiries/export/route.ts).
 */
class Enquiries extends BaseController
{
    private function filters(): array
    {
        return [
            'q'       => (string) $this->request->getGet('q'),
            'product' => (string) $this->request->getGet('product'),
            'status'  => (string) $this->request->getGet('status'),
            'from'    => (string) $this->request->getGet('from'),
            'to'      => (string) $this->request->getGet('to'),
        ];
    }

    public function index()
    {
        $db      = db_connect();
        $filters = $this->filters();

        $builder = $db->table('enquiries');
        ProductQuery::applyEnquiryFilters($builder, $filters);
        $enquiries = $builder->orderBy('createdAt', 'DESC')->limit(200)->get()->getResultArray();

        $productNames = $db->table('enquiries')->distinct()->select('productName')->orderBy('productName', 'ASC')->get()->getResultArray();

        $exportQs = http_build_query(array_filter($filters, static fn ($v) => $v !== ''));

        return view('admin/enquiries', [
            'title'             => 'Enquiries | FreshMart Admin',
            'enquiries'         => $enquiries,
            'productNames'      => array_column($productNames, 'productName'),
            'filters'           => $filters,
            'exportQs'          => $exportQs,
            'newEnquiriesCount' => db_connect()->table('enquiries')->where('status', 'NEW')->countAllResults(),
        ]);
    }

    public function status(int $id)
    {
        $status = (string) ($this->request->getJSON(true)['status'] ?? '');
        if (! in_array($status, ['NEW', 'CONTACTED', 'CLOSED'], true)) {
            return $this->response->setJSON(['success' => false]);
        }
        db_connect()->table('enquiries')->where('id', $id)->update(['status' => $status]);

        return $this->response->setJSON(['success' => true]);
    }

    public function delete(int $id)
    {
        db_connect()->table('enquiries')->where('id', $id)->delete();

        return $this->response->setJSON(['success' => true]);
    }

    public function export()
    {
        $builder = db_connect()->table('enquiries');
        ProductQuery::applyEnquiryFilters($builder, $this->filters());
        $enquiries = $builder->orderBy('createdAt', 'DESC')->get()->getResultArray();

        $csvEscape = static function ($value): string {
            $v = (string) ($value ?? '');

            return preg_match('/[",\n]/', $v) ? '"' . str_replace('"', '""', $v) . '"' : $v;
        };

        $header = ['Date', 'Customer Name', 'Company', 'Email', 'Phone', 'Location', 'Delivery Address', 'Product', 'Quantity', 'Unit', 'Preferred Delivery', 'Notes', 'Status'];

        $rows = [implode(',', $header)];
        foreach ($enquiries as $e) {
            $rows[] = implode(',', array_map($csvEscape, [
                date('Y-m-d H:i', strtotime($e['createdAt'])),
                $e['customerName'],
                $e['companyName'],
                $e['email'],
                $e['phone'],
                $e['location'],
                $e['deliveryAddress'],
                $e['productName'],
                $e['quantity'],
                $e['preferredUnit'],
                $e['preferredDate'] ? date('Y-m-d', strtotime($e['preferredDate'])) : '',
                $e['notes'],
                $e['status'],
            ]));
        }

        return $this->response
            ->setContentType('text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="freshmart-enquiries-' . date('Y-m-d') . '.csv"')
            ->setBody(implode("\n", $rows));
    }
}
