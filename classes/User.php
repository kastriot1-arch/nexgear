<?php
require_once __DIR__ . '/../classes/Database.php';

class User
{
    public static function findById(int $id): array|false
    {
        return Database::fetchOne('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function findByEmail(string $email): array|false
    {
        return Database::fetchOne('SELECT * FROM users WHERE email = ?', [strtolower(trim($email))]);
    }

    public static function all(): array
    {
        return Database::fetchAll('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
    }

    public static function count(): int
    {
        $row = Database::fetchOne('SELECT COUNT(*) AS cnt FROM users');
        return (int)($row['cnt'] ?? 0);
    }

   /* Provon nje login dhe kthen nje user array nese ka sukses dhe nje "false" nese deshton */
    public static function login(string $email, string $password): array|false
    {
        $user = self::findByEmail($email);
        if (!$user) return false;
        if (!password_verify($password, $user['password'])) return false;
        return $user;
    }

   /*Regjistron User dhe kthen nje ID nese ka sukses*/
    public static function register(string $name, string $email, string $password, string $role = 'user'): int|false
    {
        if (self::findByEmail($email)) return false;

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $now  = date('Y-m-d H:i:s');

        Database::execute(
            'INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)',
            [trim($name), strtolower(trim($email)), $hash, $role, $now, $now]
        );

        return (int)Database::lastInsertId();
    }

   
    public static function startSession(array $user): void
    {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_at']  = time();
    }

    /**
     * Destroy the current user session.
     */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
