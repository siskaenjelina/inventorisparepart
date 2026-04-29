<?php
require_once 'config.php';
check_login();

// Filter
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');

$from_esc = $conn->real_escape_string($from);
$to_esc   = $conn->real_escape_string($to);

// Laporan penjualan
$report = $conn->query("
    SELECT t.*, s.merk_tipe_motor, s.spek, s.harga_modal,
           (t.selling_price - s.harga_modal) * t.quantity AS profit
    FROM transactions_out t
    JOIN spareparts s ON t.sparepart_id = s.id
    WHERE t.transaction_date BETWEEN '$from_esc' AND '$to_esc'
    ORDER BY t.transaction_date ASC
");

// Summary
$summary = $conn->query("
    SELECT
        COUNT(t.id) AS total_trx,
        COALESCE(SUM(t.selling_price * t.quantity), 0) AS total_revenue,
        COALESCE(SUM(s.harga_modal * t.quantity), 0) AS total_modal,
        COALESCE(SUM((t.selling_price - s.harga_modal) * t.quantity), 0) AS total_profit
    FROM transactions_out t
    JOIN spareparts s ON t.sparepart_id = s.id
    WHERE t.transaction_date BETWEEN '$from_esc' AND '$to_esc'
")->fetch_assoc();

// Top selling items
$top_items = $conn->query("
    SELECT s.merk_tipe_motor, s.spek, SUM(t.quantity) AS total_qty,
           SUM(t.selling_price * t.quantity) AS total_rev
    FROM transactions_out t
    JOIN spareparts s ON t.sparepart_id = s.id
    WHERE t.transaction_date BETWEEN '$from_esc' AND '$to_esc'
    GROUP BY t.sparepart_id
    ORDER BY total_qty DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Keuangan – Siska Maju Motor</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
<style>
@media print {
    .no-print { display: none !important; }
    .main-wrapper { margin-left: 0 !important; }
    .sidebar, .top-header { display: none !important; }
    body { background: #fff !important; }
    .print-header { display: block !important; }
}
.print-header {
    display: none;
    text-align: center;
    margin-bottom: 1.5rem;
}
.print-header h1 { font-size: 1.4rem; font-weight: 800; }
.print-header p { font-size: 0.85rem; color: #666; }
</style>
</head>
<body>
<?php require_once 'includes/sidebar.php'; ?>
<div class="main-wrapper">
    <header class="top-header no-print">
        <div class="header-left">
            <button class="hamburger-btn" id="hamburgerBtn"><span class="hb-line"></span><span class="hb-line"></span><span class="hb-line"></span></button>
            <div>
                <div class="page-title">Laporan Keuangan</div>
                <div class="breadcrumb">Menu Utama / <span>Laporan</span></div>
            </div>
        </div>
        <div class="header-right">
            <button id="theme-toggle" class="theme-toggle-dash">
                <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </button>
            <div class="user-chip">
                <div class="avatar"><?= strtoupper(substr($_SESSION['username'],0,1)) ?></div>
                <span class="user-name"><?= htmlspecialchars($_SESSION['username']) ?></span>
            </div>
        </div>
    </header>

    <div class="content-area">

        <!-- Print Header -->
        <div class="print-header">
            <h1>Siska Maju Motor</h1>
            <p>Laporan Penjualan Periode: <?= date('d M Y', strtotime($from)) ?> – <?= date('d M Y', strtotime($to)) ?></p>
            <p>Dicetak pada: <?= date('d M Y H:i') ?></p>
            <hr style="margin:1rem 0">
        </div>

        <!-- Filter Form -->
        <div class="content-card no-print">
            <div class="card-header">
                <h2><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>Filter Periode Laporan</h2>
            </div>
            <div class="card-body">
                <form method="GET" class="form-row" style="align-items:flex-end">
                    <div class="form-group" style="flex:1;min-width:160px;margin:0">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
                    </div>
                    <div class="form-group" style="flex:1;min-width:160px;margin:0">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
                    </div>
                    <div style="display:flex;gap:.5rem;flex-shrink:0">
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            Tampilkan
                        </button>
                        <button type="button" class="btn btn-success" onclick="printReport()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            Cetak Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:1.25rem">
            <div class="summary-card"><div class="card-info"><h3>Total Transaksi</h3><p data-count="<?= $summary['total_trx'] ?>">0</p></div><div class="card-icon icon-blue"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg></div></div>
            <div class="summary-card"><div class="card-info"><h3>Total Pendapatan</h3><p style="font-size:1.15rem">Rp <?= number_format($summary['total_revenue'],0,'.','.') ?></p></div><div class="card-icon icon-green"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div></div>
            <div class="summary-card"><div class="card-info"><h3>Total Modal</h3><p style="font-size:1.15rem">Rp <?= number_format($summary['total_modal'],0,'.','.') ?></p></div><div class="card-icon icon-orange"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div></div>
            <div class="summary-card"><div class="card-info"><h3>Total Keuntungan</h3><p style="font-size:1.15rem;color:var(--success)">Rp <?= number_format($summary['total_profit'],0,'.','.') ?></p></div><div class="card-icon icon-green"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div></div>
        </div>

        <!-- Top Items -->
        <?php if ($top_items && $top_items->num_rows > 0): ?>
        <div class="content-card">
            <div class="card-header">
                <h2><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>Item Terlaris Periode Ini</h2>
            </div>
            <div class="table-wrap">
            <table>
                <thead><tr><th>Rank</th><th>Sparepart</th><th>Total Qty Terjual</th><th>Total Pendapatan</th></tr></thead>
                <tbody>
                <?php $rank=1; while($r=$top_items->fetch_assoc()): ?>
                <tr>
                    <td><span class="badge badge-primary">#<?= $rank++ ?></span></td>
                    <td><strong><?= htmlspecialchars($r['merk_tipe_motor']) ?></strong> – <?= htmlspecialchars($r['spek']) ?></td>
                    <td><?= $r['total_qty'] ?> unit</td>
                    <td>Rp <?= number_format($r['total_rev'],0,'.','.') ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabel Laporan Detail -->
        <div class="content-card">
            <div class="card-header">
                <h2><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Detail Transaksi Penjualan</h2>
                <span class="badge badge-primary no-print">Periode: <?= date('d M Y', strtotime($from)) ?> – <?= date('d M Y', strtotime($to)) ?></span>
            </div>
            <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Tanggal</th><th>Sparepart</th><th>Mekanik</th><th>Qty</th><th>Harga Jual</th><th>Modal</th><th>Pendapatan</th><th>Profit</th></tr>
                </thead>
                <tbody>
                <?php if ($report && $report->num_rows > 0):
                    $no=1; $report->data_seek(0);
                    while($row=$report->fetch_assoc()):
                    $profit_cls = $row['profit'] >= 0 ? 'color:var(--success)' : 'color:var(--danger)';
                ?>
                <tr>
                    <td style="color:var(--text-muted)"><?= $no++ ?></td>
                    <td><?= date('d M Y', strtotime($row['transaction_date'])) ?></td>
                    <td><strong><?= htmlspecialchars($row['merk_tipe_motor']) ?></strong><br><small style="color:var(--text-muted)"><?= htmlspecialchars($row['spek']) ?></small></td>
                    <td><?= htmlspecialchars($row['mechanic_name'] ?: '-') ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td>Rp <?= number_format($row['selling_price'],0,'.','.') ?></td>
                    <td>Rp <?= number_format($row['harga_modal'],0,'.','.') ?></td>
                    <td>Rp <?= number_format($row['selling_price']*$row['quantity'],0,'.','.') ?></td>
                    <td><strong style="<?= $profit_cls ?>">Rp <?= number_format($row['profit'],0,'.','.') ?></strong></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="9"><div class="empty-state"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg><p>Tidak ada transaksi pada periode ini.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
                <?php if ($report && $report->num_rows > 0): ?>
                <tfoot>
                    <tr style="background:var(--table-head);font-weight:700">
                        <td colspan="7" style="padding:.85rem 1rem;text-align:right">TOTAL:</td>
                        <td style="padding:.85rem 1rem">Rp <?= number_format($summary['total_revenue'],0,'.','.') ?></td>
                        <td style="padding:.85rem 1rem;color:var(--success)">Rp <?= number_format($summary['total_profit'],0,'.','.') ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
            </div>
        </div>

    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
