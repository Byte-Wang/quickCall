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

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = Database::pdo()->prepare('INSERT INTO users (phone, password_hash) VALUES (?, ?)');
        $stmt->execute([$phone, $hash]);

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
}
