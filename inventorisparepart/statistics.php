<?php
require_once 'config.php';
check_login();

// Data stok semua sparepart
$spareparts = $conn->query("SELECT id, merk_tipe_motor, spek, stok, min_stok FROM spareparts ORDER BY (stok - min_stok) ASC");

// Prediksi: rata-rata penjualan per minggu per item
$predictions = [];
$sp_all = $conn->query("SELECT id, merk_tipe_motor, spek, stok, min_stok FROM spareparts");
while ($sp = $sp_all->fetch_assoc()) {
    $id = $sp['id'];
    $r = $conn->query("
        SELECT COALESCE(SUM(quantity),0) as total_qty,
               DATEDIFF(MAX(transaction_date), MIN(transaction_date)) + 1 as days
        FROM transactions_out
        WHERE sparepart_id=$id AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ")->fetch_assoc();

    $total_qty = $r['total_qty'];
    $days      = max(1, $r['days']);
    $daily_avg = $total_qty / $days;
    $weekly    = round($daily_avg * 7, 1);
    $days_left = $daily_avg > 0 ? round($sp['stok'] / $daily_avg) : 999;
    $restok_needed = ($sp['stok'] <= $sp['min_stok']);

    $predictions[] = [
        'id'          => $id,
        'nama'        => $sp['merk_tipe_motor'] . ' – ' . $sp['spek'],
        'stok'        => $sp['stok'],
        'min_stok'    => $sp['min_stok'],
        'weekly_avg'  => $weekly,
        'days_left'   => $days_left,
        'restok'      => $restok_needed,
    ];
}

// Count stats
$total       = $conn->query("SELECT COUNT(id) as t FROM spareparts")->fetch_assoc()['t'];
$kritis      = $conn->query("SELECT COUNT(id) as t FROM spareparts WHERE stok <= min_stok")->fetch_assoc()['t'];
$aman        = $total - $kritis;
$habis       = $conn->query("SELECT COUNT(id) as t FROM spareparts WHERE stok = 0")->fetch_assoc()['t'];
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Statistik & Prediksi Restok – Siska Maju Motor</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
<?php require_once 'includes/sidebar.php'; ?>
<div class="main-wrapper">
    <header class="top-header">
        <div class="header-left">
            <button class="hamburger-btn" id="hamburgerBtn"><span class="hb-line"></span><span class="hb-line"></span><span class="hb-line"></span></button>
            <div>
                <div class="page-title">Statistik & Prediksi Restok</div>
                <div class="breadcrumb">Menu Utama / <span>Statistik</span></div>
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

        <!-- Summary -->
        <div class="summary-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:1.5rem">
            <div class="summary-card"><div class="card-info"><h3>Total Sparepart</h3><p data-count="<?= $total ?>">0</p></div><div class="card-icon icon-blue"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg></div></div>
            <div class="summary-card"><div class="card-info"><h3>Stok Aman</h3><p data-count="<?= $aman ?>" style="color:var(--success)">0</p></div><div class="card-icon icon-green"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div></div>
            <div class="summary-card"><div class="card-info"><h3>Stok Kritis</h3><p data-count="<?= $kritis ?>" style="color:var(--warning)">0</p></div><div class="card-icon icon-orange"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div></div>
            <div class="summary-card"><div class="card-info"><h3>Stok Habis</h3><p data-count="<?= $habis ?>" style="color:var(--danger)">0</p></div><div class="card-icon icon-red"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div></div>
        </div>

        <!-- Grafik Stok (Bar Visual) -->
        <div class="content-card" style="margin-bottom:1.5rem">
            <div class="card-header">
                <h2><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>Grafik Stok Sparepart</h2>
                <span style="font-size:.78rem;color:var(--text-muted)">5 item terendah stok</span>
            </div>
            <div class="card-body">
                <?php
                $spareparts->data_seek(0);
                $bars = [];
                $i = 0;
                while ($row = $spareparts->fetch_assoc()) {
                    if ($i >= 8) break;
                    $bars[] = $row;
                    $i++;
                }
                $max_stok = max(1, max(array_column($bars, 'stok')));
                ?>
                <div style="display:flex;align-items:flex-end;gap:10px;height:180px;padding-top:.5rem;overflow-x:auto">
                    <?php foreach ($bars as $b):
                        $h_pct = round($b['stok'] / $max_stok * 100);
                        $color = $b['stok'] == 0 ? 'var(--danger)' : ($b['stok'] <= $b['min_stok'] ? 'var(--warning)' : 'var(--success)');
                        $label = strlen($b['spek']) > 12 ? substr($b['spek'],0,12).'…' : $b['spek'];
                    ?>
                    <div class="bar-item" style="min-width:50px;">
                        <div class="bar-val"><?= $b['stok'] ?></div>
                        <div class="bar-fill" data-height="<?= $h_pct ?>%" style="background:<?= $color ?>;height:0;border-radius:6px 6px 0 0;transition:height .8s ease;width:100%"></div>
                        <div class="bar-label" style="font-size:.65rem;text-align:center;color:var(--text-muted)"><?= htmlspecialchars($label) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex;gap:1rem;margin-top:1rem;font-size:.78rem;">
                    <span style="display:flex;align-items:center;gap:.3rem"><span style="width:12px;height:12px;background:var(--success);border-radius:3px;display:inline-block"></span>Aman</span>
                    <span style="display:flex;align-items:center;gap:.3rem"><span style="width:12px;height:12px;background:var(--warning);border-radius:3px;display:inline-block"></span>Kritis</span>
                    <span style="display:flex;align-items:center;gap:.3rem"><span style="width:12px;height:12px;background:var(--danger);border-radius:3px;display:inline-block"></span>Habis</span>
                </div>
            </div>
        </div>

        <!-- Tabel Prediksi Restok -->
        <div class="content-card">
            <div class="card-header">
                <h2><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Prediksi Kebutuhan Restok</h2>
                <span style="font-size:.78rem;color:var(--text-muted)">Berdasarkan rata-rata penjualan 30 hari terakhir</span>
            </div>
            <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Sparepart</th><th>Stok Saat Ini</th><th>Min Stok</th><th>Avg/Minggu</th><th>Sisa Hari</th><th>Status Stok</th><th>Rekomendasi</th></tr>
                </thead>
                <tbody>
                <?php if (!empty($predictions)): foreach ($predictions as $p):
                    if ($p['stok'] == 0) { $s_cls='badge-danger'; $s_lbl='Habis'; }
                    elseif ($p['restok'])  { $s_cls='badge-warning'; $s_lbl='Kritis'; }
                    else                   { $s_cls='badge-success'; $s_lbl='Aman'; }

                    $pct = $p['min_stok'] > 0 ? min(100, round($p['stok']/$p['min_stok']*100)) : 100;
                    $pct_color = $p['stok'] == 0 ? 'var(--danger)' : ($p['restok'] ? 'var(--warning)' : 'var(--success)');

                    if ($p['stok'] == 0)      $rec = '⚠️ Segera restok sekarang!';
                    elseif ($p['restok'])     $rec = '🔔 Rencanakan restok segera';
                    elseif ($p['days_left'] < 14) $rec = '📋 Restok dalam 2 minggu';
                    else                       $rec = '✅ Stok cukup';
                ?>
                <tr>
                    <td data-label="Sparepart"><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                    <td data-label="Stok Saat Ini">
                        <div style="display:flex; flex-direction:column; align-items:flex-end; width:50%;">
                            <strong><?= $p['stok'] ?></strong>
                            <div class="progress-track" style="margin-top:4px; height:5px; width:100%;">
                                <div class="progress-fill" style="width:<?= $pct ?>%;background:<?= $pct_color ?>"></div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Min Stok"><?= $p['min_stok'] ?></td>
                    <td data-label="Avg/Minggu"><?= $p['weekly_avg'] > 0 ? $p['weekly_avg'].' unit' : '<span style="color:var(--text-muted)">-</span>' ?></td>
                    <td data-label="Sisa Hari"><?= $p['days_left'] >= 999 ? '<span style="color:var(--text-muted)">-</span>' : $p['days_left'].' hari' ?></td>
                    <td data-label="Status Stok"><span class="badge <?= $s_cls ?>"><?= $s_lbl ?></span></td>
                    <td data-label="Rekomendasi" style="font-size:.82rem"><?= $rec ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7"><div class="empty-state"><p>Belum ada data sparepart.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>

    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
