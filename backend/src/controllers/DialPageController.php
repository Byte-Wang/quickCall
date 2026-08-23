<?php

class DialPageController
{
    public static function index(): void
    {
        $user = Auth::requireUser();
        $stmt = Database::pdo()->prepare(
            'SELECT p.*,
                    (SELECT COUNT(*) FROM contacts c WHERE c.dial_page_id = p.id) AS contact_count
             FROM dial_pages p
             WHERE p.user_id = ?
             ORDER BY p.id DESC'
        );
        $stmt->execute([$user['id']]);
        $pages = $stmt->fetchAll();

        foreach ($pages as &$p) {
            $p = self::normalizePage($p);
            $p['contact_count'] = (int)$p['contact_count'];
        }
        unset($p);

        Response::json($pages);
    }

    public static function create(array $body): void
    {
        $user = Auth::requireUser();
        $name = trim((string)($body['name'] ?? ''));
        if ($name === '') {
            $name = '未命名拨号页';
        }

        $stmt = Database::pdo()->prepare('SELECT COUNT(*) FROM dial_pages WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        if ((int)$stmt->fetchColumn() >= 10) {
            Response::error('最多只能创建 10 个拨号页');
        }

        $slug = self::generateSlug();
        $stmt = Database::pdo()->prepare(
            'INSERT INTO dial_pages (user_id, name, slug) VALUES (?, ?, ?)'
        );
        $stmt->execute([$user['id'], $name, $slug]);

        $id = (int)Database::pdo()->lastInsertId();
        $page = self::findOne($id, $user['id']);
        $page['contacts'] = self::contacts($id);
        Response::json($page);
    }

    public static function show(int $id): void
    {
        $user = Auth::requireUser();
        $page = self::findOne($id, $user['id']);
        if (!$page) {
            Response::error('拨号页不存在', 404, 404);
        }

        $page['contacts'] = self::contacts($id);
        Response::json($page);
    }

    public static function update(int $id, array $body): void
    {
        $user = Auth::requireUser();
        $page = self::findOne($id, $user['id']);
        if (!$page) {
            Response::error('拨号页不存在', 404, 404);
        }

        $fields = [];
        $values = [];

        foreach (['name', 'bg_type', 'bg_color', 'bg_image'] as $f) {
            if (array_key_exists($f, $body)) {
                $fields[] = "$f = ?";
                $values[] = (string)$body[$f];
            }
        }

        if (array_key_exists('font_size', $body)) {
            $fields[] = 'font_size = ?';
            $values[] = max(12, min(72, (int)$body['font_size']));
        }

        if (array_key_exists('avatar_size', $body)) {
            $fields[] = 'avatar_size = ?';
            $values[] = max(32, min(160, (int)$body['avatar_size']));
        }

        if (array_key_exists('phone_size', $body)) {
            $fields[] = 'phone_size = ?';
            $values[] = max(10, min(48, (int)$body['phone_size']));
        }

        if (array_key_exists('show_name', $body)) {
            $fields[] = 'show_name = ?';
            $values[] = $body['show_name'] ? 1 : 0;
        }

        if ($fields) {
            $fields[] = 'updated_at = CURRENT_TIMESTAMP';
            $values[] = $id;
            $stmt = Database::pdo()->prepare(
                'UPDATE dial_pages SET ' . implode(', ', $fields) . ' WHERE id = ?'
            );
            $stmt->execute($values);
        }

        $page = self::findOne($id, $user['id']);
        $page['contacts'] = self::contacts($id);
        Response::json($page);
    }

    public static function destroy(int $id): void
    {
        $user = Auth::requireUser();
        $page = self::findOne($id, $user['id']);
        if (!$page) {
            Response::error('拨号页不存在', 404, 404);
        }

        $stmt = Database::pdo()->prepare('DELETE FROM contacts WHERE dial_page_id = ?');
        $stmt->execute([$id]);

        $stmt = Database::pdo()->prepare('DELETE FROM dial_pages WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $user['id']]);

        Response::json(null);
    }

    public static function publicShow(string $slug): void
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM dial_pages WHERE slug = ?');
        $stmt->execute([$slug]);
        $page = $stmt->fetch();

        if (!$page) {
            Response::error('拨号页不存在或链接已失效', 404, 404);
        }

        $page = self::normalizePage($page);
        unset($page['user_id']);

        $stmt = Database::pdo()->prepare(
            'SELECT id, dial_page_id, name, phone, avatar, bg_color, font_size, sort_order
             FROM contacts
             WHERE dial_page_id = ?
             ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([$page['id']]);
        $contacts = $stmt->fetchAll();

        foreach ($contacts as &$c) {
            $c = self::normalizeContact($c);
        }
        unset($c);

        $page['contacts'] = $contacts;
        Response::json($page);
    }

    private static function findOne(int $id, int $userId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM dial_pages WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$id, $userId]);
        $page = $stmt->fetch();

        if (!$page) {
            return null;
        }

        return self::normalizePage($page);
    }

    private static function normalizePage(array $p): array
    {
        $p['id'] = (int)$p['id'];
        $p['user_id'] = (int)$p['user_id'];
        $p['font_size'] = (int)$p['font_size'];
        $p['avatar_size'] = (int)($p['avatar_size'] ?? 48);
        $p['phone_size'] = (int)($p['phone_size'] ?? 12);
        $p['show_name'] = (bool)($p['show_name'] ?? 1);
        return $p;
    }

    private static function contacts(int $id): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM contacts WHERE dial_page_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([$id]);
        $contacts = $stmt->fetchAll();

        foreach ($contacts as &$c) {
            $c = self::normalizeContact($c);
        }
        unset($c);

        return $contacts;
    }

    private static function normalizeContact(array $c): array
    {
        $c['id'] = (int)$c['id'];
        $c['dial_page_id'] = (int)$c['dial_page_id'];
        $c['sort_order'] = (int)$c['sort_order'];
        $c['font_size'] = $c['font_size'] === null ? null : (int)$c['font_size'];
        return $c;
    }

    private static function generateSlug(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $pdo = Database::pdo();

        do {
            $slug = '';
            for ($i = 0; $i < 8; $i++) {
                $slug .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $stmt = $pdo->prepare('SELECT id FROM dial_pages WHERE slug = ?');
            $stmt->execute([$slug]);
        } while ($stmt->fetch());

        return $slug;
    }
}
