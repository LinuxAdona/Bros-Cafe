-- Bros Cafe Database Schema
-- Run this script in phpMyAdmin or MySQL to create the database structure

CREATE DATABASE IF NOT EXISTS broscafe_db;
USE broscafe_db;

-- Users table (for admin, employees, and customers)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'employee', 'customer') DEFAULT 'customer',
    phone VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price_dodici DECIMAL(10, 2),
    price_sedici DECIMAL(10, 2),
    image VARCHAR(255),
    status ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Inventory table
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    unit VARCHAR(20),
    reorder_level INT DEFAULT 10,
    last_restocked TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT,
    employee_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'card', 'gcash', 'other') DEFAULT 'cash',
    status ENUM('pending', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    order_type ENUM('dine-in', 'takeout', 'delivery') DEFAULT 'dine-in',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    size ENUM('dodici', 'sedici'),
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Employee shifts table
CREATE TABLE IF NOT EXISTS shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    shift_date DATE NOT NULL,
    clock_in TIMESTAMP NULL,
    clock_out TIMESTAMP NULL,
    total_hours DECIMAL(5, 2),
    status ENUM('scheduled', 'ongoing', 'completed') DEFAULT 'scheduled',
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sales report table (for performance tracking)
CREATE TABLE IF NOT EXISTS sales_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL UNIQUE,
    total_orders INT DEFAULT 0,
    total_revenue DECIMAL(10, 2) DEFAULT 0,
    total_items_sold INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inventory transactions (for tracking stock changes)
CREATE TABLE IF NOT EXISTS inventory_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    transaction_type ENUM('restock', 'sale', 'adjustment', 'waste') NOT NULL,
    quantity INT NOT NULL,
    user_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default users
-- Admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, role, phone) 
VALUES ('admin', 'admin@broscafe.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin', '+63 123 456 7890');

-- Employee user (password: employee123)
INSERT INTO users (username, email, password, full_name, role, phone) 
VALUES ('employee', 'employee@broscafe.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Employee User', 'employee', '+63 987 654 3210');

-- Sample customer (password: customer123)
INSERT INTO users (username, email, password, full_name, role, phone) 
VALUES ('customer', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Customer User', 'customer', '+63 555 123 4567');

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Iced Coffee', 'Cold coffee beverages'),
('Hot Coffee', 'Hot coffee beverages'),
('Matcha Series', 'Matcha-based drinks'),
('Non-Coffee', 'Non-coffee beverages'),
('Frappe', 'Blended iced drinks');

-- Insert sample products
INSERT INTO products (category_id, name, description, price_dodici, price_sedici, status) VALUES
(1, 'Sea Salt Latte', 'Espresso and milk topped with sea salt cream', 120.00, 150.00, 'available'),
(1, 'Spanish Latte', 'Sweet twist on a classic iced cafe latte', 120.00, 140.00, 'available'),
(1, 'White Chocolate Mocha', 'Espresso, white chocolate sauce, milk and ice', 120.00, 140.00, 'available'),
(1, 'Caramel Macchiato', 'Espresso shot with vanilla, caramel sauce, milk and ice', 130.00, 150.00, 'available'),
(2, 'Americano', 'Espresso with water and ice', 80.00, 100.00, 'available'),
(2, 'Cafe Latte', 'Espresso with vanilla and milk', 100.00, 120.00, 'available'),
(3, 'Matcha Latte', 'Creamy matcha with outside milk', 140.00, NULL, 'available'),
(3, 'Banana Pudding Matcha Latte', 'Matcha latte topped with banana pudding', 180.00, NULL, 'available'),
(4, 'Hibiscus Tea', 'Iced shaken hibiscus tea with pumps of strawberry syrup', 100.00, NULL, 'available'),
(5, 'Java Chip', 'Blend of mocha sauce, choco chips, coffee, milk and ice', 150.00, NULL, 'available');

-- Insert initial inventory for products
INSERT INTO inventory (product_id, quantity, unit, reorder_level) 
SELECT id, 100, 'servings', 20 FROM products;
