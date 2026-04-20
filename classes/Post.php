<?php
require_once __DIR__ . '/../classes/Database.php';

class Post
{
    public static function allPublished(int $limit = 0, int $offset = 0): array
    {
        $sql = 'SELECT p.*, u.name AS author_name
                FROM posts p
                JOIN users u ON u.id = p.user_id
                WHERE p.is_published = 1
                ORDER BY p.published_at DESC';
        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        }
        return Database::fetchAll($sql);
    }

    public static function all(): array
    {
        return Database::fetchAll(
            'SELECT p.*, u.name AS author_name
             FROM posts p
             JOIN users u ON u.id = p.user_id
             ORDER BY p.created_at DESC'
        );
    }

    public static function count(): int
    {
        $row = Database::fetchOne('SELECT COUNT(*) AS cnt FROM posts');
        return (int)($row['cnt'] ?? 0);
    }

    public static function countPublished(): int
    {
        $row = Database::fetchOne('SELECT COUNT(*) AS cnt FROM posts WHERE is_published = 1');
        return (int)($row['cnt'] ?? 0);
    }

    public static function findById(int $id): array|false
    {
        return Database::fetchOne(
            'SELECT p.*, u.name AS author_name
             FROM posts p
             JOIN users u ON u.id = p.user_id
             WHERE p.id = ?',
            [$id]
        );
    }

    public static function findBySlug(string $slug): array|false
    {
        return Database::fetchOne(
            'SELECT p.*, u.name AS author_name
             FROM posts p
             JOIN users u ON u.id = p.user_id
             WHERE p.slug = ? AND p.is_published = 1',
            [$slug]
        );
    }

    public static function recent(int $limit = 3): array
    {
        return Database::fetchAll(
            'SELECT p.*, u.name AS author_name
             FROM posts p
             JOIN users u ON u.id = p.user_id
             WHERE p.is_published = 1
             ORDER BY p.published_at DESC
             LIMIT ?',
            [$limit]
        );
    }

    public static function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $publishedAt = $data['is_published'] ? $now : null;

        Database::execute(
            'INSERT INTO posts
                (user_id, title, slug, excerpt, body, image, is_published, published_at, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['user_id'],
                $data['title'],
                $data['slug'],
                $data['excerpt'] ?? null,
                $data['body'],
                $data['image'] ?? null,
                $data['is_published'] ?? 0,
                $publishedAt,
                $now,
                $now,
            ]
        );
        return (int)Database::lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $now = date('Y-m-d H:i:s');
        $existing = self::findById($id);
        $publishedAt = $existing['published_at'];
        if ($data['is_published'] && !$publishedAt) {
            $publishedAt = $now;
        }

        Database::execute(
            'UPDATE posts
             SET title = ?, slug = ?, excerpt = ?, body = ?, image = ?,
                 is_published = ?, published_at = ?, updated_at = ?
             WHERE id = ?',
            [
                $data['title'],
                $data['slug'],
                $data['excerpt'] ?? null,
                $data['body'],
                $data['image'] ?? null,
                $data['is_published'] ?? 0,
                $publishedAt,
                $now,
                $id,
            ]
        );
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM posts WHERE id = ?', [$id]);
    }
}
