<?php

class ContactController
{
    public static function store(int $pageId, array $body): void
    {
        $user = Auth::requireUser();
        self::assertPageOwned($pageId, $user['id']);

        $name = trim((string)($body['name'] ?? ''));
        $phone = trim((string)($body['phone'] ?? ''));

        if ($name === '') {
            Response::error('请填写名称');
        }
        if ($phone === '') {
            Response::error('请填写手机号');
        }

        $avatar = trim((string)($body['avatar'] ?? ''));
        $bgColor = trim((string)($body['bg_color'] ?? ''));
        $fontSize = self::parseFontSize($body['font_size'] ?? null);

        $stmt = Database::pdo()->prepare(
            'SELECT COALESCE(MAX(sort_order), 0) + 1 AS next FROM contacts WHERE dial_page_id = ?'
        );
        $stmt->execute([$pageId]);
        $next = (int)$stmt->fetch()['next'];

        $stmt = Database::pdo()->prepare(
            'INSERT INTO contacts (dial_page_id, name, phone, avatar, bg_color, font_size, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$pageId, $name, $phone, $avatar, $bgColor, $fontSize, $next]);

        $id = (int)Database::pdo()->lastInsertId();
        Response::json(self::findOne($id, $user['id']));
    }

    public static function update(int $id, array $body): void
    {
        $user = Auth::requireUser();
        $contact = self::findOne($id, $user['id']);
        if (!$contact) {
            Response::error('号码不存在', 404, 404);
        }

        $fields = [];
        $values = [];

        foreach (['name', 'phone', 'avatar', 'bg_color'] as $f) {
            if (array_key_exists($f, $body)) {
                $fields[] = "$f = ?";
                $values[] = (string)$body[$f];
            }
        }

        if (array_key_exists('font_size', $body)) {
            $v = $body['font_size'];
            if ($v === '' || $v === null) {
                $fields[] = 'font_size = NULL';
            } else {
                $fields[] = 'font_size = ?';
                $values[] = max(12, min(72, (int)$v));
            }
        }

        if ($fields) {
            $values[] = $id;
            $stmt = Database::pdo()->prepare(
                'UPDATE contacts SET ' . implode(', ', $fields) . ' WHERE id = ?'
            );
            $stmt->execute($values);
        }

        Response::json(self::findOne($id, $user['id']));
    }

    public static function destroy(int $id): void
    {
        $user = Auth::requireUser();
        $contact = self::findOne($id, $user['id']);
        if (!$contact) {
            Response::error('号码不存在', 404, 404);
        }

        $stmt = Database::pdo()->prepare('DELETE FROM contacts WHERE id = ?');
        $stmt->execute([$id]);

        Response::json(null);
    }

    public static function publicContact(string $slug, int $contactId): void
    {
        $stmt = Database::pdo()->prepare('SELECT id, name FROM dial_pages WHERE slug = ?');
        $stmt->execute([$slug]);
        $page = $stmt->fetch();

        if (!$page) {
            Response::error('拨号页不存在或链接已失效', 404, 404);
        }

        $stmt = Database::pdo()->prepare(
            'SELECT id, name, phone, avatar FROM contacts WHERE id = ? AND dial_page_id = ?'
        );
        $stmt->execute([$contactId, (int)$page['id']]);
        $contact = $stmt->fetch();

        if (!$contact) {
            Response::error('号码不存在', 404, 404);
        }

        $contact['id'] = (int)$contact['id'];

        Response::json([
            'contact' => $contact,
            'page' => ['name' => $page['name']],
        ]);
    }

    private static function findOne(int $id, int $userId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT c.* FROM contacts c
             JOIN dial_pages p ON p.id = c.dial_page_id
             WHERE c.id = ? AND p.user_id = ?'
        );
        $stmt->execute([$id, $userId]);
        $contact = $stmt->fetch();

        if (!$contact) {
            return null;
        }

        $contact['id'] = (int)$contact['id'];
        $contact['dial_page_id'] = (int)$contact['dial_page_id'];
        $contact['sort_order'] = (int)$contact['sort_order'];
        $contact['font_size'] = $contact['font_size'] === null ? null : (int)$contact['font_size'];
        return $contact;
    }

    private static function assertPageOwned(int $pageId, int $userId): void
    {
        $stmt = Database::pdo()->prepare('SELECT id FROM dial_pages WHERE id = ? AND user_id = ?');
        $stmt->execute([$pageId, $userId]);
        if (!$stmt->fetch()) {
            Response::error('拨号页不存在', 404, 404);
        }
    }

    private static function parseFontSize($value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }
        return max(12, min(72, (int)$value));
    }
}
