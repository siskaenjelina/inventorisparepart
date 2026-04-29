<?php
// config.php
// Konfigurasi Database dan Sesi

session_start(); // Memulai session untuk manajemen login

$host = 'localhost';
$username = 'root';
$password = ''; // Default XAMPP tidak memiliki password
$database = 'inventorisparepart';

// Membuat koneksi ke database
$conn = new mysqli($host, $username, $password, $database);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset ke utf8 untuk mendukung berbagai karakter
$conn->set_charset("utf8mb4");

// Fungsi pembantu untuk mengecek status login
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }
}
?>
