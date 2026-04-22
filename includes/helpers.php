<?php

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}


function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}


function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}


function getFlash(): array|null
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}


function renderFlash(): void
{
    $flash = getFlash();
    if ($flash) {
        $type = match ($flash['type']) {
            'success' => 'flash-success',
            'error' => 'flash-error',
            'warning' => 'flash-warning',
            default => 'flash-info',
        };
        echo '<div class="flash-message ' . $type . '">' . e($flash['message']) . '</div>';
    }
}


function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}


function formatPrice(float $price): string
{
    return '$' . number_format($price, 2);
}


function formatDate(string $datetime, string $format = 'M j, Y'): string
{
    return date($format, strtotime($datetime));
}


function truncate(string $text, int $length = 150): string
{
    $text = strip_tags($text);
    if (strlen($text) <= $length)
        return $text;
    return rtrim(substr($text, 0, $length)) . '…';
}


function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}


function isAdmin(): bool
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}


function currentUserName(): string
{
    return $_SESSION['user_name'] ?? 'Guest';
}


function requireLogin(string $redirect = '/nexgear/login.php'): void
{
    if (!isLoggedIn()) {
        flash('warning', 'Please log in to continue.');
        redirect($redirect);
    }
}


function requireAdmin(string $redirect = '/nexgear/login.php'): void
{
    if (!isAdmin()) {
        flash('error', 'Access denied. Admins only.');
        redirect($redirect);
    }
}


function productImage(string|null $image): string
{
    if ($image && file_exists(__DIR__ . '/../assets/images/' . $image)) {
        return APP_URL . '/assets/images/' . $image;
    }
    return APP_URL . '/assets/images/placeholder-product.svg';
}


function postImage(string|null $image): string
{
    if ($image && file_exists(__DIR__ . '/../assets/images/' . $image)) {
        return APP_URL . '/assets/images/' . $image;
    }
    return APP_URL . '/assets/images/placeholder-post.svg';
}

/* CSRF token generator / validator.*/
/*Krijuesi/ gjeneratori i tokenave CSRF dhe validatori i tyre*/
/* Bllok i kodit i kryer nga AI*/
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrf(): void
{
    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])
    ) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}


function navActive(string $page): string
{
    $current = basename($_SERVER['PHP_SELF']);
    return $current === $page ? 'active' : '';
}
