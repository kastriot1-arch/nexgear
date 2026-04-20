<?php
require_once __DIR__ . '/../classes/Database.php';

class Category
{
    public static function all(): array
    {
        return Database::fetchAll('SELECT * FROM categories ORDER BY name ASC');
    }

    public static function findById(int $id): array|false
    {
        return Database::fetchOne('SELECT * FROM categories WHERE id = ?', [$id]);
    }

    public static function findBySlug(string $slug): array|false
    {
        return Database::fetchOne('SELECT * FROM categories WHERE slug = ?', [$slug]);
    }

    public static function allWithProductCount(): array
    {
        return Database::fetchAll(
            'SELECT c.*, COUNT(p.id) AS product_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id
             GROUP BY c.id
             ORDER BY c.name ASC'
        );
    }
}
