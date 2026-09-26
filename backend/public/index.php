<?php

declare(strict_types=1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/Response.php';
require __DIR__ . '/../src/Auth.php';
require __DIR__ . '/../src/controllers/AuthController.php';
require __DIR__ . '/../src/controllers/DialPageController.php';
require __DIR__ . '/../src/controllers/ContactController.php';
require __DIR__ . '/../src/controllers/UploadController.php';
require __DIR__ . '/../src/controllers/FeedbackController.php';

$method = $_SERVER['REQUEST_METHOD'];
$rawRoute = isset($_GET['r']) ? (string)$_GET['r'] : ($_SERVER['REQUEST_URI'] ?? '');
$path = parse_url($rawRoute, PHP_URL_PATH);
$path = '/' . trim((string)$path, '/');

// 静态文件：上传的头像 / 背景图
if (str_starts_with($path, '/uploads/')) {
    $filename = basename($path);
    $file = __DIR__ . '/uploads/' . $filename;

    if (is_file($file)) {
        $ext = strtolower((string)pathinfo($file, PATHINFO_EXTENSION));
        $mimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
        ];
        $mime = $mimes[$ext] ?? 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string)filesize($file));
        readfile($file);
        exit;
    }

    Response::error('文件不存在', 404, 404);
}

$segments = array_values(array_filter(explode('/', $path)));

if (($segments[0] ?? '') !== 'api') {
    Response::error('接口不存在', 404, 404);
}

$resource = $segments[1] ?? '';

if ($resource === 'auth') {
    $action = $segments[2] ?? '';

    if ($action === 'register' && $method === 'POST') {
        AuthController::register(readBody());
    }
    if ($action === 'login' && $method === 'POST') {
        AuthController::login(readBody());
    }
    if ($action === 'me' && $method === 'GET') {
        AuthController::me();
    }

    Response::error('接口不存在', 404, 404);
}

if ($resource === 'upload' && $method === 'POST') {
    UploadController::upload();
}

if ($resource === 'feedback' && $method === 'POST') {
    FeedbackController::store(readBody());
}

if ($resource === 'dial-pages') {
    if ($method === 'GET' && !isset($segments[2])) {
        DialPageController::index();
    }
    if ($method === 'POST' && !isset($segments[2])) {
        DialPageController::create(readBody());
    }

    $id = (int)($segments[2] ?? 0);

    if ($id > 0 && ($segments[3] ?? '') === 'contacts' && $method === 'POST') {
        ContactController::store($id, readBody());
    }

    if ($id > 0 && ($segments[3] ?? '') === 'contacts' && ($segments[4] ?? '') === 'reorder' && $method === 'PUT') {
        ContactController::reorder($id, readBody());
    }

    if ($id > 0 && $method === 'GET') {
        DialPageController::show($id);
    }
    if ($id > 0 && $method === 'PUT') {
        DialPageController::update($id, readBody());
    }
    if ($id > 0 && $method === 'DELETE') {
        DialPageController::destroy($id);
    }

    Response::error('接口不存在', 404, 404);
}

if ($resource === 'contacts') {
    $id = (int)($segments[2] ?? 0);

    if ($id > 0 && $method === 'PUT') {
        ContactController::update($id, readBody());
    }
    if ($id > 0 && $method === 'DELETE') {
        ContactController::destroy($id);
    }

    Response::error('接口不存在', 404, 404);
}

if ($resource === 'public') {
    $sub = $segments[2] ?? '';

    if ($sub === 'dial-page' && isset($segments[3]) && $method === 'GET') {
        DialPageController::publicShow($segments[3]);
    }
    if ($sub === 'contact' && isset($segments[3], $segments[4]) && $method === 'GET') {
        ContactController::publicContact($segments[3], (int)$segments[4]);
    }

    Response::error('接口不存在', 404, 404);
}

Response::error('接口不存在', 404, 404);

function readBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
