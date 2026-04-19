<?php
require_once __DIR__ . '/../classes/Database.php';

class Product
{
    public static function all(int $limit = 0, int $offset = 0): array
    {
        $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
                FROM products p
                JOIN categories c ON c.id = p.category_id
                ORDER BY p.created_at DESC';
        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        }
        return Database::fetchAll($sql);
    }

    public static function count(): int
    {
        $row = Database::fetchOne('SELECT COUNT(*) AS cnt FROM products');
        return (int)($row['cnt'] ?? 0);
    }

    public static function countByCategory(int $categoryId): int
    {
        $row = Database::fetchOne(
            'SELECT COUNT(*) AS cnt FROM products WHERE category_id = ?',
            [$categoryId]
        );
        return (int)($row['cnt'] ?? 0);
    }

    public static function findById(int $id): array|false
    {
        return Database::fetchOne(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.id = ?',
            [$id]
        );
    }

    public static function findBySlug(string $slug): array|false
    {
        return Database::fetchOne(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.slug = ?',
            [$slug]
        );
    }

    public static function featured(int $limit = 6): array
    {
        return Database::fetchAll(
            'SELECT p.*, c.name AS category_name
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.is_featured = 1
             ORDER BY p.created_at DESC
             LIMIT ?',
            [$limit]
        );
    }

    public static function byCategory(int $categoryId, int $limit = 0, int $offset = 0): array
    {
        $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
                FROM products p
                JOIN categories c ON c.id = p.category_id
                WHERE p.category_id = ?
                ORDER BY p.created_at DESC';
        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        }
        return Database::fetchAll($sql, [$categoryId]);
    }

    public static function search(string $query): array
    {
        $like = '%' . $query . '%';
        return Database::fetchAll(
            'SELECT p.*, c.name AS category_name
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.name LIKE ? OR p.description LIKE ?
             ORDER BY p.name ASC',
            [$like, $like]
        );
    }

    public static function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        Database::execute(
            'INSERT INTO products
                (category_id, name, slug, description, price, stock, image, is_featured, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['category_id'],
                $data['name'],
                $data['slug'],
                $data['description'] ?? null,
                $data['price'],
                $data['stock'] ?? 0,
                $data['image'] ?? null,
                $data['is_featured'] ?? 0,
                $now,
                $now,
            ]
        );
        return (int)Database::lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $now = date('Y-m-d H:i:s');
        Database::execute(
            'UPDATE products
             SET category_id = ?, name = ?, slug = ?, description = ?,
                 price = ?, stock = ?, image = ?, is_featured = ?, updated_at = ?
             WHERE id = ?',
            [
                $data['category_id'],
                $data['name'],
                $data['slug'],
                $data['description'] ?? null,
                $data['price'],
                $data['stock'] ?? 0,
                $data['image'] ?? null,
                $data['is_featured'] ?? 0,
                $now,
                $id,
            ]
        );
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM products WHERE id = ?', [$id]);
    }

    public static function related(int $categoryId, int $excludeId, int $limit = 4): array
    {
        return Database::fetchAll(
            'SELECT p.*, c.name AS category_name
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.category_id = ? AND p.id != ?
             ORDER BY RAND()
             LIMIT ?',
            [$categoryId, $excludeId, $limit]
        );
    }
}
