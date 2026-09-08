-- Sweet Mart Database
-- Run this in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS sweetmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sweetmart;

-- ===========================
-- USERS
-- ===========================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('customer','admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===========================
-- CATEGORIES
-- ===========================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT '🍰'
);

-- ===========================
-- PRODUCTS
-- ===========================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(200) DEFAULT 'placeholder.jpg',
    flavours TEXT,
    sizes TEXT,
    rating DECIMAL(3,1) DEFAULT 4.5,
    reviews_count INT DEFAULT 0,
    stock INT DEFAULT 50,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- ===========================
-- CART
-- ===========================
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    session_id VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ===========================
-- CART ITEMS
-- ===========================
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    custom_box_id INT DEFAULT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- ===========================
-- ORDERS
-- ===========================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    postal_code VARCHAR(10),
    delivery_date DATE,
    payment_method ENUM('cod','card') DEFAULT 'cod',
    subtotal DECIMAL(10,2) NOT NULL,
    delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 350.00,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('Pending','Confirmed','Preparing','Out for Delivery','Delivered') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ===========================
-- ORDER ITEMS
-- ===========================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    custom_box_id INT DEFAULT NULL,
    product_name VARCHAR(150) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- ===========================
-- CUSTOM BOXES
-- ===========================
CREATE TABLE IF NOT EXISTS custom_boxes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    box_size ENUM('Small','Medium','Large') NOT NULL,
    occasion VARCHAR(50),
    message TEXT,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ===========================
-- CUSTOM BOX ITEMS
-- ===========================
CREATE TABLE IF NOT EXISTS custom_box_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    custom_box_id INT NOT NULL,
    dessert_type VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (custom_box_id) REFERENCES custom_boxes(id) ON DELETE CASCADE
);

-- ===========================
-- PAYMENTS
-- ===========================
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    method ENUM('cod','card') NOT NULL,
    status ENUM('Pending','Paid') DEFAULT 'Pending',
    paid_at TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- ===========================
