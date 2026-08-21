<?php

class Response
{
    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            ['code' => 0, 'message' => 'ok', 'data' => $data],
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }

    public static function error(string $message, int $code = 1, int $status = 400): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            ['code' => $code, 'message' => $message, 'data' => null],
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }
}
