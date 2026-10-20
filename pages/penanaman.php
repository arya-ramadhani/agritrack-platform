<?php
session_start();
require_once '../config/koneksi.php';
requireLogin();

// Ambil id user login
$id_user = $_SESSION['user_id'];

// Data Penanaman
$query = "
    SELECT p.*, l.nama_lahan, l.lokasi 
    FROM tbl_penanaman p
    JOIN tbl_lahan l ON p.id_lahan = l.id_lahan
    WHERE p.id_user = '$id_user'
    ORDER BY p.created_at DESC
";
$result = $conn->query($query);

// Data Lahan Dropdown
$lahan_query = "
    SELECT id_lahan, nama_lahan 
    FROM tbl_lahan 
    WHERE status_lahan = 'aktif'
    AND id_user = '$id_user'
";
$lahan_result = $conn->query($lahan_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penanaman - AgriTrack</title>
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
                <li class="nav-item"><a class="nav-link active" href="penanaman.php"><i class="fas fa-leaf"></i> Penanaman</a></li>
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
                <span class="page-icon" style="background:linear-gradient(135deg,#1565c0,#0d47a1);">
                    <i class="fas fa-leaf"></i>
                </span>
                Data Penanaman
            </h2>
            <p class="page-subtitle">Pantau siklus pertumbuhan tanaman dan jadwal tanam</p>
        </div>
        <div class="d-flex align-items-center gap-2 mt-3">
            <a href="../export/penanaman/excel_export.php?type=panen" class="btn-export-excel">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="../export/penanaman/pdf_export.php?type=panen" class="btn-export-pdf">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            <button class="btn-agri-primary" style="background:linear-gradient(135deg,#1565c0,#0d47a1)" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus"></i> Tambah Penanaman
            </button>
        </div>
    </div>

    <!-- Alert -->
    <div id="alertContainer" class="mb-3"></div>

    <!-- Table Card -->
    <div class="agri-card mb-5">
        <div class="card-header">
            <h6>
                <span class="card-header-icon" style="background:rgba(21,101,192,0.12);color:#1565c0;">
                    <i class="fas fa-seedling"></i>
                </span>
                Daftar Aktivitas Penanaman
            </h6>
            <span style="font-size:.8rem;color:var(--gray-400);">
                <?php
                $total_p = $conn->query("SELECT COUNT(*) as t FROM tbl_penanaman WHERE id_user='$id_user'")->fetch_assoc()['t'];
                echo "$total_p jadwal penanaman";
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
                            <th>Nama Tanaman</th>
                            <th>Varietas</th>
                            <th>Tanggal Tanam</th>
                            <th>Jumlah Bibit</th>
                            <th>Status Pertumbuhan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php 
                        $no = 1;
                        $hasData = false;
                        $statusColors = [
                            'pembibitan' => 'amber',
                            'vegetatif'  => 'blue',
                            'generatif'  => 'teal',
                            'siap_panen' => 'green',
                        ];
                        while($row = $result->fetch_assoc()): 
                            $hasData = true;
                            $badgeColor = $statusColors[$row['status_pertumbuhan']] ?? 'gray';
                        ?>
                        <tr>
                            <td class="ps-4 row-number"><?= $no++ ?></td>
                            <td>
                                <span style="font-weight:600;color:var(--gray-800);"><?= htmlspecialchars($row['nama_lahan']) ?></span>
                            </td>
                            <td><span style="font-weight:500;"><?= htmlspecialchars($row['nama_tanaman']) ?></span></td>
                            <td style="color:var(--gray-500);"><?= htmlspecialchars($row['varietas']) ?></td>
                            <td>
                                <i class="fas fa-calendar-alt me-1" style="color:var(--gray-400);font-size:.8rem;"></i>
                                <?= date('d/m/Y', strtotime($row['tanggal_tanam'])) ?>
                            </td>
                            <td><strong><?= number_format($row['jumlah_bibit']) ?></strong> bibit</td>
                            <td>
                                <span class="agri-badge <?= $badgeColor ?>">
                                    <i class="fas fa-circle" style="font-size:.5rem;"></i>
                                    <?= ucfirst(str_replace('_', ' ', $row['status_pertumbuhan'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn-icon edit" onclick="editData(<?= $row['id_penanaman'] ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon delete" onclick="deleteData(<?= $row['id_penanaman'] ?>)" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(!$hasData): ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-leaf"></i>
                                    <p>Belum ada data penanaman. Klik tombol <strong>Tambah Penanaman</strong> di atas untuk memulai.</p>
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
            <div class="modal-header agri-modal-header" style="background:linear-gradient(135deg,#1565c0,#0d47a1);">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-1"></i> Tambah Data Penanaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lahan Pertanian</label>
                        <select name="id_lahan" class="form-select" required>
                            <option value="">-- Pilih Lahan --</option>
                            <?php 
                            mysqli_data_seek($lahan_result, 0);
                            while($lahan = $lahan_result->fetch_assoc()): 
                            ?>
                            <option value="<?= $lahan['id_lahan'] ?>"><?= htmlspecialchars($lahan['nama_lahan']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Tanaman</label>
                        <input type="text" name="nama_tanaman" class="form-control" placeholder="Contoh: Padi, Jagung, Cabai" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Varietas</label>
                        <input type="text" name="varietas" class="form-control" placeholder="Contoh: Ciherang, Hibrida" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Tanam</label>
                        <input type="date" name="tanggal_tanam" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Bibit</label>
                        <input type="number" name="jumlah_bibit" class="form-control" placeholder="Jumlah bibit/benih" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Pertumbuhan</label>
                        <select name="status_pertumbuhan" class="form-select" required>
                            <option value="pembibitan">Pembibitan</option>
                            <option value="vegetatif">Vegetatif</option>
                            <option value="generatif">Generatif</option>
                            <option value="siap_panen">Siap Panen</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-agri-primary" style="background:linear-gradient(135deg,#1565c0,#0d47a1);">
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
            <div class="modal-header agri-modal-header" style="background:linear-gradient(135deg,#1565c0,#0d47a1);">
                <h5 class="modal-title"><i class="fas fa-edit me-1"></i> Edit Data Penanaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit">
                <input type="hidden" name="id_penanaman" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lahan Pertanian</label>
                        <select name="id_lahan" id="edit_lahan" class="form-select" required>
                            <?php 
                            mysqli_data_seek($lahan_result, 0);
                            while($lahan = $lahan_result->fetch_assoc()): 
                            ?>
                            <option value="<?= $lahan['id_lahan'] ?>"><?= htmlspecialchars($lahan['nama_lahan']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Tanaman</label>
                        <input type="text" name="nama_tanaman" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Varietas</label>
                        <input type="text" name="varietas" id="edit_varietas" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Tanam</label>
                        <input type="date" name="tanggal_tanam" id="edit_tanggal" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Bibit</label>
                        <input type="number" name="jumlah_bibit" id="edit_jumlah" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Pertumbuhan</label>
                        <select name="status_pertumbuhan" id="edit_status" class="form-select" required>
                            <option value="pembibitan">Pembibitan</option>
                            <option value="vegetatif">Vegetatif</option>
                            <option value="generatif">Generatif</option>
                            <option value="siap_panen">Siap Panen</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-agri-primary" style="background:linear-gradient(135deg,#1565c0,#0d47a1);">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Form Tambah
    document.getElementById('formTambah').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'create');
        
        fetch('../controller/penanaman_crud.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showAlert('success', '<i class="fas fa-check-circle me-1"></i> Data penanaman berhasil ditambahkan!');
                bootstrap.Modal.getInstance(document.getElementById('modalTambah')).hide();
                setTimeout(() => location.reload(), 1400);
            } else {
                showAlert('danger', 'Gagal menambahkan data: ' + data.message);
            }
        });
    });

    // Form Edit
    document.getElementById('formEdit').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'update');
        
        fetch('../controller/penanaman_crud.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showAlert('success', '<i class="fas fa-check-circle me-1"></i> Data penanaman berhasil diupdate!');
                bootstrap.Modal.getInstance(document.getElementById('modalEdit')).hide();
                setTimeout(() => location.reload(), 1400);
            } else {
                showAlert('danger', 'Gagal mengupdate data!');
            }
        });
    });

    function editData(id) {
        fetch(`../controller/penanaman_crud.php?action=read&id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_id').value = data.id_penanaman;
            document.getElementById('edit_lahan').value = data.id_lahan;
            document.getElementById('edit_nama').value = data.nama_tanaman;
            document.getElementById('edit_varietas').value = data.varietas;
            document.getElementById('edit_tanggal').value = data.tanggal_tanam;
            document.getElementById('edit_jumlah').value = data.jumlah_bibit;
            document.getElementById('edit_status').value = data.status_pertumbuhan;
            new bootstrap.Modal(document.getElementById('modalEdit')).show();
        });
    }

    function deleteData(id) {
        if(confirm('Yakin ingin menghapus data penanaman ini?')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id_penanaman', id);
            
            fetch('../controller/penanaman_crud.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showAlert('success', '<i class="fas fa-check-circle me-1"></i> Data berhasil dihapus!');
                    setTimeout(() => location.reload(), 1400);
                } else {
                    showAlert('danger', 'Gagal menghapus data!');
                }
            });
        }
    }

    function showAlert(type, message) {
        const alert = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.getElementById('alertContainer').innerHTML = alert;
    }
</script>
</body>
</html>