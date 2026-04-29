-- Struktur Database Siska Maju Motor
-- Disesuaikan untuk mencegah duplikasi pemesanan dan pencatatan stok yang akurat

CREATE DATABASE IF NOT EXISTS inventorisparepart;
USE inventorisparepart;

-- 1. Tabel users (Admin Login)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    google_id VARCHAR(255) UNIQUE,
    role ENUM('admin', 'staff') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (Password: admin123, email ditambahkan untuk testing)
INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@siskamajumotor.com', 'admin123', 'admin') ON DUPLICATE KEY UPDATE id=id;

-- 2. Tabel categories (Kategori Sparepart)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO categories (name) VALUES ('Busi'), ('Kampas Rem'), ('Oli'), ('Ban') ON DUPLICATE KEY UPDATE id=id;

-- 3. Tabel spareparts (Data Utama)
CREATE TABLE IF NOT EXISTS spareparts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    merk_tipe_motor VARCHAR(150) NOT NULL, -- Contoh: Honda Beat
    spek VARCHAR(150) NOT NULL,            -- Contoh: Busi, Kampas Rem Depan
    harga_modal DECIMAL(10, 2) NOT NULL,
    harga_jual DECIMAL(10, 2) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    min_stok INT NOT NULL DEFAULT 5,       -- Reorder point untuk mencegah kehabisan/duplikasi
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert dummy data sesuai permintaan
INSERT INTO spareparts (merk_tipe_motor, spek, harga_modal, harga_jual, stok, min_stok) VALUES 
('Honda Beat', 'Busi', 20000.00, 30000.00, 50, 10),
('Honda Beat', 'Kampas Rem Depan', 25000.00, 35000.00, 30, 5),
('Honda Beat', 'Kampas Rem Belakang', 23000.00, 33000.00, 25, 5)
ON DUPLICATE KEY UPDATE id=id;

-- 4. Tabel suppliers (Sumber Barang)
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(50),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO suppliers (name, contact) VALUES ('Supplier A', '08123456789'), ('Supplier B', '08987654321') ON DUPLICATE KEY UPDATE id=id;

-- 5. Tabel transactions_in (Barang Masuk)
CREATE TABLE IF NOT EXISTS transactions_in (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sparepart_id INT NOT NULL,
    supplier_id INT NOT NULL,
    quantity INT NOT NULL,
    purchase_price DECIMAL(10, 2) NOT NULL,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sparepart_id) REFERENCES spareparts(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

-- 6. Tabel transactions_out (Barang Keluar)
CREATE TABLE IF NOT EXISTS transactions_out (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sparepart_id INT NOT NULL,
    quantity INT NOT NULL,
    selling_price DECIMAL(10, 2) NOT NULL,
    transaction_date DATE NOT NULL,
    mechanic_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sparepart_id) REFERENCES spareparts(id)
);
