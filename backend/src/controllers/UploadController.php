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

    public static function uploadAudio(): void
    {
        Auth::requireUser();

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            Response::error('请选择要上传的音频');
        }

        $file = $_FILES['file'];

        if ($file['size'] > 10 * 1024 * 1024) {
            Response::error('音频不能超过 10MB');
        }

        $allowed = [
            'audio/mpeg' => 'mp3',
            'audio/mp3' => 'mp3',
            'audio/mp4' => 'm4a',
            'audio/x-m4a' => 'm4a',
            'audio/aac' => 'aac',
            'audio/wav' => 'wav',
            'audio/x-wav' => 'wav',
            'audio/webm' => 'webm',
            'audio/ogg' => 'ogg',
            'application/ogg' => 'ogg',
        ];

        $mime = self::detectAudioMime($file);

        if ($mime === null || !isset($allowed[$mime])) {
            Response::error('仅支持 MP3 / M4A / WAV / WEBM / OGG / AAC 音频');
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

    /** 通过客户端 MIME 类型判断音频类型，必要时回退到扩展名 */
    private static function detectAudioMime(array $file): ?string
    {
        $mime = strtolower(trim((string)($file['type'] ?? '')));
        if ($mime !== '' && $mime !== 'application/octet-stream') {
            return $mime;
        }

        $ext = strtolower((string)pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        $map = [
            'mp3' => 'audio/mpeg',
            'm4a' => 'audio/mp4',
            'mp4' => 'audio/mp4',
            'aac' => 'audio/aac',
            'wav' => 'audio/wav',
            'webm' => 'audio/webm',
            'ogg' => 'audio/ogg',
        ];

        return $map[$ext] ?? null;
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
