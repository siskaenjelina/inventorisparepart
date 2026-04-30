<?php
require_once 'config.php';
check_login();

// Pastikan kolom category_id ada
$conn->query("ALTER TABLE spareparts ADD COLUMN IF NOT EXISTS category_id INT DEFAULT NULL");

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $merk = trim($_POST['merk_tipe_motor'] ?? '');
        $spek = trim($_POST['spek'] ?? '');
        $hm   = (float)($_POST['harga_modal'] ?? 0);
        $hj   = (float)($_POST['harga_jual'] ?? 0);
        $stok = (int)($_POST['stok'] ?? 0);
        $min  = (int)($_POST['min_stok'] ?? 5);
        $cat  = (int)($_POST['category_id'] ?? 0) ?: 'NULL';
        $conn->query("INSERT INTO spareparts (merk_tipe_motor,spek,harga_modal,harga_jual,stok,min_stok,category_id) VALUES ('".addslashes($merk)."','".addslashes($spek)."',$hm,$hj,$stok,$min,$cat)");
        $msg = 'success:Sparepart berhasil ditambahkan!';
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $merk = addslashes(trim($_POST['merk_tipe_motor'] ?? ''));
        $spek = addslashes(trim($_POST['spek'] ?? ''));
        $hm   = (float)($_POST['harga_modal'] ?? 0);
        $hj   = (float)($_POST['harga_jual'] ?? 0);
        $stok = (int)($_POST['stok'] ?? 0);
        $min  = (int)($_POST['min_stok'] ?? 5);
        $cat  = (int)($_POST['category_id'] ?? 0) ?: 'NULL';
        $conn->query("UPDATE spareparts SET merk_tipe_motor='$merk',spek='$spek',harga_modal=$hm,harga_jual=$hj,stok=$stok,min_stok=$min,category_id=$cat WHERE id=$id");
        $msg = 'success:Sparepart berhasil diperbarui!';
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM spareparts WHERE id=$id");
        $msg = 'success:Sparepart berhasil dihapus!';
    }
}
list($mtype,$mtext) = $msg ? explode(':',$msg,2) : ['',''];

$tab     = $_GET['tab'] ?? 'stok';
$cat_f   = (int)($_GET['cat'] ?? 0);
$search  = trim($_GET['q'] ?? '');

// Filter stok
$where = [];
if ($cat_f) $where[] = "category_id=$cat_f";
if ($search) $where[] = "(merk_tipe_motor LIKE '%".addslashes($search)."%' OR spek LIKE '%".addslashes($search)."%')";
$where_sql = $where ? 'WHERE '.implode(' AND ',$where) : '';
$spareparts = $conn->query("SELECT s.*,c.name as cat_name FROM spareparts s LEFT JOIN categories c ON s.category_id=c.id $where_sql ORDER BY s.merk_tipe_motor ASC");
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$total = $conn->query("SELECT COUNT(id) as t FROM spareparts")->fetch_assoc()['t'];
$low   = $conn->query("SELECT COUNT(id) as t FROM spareparts WHERE stok<=min_stok")->fetch_assoc()['t'];

