<?php

class UploadController
{
    public static function upload(): void
    {
        Auth::requireUser();

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            Response::error('请选择要上传的文件');
        }

        $file = $_FILES['file'];

        if ($file['size'] > 5 * 1024 * 1024) {
            Response::error('图片不能超过 5MB');
        }

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        $mime = self::detectImageMime($file['tmp_name']);

        if ($mime === null || !isset($allowed[$mime])) {
            Response::error('仅支持 JPG / PNG / WebP / GIF 图片');
        }

        $ext = $allowed[$mime];
        $name = bin2hex(random_bytes(12)) . '.' . $ext;

        $dir = dirname(__DIR__, 2) . '/public/uploads';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            Response::error('文件上传失败');
        }

        Response::json(['url' => '/uploads/' . $name]);
    }

    /** 通过文件头魔数判断图片类型，避免依赖 fileinfo 扩展 */
    private static function detectImageMime(string $path): ?string
    {
        $fp = fopen($path, 'rb');
        if ($fp === false) {
            return null;
        }

        $head = fread($fp, 12);
        fclose($fp);

        if (str_starts_with($head, "\xFF\xD8\xFF")) {
            return 'image/jpeg';
        }
        if (str_starts_with($head, "\x89PNG\r\n\x1A\n")) {
            return 'image/png';
        }
        if (str_starts_with($head, 'GIF8')) {
            return 'image/gif';
        }
        if (str_starts_with($head, 'RIFF') && substr($head, 8, 4) === 'WEBP') {
            return 'image/webp';
        }

        return null;
    }
}
