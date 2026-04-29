<?php
require_once 'config.php';
check_login();

$msg = '';

// Proses Tambah Transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sparepart_id = (int)($_POST['sparepart_id'] ?? 0);
    $quantity     = (int)($_POST['quantity'] ?? 0);
    $selling_price = (float)($_POST['selling_price'] ?? 0);
    $trx_date     = $_POST['transaction_date'] ?? date('Y-m-d');
    $mechanic     = trim($_POST['mechanic_name'] ?? '');

    if ($sparepart_id > 0 && $quantity > 0 && $selling_price > 0) {
        // Cek stok cukup
        $row = $conn->query("SELECT stok FROM spareparts WHERE id=$sparepart_id")->fetch_assoc();
        if ($row && $row['stok'] >= $quantity) {
            $stmt = $conn->prepare("INSERT INTO transactions_out (sparepart_id, quantity, selling_price, transaction_date, mechanic_name) VALUES (?,?,?,?,?)");
            $stmt->bind_param('iidss', $sparepart_id, $quantity, $selling_price, $trx_date, $mechanic);
            $stmt->execute();
            // Kurangi stok
            $conn->query("UPDATE spareparts SET stok = stok - $quantity WHERE id=$sparepart_id");
            $msg = 'success:Transaksi penjualan berhasil dicatat!';
        } else {
            $msg = 'danger:Stok tidak mencukupi!';
        }
    } else {
        $msg = 'danger:Harap isi semua field yang diperlukan!';
    }
}

list($msg_type, $msg_text) = $msg ? explode(':', $msg, 2) : ['', ''];

// Daftar sparepart untuk dropdown
$spareparts_list = $conn->query("SELECT id, merk_tipe_motor, spek, harga_jual, stok FROM spareparts ORDER BY merk_tipe_motor ASC");

// Daftar transaksi
$filter_date = $_GET['date'] ?? '';
$sql_t = "SELECT t.*, s.merk_tipe_motor, s.spek FROM transactions_out t JOIN spareparts s ON t.sparepart_id=s.id";
if ($filter_date) {
    $fd = $conn->real_escape_string($filter_date);
    $sql_t .= " WHERE t.transaction_date='$fd'";
}
$sql_t .= " ORDER BY t.created_at DESC";
$transactions = $conn->query($sql_t);

