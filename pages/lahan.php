<?php
session_start();
require_once '../config/koneksi.php';
requireLogin();

$id_user = $_SESSION['user_id'];

$query = "SELECT * FROM tbl_lahan WHERE id_user = '$id_user' ORDER BY created_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Lahan - AgriTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg agri-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="../dashboard.php">
            <span class="brand-icon"><i class="fas fa-seedling"></i></span>
            AgriTrack
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="../dashboard.php"><i class="fas fa-home"></i> Beranda</a></li>
                <li class="nav-item"><a class="nav-link active" href="lahan.php"><i class="fas fa-map"></i> Data Lahan</a></li>
                <li class="nav-item"><a class="nav-link" href="penanaman.php"><i class="fas fa-leaf"></i> Penanaman</a></li>
                <li class="nav-item"><a class="nav-link" href="panen.php"><i class="fas fa-box"></i> Data Panen</a></li>
                <li class="nav-item"><a class="nav-link" href="cuaca.php"><i class="fas fa-cloud-sun"></i> Info Cuaca</a></li>
                <li class="nav-item"><a class="nav-link" href="tentang.php"><i class="fas fa-info-circle"></i> Tentang</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <div class="user-pill">
                    <i class="fas fa-user-circle"></i>
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></span>
                </div>
                <a href="../logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container" style="max-width:1200px;">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-start justify-content-between">
        <div>
            <h2>
                <span class="page-icon" style="background:linear-gradient(135deg,#2e7d32,#1b4332);">
                    <i class="fas fa-map"></i>
                </span>
                Data Lahan Pertanian
            </h2>
            <p class="page-subtitle">Kelola dan pantau semua lahan pertanian Anda</p>
        </div>
        <div class="d-flex align-items-center gap-2 mt-3">
            <a href="../export/lahan/excel_export.php" class="btn-export-excel">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="../export/lahan/pdf_export.php" class="btn-export-pdf">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            <button class="btn-agri-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus"></i> Tambah Lahan
            </button>
        </div>
    </div>

    <div id="alertContainer" class="mb-3"></div>

    <!-- Table Card -->
    <div class="agri-card mb-5">
        <div class="card-header">
            <h6>
                <span class="card-header-icon" style="background:var(--green-100);color:var(--green-700);">
                    <i class="fas fa-list"></i>
                </span>
                Daftar Lahan
            </h6>
            <span style="font-size:.8rem;color:var(--gray-400);">
                <?php
                $total = $conn->query("SELECT COUNT(*) as t FROM tbl_lahan WHERE id_user='$id_user'")->fetch_assoc()['t'];
                echo "$total lahan terdaftar";
                ?>
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="agri-table">
                    <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Nama Lahan</th>
                            <th>Lokasi</th>
                            <th>Luas (Ha)</th>
                            <th>Jenis Tanah</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php
                        $no = 1;
                        $hasData = false;
                        while ($row = $result->fetch_assoc()):
                            $hasData = true;
                        ?>
                        <tr>
                            <td class="ps-4 row-number"><?= $no++ ?></td>
                            <td><span style="font-weight:600;color:var(--gray-800);"><?= htmlspecialchars($row['nama_lahan']) ?></span></td>
                            <td>
                                <i class="fas fa-map-marker-alt me-1" style="color:var(--gray-400);font-size:.8rem;"></i>
                                <?= htmlspecialchars($row['lokasi']) ?>
                            </td>
                            <td><strong><?= number_format($row['luas_hektar'], 2) ?></strong> Ha</td>
                            <td><?= htmlspecialchars($row['jenis_tanah']) ?></td>
                            <td>
                                <span class="agri-badge <?= $row['status_lahan'] == 'aktif' ? 'green' : 'gray' ?>">
                                    <i class="fas fa-circle" style="font-size:.5rem;"></i>
                                    <?= ucfirst($row['status_lahan']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn-icon edit" onclick="editData(<?= $row['id_lahan'] ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon delete" onclick="deleteData(<?= $row['id_lahan'] ?>)" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if (!$hasData): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-map"></i>
                                    <p>Belum ada lahan. <a href="#" onclick="document.getElementById('modalTambahTrigger').click()" style="color:var(--green-700);">Tambah sekarang â†’</a></p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header agri-modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Tambah Data Lahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lahan</label>
                        <input type="text" name="nama_lahan" class="form-control" placeholder="Contoh: Lahan Sawah A" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi</label>
                        <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Desa Tanjung, Bangka" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Luas (Hektar)</label>
                        <input type="number" step="0.01" name="luas_hektar" class="form-control" placeholder="Contoh: 2.50" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Tanah</label>
                        <select name="jenis_tanah" class="form-select" required>
                            <option value="">Pilih Jenis Tanah</option>
                            <option value="Alluvial">Alluvial</option>
                            <option value="Laterit">Laterit</option>
                            <option value="Vulkanik">Vulkanik</option>
                            <option value="Organosol">Organosol</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Lahan</label>
                        <select name="status_lahan" class="form-select" required>
                            <option value="aktif">Aktif</option>
                            <option value="tidak_aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-agri-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header agri-modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Data Lahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit">
                <input type="hidden" name="id_lahan" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lahan</label>
                        <input type="text" name="nama_lahan" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi</label>
                        <input type="text" name="lokasi" id="edit_lokasi" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Luas (Hektar)</label>
                        <input type="number" step="0.01" name="luas_hektar" id="edit_luas" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Tanah</label>
                        <select name="jenis_tanah" id="edit_jenis" class="form-select" required>
                            <option value="Alluvial">Alluvial</option>
                            <option value="Laterit">Laterit</option>
                            <option value="Vulkanik">Vulkanik</option>
                            <option value="Organosol">Organosol</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Lahan</label>
                        <select name="status_lahan" id="edit_status" class="form-select" required>
                            <option value="aktif">Aktif</option>
                            <option value="tidak_aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-agri-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('formTambah').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'create');
        fetch('../controller/lahan_crud.php', { method: 'POST', body: formData })
            .then(r => r.json()).then(data => {
                if (data.success) {
                    showAlert('success', '<i class="fas fa-check-circle me-2"></i>Data berhasil ditambahkan!');
                    bootstrap.Modal.getInstance(document.getElementById('modalTambah')).hide();
                    setTimeout(() => location.reload(), 1400);
                } else {
                    showAlert('danger', 'Gagal menambahkan data!');
                }
            });
    });

    document.getElementById('formEdit').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'update');
        fetch('../controller/lahan_crud.php', { method: 'POST', body: formData })
            .then(r => r.json()).then(data => {
                if (data.success) {
                    showAlert('success', '<i class="fas fa-check-circle me-2"></i>Data berhasil diupdate!');
                    bootstrap.Modal.getInstance(document.getElementById('modalEdit')).hide();
                    setTimeout(() => location.reload(), 1400);
                } else {
                    showAlert('danger', 'Gagal mengupdate data!');
                }
            });
    });

    function editData(id) {
        fetch(`../controller/lahan_crud.php?action=read&id=${id}`)
            .then(r => r.json()).then(data => {
                document.getElementById('edit_id').value = data.id_lahan;
                document.getElementById('edit_nama').value = data.nama_lahan;
                document.getElementById('edit_lokasi').value = data.lokasi;
                document.getElementById('edit_luas').value = data.luas_hektar;
                document.getElementById('edit_jenis').value = data.jenis_tanah;
                document.getElementById('edit_status').value = data.status_lahan;
                new bootstrap.Modal(document.getElementById('modalEdit')).show();
            });
    }

    function deleteData(id) {
        if (!confirm('Yakin ingin menghapus lahan ini?')) return;
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id_lahan', id);
        fetch('../controller/lahan_crud.php', { method: 'POST', body: formData })
            .then(r => r.json()).then(data => {
                if (data.success) {
                    showAlert('success', '<i class="fas fa-check-circle me-2"></i>Data berhasil dihapus!');
                    setTimeout(() => location.reload(), 1400);
                } else {
                    showAlert('danger', 'Gagal menghapus data!');
                }
            });
    }

    function showAlert(type, message) {
        document.getElementById('alertContainer').innerHTML =
            `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
    }
</script>
</body>
</html>
