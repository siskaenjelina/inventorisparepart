<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Siska Maju Motor Inventory</title>
    <!-- Google Fonts for modern typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    
    <!-- Theme Toggle Button -->
    <button id="theme-toggle" class="theme-toggle" aria-label="Toggle Dark Mode">
        <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
        <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
    </button>

    <div class="split-container">
        <!-- Left Side: Branding / Logo -->
        <div class="split-left">
            <div class="branding-content">
                <img src="assets/images/logo.png" alt="Siska Maju Motor Logo" class="brand-logo">
                <h1 class="brand-title">Siska Maju Motor</h1>
                <p class="brand-subtitle">Sistem Informasi Inventori Sparepart<br>Cepat, Tepat, dan Akurat.</p>
            </div>
            <!-- Decorative Elements -->
            <div class="circle-decoration circle-1"></div>
            <div class="circle-decoration circle-2"></div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="split-right">
            <div class="login-wrapper">
                <div class="login-header">
                    <h2>Welcome back!</h2>
                    <p>Silakan masuk ke akun admin Anda.</p>
                </div>
                
                <form class="login-form" action="login_process.php" method="POST">
                    <div class="input-group">
                        <label for="username">Username</label>
                        <div class="input-field">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <input type="text" id="username" name="username" placeholder="admin" required autocomplete="off">
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="input-field">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <input type="password" id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-login">Login Sekarang</button>
                </form>




                <div class="login-footer">
                    <p>Sistem Informasi &copy; <?php echo date('Y'); ?> Siska Maju Motor</p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/theme.js"></script>
</body>
</html>
