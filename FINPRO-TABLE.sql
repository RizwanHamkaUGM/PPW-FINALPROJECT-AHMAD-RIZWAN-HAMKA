create database IF NOT EXISTS FINPRO;
USE FINPRO;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS accessories;
DROP TABLE IF EXISTS clothing;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS product_categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    phone_number VARCHAR(15) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_verified BOOLEAN DEFAULT FALSE
);

-- Tabel kategori umum
CREATE TABLE product_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(15) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Tabel induk: semua produk
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2),
    image_url TEXT,
    stock INT,
    category_id INT,
    type ENUM('clothing', 'accessory') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE SET NULL
);


-- Tabel detail clothing (jika perlu menambah atribut khusus clothing seperti ukuran, bahan, dll.)
CREATE TABLE clothing (
    product_id INT PRIMARY KEY,
    size ENUM('XS', 'S', 'M', 'L', 'XL') NOT NULL,
    material VARCHAR(100),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);


-- Tabel detail accessories (jika perlu menambah atribut khusus accessories seperti tipe bahan, ukuran khusus, dll.)
CREATE TABLE accessories (
    product_id INT PRIMARY KEY,
    type_detail VARCHAR(100),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);


-- Tabel pesanan
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_price DECIMAL(10,2),
    status ENUM('pending', 'paid', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel item dalam pesanan
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    item_price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

CREATE TABLE cart_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Tabel pembayaran
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'confirmed', 'failed') DEFAULT 'pending',
    proof_image_url TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

INSERT INTO product_categories (name) VALUES
('Hoodie'),('Shirt'),('Pants'),('Shoes'),('Decks'),
('Hats'),('Socks'),('Jewelry');


# ============================================= DTA DUMMY CLAUDE ===========================================================================================================================
-- Data Dummy untuk Database E-commerce
select * from users;
-- 1. Insert data users
INSERT INTO users (name, email, password, role, phone_number, is_verified) VALUES
('Ahmad Rizki', 'ahmad.rizki@email.com', '123', 'admin', '081234567890', TRUE),
('Siti Nurhaliza', 'siti.nurhaliza@email.com', '123', 'user', '082345678901', TRUE),
('Budi Santoso', 'budi.santoso@email.com', '123', 'user', '083456789012', FALSE),
('Maya Dewi', 'maya.dewi@email.com', '123', 'user', '084567890123', TRUE),
('Andi Pratama', 'andi.pratama@email.com', '123', 'user', '085678901234', TRUE),
('Lisa Permata', 'lisa.permata@email.com', '123', 'user', '086789012345', FALSE),
('Reza Fadilah', 'reza.fadilah@email.com', '123', 'user', '087890123456', TRUE),
('Nina Sari', 'nina.sari@email.com', '123', 'user', '088901234567', TRUE);

-- 2. Insert data product_categories (sesuai permintaan)
INSERT INTO product_categories (name) VALUES
('Hoodie'),
('Shirt'),
('Pants'),
('Shoes'),
('Decks'),
('Hats'),
('Socks'),
('Jewelry');

