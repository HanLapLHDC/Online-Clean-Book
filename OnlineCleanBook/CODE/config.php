<?php
/**
 * Shared Mộc Books configuration.
 * Local defaults are intended for a development machine only.
 * Override with environment variables on a deployed environment.
 */
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function mocbooks_db_config(): array
{
    return [
        'host' => getenv('MOCBOOKS_DB_HOST') ?: '127.0.0.1',
        'port' => getenv('MOCBOOKS_DB_PORT') ?: '3306',
        'name' => getenv('MOCBOOKS_DB_NAME') ?: 'bookstore_dev',
        'user' => getenv('MOCBOOKS_DB_USER') ?: 'root',
        'pass' => getenv('MOCBOOKS_DB_PASS') !== false ? (string)getenv('MOCBOOKS_DB_PASS') : '',
    ];
}

function mocbooks_pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = mocbooks_db_config();
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $cfg['host'],
        $cfg['port'],
        $cfg['name']
    );

    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function mocbooks_csrf_token(): string
{
    if (empty($_SESSION['mocbooks_csrf'])) {
        $_SESSION['mocbooks_csrf'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['mocbooks_csrf'];
}
