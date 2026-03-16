-- Database for BAPS Bal Pravrutti Management System

CREATE DATABASE IF NOT EXISTS bal_pravrutti;
USE bal_pravrutti;

-- Roles table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles (role_name) VALUES 
('Saint'), 
('Nirdheshak'), 
('Agresar'), 
('Nirikshak'), 
('Karyakar'), 
('Sah-Karyakar');

-- Zones table
CREATE TABLE IF NOT EXISTS zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_name VARCHAR(100) NOT NULL UNIQUE
);

-- Clusters table
CREATE TABLE IF NOT EXISTS clusters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cluster_name VARCHAR(100) NOT NULL,
    zone_id INT,
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE
);

-- Mandals table
CREATE TABLE IF NOT EXISTS mandals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mandal_name VARCHAR(100) NOT NULL,
    cluster_id INT,
    karyakar_id INT,
    FOREIGN KEY (cluster_id) REFERENCES clusters(id) ON DELETE CASCADE,
    FOREIGN KEY (karyakar_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT,
    zone_id INT DEFAULT NULL,
    cluster_id INT DEFAULT NULL,
    mandal_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (zone_id) REFERENCES zones(id),
    FOREIGN KEY (cluster_id) REFERENCES clusters(id),
    FOREIGN KEY (mandal_id) REFERENCES mandals(id)
);

-- Balaks table
CREATE TABLE IF NOT EXISTS balaks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    mandal_id INT,
    dob DATE,
    contact_number VARCHAR(15),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mandal_id) REFERENCES mandals(id) ON DELETE CASCADE
);

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    balak_id INT,
    status ENUM('Present', 'Absent') NOT NULL,
    attendance_date DATE NOT NULL,
    marked_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (balak_id) REFERENCES balaks(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id)
);

-- Mukhpath Progress table
CREATE TABLE IF NOT EXISTS mukhpath_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    balak_id INT,
    item_name VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'In Progress', 'Completed') DEFAULT 'Pending',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (balak_id) REFERENCES balaks(id) ON DELETE CASCADE
);

-- Sampark (Home Visits) table
CREATE TABLE IF NOT EXISTS sampark (
    id INT AUTO_INCREMENT PRIMARY KEY,
    balak_id INT,
    visit_date DATE NOT NULL,
    remarks TEXT,
    visited_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (balak_id) REFERENCES balaks(id) ON DELETE CASCADE,
    FOREIGN KEY (visited_by) REFERENCES users(id)
);

-- Announcements table
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_by INT,
    target_role_id INT DEFAULT NULL, -- NULL means all
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (target_role_id) REFERENCES roles(id)
);

-- Insert default Saint (Super Admin) for testing
-- Password is 'admin123' hashed with bcrypt
INSERT INTO users (full_name, email, password, role_id) 
VALUES ('Main Saint', 'saint@baps.org', '$2y$10$YKIySpwMBc4KOyaFjOjpTOQq4jUF3cq5tS/bvbdvNuLdaGBB0u3dG', 1);
