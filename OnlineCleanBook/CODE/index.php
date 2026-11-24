<?php
require_once __DIR__ . '/config.php';

$storeData = null;
$connectionError = '';
try {
    $pdo = mocbooks_pdo();

    $categories = $pdo->query('SELECT id, name, slug, description FROM categories ORDER BY id')->fetchAll();
    $books = $pdo->query('SELECT id, category_id, title, author, price, member_price, stock_quantity, image_url, description FROM books ORDER BY id')->fetchAll();
    $users = $pdo->query('SELECT id, name, email, role, is_member FROM users ORDER BY id')->fetchAll();
    $orders = $pdo->query('SELECT id, user_id, guest_name, guest_email, guest_phone, guest_address, total_amount, status, created_at FROM orders ORDER BY id')->fetchAll();
    $orderItems = $pdo->query('SELECT id, order_id, book_id, quantity, unit_price FROM order_items ORDER BY id')->fetchAll();

    foreach ($books as &$book) {
        foreach (['id', 'category_id', 'stock_quantity'] as $field) $book[$field] = (int)$book[$field];
        foreach (['price', 'member_price'] as $field) $book[$field] = (float)$book[$field];
    }
    unset($book);
    foreach ($categories as &$category) $category['id'] = (int)$category['id'];
    unset($category);
    foreach ($users as &$user) {
        $user['id'] = (int)$user['id'];
        $user['is_member'] = (bool)$user['is_member'];
    }
    unset($user);
    foreach ($orders as &$order) {
        $order['id'] = (int)$order['id'];
        $order['user_id'] = $order['user_id'] === null ? null : (int)$order['user_id'];
        $order['total_amount'] = (float)$order['total_amount'];
    }
    unset($order);
    foreach ($orderItems as &$item) {
        foreach (['id', 'order_id', 'book_id', 'quantity'] as $field) $item[$field] = (int)$item[$field];
        $item['unit_price'] = (float)$item['unit_price'];
    }
    unset($item);

    $storeData = [
        'categories' => $categories,
        'books' => $books,
        'users' => $users,
        'orders' => $orders,
        'order_items' => $orderItems
    ];
} catch (Throwable $e) {
    // Do not expose SQL details or local database credentials in the browser.
    error_log('[MocBooks index] ' . $e->getMessage());
    $connectionError = 'Chưa kết nối được cơ sở dữ liệu. Hãy kiểm tra MySQL, cấu hình trong config.php và hai file schema.sql, seed.sql.';
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#174c3d">
  <meta name="description" content="Mộc Books - Cửa hàng sách trực tuyến. Khám phá sách hay và ưu đãi dành cho thành viên.">
  <title>Mộc Books | Cửa hàng sách trực tuyến</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div id="app">
    <?php if ($connectionError): ?>
      <main class="container" style="padding:80px 0;max-width:760px">
        <h1>Chào mừng đến với Mộc Books</h1>
        <p><?= htmlspecialchars($connectionError, ENT_QUOTES, 'UTF-8') ?></p>
        <p>Import <strong>schema.sql</strong> trước, sau đó import <strong>seed.sql</strong> trong phpMyAdmin.</p>
      </main>
    <?php else: ?>
      <div class="loading-screen"><span class="loading-mark">m.</span><p>Đang mở tủ sách dành cho bạn...</p></div>
    <?php endif; ?>
  </div>
  <div id="toast" class="toast" role="status" aria-live="polite"></div>
  <script>
    window.__MOCBOOKS_FALLBACK__ = <?= $storeData === null ? 'null' : json_encode($storeData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    window.__MOCBOOKS_DB_ERROR__ = <?= json_encode($connectionError, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    window.MOCBOOKS_ASYNC_CONFIG = {
      endpoint: 'async_api.php',
      csrfToken: <?= json_encode(mocbooks_csrf_token(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
    };
  </script>
  <script src="app.js" defer></script>
  <script src="async_bridge.js" defer></script>
</body>
</html>
