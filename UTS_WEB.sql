-- Database User Management System
-- Buat database
CREATE DATABASE IF NOT EXISTS UTS_WEB;
USE UTS_WEB;

-- Tabel users untuk menyimpan data pengguna
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(255) NOT NULL,
    telepon VARCHAR(20),
    is_active TINYINT(1) DEFAULT 0,
    activation_token VARCHAR(255),
    reset_token VARCHAR(255),
    reset_token_expiry DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_activation_token (activation_token),
    INDEX idx_reset_token (reset_token)
);

-- Tabel products untuk menyimpan data produk
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(255) NOT NULL,
    kode_produk VARCHAR(50) UNIQUE NOT NULL,
    kategori VARCHAR(100),
    harga DECIMAL(10, 2) NOT NULL,
    stok INT DEFAULT 0,
    deskripsi TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_kode_produk (kode_produk),
    INDEX idx_kategori (kategori)
);
