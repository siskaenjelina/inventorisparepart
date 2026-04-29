<?php
require_once 'config.php';
check_login();

$msg = '';
$edit_data = null;

// Proses Tambah/Edit/Hapus
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name   = trim($_POST['name'] ?? '');
    $desc   = trim($_POST['description'] ?? '');

    if ($action === 'add' && $name !== '') {
        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?,?)");
        $stmt->bind_param('ss', $name, $desc);
        $stmt->execute();
        $msg = '<div class="alert alert-success" data-auto-dismiss><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Kategori berhasil ditambahkan!</div>';
    } elseif ($action === 'edit' && $name !== '') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
        $stmt->bind_param('ssi', $name, $desc, $id);
        $stmt->execute();
        $msg = '<div class="alert alert-success" data-auto-dismiss><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Kategori berhasil diperbarui!</div>';
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM categories WHERE id=$id");
        $msg = '<div class="alert alert-success" data-auto-dismiss><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Kategori berhasil dihapus!</div>';
    }
}

$cats = $conn->query("SELECT c.*, (SELECT COUNT(s.id) FROM spareparts s WHERE 1) as jumlah FROM categories c ORDER BY c.name ASC");
$total_cat = $conn->query("SELECT COUNT(id) as t FROM categories")->fetch_assoc()['t'];
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Kategori – Siska Maju Motor</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
<?php require_once 'includes/sidebar.php'; ?>
<div class="main-wrapper">
    <header class="top-header">
        <div class="header-left">
            <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle Menu">
                <span class="hb-line"></span><span class="hb-line"></span><span class="hb-line"></span>
            </button>
            <div>
                <div class="page-title">Kelola Kategori</div>
                <div class="breadcrumb">Manajemen / <span>Kategori</span></div>
            </div>
        </div>
        <div class="header-right">
            <button id="theme-toggle" class="theme-toggle-dash" aria-label="Toggle Dark Mode">
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
        <?= $msg ?>

        <div class="summary-grid" style="grid-template-columns: repeat(auto-fit,minmax(180px,1fr));margin-bottom:1.25rem;">
            <div class="summary-card">
                <div class="card-info"><h3>Total Kategori</h3><p data-count="<?= $total_cat ?>">0</p></div>
                <div class="card-icon icon-purple">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    Daftar Kategori
                </h2>
                <div style="display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;">
                    <div class="search-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="tableSearch" class="form-control" placeholder="Cari kategori...">
                    </div>
                    <button class="btn btn-primary" onclick="openModal('modalAdd')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Kategori
                    </button>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>#</th><th>Nama Kategori</th><th>Deskripsi</th><th>Dibuat</th><th style="text-align:center">Aksi</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($cats && $cats->num_rows > 0): $no=1; while($row=$cats->fetch_assoc()): ?>
                    <tr>
                        <td style="color:var(--text-muted)"><?= $no++ ?></td>
                        <td><span class="badge badge-primary"><?= htmlspecialchars($row['name']) ?></span></td>
                        <td style="color:var(--text-muted)"><?= htmlspecialchars($row['description'] ?: '-') ?></td>
                        <td style="color:var(--text-muted)"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                        <td>
                            <div class="action-btns" style="justify-content:center">
                                <button class="btn btn-sm btn-warning" onclick="openEditModal(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>', '<?= addslashes($row['description']) ?>')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </button>
                                <form id="del-<?= $row['id'] ?>" method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('del-<?= $row['id'] ?>')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5"><div class="empty-state"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg><p>Belum ada kategori. Tambahkan sekarang!</p></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-backdrop" id="modalAdd">
    <div class="modal">
        <div class="modal-header">
            <h3>Tambah Kategori Baru</h3>
            <button class="modal-close" onclick="closeModal('modalAdd')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label class="form-label">Nama Kategori <span style="color:var(--danger)">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="cth: Busi, Oli, Kampas Rem" required>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi singkat kategori (opsional)"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalAdd')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal-backdrop" id="modalEdit">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Kategori</h3>
            <button class="modal-close" onclick="closeModal('modalEdit')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-group">
                <label class="form-label">Nama Kategori <span style="color:var(--danger)">*</span></label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/app.js"></script>
<script>
function openEditModal(id, name, desc) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = desc;
    openModal('modalEdit');
}
</script>
</body>
</html>
