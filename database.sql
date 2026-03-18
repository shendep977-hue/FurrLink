-- FURLINK PostgreSQL Database Schema

-- Drop tables if they exist to allow clean re-initialization
DROP TABLE IF EXISTS adoption_requests CASCADE;
DROP TABLE IF EXISTS pets CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Users Table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL CHECK (role IN ('seller', 'adopter', 'admin')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Pets Table
CREATE TABLE pets (
    id SERIAL PRIMARY KEY,
    seller_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    breed VARCHAR(100) NOT NULL,
    age INTEGER NOT NULL,
    gender VARCHAR(20) NOT NULL CHECK (gender IN ('Male', 'Female', 'Unknown')),
    description TEXT,
    price DECIMAL(10, 2) DEFAULT 0.00,
    image VARCHAR(255),
    status VARCHAR(20) DEFAULT 'Available' CHECK (status IN ('Available', 'Adopted')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Adoption Requests Table
CREATE TABLE adoption_requests (
    id SERIAL PRIMARY KEY,
    pet_id INTEGER NOT NULL REFERENCES pets(id) ON DELETE CASCADE,
    adopter_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(pet_id, adopter_id) -- Prevent duplicate requests for the same pet by the same user
);

-- Insert a default admin user (password: admin123 - hashed using PHP's password_hash)
-- The hash below is for 'admin123' using PASSWORD_DEFAULT (bcrypt)
INSERT INTO users (name, email, password, role) VALUES 
('Admin Account', 'admin@furlink.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