$cats_arr = [];
$categories->data_seek(0);
while($c=$categories->fetch_assoc()) $cats_arr[] = $c;
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Data Sparepart – Siska Maju Motor</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
<style>
.tab-bar{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:1.25rem;}
.tab-btn{padding:.65rem 1.25rem;font-size:.85rem;font-weight:600;color:var(--text-muted);background:none;border:none;border-bottom:2.5px solid transparent;margin-bottom:-2px;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:.4rem;font-family:inherit;}
.tab-btn:hover{color:var(--text);}
.tab-btn.active{color:var(--primary);border-bottom-color:var(--primary);}
.tab-btn svg{width:15px;height:15px;}
.tab-pane{display:none;}.tab-pane.active{display:block;}
.filter-bar{display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;padding:.85rem 1.1rem;background:var(--table-head);border-bottom:1px solid var(--border);}
.filter-bar select.form-control{width:auto;}
.filter-bar .form-control{padding:.45rem .75rem;font-size:.82rem;}
.sp-table td small{color:var(--text-muted);font-size:.75rem;display:block;margin-top:1px;}
.stok-num{font-size:1rem;font-weight:700;letter-spacing:-.02em;}
.stok-aman{color:var(--success)}.stok-kritis{color:var(--warning)}.stok-habis{color:var(--danger)}
.kelola-toolbar{display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;padding:.85rem 1.1rem;background:var(--table-head);border-bottom:1px solid var(--border);}
.empty-row td{text-align:center;padding:2rem;color:var(--text-muted);font-size:.85rem;}
</style>
</head>
<body>
<?php require_once 'includes/sidebar.php'; ?>
<div class="main-wrapper">
<header class="top-header">
  <div class="header-left">
    <button class="hamburger-btn" id="hamburgerBtn"><span class="hb-line"></span><span class="hb-line"></span><span class="hb-line"></span></button>
    <div><div class="page-title">Data Sparepart</div><div class="breadcrumb">Manajemen / <span>Sparepart</span></div></div>
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
<?php if($mtext): ?>
<div class="alert alert-<?= $mtype ?>" data-auto-dismiss><?= htmlspecialchars($mtext) ?></div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="summary-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));margin-bottom:1.25rem">
  <div class="summary-card"><div class="card-info"><h3>Total Item</h3><p data-count="<?= $total ?>">0</p></div><div class="card-icon icon-blue"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg></div></div>
  <div class="summary-card"><div class="card-info"><h3>Stok Kritis</h3><p data-count="<?= $low ?>" style="color:var(--danger)">0</p></div><div class="card-icon icon-red"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/></svg></div></div>
  <div class="summary-card"><div class="card-info"><h3>Kategori</h3><p><?= count($cats_arr) ?></p></div><div class="card-icon icon-purple"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg></div></div>
</div>

