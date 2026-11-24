<?php
/**
 * Asynchronous interaction endpoint for Checkpoint 02.
 * Stores lightweight UI-event telemetry; it does NOT save orders or catalog data.
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');

function async_json_response(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    async_json_response(405, ['ok' => false, 'message' => 'Method not allowed.']);
}

$contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
if ($contentLength > 8192) {
    async_json_response(413, ['ok' => false, 'message' => 'Request is too large.']);
}

$csrf = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
$sessionCsrf = (string)($_SESSION['mocbooks_csrf'] ?? '');
if ($csrf === '' || $sessionCsrf === '' || !hash_equals($sessionCsrf, $csrf)) {
    async_json_response(419, ['ok' => false, 'message' => 'Session token is missing or expired. Refresh the page and try again.']);
}

// Basic per-session rate limit to prevent excessive event writes.
$now = time();
$recent = $_SESSION['mocbooks_async_hits'] ?? [];
if (!is_array($recent)) {
    $recent = [];
}
$recent = array_values(array_filter($recent, static fn($stamp) => is_int($stamp) && $stamp > ($now - 60)));
if (count($recent) >= 60) {
    async_json_response(429, ['ok' => false, 'message' => 'Too many events. Please try again shortly.']);
}
$recent[] = $now;
$_SESSION['mocbooks_async_hits'] = $recent;

$raw = file_get_contents('php://input');
if (is_string($raw) && strlen($raw) > 8192) {
    async_json_response(413, ['ok' => false, 'message' => 'Request is too large.']);
}
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    async_json_response(400, ['ok' => false, 'message' => 'Invalid JSON payload.']);
}

$eventName = trim((string)($payload['event'] ?? ''));
$allowedEvents = [
    'boot', 'route-change', 'action-click', 'sort-change', 'checkout-submit',
    'book-form-submit', 'category-form-submit', 'manual-sync', 'online-retry'
];
if (!in_array($eventName, $allowedEvents, true)) {
    async_json_response(422, ['ok' => false, 'message' => 'Unsupported event name.']);
}

$route = trim((string)($payload['route'] ?? 'home'));
if (!preg_match('/^(home|catalog|cart|checkout|success|admin|detail\/[0-9]{1,10})$/', $route)) {
    $route = 'other';
}

$metrics = $payload['metrics'] ?? [];
if (!is_array($metrics)) {
    $metrics = [];
}
$toCount = static function ($value): int {
    if (!is_numeric($value)) {
        return 0;
    }
    return max(0, min(100000, (int)$value));
};
$cartLines = $toCount($metrics['cart_lines'] ?? 0);
$cartQuantity = $toCount($metrics['cart_quantity'] ?? 0);
$bookCount = $toCount($metrics['book_count'] ?? 0);

try {
    $pdo = mocbooks_pdo();
    $stmt = $pdo->prepare(
        'INSERT INTO async_events (event_name, route, cart_lines, cart_quantity, book_count) VALUES (:event_name, :route, :cart_lines, :cart_quantity, :book_count)'
    );
    $stmt->execute([
        ':event_name' => $eventName,
        ':route' => $route,
        ':cart_lines' => $cartLines,
        ':cart_quantity' => $cartQuantity,
        ':book_count' => $bookCount,
    ]);

    async_json_response(200, [
        'ok' => true,
        'stored' => true,
        'event_id' => (int)$pdo->lastInsertId(),
        'server_time' => date(DATE_ATOM),
    ]);
} catch (Throwable $e) {
    error_log('[MocBooks async_api] ' . $e->getMessage());
    async_json_response(503, [
        'ok' => false,
        'message' => 'The asynchronous event could not be stored. Check the database connection and async_events table.',
    ]);
}
