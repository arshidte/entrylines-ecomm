<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Services;

/**
 * Handles admin image uploads. Validates type/size (max 2 MB), downscales and
 * recompresses to an optimum size, then stores the file under public/uploads/{type}.
 * Returns JSON { success, url } so the existing JSON-based save flows can persist
 * the resulting URL just like a pasted link.
 */
class Uploads extends BaseController
{
    /** Max output width (px) per upload context — larger images are downscaled. */
    private const MAX_WIDTH = [
        'banners'    => 2000,
        'products'   => 1400,
        'categories' => 1200,
        'misc'       => 1600,
    ];

    private const MAX_BYTES = 2 * 1024 * 1024; // 2 MB

    /** Allowed MIME types mapped to the extension we save with. */
    private const ALLOWED = [
        'image/jpeg'  => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png'   => 'png',
        'image/webp'  => 'webp',
        'image/gif'   => 'gif',
    ];

    public function image()
    {
        $file = $this->request->getFile('file');
        $type = (string) ($this->request->getPost('type') ?? '');
        if (! isset(self::MAX_WIDTH[$type])) {
            $type = 'misc';
        }

        if ($file === null || ! $file->isValid()) {
            $reason = $file ? $file->getErrorString() : 'No file was received.';

            // A file that exceeds the PHP/HTML limit shows up as an upload error too.
            return $this->fail($reason);
        }

        if ($file->getSize() > self::MAX_BYTES) {
            return $this->fail('Image is larger than 2 MB. Please choose a smaller file.');
        }

        $mime = $file->getMimeType();
        if (! isset(self::ALLOWED[$mime])) {
            return $this->fail('Unsupported image type. Use JPG, PNG, WebP or GIF.');
        }
        $ext = self::ALLOWED[$mime];

        $dir = FCPATH . 'uploads/' . $type . '/';
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return $this->fail('Could not create the upload folder on the server.');
        }

        $name = bin2hex(random_bytes(8)) . '-' . time() . '.' . $ext;
        $dest = $dir . $name;

        try {
            if ($ext === 'gif') {
                // Preserve possible animation — store as-is (already under 2 MB).
                $file->move($dir, $name);
            } else {
                $info  = getimagesize($file->getTempName());
                $width = (int) ($info[0] ?? 0);
                $maxW  = self::MAX_WIDTH[$type];

                $image = Services::image('gd')->withFile($file->getTempName());
                if ($width > $maxW) {
                    // master 'width' scales height proportionally.
                    $image->resize($maxW, $maxW, true, 'width');
                }
                $image->save($dest, 80);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Image upload failed: ' . $e->getMessage());

            return $this->fail('Could not process the image. Please try a different file.');
        }

        return $this->response->setJSON([
            'success' => true,
            'url'     => base_url('uploads/' . $type . '/' . $name),
        ]);
    }

    private function fail(string $message)
    {
        return $this->response->setJSON(['success' => false, 'message' => $message]);
    }
}