<!-- Tab Bar -->
<div class="content-card">
  <div class="tab-bar" style="padding:0 1.1rem;">
    <button class="tab-btn <?= $tab==='stok'?'active':'' ?>" onclick="switchTab('stok',this)">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
      Lihat Stok
    </button>
    <button class="tab-btn <?= $tab==='kelola'?'active':'' ?>" onclick="switchTab('kelola',this)">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Kelola Data
    </button>
  </div>

  <!-- TAB 1: Lihat Stok -->
  <div class="tab-pane <?= $tab==='stok'?'active':'' ?>" id="tab-stok">
    <!-- Filter Bar -->
    <form method="GET" class="filter-bar">
      <input type="hidden" name="tab" value="stok">
      <div style="display:flex;gap:.6rem;flex-wrap:wrap;width:100%;align-items:center;">
        <select name="cat" class="form-control" onchange="this.form.submit()" style="flex:1;min-width:140px;">
          <option value="">Semua Kategori</option>
          <?php foreach($cats_arr as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $cat_f==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="search-wrap" style="flex:2;min-width:180px;">
          <input type="text" name="q" class="form-control" placeholder="Cari nama / spesifikasi..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div style="display:flex;gap:.4rem;">
          <button type="submit" class="btn btn-outline btn-sm">Cari</button>
          <?php if($cat_f||$search): ?><a href="spareparts.php?tab=stok" class="btn btn-outline btn-sm">Reset</a><?php endif; ?>
        </div>
      </div>
    </form>

    <div class="table-wrap">
    <table class="sp-table">
      <thead><tr><th>ID</th><th>Merk / Tipe Motor</th><th>Spesifikasi</th><th>Kategori</th><th>Harga Jual</th><th>Stok</th><th>Min Stok</th><th>Status</th></tr></thead>
      <tbody>
      <?php
      $no=1;
      if($spareparts && $spareparts->num_rows>0):
        while($row=$spareparts->fetch_assoc()):
          $cls = $row['stok']==0?'stok-habis':($row['stok']<=$row['min_stok']?'stok-kritis':'stok-aman');
          $bcls= $row['stok']==0?'badge-danger':($row['stok']<=$row['min_stok']?'badge-warning':'badge-success');
          $blbl= $row['stok']==0?'Habis':($row['stok']<=$row['min_stok']?'Kritis':'Aman');
      ?>
      <tr>
        <td data-label="ID"><?= $no++ ?></td>
        <td data-label="Merk / Tipe Motor"><strong><?= htmlspecialchars($row['merk_tipe_motor']) ?></strong></td>
        <td data-label="Spesifikasi"><?= htmlspecialchars($row['spek']) ?></td>
        <td data-label="Kategori"><?= $row['cat_name'] ? '<span class="badge badge-primary">'.htmlspecialchars($row['cat_name']).'</span>' : '<span style="color:var(--text-muted)">—</span>' ?></td>
        <td data-label="Harga Jual">Rp <?= number_format($row['harga_jual'],0,'.','.') ?></td>
        <td data-label="Stok"><span class="stok-num <?= $cls ?>"><?= $row['stok'] ?></span></td>
        <td data-label="Min Stok" style="color:var(--text-muted)"><?= $row['min_stok'] ?></td>
        <td data-label="Status"><span class="badge <?= $bcls ?>"><?= $blbl ?></span></td>
      </tr>
      <?php endwhile; else: ?>
      <tr class="empty-row"><td colspan="8">Tidak ada data yang sesuai filter.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>

  <!-- TAB 2: Kelola Data -->
  <div class="tab-pane <?= $tab==='kelola'?'active':'' ?>" id="tab-kelola">
    <!-- Toolbar Tambah -->
    <div class="kelola-toolbar">
      <span style="font-size:.85rem;font-weight:600;color:var(--text)">Kelola Data Sparepart</span>
      <div style="flex:1"></div>
      <button class="btn btn-primary btn-sm" onclick="openModal('modalAdd')">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Sparepart
      </button>
    </div>

    <div class="table-wrap">
    <table>
      <thead><tr><th>ID</th><th>Merk / Tipe Motor</th><th>Spesifikasi</th><th>Kategori</th><th>Harga Modal</th><th>Harga Jual</th><th>Stok</th><th style="text-align:center">Aksi</th></tr></thead>
      <tbody>
      <?php
      $conn->query("SELECT 1"); // reset
      $all = $conn->query("SELECT s.*,c.name as cat_name FROM spareparts s LEFT JOIN categories c ON s.category_id=c.id ORDER BY s.merk_tipe_motor ASC");
      $no=1;
      if($all && $all->num_rows>0):
        while($row=$all->fetch_assoc()):
      ?>
      <tr>
        <td data-label="ID"><?= $no++ ?></td>
        <td data-label="Merk / Tipe Motor"><strong><?= htmlspecialchars($row['merk_tipe_motor']) ?></strong></td>
        <td data-label="Spesifikasi"><?= htmlspecialchars($row['spek']) ?></td>
        <td data-label="Kategori"><?= $row['cat_name'] ? htmlspecialchars($row['cat_name']) : '<span style="color:var(--text-muted)">—</span>' ?></td>
        <td data-label="Harga Modal">Rp <?= number_format($row['harga_modal'],0,'.','.') ?></td>
        <td data-label="Harga Jual">Rp <?= number_format($row['harga_jual'],0,'.','.') ?></td>
        <td data-label="Stok"><strong><?= $row['stok'] ?></strong></td>
        <td data-label="Aksi">
          <div class="action-btns" style="justify-content:center">
            <button class="btn btn-sm btn-warning" onclick="openEdit(<?= $row['id'] ?>,'<?= addslashes($row['merk_tipe_motor']) ?>','<?= addslashes($row['spek']) ?>',<?= $row['harga_modal'] ?>,<?= $row['harga_jual'] ?>,<?= $row['stok'] ?>,<?= $row['min_stok'] ?>,<?= (int)$row['category_id'] ?>)">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Edit
            </button>
            <form id="del-<?= $row['id'] ?>" method="POST" style="display:inline">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <input type="hidden" name="tab" value="kelola">
              <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('del-<?= $row['id'] ?>')">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg> Hapus
              </button>
            </form>
          </div>
        </td>
      </tr>
      <?php endwhile; else: ?>
      <tr class="empty-row"><td colspan="8">Belum ada data sparepart. Klik "Tambah Sparepart" untuk mulai.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div><!-- end content-card -->
</div><!-- end content-area -->
</div><!-- end main-wrapper -->

<!-- Modal Tambah -->
<div class="modal-backdrop" id="modalAdd">
<div class="modal modal-lg">
  <div class="modal-header"><h3>Tambah Sparepart Baru</h3><button class="modal-close" onclick="closeModal('modalAdd')">&times;</button></div>
  <form method="POST">
    <input type="hidden" name="action" value="add">
    <input type="hidden" name="tab" value="kelola">
    <div class="form-grid">
      <div class="form-group"><label class="form-label">Merk / Tipe Motor *</label><input type="text" name="merk_tipe_motor" class="form-control" placeholder="Honda Beat" required></div>
      <div class="form-group"><label class="form-label">Spesifikasi *</label><input type="text" name="spek" class="form-control" placeholder="Busi, Kampas Rem Depan..." required></div>
      <div class="form-group"><label class="form-label">Kategori</label>
        <select name="category_id" class="form-control">
          <option value="">— Pilih Kategori —</option>
          <?php foreach($cats_arr as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Stok Awal</label><input type="number" name="stok" class="form-control" min="0" value="0"></div>
      <div class="form-group"><label class="form-label">Harga Modal (Rp) *</label><input type="number" name="harga_modal" class="form-control" min="0" step="500" required></div>
      <div class="form-group"><label class="form-label">Harga Jual (Rp) *</label><input type="number" name="harga_jual" class="form-control" min="0" step="500" required></div>
      <div class="form-group"><label class="form-label">Min Stok (Reorder Point)</label><input type="number" name="min_stok" class="form-control" min="1" value="5"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('modalAdd')">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
  </form>
</div></div>

<!-- Modal Edit -->
<div class="modal-backdrop" id="modalEdit">
<div class="modal modal-lg">
  <div class="modal-header"><h3>Edit Sparepart</h3><button class="modal-close" onclick="closeModal('modalEdit')">&times;</button></div>
  <form method="POST">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="tab" value="kelola">
    <input type="hidden" name="id" id="e_id">
    <div class="form-grid">
      <div class="form-group"><label class="form-label">Merk / Tipe Motor *</label><input type="text" name="merk_tipe_motor" id="e_merk" class="form-control" required></div>
      <div class="form-group"><label class="form-label">Spesifikasi *</label><input type="text" name="spek" id="e_spek" class="form-control" required></div>
      <div class="form-group"><label class="form-label">Kategori</label>
        <select name="category_id" id="e_cat" class="form-control">
          <option value="">— Pilih Kategori —</option>
          <?php foreach($cats_arr as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Stok</label><input type="number" name="stok" id="e_stok" class="form-control" min="0"></div>
      <div class="form-group"><label class="form-label">Harga Modal (Rp) *</label><input type="number" name="harga_modal" id="e_modal" class="form-control" min="0" step="500" required></div>
      <div class="form-group"><label class="form-label">Harga Jual (Rp) *</label><input type="number" name="harga_jual" id="e_jual" class="form-control" min="0" step="500" required></div>
      <div class="form-group"><label class="form-label">Min Stok</label><input type="number" name="min_stok" id="e_min" class="form-control" min="1"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('modalEdit')">Batal</button><button type="submit" class="btn btn-primary">Simpan Perubahan</button></div>
  </form>
</div></div>

<script src="assets/js/app.js"></script>
<script>
function switchTab(t, btn) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
  document.getElementById('tab-' + t).classList.add('active');
  (btn || document.querySelector('[onclick*="switchTab(\'' + t + '\'"]')).classList.add('active');
}
function openEdit(id, merk, spek, hm, hj, stok, min, cat) {
  document.getElementById('e_id').value    = id;
  document.getElementById('e_merk').value  = merk;
  document.getElementById('e_spek').value  = spek;
  document.getElementById('e_modal').value = hm;
  document.getElementById('e_jual').value  = hj;
  document.getElementById('e_stok').value  = stok;
  document.getElementById('e_min').value   = min;
  document.getElementById('e_cat').value   = cat || '';
  openModal('modalEdit');
}
// Restore tab from URL or POST redirect
(function() {
  const urlTab = new URLSearchParams(location.search).get('tab') || '<?= $_POST['tab'] ?? '' ?>';
  if (urlTab === 'kelola') {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-kelola').classList.add('active');
    document.querySelectorAll('.tab-btn')[1].classList.add('active');
  }
})();
</script>
</body></html>

