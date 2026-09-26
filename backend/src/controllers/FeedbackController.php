<?php

class FeedbackController
{
    public static function store(array $body): void
    {
        $content = trim((string)($body['content'] ?? ''));
        if ($content === '') {
            Response::error('请填写反馈内容');
        }
        if (strlen($content) > 10000) {
            Response::error('反馈内容过长');
        }

        $user = Auth::currentUser();
        $userId = $user ? (int)$user['id'] : null;

        $userAgent = self::truncate((string)($body['user_agent'] ?? ''), 255);
        $platform = self::truncate((string)($body['platform'] ?? ''), 64);
        $language = self::truncate((string)($body['language'] ?? ''), 32);
        $screen = self::truncate((string)($body['screen'] ?? ''), 32);

        $clientTime = null;
        $rawTime = trim((string)($body['client_time'] ?? ''));
        if ($rawTime !== '') {
            $ts = strtotime($rawTime);
            if ($ts !== false) {
                $clientTime = date('Y-m-d H:i:s', $ts);
            }
        }

        $ip = self::clientIp();

        $stmt = Database::pdo()->prepare(
            'INSERT INTO feedback (user_id, content, user_agent, platform, language, screen, client_time, ip)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $content, $userAgent, $platform, $language, $screen, $clientTime, $ip]);

        Response::json(null);
    }

    private static function truncate(string $value, int $length): string
    {
        if (strlen($value) <= $length) {
            return $value;
        }
        return substr($value, 0, $length);
    }

    private static function clientIp(): string
    {
        foreach (['HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR'] as $key) {
            $value = $_SERVER[$key] ?? '';
            if ($value !== '') {
                $ip = trim(explode(',', $value)[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        $ip = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }
}