-- 3. Insert data products
INSERT INTO products (name, price, image_url, stock, category_id, type) VALUES
-- Hoodie (category_id = 1)
('Supreme Box Logo Hoodie Black', 850000.00, 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=500&h=500&fit=crop', 15, 1, 'clothing'),
('Nike Tech Fleece Hoodie', 650000.00, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=500&h=500&fit=crop', 25, 1, 'clothing'),
('Adidas Originals Trefoil Hoodie', 550000.00, 'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=500&h=500&fit=crop', 20, 1, 'clothing'),

-- Shirt (category_id = 2)
('Uniqlo Basic T-Shirt White', 150000.00, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=500&h=500&fit=crop', 50, 2, 'clothing'),
('Champion Script Logo Tee', 350000.00, 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=500&h=500&fit=crop', 30, 2, 'clothing'),
('Tommy Hilfiger Polo Shirt', 450000.00, 'https://images.unsplash.com/photo-1586790170083-2f9ceadc732d?w=500&h=500&fit=crop', 18, 2, 'clothing'),

-- Pants (category_id = 3)
('Levi\'s 501 Original Jeans', 750000.00, 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=500&h=500&fit=crop', 22, 3, 'clothing'),
('Nike Dri-FIT Joggers', 480000.00, 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=500&h=500&fit=crop', 35, 3, 'clothing'),
('Dickies Work Pants Khaki', 320000.00, 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=500&h=500&fit=crop', 28, 3, 'clothing'),

-- Shoes (category_id = 4)
('Nike Air Jordan 1 High', 2500000.00, 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=500&h=500&fit=crop', 12, 4, 'accessory'),
('Adidas Ultraboost 22', 2200000.00, 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=500&h=500&fit=crop', 18, 4, 'accessory'),
('Converse Chuck Taylor All Star', 850000.00, 'https://images.unsplash.com/photo-1520256862855-398228c41684?w=500&h=500&fit=crop', 40, 4, 'accessory'),

-- Decks (category_id = 5)
('Santa Cruz Classic Dot Skateboard', 1200000.00, 'https://images.unsplash.com/photo-1547036967-23d11aacaee0?w=500&h=500&fit=crop', 8, 5, 'accessory'),
('Powell Peralta Dragon Deck', 950000.00, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=500&h=500&fit=crop', 10, 5, 'accessory'),
('Element Nature Series Deck', 800000.00, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=500&h=500&fit=crop', 15, 5, 'accessory'),

-- Hats (category_id = 6)
('New Era 59FIFTY Yankees Cap', 450000.00, 'https://images.unsplash.com/photo-1575428652377-a2d80e2277fc?w=500&h=500&fit=crop', 25, 6, 'accessory'),
('Nike Dri-FIT Running Hat', 280000.00, 'https://images.unsplash.com/photo-1594736797933-d0a9ba4dba30?w=500&h=500&fit=crop', 30, 6, 'accessory'),
('Carhartt WIP Acrylic Watch Hat', 350000.00, 'https://images.unsplash.com/photo-1566150905458-1bf1fc113f0d?w=500&h=500&fit=crop', 20, 6, 'accessory'),

-- Socks (category_id = 7)
('Nike Elite Basketball Socks', 180000.00, 'https://images.unsplash.com/photo-1586350781685-fa27a09784b4?w=500&h=500&fit=crop', 60, 7, 'accessory'),
('Adidas Originals Crew Socks 3-Pack', 150000.00, 'https://images.unsplash.com/photo-1544966503-7cc5ac882d5f?w=500&h=500&fit=crop', 45, 7, 'accessory'),
('Stance Icon No Show Socks', 220000.00, 'https://images.unsplash.com/photo-1544966503-7cc5ac882d5f?w=500&h=500&fit=crop', 38, 7, 'accessory'),

-- Jewelry (category_id = 8)
('Pandora Silver Chain Bracelet', 850000.00, 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=500&h=500&fit=crop', 15, 8, 'accessory'),
('Tiffany & Co. Return to Heart Necklace', 2500000.00, 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=500&h=500&fit=crop', 8, 8, 'accessory'),
('Casio G-Shock Digital Watch', 1200000.00, 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?w=500&h=500&fit=crop', 12, 8, 'accessory');

-- 4. Insert data clothing (detail untuk produk clothing)
INSERT INTO clothing (product_id, size, material) VALUES
(1, 'L', '100% Cotton Fleece'),
(2, 'M', 'Tech Fleece Polyester'),
(3, 'XL', 'Cotton Blend'),
(4, 'S', '100% Cotton'),
(5, 'M', 'Cotton Jersey'),
(6, 'L', 'Cotton Pique'),
(7, 'L', 'Denim Cotton'),
(8, 'M', 'Polyester Blend'),
(9, 'XL', 'Cotton Twill');

-- 5. Insert data accessories (detail untuk produk accessory)
INSERT INTO accessories (product_id, type_detail) VALUES
(10, 'High-top Basketball Sneakers'),
(11, 'Running Shoes with Boost Technology'),
(12, 'Classic Canvas Low-top Sneakers'),
(13, 'Complete Skateboard with Trucks and Wheels'),
(14, 'Professional Skateboard Deck 8.0"'),
(15, 'Eco-friendly Bamboo Skateboard Deck'),
(16, 'Fitted Baseball Cap with Embroidered Logo'),
(17, 'Moisture-wicking Sports Cap'),
(18, 'Warm Winter Beanie'),
(19, 'Performance Athletic Socks'),
(20, 'Casual Crew Socks Set'),
(21, 'No-show Invisible Socks'),
(22, 'Sterling Silver Charm Bracelet'),
(23, 'Sterling Silver Heart Pendant'),
(24, 'Digital Sports Watch with Multiple Features');

-- 6. Insert data orders
INSERT INTO orders (user_id, total_price, status) VALUES
(2, 1500000.00, 'completed'),
(3, 850000.00, 'shipped'),
(4, 2850000.00, 'paid'),
(5, 630000.00, 'pending'),
(6, 1750000.00, 'completed'),
(7, 480000.00, 'cancelled'),
(8, 2200000.00, 'shipped'),
(2, 1050000.00, 'paid'),
(3, 800000.00, 'completed'),
(4, 450000.00, 'pending');

-- 7. Insert data order_items
INSERT INTO order_items (order_id, product_id, quantity, item_price) VALUES
-- Order 1 (user_id = 2)
(1, 1, 1, 850000.00),
(1, 4, 2, 150000.00),
(1, 19, 2, 180000.00),

-- Order 2 (user_id = 3)
(2, 1, 1, 850000.00),

-- Order 3 (user_id = 4)
(3, 10, 1, 2500000.00),
(3, 4, 1, 150000.00),
(3, 20, 1, 150000.00),

-- Order 4 (user_id = 5)
(4, 8, 1, 480000.00),
(4, 4, 1, 150000.00),

-- Order 5 (user_id = 6)
(5, 7, 1, 750000.00),
(5, 2, 1, 650000.00),
(5, 5, 1, 350000.00),

-- Order 6 (user_id = 7)
(6, 8, 1, 480000.00),

-- Order 7 (user_id = 8)
(7, 11, 1, 2200000.00),

-- Order 8 (user_id = 2)
(8, 6, 1, 450000.00),
(8, 16, 1, 450000.00),
(8, 4, 1, 150000.00),

-- Order 9 (user_id = 3)
(9, 15, 1, 800000.00),

-- Order 10 (user_id = 4)
(10, 16, 1, 450000.00);

-- 8. Insert data payments
INSERT INTO payments (order_id, payment_method, payment_status, proof_image_url) VALUES
(1, 'Bank Transfer', 'confirmed', 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=500&h=300&fit=crop'),
(2, 'E-wallet (GoPay)', 'confirmed', 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=500&h=300&fit=crop'),
(3, 'Credit Card', 'confirmed', 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=500&h=300&fit=crop'),
(4, 'Bank Transfer', 'pending', NULL),
(5, 'E-wallet (OVO)', 'confirmed', 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=500&h=300&fit=crop'),
(6, 'Credit Card', 'failed', 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=500&h=300&fit=crop'),
(7, 'Bank Transfer', 'confirmed', 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=500&h=300&fit=crop'),
(8, 'E-wallet (DANA)', 'confirmed', 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=500&h=300&fit=crop'),
(9, 'Cash on Delivery', 'confirmed', NULL),
(10, 'Bank Transfer', 'pending', NULL);

