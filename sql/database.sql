CREATE DATABASE IF NOT EXISTS DstyleApparel_Records
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE DstyleApparel_Records;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(30) NULL,
    address TEXT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(190) NOT NULL,
    brand VARCHAR(120) NULL,
    description TEXT NULL,
    category_id INT UNSIGNED NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock_qty INT NOT NULL DEFAULT 0,
    sizes_json JSON NULL,
    image VARCHAR(255) NOT NULL,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_products_category (category_id),
    INDEX idx_products_name (product_name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS favorites (
    user_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, product_id),
    CONSTRAINT fk_favorites_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_favorites_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    size VARCHAR(50) NOT NULL DEFAULT 'One Size',
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart_item (user_id, product_id, size),
    CONSTRAINT fk_cart_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_cart_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (name) VALUES
('Men'), ('Women'), ('Kids'), ('Shoes'), ('Accessories');

-- Demo admin account.
-- Email: admin@dstyleapp.com
-- Password: admin123
INSERT INTO users (first_name, last_name, email, password_hash, role)
SELECT 'DSTYLE', 'Admin', 'admin@dstyleapp.com', '$2y$12$XEa5NBXUybCuybGGldzpHuaG2gt4IhWHoRCzWKc9ePKnDQwEJVNhS', 'admin'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'admin@dstyleapp.com'
);

-- The homepage and products page can use your existing images/ files.
-- Change the image filenames below only after placing those files inside images/.
INSERT INTO products (product_name, brand, description, category_id, price, cost_price, stock_qty, sizes_json, image, featured)
SELECT 'Vintage Branded Tee', 'D’STYLE', 'Pre-loved branded shirt in good condition.', c.id, 450.00, 250.00, 8, JSON_ARRAY('S', 'M', 'L', 'XL'), 'images/product1.jpg', 1
FROM categories c WHERE c.name = 'Men'
AND NOT EXISTS (SELECT 1 FROM products WHERE product_name = 'Vintage Branded Tee');

INSERT INTO products (product_name, brand, description, category_id, price, cost_price, stock_qty, sizes_json, image, featured)
SELECT 'Classic Denim Jacket', 'D’STYLE', 'Versatile second-hand denim jacket.', c.id, 850.00, 500.00, 5, JSON_ARRAY('S', 'M', 'L'), 'images/product2.jpg', 1
FROM categories c WHERE c.name = 'Women'
AND NOT EXISTS (SELECT 1 FROM products WHERE product_name = 'Classic Denim Jacket');

INSERT INTO products (product_name, brand, description, category_id, price, cost_price, stock_qty, sizes_json, image, featured)
SELECT 'Everyday Sneakers', 'D’STYLE', 'Comfortable pre-loved sneakers for everyday use.', c.id, 950.00, 600.00, 4, JSON_ARRAY('38', '39', '40', '41', '42'), 'images/product3.jpg', 1
FROM categories c WHERE c.name = 'Shoes'
AND NOT EXISTS (SELECT 1 FROM products WHERE product_name = 'Everyday Sneakers');