// Stats
$today_count   = $conn->query("SELECT COUNT(id) as t FROM transactions_out WHERE DATE(transaction_date)=CURDATE()")->fetch_assoc()['t'];
$today_revenue = $conn->query("SELECT COALESCE(SUM(selling_price*quantity),0) as t FROM transactions_out WHERE DATE(transaction_date)=CURDATE()")->fetch_assoc()['t'];
$month_revenue = $conn->query("SELECT COALESCE(SUM(selling_price*quantity),0) as t FROM transactions_out WHERE MONTH(transaction_date)=MONTH(NOW()) AND YEAR(transaction_date)=YEAR(NOW())")->fetch_assoc()['t'];
$total_trx     = $conn->query("SELECT COUNT(id) as t FROM transactions_out")->fetch_assoc()['t'];
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Transaksi Penjualan – Siska Maju Motor</title>
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
                <div class="page-title">Transaksi Penjualan</div>
                <div class="breadcrumb">Manajemen / <span>Transaksi</span></div>
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
        <?php if ($msg_text): ?>
        <div class="alert alert-<?= $msg_type ?>" data-auto-dismiss>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            <?= htmlspecialchars($msg_text) ?>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="summary-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:1.25rem">
            <div class="summary-card"><div class="card-info"><h3>Transaksi Hari Ini</h3><p data-count="<?= $today_count ?>">0</p></div><div class="card-icon icon-blue"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></div></div>
            <div class="summary-card"><div class="card-info"><h3>Pendapatan Hari Ini</h3><p style="font-size:1.2rem">Rp <?= number_format($today_revenue,0,'.','.') ?></p></div><div class="card-icon icon-green"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div></div>
            <div class="summary-card"><div class="card-info"><h3>Pendapatan Bulan Ini</h3><p style="font-size:1.2rem">Rp <?= number_format($month_revenue,0,'.','.') ?></p></div><div class="card-icon icon-purple"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div></div>
            <div class="summary-card"><div class="card-info"><h3>Total Transaksi</h3><p data-count="<?= $total_trx ?>">0</p></div><div class="card-icon icon-orange"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div></div>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>Riwayat Transaksi</h2>
                <div style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center">
                    <form method="GET" style="display:flex;gap:.5rem;align-items:center">
                        <input type="date" name="date" class="form-control" style="width:auto" value="<?= htmlspecialchars($filter_date) ?>" onchange="this.form.submit()">
                        <?php if($filter_date): ?><a href="transactions.php" class="btn btn-outline btn-sm">Reset</a><?php endif; ?>
                    </form>
                    <a href="reports.php" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                        Lihat Laporan
                    </a>
                    <button class="btn btn-primary" onclick="openModal('modalAdd')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Catat Transaksi
                    </button>
                </div>
            </div>
            <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Tanggal</th><th>Sparepart</th><th>Mekanik</th><th>Qty</th><th>Harga Satuan</th><th>Total</th></tr>
                </thead>
                <tbody>
                <?php if ($transactions && $transactions->num_rows > 0): $no=1; while($row=$transactions->fetch_assoc()): ?>
                <tr>
                    <td style="color:var(--text-muted)"><?= $no++ ?></td>
                    <td><?= date('d M Y', strtotime($row['transaction_date'])) ?></td>
                    <td><strong><?= htmlspecialchars($row['merk_tipe_motor']) ?></strong><br><small style="color:var(--text-muted)"><?= htmlspecialchars($row['spek']) ?></small></td>
                    <td><?= htmlspecialchars($row['mechanic_name'] ?: '-') ?></td>
                    <td><span class="badge badge-info"><?= $row['quantity'] ?></span></td>
                    <td>Rp <?= number_format($row['selling_price'],0,'.','.') ?></td>
                    <td><strong>Rp <?= number_format($row['selling_price']*$row['quantity'],0,'.','.') ?></strong></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="7"><div class="empty-state"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><p>Belum ada transaksi<?= $filter_date ? ' pada tanggal ini' : '' ?>.</p></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Catat Transaksi -->
<div class="modal-backdrop" id="modalAdd">
<div class="modal modal-lg">
    <div class="modal-header">
        <h3>Catat Transaksi Penjualan</h3>
        <button class="modal-close" onclick="closeModal('modalAdd')">&times;</button>
    </div>
    <form method="POST">
        <input type="hidden" name="action" value="add_trx">
        <div class="form-grid">
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Pilih Sparepart *</label>
                <select name="sparepart_id" id="sp_select" class="form-control" required onchange="fillHarga(this)">
                    <option value="">-- Pilih Sparepart --</option>
                    <?php
                    $spareparts_list->data_seek(0);
                    while($sp=$spareparts_list->fetch_assoc()):
                    ?>
                    <option value="<?= $sp['id'] ?>" data-harga="<?= $sp['harga_jual'] ?>" data-stok="<?= $sp['stok'] ?>">
                        <?= htmlspecialchars($sp['merk_tipe_motor'].' – '.$sp['spek']) ?> (Stok: <?= $sp['stok'] ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
                <small id="stok_info" style="color:var(--text-muted);margin-top:4px;display:block"></small>
            </div>
            <div class="form-group">
                <label class="form-label">Harga Jual (Rp) *</label>
                <input type="number" name="selling_price" id="harga_input" class="form-control" min="0" step="500" required>
            </div>
            <div class="form-group">
                <label class="form-label">Jumlah Qty *</label>
                <input type="number" name="quantity" class="form-control" min="1" value="1" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Mekanik</label>
                <input type="text" name="mechanic_name" class="form-control" placeholder="Opsional">
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Transaksi *</label>
                <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('modalAdd')">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
        </div>
    </form>
</div>
</div>

<script src="assets/js/app.js"></script>
<script>
function fillHarga(sel) {
    const opt = sel.options[sel.selectedIndex];
    const harga = opt.getAttribute('data-harga') || '';
    const stok  = opt.getAttribute('data-stok') || '';
    document.getElementById('harga_input').value = harga;
    document.getElementById('stok_info').textContent = stok ? 'Stok tersedia: ' + stok + ' unit' : '';
}
</script>
</body>
</html>
