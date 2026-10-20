<?php
session_start();
require_once 'config/koneksi.php';
requireLogin();

$id_user = $_SESSION['user_id'];

$stats = [
    'lahan'       => $conn->query("SELECT COUNT(*) as total FROM tbl_lahan WHERE status_lahan = 'aktif' AND id_user = '$id_user'")->fetch_assoc()['total'],
    'penanaman'   => $conn->query("SELECT COUNT(*) as total FROM tbl_penanaman WHERE id_user = '$id_user'")->fetch_assoc()['total'],
    'panen'       => $conn->query("SELECT COUNT(*) as total FROM tbl_panen WHERE id_user = '$id_user'")->fetch_assoc()['total'],
    'hasil_total' => $conn->query("SELECT COALESCE(SUM(hasil_panen_kg), 0) as total FROM tbl_panen WHERE id_user = '$id_user'")->fetch_assoc()['total']
];

$recent_planting = $conn->query("
    SELECT p.*, l.nama_lahan
    FROM tbl_penanaman p
    JOIN tbl_lahan l ON p.id_lahan = l.id_lahan
    WHERE p.id_user = '$id_user'
    ORDER BY p.created_at DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AgriTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- â”€â”€ Navbar â”€â”€ -->
<nav class="navbar navbar-expand-lg agri-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <span class="brand-icon"><i class="fas fa-seedling"></i></span>
            AgriTrack
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-home"></i> Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pages/lahan.php">
                        <i class="fas fa-map"></i> Data Lahan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pages/penanaman.php">
                        <i class="fas fa-leaf"></i> Penanaman
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pages/panen.php">
                        <i class="fas fa-box"></i> Data Panen
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pages/cuaca.php">
                        <i class="fas fa-cloud-sun"></i> Info Cuaca
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pages/tentang.php">
                        <i class="fas fa-info-circle"></i> Tentang
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <div class="user-pill">
                    <i class="fas fa-user-circle"></i>
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></span>
                </div>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- â”€â”€ Main Content â”€â”€ -->
<div class="container" style="max-width: 1200px;">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-start justify-content-between">
        <div>
            <h2>
                <span class="page-icon" style="background: linear-gradient(135deg,#2e7d32,#1b4332);">
                    <i class="fas fa-chart-line"></i>
                </span>
                Dashboard
            </h2>
            <p class="page-subtitle">Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></strong> - Kelola lahan pertanian Anda dengan presisi.</p>
        </div>
        <div class="d-none d-md-flex align-items-center gap-2 mt-3">
            <span style="font-size:.82rem; color:var(--gray-500);">
                <i class="fas fa-calendar-alt me-1"></i>
                <?= date('l, d F Y') ?>
            </span>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4 fade-in">
        <div class="col-6 col-xl-3">
            <div class="stat-card green">
                <div class="stat-icon"><i class="fas fa-map"></i></div>
                <div class="stat-value"><?= $stats['lahan'] ?></div>
                <div class="stat-label">Lahan Aktif</div>
                <div class="stat-trend"><i class="fas fa-circle-dot me-1"></i>Status aktif saat ini</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fas fa-leaf"></i></div>
                <div class="stat-value"><?= $stats['penanaman'] ?></div>
                <div class="stat-label">Total Penanaman</div>
                <div class="stat-trend"><i class="fas fa-circle-dot me-1"></i>Semua musim tanam</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card amber">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <div class="stat-value"><?= $stats['panen'] ?></div>
                <div class="stat-label">Total Panen</div>
                <div class="stat-trend"><i class="fas fa-circle-dot me-1"></i>Riwayat seluruh panen</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card teal">
                <div class="stat-icon"><i class="fas fa-weight"></i></div>
                <div class="stat-value"><?= number_format($stats['hasil_total'], 0, ',', '.') ?></div>
                <div class="stat-label">Hasil Panen (kg)</div>
                <div class="stat-trend"><i class="fas fa-circle-dot me-1"></i>Total berat keseluruhan</div>
            </div>
        </div>
    </div>



    <!-- Recent Planting Table -->
    <div class="agri-card fade-in mb-5">
        <div class="card-header">
            <h6>
                <span class="card-header-icon" style="background:var(--green-100); color:var(--green-700);">
                    <i class="fas fa-clock"></i>
                </span>
                Penanaman Terbaru
            </h6>
            <a href="pages/penanaman.php" style="font-size:.82rem; color:var(--green-700); font-weight:600; text-decoration:none;">
                Lihat semua <i class="fas fa-arrow-right ms-1"></i>
            </a>
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
                            <th>Tanggal Tanam</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $hasData = false;
                        while ($row = $recent_planting->fetch_assoc()):
                            $hasData = true;
                            $statusColors = [
                                'pembibitan' => 'amber',
                                'vegetatif'  => 'blue',
                                'generatif'  => 'teal',
                                'siap_panen' => 'green',
                            ];
                            $color = $statusColors[$row['status_pertumbuhan']] ?? 'gray';
                        ?>
                        <tr>
                            <td class="ps-4 row-number"><?= $no++ ?></td>
                            <td>
                                <span style="font-weight:600; color:var(--gray-800);"><?= htmlspecialchars($row['nama_lahan']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['nama_tanaman']) ?></td>
                            <td style="color:var(--gray-500);"><?= htmlspecialchars($row['varietas']) ?></td>
                            <td>
                                <i class="fas fa-calendar-alt me-1" style="color:var(--gray-400);"></i>
                                <?= date('d M Y', strtotime($row['tanggal_tanam'])) ?>
                            </td>
                            <td>
                                <span class="agri-badge <?= $color ?>">
                                    <?= ucfirst(str_replace('_', ' ', $row['status_pertumbuhan'])) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if (!$hasData): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fas fa-seedling"></i>
                                    <p>Belum ada data penanaman. <a href="pages/penanaman.php" style="color:var(--green-700);">Tambah sekarang â†’</a></p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div><!-- /container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
