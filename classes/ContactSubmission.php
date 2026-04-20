<?php
require_once __DIR__ . '/../classes/Database.php';

class ContactSubmission
{
    public static function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        Database::execute(
            'INSERT INTO contact_submissions (name, email, subject, message, is_read, created_at, updated_at)
             VALUES (?, ?, ?, ?, 0, ?, ?)',
            [
                trim($data['name']),
                strtolower(trim($data['email'])),
                trim($data['subject'] ?? ''),
                trim($data['message']),
                $now,
                $now,
            ]
        );
        return (int)Database::lastInsertId();
    }

    public static function all(): array
    {
        return Database::fetchAll(
            'SELECT * FROM contact_submissions ORDER BY created_at DESC'
        );
    }

    public static function count(): int
    {
        $row = Database::fetchOne('SELECT COUNT(*) AS cnt FROM contact_submissions');
        return (int)($row['cnt'] ?? 0);
    }

    public static function countUnread(): int
    {
        $row = Database::fetchOne('SELECT COUNT(*) AS cnt FROM contact_submissions WHERE is_read = 0');
        return (int)($row['cnt'] ?? 0);
    }

    public static function findById(int $id): array|false
    {
        return Database::fetchOne('SELECT * FROM contact_submissions WHERE id = ?', [$id]);
    }

    public static function markAsRead(int $id): void
    {
        Database::execute(
            'UPDATE contact_submissions SET is_read = 1, updated_at = ? WHERE id = ?',
            [date('Y-m-d H:i:s'), $id]
        );
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM contact_submissions WHERE id = ?', [$id]);
    }
}
