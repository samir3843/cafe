-- Database Schema Update for Café Management System

-- Drop existing tables to ensure clean state (optional, remove if preserving data)
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS menu;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Users Table (Updated for new roles)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'kitchen') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default users
INSERT INTO users (username, password, role) VALUES 
('admin', 'admin123', 'admin'),
('staff', 'staff123', 'staff'),
('chef', 'chef123', 'kitchen');

-- Categories Table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
);

-- Insert default categories
INSERT INTO categories (name) VALUES ('Drinks'), ('Snacks'), ('Meals'), ('Desserts');

-- Menu Table
CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    description TEXT,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Insert sample menu items
INSERT INTO menu (name, category_id, price, image_url, status) VALUES 
('Coffee', 1, 5.00, 'images/coffee.jpg', 'active'),
('Tea', 1, 3.00, 'images/tea.jpg', 'active'),
('Sandwich', 3, 8.50, 'images/sandwich.jpg', 'active'),
('Cake', 4, 4.50, 'images/cake.jpg', 'active');

-- Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(20) NOT NULL UNIQUE, -- e.g., ORD20240211001
    user_id INT, -- Staff who created the order
    table_number INT,
    customer_name VARCHAR(100),
    total_amount DECIMAL(10, 2) DEFAULT 0.00,
    payment_status ENUM('paid', 'unpaid') DEFAULT 'unpaid',
    kitchen_status ENUM('pending', 'preparing', 'ready', 'picked_up') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order Items Table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL, -- Price at the time of order
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE SET NULL
);
