USE bookstore_dev;

CREATE TABLE IF NOT EXISTS async_events (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    event_name VARCHAR(80) NOT NULL,
    route VARCHAR(120) NOT NULL DEFAULT 'home',
    cart_lines INT UNSIGNED NOT NULL DEFAULT 0,
    cart_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    book_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_async_events_created_at (created_at),
    INDEX idx_async_events_event_name (event_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
