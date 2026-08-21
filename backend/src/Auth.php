<?php

class Auth
{
    public static function issueToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $stmt = Database::pdo()->prepare('INSERT INTO auth_tokens (token, user_id) VALUES (?, ?)');
        $stmt->execute([$token, $userId]);
        return $token;
    }

    public static function currentUser(): ?array
    {
        $token = self::bearerToken();
        if ($token === null) {
            return null;
        }

        $stmt = Database::pdo()->prepare(
            'SELECT u.id, u.phone FROM users u
             JOIN auth_tokens t ON t.user_id = u.id
             WHERE t.token = ?'
        );
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            return null;
        }

        $user['id'] = (int)$user['id'];
        return $user;
    }

    public static function requireUser(): array
    {
        $user = self::currentUser();
        if (!$user) {
            Response::error('未登录或登录已过期', 401, 401);
        }
        return $user;
    }

    private static function bearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if ($header === '' && function_exists('getallheaders')) {
            $headers = getallheaders();
            $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }

        if (preg_match('/Bearer\s+(\S+)/i', $header, $m)) {
            return $m[1];
        }

        return null;
    }
}
