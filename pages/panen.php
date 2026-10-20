<?php
session_start();
require_once '../config/koneksi.php';
requireLogin();

$id_user = $_SESSION['user_id'];

$query = "
    SELECT p.*, pn.nama_tanaman, pn.varietas, l.nama_lahan
    FROM tbl_panen p
    JOIN tbl_penanaman pn ON p.id_penanaman = pn.id_penanaman
    JOIN tbl_lahan l ON pn.id_lahan = l.id_lahan
    WHERE p.id_user = '$id_user'
    ORDER BY p.created_at DESC
";
$result = $conn->query($query);

// Ambil data penanaman untuk dropdown
$penanaman_query = "
    SELECT pn.id_penanaman, pn.nama_tanaman, pn.varietas, l.nama_lahan
    FROM tbl_penanaman pn
    JOIN tbl_lahan l ON pn.id_lahan = l.id_lahan
    WHERE pn.id_user = '$id_user'
    AND pn.status_pertumbuhan IN ('generatif', 'siap_panen')
    ORDER BY l.nama_lahan, pn.nama_tanaman
";
$penanaman_result = $conn->query($penanaman_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Panen - AgriTrack</title>
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
                <li class="nav-item"><a class="nav-link" href="lahan.php"><i class="fas fa-map"></i> Data Lahan</a></li>
                <li class="nav-item"><a class="nav-link" href="penanaman.php"><i class="fas fa-leaf"></i> Penanaman</a></li>
                <li class="nav-item"><a class="nav-link active" href="panen.php"><i class="fas fa-box"></i> Data Panen</a></li>
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
                <span class="page-icon" style="background:linear-gradient(135deg,#e65100,#bf360c);">
                    <i class="fas fa-box"></i>
                </span>
                Data Hasil Panen
            </h2>
            <p class="page-subtitle">Pencatatan produksi, tonase panen, dan kualitas hasil pertanian</p>
        </div>
        <div class="d-flex align-items-center gap-2 mt-3">
            <a href="../export/panen/excel_export.php?type=panen" class="btn-export-excel">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="../export/panen/pdf_export.php?type=panen" class="btn-export-pdf">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            <button class="btn-agri-primary" style="background:linear-gradient(135deg,#e65100,#bf360c)" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus"></i> Tambah Panen
            </button>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer" class="mb-3"></div>

    <!-- Table Card -->
    <div class="agri-card mb-5">
        <div class="card-header">
            <h6>
                <span class="card-header-icon" style="background:rgba(230,81,0,0.12);color:#e65100;">
                    <i class="fas fa-tractor"></i>
                </span>
                Riwayat Panen
            </h6>
            <span style="font-size:.8rem;color:var(--gray-400);">
                <?php
                $total_panen = $conn->query("SELECT COUNT(*) as t FROM tbl_panen WHERE id_user='$id_user'")->fetch_assoc()['t'];
                echo "$total_panen data panen";
                ?>
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="agri-table">
                    <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Lahan</th>
                            <th>Tanaman</th>
                            <th>Varietas</th>
                            <th>Tanggal Panen</th>
                            <th>Hasil (kg)</th>
                            <th>Kualitas</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php
                        if ($result && $result->num_rows > 0):
                            $no = 1;
                            while ($row = $result->fetch_assoc()):
                                $gradeColors = ['A' => 'green', 'B' => 'blue', 'C' => 'amber'];
                                $badgeColor = $gradeColors[$row['kualitas']] ?? 'gray';
                        ?>
                        <tr>
                            <td class="ps-4 row-number"><?= $no++ ?></td>
                            <td><span style="font-weight:600;color:var(--gray-800);"><?= htmlspecialchars($row['nama_lahan']) ?></span></td>
                            <td><span style="font-weight:500;"><?= htmlspecialchars($row['nama_tanaman']) ?></span></td>
                            <td style="color:var(--gray-500);"><?= htmlspecialchars($row['varietas']) ?></td>
                            <td>
                                <i class="fas fa-calendar-alt me-1" style="color:var(--gray-400);font-size:.8rem;"></i>
                                <?= date('d/m/Y', strtotime($row['tanggal_panen'])) ?>
                            </td>
                            <td><strong style="color:var(--green-700);font-size:1.05rem;"><?= number_format($row['hasil_panen_kg'], 2) ?></strong> kg</td>
                            <td>
                                <span class="agri-badge <?= $badgeColor ?>">
                                    <i class="fas fa-award" style="font-size:.65rem;"></i>
                                    Grade <?= htmlspecialchars($row['kualitas']) ?>
                                </span>
                            </td>
                            <td style="color:var(--gray-600);font-size:.85rem;">
                                <?= htmlspecialchars(substr($row['catatan'] ?? '', 0, 30)) ?><?= strlen($row['catatan'] ?? '') > 30 ? '...' : '' ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn-icon edit" onclick="editData(<?= $row['id_panen'] ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon delete" onclick="deleteData(<?= $row['id_panen'] ?>)" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fas fa-box-open"></i>
                                    <p>Belum ada data panen yang tercatat. Silakan klik tombol <strong>Tambah Panen</strong> di atas.</p>
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
            <div class="modal-header agri-modal-header" style="background:linear-gradient(135deg,#e65100,#bf360c);">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-1"></i> Tambah Data Panen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <?php if ($penanaman_result && $penanaman_result->num_rows > 0): ?>
                        <div class="mb-3">
                            <label class="form-label">Pilih Penanaman <span class="text-danger">*</span></label>
                            <select name="id_penanaman" class="form-select" required>
                                <option value="">-- Pilih Penanaman Siap Panen --</option>
                                <?php
                                mysqli_data_seek($penanaman_result, 0);
                                while ($pn = $penanaman_result->fetch_assoc()):
                                ?>
                                    <option value="<?= $pn['id_penanaman'] ?>">
                                        <?= htmlspecialchars($pn['nama_lahan']) ?> — <?= htmlspecialchars($pn['nama_tanaman']) ?> (<?= htmlspecialchars($pn['varietas']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <small class="text-muted" style="font-size:.78rem;">Hanya menampilkan tanaman berstatus Generatif atau Siap Panen</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Panen <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_panen" class="form-control" required max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hasil Panen (kg) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="hasil_panen_kg" class="form-control" required min="0.01" placeholder="Contoh: 150.50">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kualitas <span class="text-danger">*</span></label>
                            <select name="kualitas" class="form-select" required>
                                <option value="A">Grade A — Premium (Kualitas Terbaik)</option>
                                <option value="B" selected>Grade B — Standar (Kualitas Baik)</option>
                                <option value="C">Grade C — Rendah (Kualitas Cukup)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="3" placeholder="Tambahkan catatan hasil panen, kondisi buah/daun..."></textarea>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Tidak ada tanaman yang siap dipanen. Pastikan ada data penanaman dengan status "Generatif" atau "Siap Panen".
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <?php if ($penanaman_result && $penanaman_result->num_rows > 0): ?>
                        <button type="submit" class="btn-agri-primary" style="background:linear-gradient(135deg,#e65100,#bf360c);">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header agri-modal-header" style="background:linear-gradient(135deg,#e65100,#bf360c);">
                <h5 class="modal-title"><i class="fas fa-edit me-1"></i> Edit Data Panen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit">
                <input type="hidden" name="id_panen" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Penanaman <span class="text-danger">*</span></label>
                        <select name="id_penanaman" id="edit_penanaman" class="form-select" required>
                            <?php
                            mysqli_data_seek($penanaman_result, 0);
                            while ($pn = $penanaman_result->fetch_assoc()):
                            ?>
                                <option value="<?= $pn['id_penanaman'] ?>">
                                    <?= htmlspecialchars($pn['nama_lahan']) ?> — <?= htmlspecialchars($pn['nama_tanaman']) ?> (<?= htmlspecialchars($pn['varietas']) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Panen <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_panen" id="edit_tanggal" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hasil Panen (kg) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="hasil_panen_kg" id="edit_hasil" class="form-control" required min="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kualitas <span class="text-danger">*</span></label>
                        <select name="kualitas" id="edit_kualitas" class="form-select" required>
                            <option value="A">Grade A — Premium</option>
                            <option value="B">Grade B — Standar</option>
                            <option value="C">Grade C — Rendah</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" id="edit_catatan" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-agri-primary" style="background:linear-gradient(135deg,#e65100,#bf360c);">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    /* ================== FORM TAMBAH ================== */
    document.getElementById('formTambah').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'create');

        fetch('../controller/panen_crud.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', '<i class="fas fa-check-circle me-1"></i> Data panen berhasil ditambahkan!');
                    bootstrap.Modal.getInstance(document.getElementById('modalTambah')).hide();
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showAlert('danger', data.message || 'Gagal menambahkan data');
                }
            })
            .catch(err => {
                showAlert('danger', 'Error: ' + err.message);
            });
    });

    /* ================== FORM EDIT ================== */
    document.getElementById('formEdit').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'update');

        fetch('../controller/panen_crud.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', '<i class="fas fa-check-circle me-1"></i> Data panen berhasil diperbarui!');
                    bootstrap.Modal.getInstance(document.getElementById('modalEdit')).hide();
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showAlert('danger', data.message || 'Gagal update data');
                }
            })
            .catch(err => {
                showAlert('danger', 'Error: ' + err.message);
            });
    });

    /* ================== EDIT DATA ================== */
    function editData(id) {
        fetch(`../controller/panen_crud.php?action=read&id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (!data || !data.id_panen) {
                    showAlert('danger', 'Data tidak ditemukan');
                    return;
                }

                document.getElementById('edit_id').value = data.id_panen;
                document.getElementById('edit_penanaman').value = data.id_penanaman;
                document.getElementById('edit_tanggal').value = data.tanggal_panen;
                document.getElementById('edit_hasil').value = data.hasil_panen_kg;
                document.getElementById('edit_kualitas').value = data.kualitas;
                document.getElementById('edit_catatan').value = data.catatan || '';

                new bootstrap.Modal(document.getElementById('modalEdit')).show();
            })
            .catch(err => {
                showAlert('danger', 'Gagal memuat data');
            });
    }

    /* ================== DELETE DATA ================== */
    function deleteData(id) {
        if (!confirm('Yakin ingin menghapus data panen ini?')) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id_panen', id);

        fetch('../controller/panen_crud.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', '<i class="fas fa-check-circle me-1"></i> Data berhasil dihapus!');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showAlert('danger', data.message || 'Gagal menghapus data');
                }
            })
            .catch(err => {
                showAlert('danger', 'Error: ' + err.message);
            });
    }

    /* ================== ALERT ================== */
    function showAlert(type, message) {
        document.getElementById('alertContainer').innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }
</script>
</body>
</html>