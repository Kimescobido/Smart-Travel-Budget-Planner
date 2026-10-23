-- Database creation
CREATE DATABASE IF NOT EXISTS travel_planner_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE travel_planner_db;

-- 1. Users Table (Supports Admin & Regular User roles)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Trips Table (Primary CRUD Entity)
CREATE TABLE IF NOT EXISTS trips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    destination_city VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    base_currency VARCHAR(10) DEFAULT 'USD',
    total_budget DECIMAL(10, 2) DEFAULT 0.00,
    cover_image_url TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. Itinerary Items / Expenses Table (Child CRUD Entity)
CREATE TABLE IF NOT EXISTS itinerary_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    day_number INT DEFAULT 1,
    activity_title VARCHAR(200) NOT NULL,
    cost_local DECIMAL(10, 2) DEFAULT 0.00,
    local_currency VARCHAR(10) DEFAULT 'USD',
    cost_base DECIMAL(10, 2) DEFAULT 0.00,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE
);