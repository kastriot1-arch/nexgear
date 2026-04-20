<?php
require_once __DIR__ . '/../classes/Database.php';

class PageContent
{
    public static function findByPageSection(string $page, string $section): array|false
    {
        return Database::fetchOne(
            'SELECT * FROM page_content WHERE page = ? AND section = ?',
            [$page, $section]
        );
    }

    public static function findByPage(string $page): array
    {
        return Database::fetchAll(
            'SELECT * FROM page_content WHERE page = ? ORDER BY `order` ASC',
            [$page]
        );
    }

    /**
     * Returns a keyed map: section => row
     */
    public static function mapByPage(string $page): array
    {
        $rows = self::findByPage($page);
        $map  = [];
        foreach ($rows as $row) {
            $map[$row['section']] = $row;
        }
        return $map;
    }
}
