<?php
require_once 'config.php';
check_login();

$username = htmlspecialchars($_SESSION['username']);
$role     = htmlspecialchars($_SESSION['role']);
$initial  = strtoupper(substr($username, 0, 1));

// Statistik
$total_items      = $conn->query("SELECT COUNT(id) as t FROM spareparts")->fetch_assoc()['t'];
$low_stock        = $conn->query("SELECT COUNT(id) as t FROM spareparts WHERE stok <= min_stok")->fetch_assoc()['t'];
$total_categories = $conn->query("SELECT COUNT(id) as t FROM categories")->fetch_assoc()['t'];

// Total pendapatan bulan ini
$r = $conn->query("SELECT COALESCE(SUM(selling_price * quantity),0) as total FROM transactions_out WHERE MONTH(transaction_date)=MONTH(NOW()) AND YEAR(transaction_date)=YEAR(NOW())");
$revenue_month = $r->fetch_assoc()['total'];

// Total transaksi hari ini
$r2 = $conn->query("SELECT COUNT(id) as t FROM transactions_out WHERE DATE(transaction_date)=CURDATE()");
$trx_today = $r2->fetch_assoc()['t'];

// 5 stok paling tipis
$low_items = $conn->query("SELECT merk_tipe_motor, spek, stok, min_stok FROM spareparts ORDER BY (stok - min_stok) ASC LIMIT 5");

// Transaksi terbaru
$recent_trx = $conn->query("SELECT t.id, s.merk_tipe_motor, s.spek, t.quantity, t.selling_price, t.transaction_date FROM transactions_out t JOIN spareparts s ON t.sparepart_id=s.id ORDER BY t.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard – Siska Maju Motor</title>
<meta name="description" content="Dashboard manajemen inventori spare part Siska Maju Motor">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<?php require_once 'includes/sidebar.php'; ?>

<div class="main-wrapper">
    <!-- Top Header -->
    <header class="top-header">
        <div class="header-left">
            <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle Menu">
                <span class="hb-line"></span>
                <span class="hb-line"></span>
                <span class="hb-line"></span>
            </button>
            <div>
                <div class="page-title">Dashboard</div>
                <div class="breadcrumb">Selamat datang, <span><?= $username ?></span>!</div>
            </div>
        </div>
        <div class="header-right">
            <button id="theme-toggle" class="theme-toggle-dash" aria-label="Toggle Dark Mode">
                <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </button>
            <div class="user-chip">
                <div class="avatar"><?= $initial ?></div>
                <span class="user-name"><?= $username ?></span>
            </div>
        </div>
    </header>

    <!-- Content -->
    <div class="content-area">

        <!-- Summary Cards -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="card-info">
                    <h3>Total Sparepart</h3>
                    <p data-count="<?= $total_items ?>">0</p>
                    <div class="card-sub">Jenis item terdaftar</div>
                </div>
                <div class="card-icon icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-info">
                    <h3>Stok Kritis</h3>
                    <p data-count="<?= $low_stock ?>" style="color:var(--danger)">0</p>
                    <div class="card-sub">Perlu segera restok</div>
                </div>
                <div class="card-icon icon-red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-info">
                    <h3>Kategori</h3>
                    <p data-count="<?= $total_categories ?>">0</p>
                    <div class="card-sub">Kategori aktif</div>
                </div>
                <div class="card-icon icon-purple">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-info">
                    <h3>Pendapatan Bulan Ini</h3>
                    <p style="font-size:1.3rem">Rp <?= number_format($revenue_month,0,'.','.') ?></p>
                    <div class="card-sub">Transaksi hari ini: <?= $trx_today ?></div>
                </div>
                <div class="card-icon icon-green">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
        </div>

        <!-- Bottom Grid -->
        <div class="dash-bottom-grid">

            <!-- Stok Kritis -->
            <div class="content-card" style="grid-column: span 1;">
                <div class="card-header">
                    <h2>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                        Peringatan Stok Kritis
                    </h2>
                    <a href="spareparts.php" class="btn btn-sm btn-outline">Kelola &rarr;</a>
                </div>
                <div class="card-body" style="padding:0;">
                    <?php if ($low_items->num_rows > 0): ?>
                    <div class="table-wrap">
                    <table>
                        <thead><tr><th>Sparepart</th><th>Stok</th><th>Min</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php while($row = $low_items->fetch_assoc()): 
                            $pct = $row['min_stok'] > 0 ? min(100, round($row['stok']/$row['min_stok']*100)) : 100;
                            $cls = $row['stok'] == 0 ? 'badge-danger' : ($row['stok'] <= $row['min_stok'] ? 'badge-warning' : 'badge-success');
                            $lbl = $row['stok'] == 0 ? 'Habis' : 'Kritis';
                        ?>
                        <tr>
                            <td data-label="Sparepart"><strong><?= htmlspecialchars($row['merk_tipe_motor']) ?></strong><br><small style="color:var(--text-muted)"><?= htmlspecialchars($row['spek']) ?></small></td>
                            <td data-label="Stok"><strong><?= $row['stok'] ?></strong></td>
                            <td data-label="Min"><?= $row['min_stok'] ?></td>
                            <td data-label="Status"><span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <p>Semua stok aman!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Transaksi Terbaru -->
            <div class="content-card" style="grid-column: span 1;">
                <div class="card-header">
                    <h2>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        Transaksi Terbaru
                    </h2>
                    <a href="transactions.php" class="btn btn-sm btn-outline">Lihat Semua &rarr;</a>
                </div>
                <div class="card-body" style="padding:0;">
                    <?php if ($recent_trx && $recent_trx->num_rows > 0): ?>
                    <div class="table-wrap">
                    <table>
                        <thead><tr><th>ID</th><th>Sparepart</th><th>Qty</th><th>Total</th></tr></thead>
                        <tbody>
                        <?php $no=1; while($row = $recent_trx->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID"><?= $no++ ?></td>
                            <td data-label="Sparepart"><?= htmlspecialchars($row['merk_tipe_motor']) ?> <small style="color:var(--text-muted)"><?= htmlspecialchars($row['spek']) ?></small></td>
                            <td data-label="Qty"><?= $row['quantity'] ?></td>
                            <td data-label="Total">Rp <?= number_format($row['selling_price']*$row['quantity'],0,'.','.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        <p>Belum ada transaksi</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- end grid -->

    </div><!-- end content-area -->
</div><!-- end main-wrapper -->

<script src="assets/js/app.js"></script>
</body>
</html>
