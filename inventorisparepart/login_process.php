<?php
// login_process.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validasi input kosong
    if (empty($username) || empty($password)) {
        echo "<script>alert('Username dan Password wajib diisi!'); window.location.href='index.php';</script>";
        exit;
    }

    // Menggunakan prepared statement untuk mencegah SQL Injection
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $hashed_password = $user['password'];

        // Cek password.
        // Mendukung password default (plain text 'admin123') ATAU password yang di-hash dari registrasi baru
        if ($password === $hashed_password || password_verify($password, $hashed_password)) {
            // Login sukses
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect ke dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            // Password salah
            echo "<script>alert('Password salah!'); window.location.href='index.php';</script>";
        }
    } else {
        // Username tidak ditemukan
        echo "<script>alert('Username tidak terdaftar!'); window.location.href='index.php';</script>";
    }
    
    $stmt->close();
} else {
    // Jika diakses tidak melalui POST, kembalikan ke index
    header("Location: index.php");
    exit;
}
$conn->close();
?>
