<?php

class AuthController
{
    public static function register(array $body): void
    {
        $phone = trim((string)($body['phone'] ?? ''));
        $password = (string)($body['password'] ?? '');

        if (!preg_match('/^1\d{10}$/', $phone)) {
            Response::error('请输入正确的手机号');
        }
        if (strlen($password) < 6) {
            Response::error('密码至少 6 位');
        }

        $stmt = Database::pdo()->prepare('SELECT id FROM users WHERE phone = ?');
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            Response::error('该手机号已注册');
        }

        $ip = self::clientIp();
        if ($ip === '') {
            Response::error('无法获取请求IP，禁止注册');
        }

        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM users WHERE register_ip = ? AND created_at >= NOW() - INTERVAL 24 HOUR'
        );
        $stmt->execute([$ip]);
        if ((int)$stmt->fetchColumn() >= 2) {
            Response::error('同一IP24小时内最多注册2个账号');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = Database::pdo()->prepare('INSERT INTO users (phone, password_hash, register_ip) VALUES (?, ?, ?)');
        $stmt->execute([$phone, $hash, $ip]);

        $userId = (int)Database::pdo()->lastInsertId();
        $token = Auth::issueToken($userId);

        Response::json([
            'token' => $token,
            'user' => ['id' => $userId, 'phone' => $phone],
        ]);
    }

    public static function login(array $body): void
    {
        $phone = trim((string)($body['phone'] ?? ''));
        $password = (string)($body['password'] ?? '');

        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE phone = ?');
        $stmt->execute([$phone]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Response::error('手机号或密码错误');
        }

        $userId = (int)$user['id'];
        $token = Auth::issueToken($userId);

        Response::json([
            'token' => $token,
            'user' => ['id' => $userId, 'phone' => $user['phone']],
        ]);
    }

    public static function me(): void
    {
        Response::json(Auth::requireUser());
    }

    private static function clientIp(): string
    {
        $headers = [
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
        ];
        foreach ($headers as $key) {
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
