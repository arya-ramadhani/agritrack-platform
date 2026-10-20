<?php
session_start();
require_once '../config/koneksi.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang - AgriTrack</title>
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
                <li class="nav-item"><a class="nav-link" href="panen.php"><i class="fas fa-box"></i> Data Panen</a></li>
                <li class="nav-item"><a class="nav-link" href="cuaca.php"><i class="fas fa-cloud-sun"></i> Info Cuaca</a></li>
                <li class="nav-item"><a class="nav-link active" href="tentang.php"><i class="fas fa-info-circle"></i> Tentang</a></li>
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
    <div class="page-header">
        <h2>
            <span class="page-icon" style="background:linear-gradient(135deg,#2e7d32,#1b4332);">
                <i class="fas fa-info-circle"></i>
            </span>
            Tentang AgriTrack
        </h2>
        <p class="page-subtitle">Sistem Informasi Manajemen Pertanian Presisi Berbasis Digital</p>
    </div>

    <div class="row g-4 mb-5">
        <!-- Deskripsi Aplikasi -->
        <div class="col-12">
            <div class="agri-card">
                <div class="card-header">
                    <h6>
                        <span class="card-header-icon" style="background:var(--green-100);color:var(--green-700);">
                            <i class="fas fa-seedling"></i>
                        </span>
                        Deskripsi Sistem
                    </h6>
                </div>
                <div class="card-body p-4">
                    <p style="font-size:1rem;color:var(--gray-700);line-height:1.7;">
                        <strong>AgriTrack</strong> adalah platform manajemen pertanian presisi berbasis web yang dirancang khusus untuk membantu petani, kelompok tani, dan pengelola lahan dalam mengelola dan mendokumentasikan seluruh rantai aktivitas pertanian secara digital.
                    </p>
                    <p style="font-size:1rem;color:var(--gray-700);line-height:1.7;" class="mb-0">
                        Sistem ini mengintegrasikan data geografis lahan, siklus fase tanam (pembibitan, vegetatif, generatif, hingga siap panen), kalkulasi tonase hasil panen beserta grading kualitas (Grade A, B, C), serta terhubung dengan data prakiraan cuaca real-time dari <strong>BMKG</strong> wilayah Kepulauan Bangka Belitung untuk membantu pengambilan keputusan yang lebih akurat.
                    </p>
                </div>
            </div>
        </div>

        <!-- Modul Utama -->
        <div class="col-12">
            <div class="agri-card">
                <div class="card-header">
                    <h6>
                        <span class="card-header-icon" style="background:rgba(21,101,192,0.12);color:#1565c0;">
                            <i class="fas fa-layer-group"></i>
                        </span>
                        Modul & Fitur Utama
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background:var(--green-50);border:1px solid var(--green-100);">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span style="width:36px;height:36px;border-radius:8px;background:var(--green-600);color:white;display:inline-flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-map-marked-alt"></i>
                                    </span>
                                    <h6 class="mb-0" style="color:var(--green-800);font-weight:700;">Manajemen Lahan</h6>
                                </div>
                                <p class="mb-0" style="font-size:.85rem;color:var(--gray-600);line-height:1.5;">
                                    Pendataan aset lahan, koordinat lokasi, luas hektar, karakteristik jenis tanah (Alluvial, Laterit, Vulkanik, Organosol), dan status keaktifan lahan.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background:rgba(21,101,192,0.06);border:1px solid rgba(21,101,192,0.15);">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span style="width:36px;height:36px;border-radius:8px;background:#1565c0;color:white;display:inline-flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-leaf"></i>
                                    </span>
                                    <h6 class="mb-0" style="color:#0d47a1;font-weight:700;">Siklus Penanaman</h6>
                                </div>
                                <p class="mb-0" style="font-size:.85rem;color:var(--gray-600);line-height:1.5;">
                                    Monitoring tahapan tanam dari persemaian bibit, pencatatan varietas bibit unggul, estimasi kalender tanam, hingga kesiapan panen.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background:rgba(230,81,0,0.06);border:1px solid rgba(230,81,0,0.15);">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span style="width:36px;height:36px;border-radius:8px;background:#e65100;color:white;display:inline-flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-box"></i>
                                    </span>
                                    <h6 class="mb-0" style="color:#bf360c;font-weight:700;">Produksi & Panen</h6>
                                </div>
                                <p class="mb-0" style="font-size:.85rem;color:var(--gray-600);line-height:1.5;">
                                    Pencatatan berat bersih panen dalam kilogram, grading standar kualitas mutu (Grade A/B/C), catatan panen, serta ekspor laporan ke Excel & PDF.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Kampus / Akademik -->
        <div class="col-12">
            <div class="agri-card" style="background:linear-gradient(135deg,#1b4332 0%,#2d6a4f 100%);color:white;border:none;">
                <div class="card-body text-center p-4">
                    <span style="font-size:2rem;display:inline-block;margin-bottom:.5rem;">🎓</span>
                    <h5 style="font-weight:700;color:white;margin-bottom:.35rem;">Proyek Aplikasi Pertanian Presisi</h5>
                    <small style="color:rgba(255,255,255,0.7);font-size:.82rem;">
                        © <?= date('Y') ?> AgriTrack. Politeknik Manufaktur Negeri Bangka Belitung.
                    </small>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>