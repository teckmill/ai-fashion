-- Create the database
CREATE DATABASE IF NOT EXISTS ai_fashion;
USE ai_fashion;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User preferences table
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    style_preference VARCHAR(50),
    color_preference VARCHAR(50),
    season_preference VARCHAR(20),
    height_feet INT,
    height_inches INT,
    weight INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- User sessions table
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Saved outfits table
CREATE TABLE IF NOT EXISTS saved_outfits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    outfit_name VARCHAR(100),
    outfit_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Product recommendations table
CREATE TABLE IF NOT EXISTS product_recommendations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id VARCHAR(100) NOT NULL,
    retailer_id VARCHAR(50) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_url TEXT NOT NULL,
    image_url TEXT,
    price DECIMAL(10,2),
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- User interactions table for machine learning
CREATE TABLE IF NOT EXISTS user_interactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    interaction_type ENUM('view', 'like', 'save', 'purchase', 'dismiss') NOT NULL,
    item_id VARCHAR(100) NOT NULL,
    item_type ENUM('outfit', 'product', 'style') NOT NULL,
    interaction_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Style preferences table for machine learning
CREATE TABLE IF NOT EXISTS style_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    preference_score FLOAT NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Retailer configurations
CREATE TABLE IF NOT EXISTS retailers (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    api_endpoint TEXT NOT NULL,
    api_key VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Product categories mapping
CREATE TABLE IF NOT EXISTS product_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(50) NOT NULL,
    parent_category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_category_id) REFERENCES product_categories(id) ON DELETE SET NULL
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    image_url TEXT NOT NULL,
    affiliate_url TEXT,
    brand VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample products
INSERT INTO products (name, description, price, category, image_url, brand) VALUES
('Classic White T-Shirt', 'Essential cotton crew neck t-shirt', 19.99, 'tops', 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab', 'Basic Essentials'),
('Black Skinny Jeans', 'High-waisted stretchy denim jeans', 49.99, 'bottoms', 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246', 'Denim Co'),
('Floral Summer Dress', 'Light and breezy floral print dress', 59.99, 'dresses', 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1', 'Summer Vibes'),
('Leather Jacket', 'Classic black leather motorcycle jacket', 129.99, 'outerwear', 'https://images.unsplash.com/photo-1551028719-00167b16eac5', 'Urban Edge'),
('White Sneakers', 'Minimalist leather sneakers', 79.99, 'shoes', 'https://images.unsplash.com/photo-1544441893-675973e31985', 'Comfort Step'),
('Gold Necklace', 'Delicate layered gold-plated necklace', 29.99, 'accessories', 'https://images.unsplash.com/photo-1599643477877-530eb83abc2e', 'Glam Accessories'),
('Navy Blazer', 'Classic fitted navy blazer', 89.99, 'outerwear', 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35', 'Professional Wear'),
('Silk Blouse', 'Elegant silk button-up blouse', 69.99, 'tops', 'https://images.unsplash.com/photo-1604336753229-27d43b69e41f', 'Luxe Basic'),
('Pleated Skirt', 'Midi length pleated skirt', 45.99, 'bottoms', 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa', 'Modern Classic'),
('Ankle Boots', 'Classic black leather ankle boots', 99.99, 'shoes', 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2', 'Footwear Plus');