-- CONTACT MESSAGES
-- ===========================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(150),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===========================
-- SEED: ADMIN USER
-- Password: Admin@1234
-- ===========================
INSERT INTO users (full_name, email, phone, password, role) VALUES
('Sweet Mart Admin', 'admin@sweetmart.lk', '+94 77 123 4567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Default customer (password: Customer@1)
INSERT INTO users (full_name, email, phone, password, role) VALUES
('Nimal Perera', 'nimal@example.com', '+94 71 234 5678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Sanduni Silva', 'sanduni@example.com', '+94 76 345 6789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- ===========================
-- SEED: CATEGORIES
-- ===========================
INSERT INTO categories (name, slug, icon) VALUES
('Cakes', 'cakes', '🎂'),
('Cupcakes', 'cupcakes', '🧁'),
('Brownies', 'brownies', '🍫'),
('Cookies', 'cookies', '🍪'),
('Donuts', 'donuts', '🍩'),
('Gift Boxes', 'gift-boxes', '🎁');

-- ===========================
-- SEED: PRODUCTS
-- ===========================
-- CAKES (category_id = 1)
INSERT INTO products (category_id, name, description, price, image, flavours, sizes, rating, reviews_count, stock, is_featured) VALUES
(1, 'Chocolate Fudge Cake', 'A rich, decadent chocolate fudge cake layered with silky chocolate ganache and topped with glossy chocolate drip. Perfect for any celebration.', 3200.00, 'cake_choc_fudge.jpg', '["Chocolate","Dark Chocolate","Milk Chocolate"]', '["1 kg","2 kg","3 kg"]', 4.9, 128, 20, 1),
(1, 'Red Velvet Cake', 'Classic red velvet with velvety crimson layers and smooth cream cheese frosting. A showstopper for birthdays and anniversaries.', 2900.00, 'cake_red_velvet.jpg', '["Original Red Velvet","Red Velvet with Oreo"]', '["1 kg","2 kg","3 kg"]', 4.8, 96, 20, 1),
(1, 'Vanilla Celebration Cake', 'Light, fluffy vanilla sponge layered with whipped vanilla cream and fresh seasonal fruits. A crowd favourite.', 2600.00, 'cake_vanilla.jpg', '["Vanilla","Vanilla Berry"]', '["1 kg","2 kg","3 kg"]', 4.7, 74, 20, 0),
(1, 'Black Forest Cake', 'Traditional Black Forest with chocolate sponge, whipped cream, and cherries. A timeless classic.', 3500.00, 'cake_black_forest.jpg', '["Classic","Extra Cherry"]', '["1 kg","2 kg","3 kg"]', 4.8, 82, 20, 0);

-- CUPCAKES (category_id = 2)
INSERT INTO products (category_id, name, description, price, image, flavours, sizes, rating, reviews_count, stock, is_featured) VALUES
(2, 'Chocolate Cupcake', 'Moist chocolate cupcake topped with rich chocolate buttercream swirls and a chocolate drizzle. A chocolate lover\'s dream.', 280.00, 'cupcake_choc.jpg', '["Dark Chocolate","Milk Chocolate"]', '["Standard"]', 4.7, 210, 100, 1),
(2, 'Red Velvet Cupcake', 'Beautiful red velvet cupcake with luscious cream cheese frosting and a dusting of red velvet crumbs.', 300.00, 'cupcake_red_velvet.jpg', '["Classic","With Oreo"]', '["Standard"]', 4.8, 185, 100, 1),
(2, 'Vanilla Cupcake', 'Tender vanilla cupcake with fluffy vanilla buttercream, decorated with pastel sprinkles. Sweet simplicity at its best.', 260.00, 'cupcake_vanilla.jpg', '["Classic Vanilla","Strawberry Vanilla"]', '["Standard"]', 4.6, 156, 100, 0);

-- BROWNIES (category_id = 3)
INSERT INTO products (category_id, name, description, price, image, flavours, sizes, rating, reviews_count, stock, is_featured) VALUES
(3, 'Classic Chocolate Brownie', 'Dense, fudgy, and perfectly gooey chocolate brownie with a crinkly top and rich cocoa flavour.', 220.00, 'brownie_classic.jpg', '["Classic Fudge"]', '["Single Piece","Box of 6"]', 4.8, 320, 150, 1),
(3, 'Nutella Brownie', 'Fudgy chocolate brownie swirled with generous Nutella and finished with a dusting of hazelnuts.', 260.00, 'brownie_nutella.jpg', '["Nutella Swirl"]', '["Single Piece","Box of 6"]', 4.9, 280, 150, 1),
(3, 'Chocolate Chip Brownie', 'Classic brownie studded with premium chocolate chips throughout for maximum chocolatey goodness.', 240.00, 'brownie_choc_chip.jpg', '["Chocolate Chip"]', '["Single Piece","Box of 6"]', 4.7, 198, 150, 0);

-- COOKIES (category_id = 4)
INSERT INTO products (category_id, name, description, price, image, flavours, sizes, rating, reviews_count, stock, is_featured) VALUES
(4, 'Chocolate Chip Cookies', 'Golden, crispy-edged and chewy-centred cookies loaded with premium chocolate chips. Baked fresh daily.', 480.00, 'cookie_choc_chip.jpg', '["Classic"]', '["Pack of 6","Pack of 12"]', 4.8, 245, 200, 0),
(4, 'Double Chocolate Cookies', 'Intensely chocolatey cookies made with cocoa dough and packed with chocolate chips for the ultimate chocolate fix.', 520.00, 'cookie_double_choc.jpg', '["Double Chocolate"]', '["Pack of 6","Pack of 12"]', 4.9, 187, 200, 0),
(4, 'Butter Cookies', 'Melt-in-your-mouth Danish-style butter cookies with a delicate crunch and rich buttery flavour.', 420.00, 'cookie_butter.jpg', '["Original Butter","Vanilla Butter"]', '["Pack of 6","Pack of 12"]', 4.6, 142, 200, 0);

-- DONUTS (category_id = 5)
INSERT INTO products (category_id, name, description, price, image, flavours, sizes, rating, reviews_count, stock, is_featured) VALUES
(5, 'Chocolate Donut', 'Fluffy, ring-shaped donut topped with glossy chocolate glaze and chocolate sprinkles.', 180.00, 'donut_choc.jpg', '["Dark Chocolate","Milk Chocolate"]', '["Standard"]', 4.7, 165, 80, 0),
(5, 'Strawberry Donut', 'Soft donut with a pretty pink strawberry glaze and rainbow sprinkles. A colourful crowd pleaser.', 190.00, 'donut_strawberry.jpg', '["Strawberry"]', '["Standard"]', 4.6, 143, 80, 0),
(5, 'Glazed Donut', 'The timeless classic — a perfectly fried ring donut with a thin, sweet vanilla glaze. Simply irresistible.', 160.00, 'donut_glazed.jpg', '["Classic Glaze","Cinnamon Glaze"]', '["Standard"]', 4.5, 198, 80, 0);

-- GIFT BOXES (category_id = 6)
INSERT INTO products (category_id, name, description, price, image, flavours, sizes, rating, reviews_count, stock, is_featured) VALUES
(6, 'Birthday Sweet Box', 'A curated birthday surprise box with assorted cupcakes, brownies, macarons, and a personalised birthday card. Perfect gift!', 2800.00, 'box_birthday.jpg', '["Classic Birthday","Chocolate Lover","Pink & Gold"]', '["Small (8 items)","Medium (12 items)","Large (16 items)"]', 4.9, 310, 30, 1),
(6, 'Chocolate Lover Box', 'The ultimate gift for chocolate enthusiasts — chocolate cake slice, brownies, truffles, and chocolate cookies.', 3200.00, 'box_chocolate.jpg', '["Dark Chocolate","Milk Chocolate Mix"]', '["Medium (10 items)","Large (14 items)"]', 4.8, 220, 30, 1),
(6, 'Mini Dessert Box', 'A delightful assortment of bite-sized sweets — mini cupcakes, mini brownies, and cookies. Great for sharing.', 1800.00, 'box_mini.jpg', '["Classic Mix","Chocolate Mix"]', '["Small (6 items)"]', 4.7, 175, 30, 0),
(6, 'Celebration Gift Box', 'An impressive luxury celebration box with premium sweets, fresh flowers, and a hand-written message card.', 4500.00, 'box_celebration.jpg', '["Elegant White","Rose Gold","Chocolate Brown"]', '["Large (20 items)"]', 5.0, 89, 30, 1);

-- ===========================
-- SEED: SAMPLE ORDERS
-- ===========================
INSERT INTO orders (user_id, full_name, email, phone, address, city, postal_code, delivery_date, payment_method, subtotal, delivery_fee, total, status) VALUES
(2, 'Nimal Perera', 'nimal@example.com', '+94 71 234 5678', '45/A, Galle Road, Wellawatte', 'Colombo', '00600', '2026-08-15', 'cod', 5780.00, 0.00, 5780.00, 'Delivered'),
(2, 'Nimal Perera', 'nimal@example.com', '+94 71 234 5678', '45/A, Galle Road, Wellawatte', 'Colombo', '00600', '2026-08-20', 'cod', 2800.00, 200.00, 3000.00, 'Preparing'),
(3, 'Sanduni Silva', 'sanduni@example.com', '+94 76 345 6789', '12, Temple Road, Nugegoda', 'Nugegoda', '10250', '2026-08-18', 'card', 1800.00, 350.00, 2150.00, 'Confirmed');

INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price) VALUES
(1, 1, 'Chocolate Fudge Cake', 1, 3200.00),
(1, 5, 'Chocolate Cupcake', 6, 280.00),
(1, 7, 'Classic Chocolate Brownie', 5, 220.00),
(2, 16, 'Birthday Sweet Box', 1, 2800.00),
(3, 19, 'Mini Dessert Box', 1, 1800.00);

-- ===========================
-- NOTE ON PASSWORDS
-- ===========================
-- Admin: admin@sweetmart.lk / password
-- Customers: password (same hash above, replace with proper hash in production)
-- To generate a proper hash for "Admin@1234", use: password_hash('Admin@1234', PASSWORD_DEFAULT)
