<?php
// register_process.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validasi input kosong
    if (empty($username) || empty($email) || empty($password)) {
        echo "<script>alert('Semua kolom (Username, Email, Password) wajib diisi!'); window.location.href='register.php';</script>";
        exit;
    }

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Format email tidak valid!'); window.location.href='register.php';</script>";
        exit;
    }

    // Cek apakah username atau email sudah ada di database
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt_check->bind_param("ss", $username, $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo "<script>alert('Username atau Email sudah terdaftar! Silakan gunakan yang lain.'); window.location.href='register.php';</script>";
        $stmt_check->close();
        exit;
    }
    $stmt_check->close();

    // Hashing password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'admin'; // Set default role sebagai admin untuk registrasi ini

    // Insert user baru ke database
    $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt_insert->bind_param("ssss", $username, $email, $hashed_password, $role);

    if ($stmt_insert->execute()) {
        echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat registrasi. Silakan coba lagi.'); window.location.href='register.php';</script>";
    }

    $stmt_insert->close();
} else {
    // Jika bukan POST, kembalikan ke register
    header("Location: register.php");
    exit;
}
$conn->close();
?>
