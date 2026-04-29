<?php
// Tentukan halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);
$username = htmlspecialchars($_SESSION['username']);
$role     = htmlspecialchars($_SESSION['role']);
$initial  = strtoupper(substr($username, 0, 1));
?>
<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">

    <!-- ===== HEADER SIDEBAR ===== -->
    <div class="sidebar-header">
        <div class="sidebar-logo-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                <path d="M2 17l10 5 10-5"/>
                <path d="M2 12l10 5 10-5"/>
            </svg>
        </div>
        <div class="sidebar-brand-wrap">
            <span class="sidebar-brand">Siska Maju Motor</span>
            <span class="sidebar-tagline">Inventori Sparepart</span>
        </div>
        <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Tutup Menu">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    <!-- ===== NAVIGASI ===== -->
    <nav class="sidebar-nav">

        <!-- Grup: Menu Utama -->
        <div class="nav-group">
            <p class="nav-group-label">MENU UTAMA</p>
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php" class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                            </svg>
                        </span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="statistics.php" class="nav-link <?= $current_page === 'statistics.php' ? 'active' : '' ?>">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
                                <line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/>
                            </svg>
                        </span>
                        <span class="nav-text">Statistik &amp; Prediksi</span>
                    </a>
                </li>
                <li>
                    <a href="reports.php" class="nav-link <?= $current_page === 'reports.php' ? 'active' : '' ?>">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                        </span>
                        <span class="nav-text">Laporan Keuangan</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Divider -->
        <div class="nav-divider"></div>

        <!-- Grup: Manajemen -->
        <div class="nav-group">
            <p class="nav-group-label">MANAJEMEN DATA</p>
            <ul class="sidebar-menu">
                <li>
                    <a href="categories.php" class="nav-link <?= $current_page === 'categories.php' ? 'active' : '' ?>">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                            </svg>
                        </span>
                        <span class="nav-text">Kelola Kategori</span>
                    </a>
                </li>
                <li>
                    <a href="spareparts.php" class="nav-link <?= $current_page === 'spareparts.php' ? 'active' : '' ?>">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                                <polyline points="2 17 12 22 22 17"/>
                                <polyline points="2 12 12 17 22 12"/>
                            </svg>
                        </span>
                        <span class="nav-text">Data Sparepart</span>
                    </a>
                </li>
                <li>
                    <a href="transactions.php" class="nav-link <?= $current_page === 'transactions.php' ? 'active' : '' ?>">
                        <span class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                        </span>
                        <span class="nav-text">Transaksi Penjualan</span>
                    </a>
                </li>
            </ul>
        </div>

    </nav>

    <!-- ===== FOOTER SIDEBAR: Info User + Tombol Keluar ===== -->
    <div class="sidebar-footer">
        <div class="sidebar-user-info">
            <div class="sidebar-avatar"><?= $initial ?></div>
            <div class="sidebar-user-text">
                <span class="sidebar-username"><?= $username ?></span>
                <span class="sidebar-userrole"><?= ucfirst($role) ?></span>
            </div>
        </div>
        <a href="logout.php" class="btn-logout-sidebar" title="Keluar dari sistem">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>Keluar</span>
        </a>
    </div>

</aside>
