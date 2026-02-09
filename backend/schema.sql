-- Database Schema for Café Management System

-- Users Table
-- Stores login credentials and roles for Admin and Receptionists
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Storing simple passwords for beginner-friendliness, but normally should be hashed
    role ENUM('admin', 'receptionist') NOT NULL
);

-- Orders Table
-- Stores customer orders linked to the receptionist who entered them
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    table_number INT NOT NULL,
    order_details TEXT NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
