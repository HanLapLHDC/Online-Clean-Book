USE bookstore_dev;

INSERT INTO categories (name, slug, description) VALUES
('Văn học', 'van-hoc', 'Tiểu thuyết, truyện ngắn và tác phẩm văn học'),
('Kinh tế', 'kinh-te', 'Kinh doanh, tài chính cá nhân và quản trị'),
('Công nghệ', 'cong-nghe', 'Lập trình, cơ sở dữ liệu và khoa học máy tính');

INSERT INTO books (category_id, title, author, price, member_price, stock_quantity, image_url, description) VALUES
(1, 'Nhà Giả Kim', 'Paulo Coelho', 85000.00, 72000.00, 25, '/images/nha-gia-kim.jpg', 'Hành trình theo đuổi ước mơ'),
(1, 'Dế Mèn Phiêu Lưu Ký', 'To Hoai', 65000.00, 55000.00, 30, '/images/de-men.jpg', 'Tác phẩm văn học thiếu nhi kinh điển'),
(2, 'Tư Duy Nhanh Và Chậm', 'Daniel Kahneman', 180000.00, 150000.00, 15, '/images/tu-duy-nhanh-cham.jpg', 'Hai hệ thống tư duy của con người'),
(2, 'Cha Giàu Cha Nghèo', 'Robert T. Kiyosaki', 120000.00, 100000.00, 20, '/images/cha-giau-cha-ngheo.jpg', 'Kiến thức tài chính cơ bản'),
(3, 'Nhập Môn Cơ Sở Dữ Liệu', 'Nguyen Van A', 140000.00, 119000.00, 18, '/images/csdl.jpg', 'Thiết kế bảng, SQL và tối ưu truy vấn'),
(3, 'Clean Code', 'Robert C. Martin', 220000.00, 185000.00, 12, '/images/clean-code.jpg', 'Kỹ thuật viết mã sạch, dễ bảo trì');

INSERT INTO users (name, email, password_hash, role, is_member) VALUES
('Quản trị cửa hàng', 'admin@bookstore.local', '$2y$12$l/KNF4vhwGxRSFRBr2gKxuyIwL44Jvt9dh53qKv4uG6fCiXCMJ3y.', 'admin', FALSE),
('Nguyễn Thành Viên', 'member@bookstore.local', '$2y$12$O/At543ySI/xCFyqFZYzzefWHtxt4wW6dXXKIlQxWqOKGMG.xLW7.', 'customer', TRUE),
('Trần Khách Thường', 'user@bookstore.local', '$2y$12$Uwhi54J0HldtREsX7TjtaOaprhdUesmiI9zdoUQ4MV0nNfmyGjSYq', 'customer', FALSE);

INSERT INTO orders (user_id, guest_name, guest_email, guest_phone, guest_address, total_amount, status) VALUES
(NULL, 'Lê Khách Vãng Lai', 'guest@example.com', '0901234567', '123 Nguyen Trai, Quan 1, TP.HCM', 85000.00, 'pending'),
(2, NULL, NULL, NULL, NULL, 150000.00, 'completed');

INSERT INTO order_items (order_id, book_id, quantity, unit_price) VALUES
(1, 1, 1, 85000.00),
(2, 3, 1, 150000.00);
