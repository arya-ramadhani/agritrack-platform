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

// Data grafik panen dimuat via AJAX (api/chart_panen.php) agar filter bulan bisa dinamis

// Distribusi status pertumbuhan penanaman aktif
$status_query = $conn->query("
    SELECT status_pertumbuhan, COUNT(*) AS jumlah
    FROM tbl_penanaman
    WHERE id_user = '$id_user'
    GROUP BY status_pertumbuhan
");
$status_labels = [];
$status_data   = [];
$status_color_map = [
    'pembibitan' => '#f59e0b',
    'vegetatif'  => '#3b82f6',
    'generatif'  => '#14b8a6',
    'siap_panen' => '#22c55e',
];
$status_colors = [];
while ($row = $status_query->fetch_assoc()) {
    $status_labels[] = ucfirst(str_replace('_', ' ', $row['status_pertumbuhan']));
    $status_data[]   = (int)$row['jumlah'];
    $status_colors[] = $status_color_map[$row['status_pertumbuhan']] ?? '#6b7280';
}
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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


    <!-- Charts Row -->
    <div class="row g-4 mb-5 fade-in">


        <!-- Bar Chart: Hasil Panen per Bulan -->
        <div class="col-12 col-lg-7">
            <div class="agri-card h-100">
                <div class="card-header" style="flex-wrap:wrap; gap:.5rem;">
                    <h6 style="margin:0;">
                        <span class="card-header-icon" style="background:var(--green-100); color:var(--green-700);">
                            <i class="fas fa-chart-bar"></i>
                        </span>
                        <span id="chartTitle">Hasil Panen 6 Bulan Terakhir</span>
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <!-- Dropdown filter periode -->
                        <select id="periodSelect" onchange="loadChart(parseInt(this.value))"
                            style="padding:5px 10px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:.78rem; font-weight:600; color:#374151; background:#fff; cursor:pointer; outline:none; appearance:auto;">
                            <option value="3">3 Bulan</option>
                            <option value="6" selected>6 Bulan</option>
                            <option value="12">12 Bulan</option>
                        </select>
                        <a href="pages/panen.php" style="font-size:.82rem; color:var(--green-700); font-weight:600; text-decoration:none; white-space:nowrap;">
                            Lihat semua <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body" style="padding: 1.25rem; position:relative; min-height:160px;">
                    <div id="chartBarEmpty" class="empty-state" style="display:none;">
                        <i class="fas fa-chart-bar"></i>
                        <p>Belum ada data panen dalam periode ini. <a href="pages/panen.php" style="color:var(--green-700);">Tambah sekarang →</a></p>
                    </div>
                    <div id="chartBarLoading" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.7);border-radius:12px;z-index:5;">
                        <div style="text-align:center; color:#64748b;">
                            <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                            <div style="font-size:.8rem;">Memuat data...</div>
                        </div>
                    </div>
                    <canvas id="chartPanenBulanan" style="max-height:280px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Doughnut Chart: Status Pertumbuhan -->
        <div class="col-12 col-lg-5">
            <div class="agri-card h-100">
                <div class="card-header">
                    <h6>
                        <span class="card-header-icon" style="background:var(--blue-100,#dbeafe); color:var(--blue-700,#1d4ed8);">
                            <i class="fas fa-chart-pie"></i>
                        </span>
                        Distribusi Status Tanaman
                    </h6>
                    <a href="pages/penanaman.php" style="font-size:.82rem; color:var(--green-700); font-weight:600; text-decoration:none;">
                        Lihat semua <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center" style="padding:1.25rem;">
                    <?php if (empty($status_data)): ?>
                    <div class="empty-state">
                        <i class="fas fa-seedling"></i>
                        <p>Belum ada data penanaman. <a href="pages/penanaman.php" style="color:var(--green-700);">Tambah sekarang →</a></p>
                    </div>
                    <?php else: ?>
                    <canvas id="chartStatusTanaman" style="max-height:230px; max-width:230px;"></canvas>
                    <div class="mt-3 d-flex flex-wrap gap-2 justify-content-center" id="statusLegend"></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <script>
    // ── Bar Chart (AJAX + filter periode) ────────────────────────────
    let barChartInstance = null;
    const periodeLabels  = { 3: '3 Bulan Terakhir', 6: '6 Bulan Terakhir', 12: '12 Bulan Terakhir' };

    function loadChart(bulan) {
        document.getElementById('chartTitle').textContent = 'Hasil Panen ' + (periodeLabels[bulan] || bulan + ' Bulan Terakhir');
        document.getElementById('chartBarLoading').style.display = 'flex';
        document.getElementById('chartBarEmpty').style.display   = 'none';
        document.getElementById('chartPanenBulanan').style.display = 'none';

        fetch('api/chart_panen.php?bulan=' + bulan)
            .then(r => r.json())
            .then(json => {
                document.getElementById('chartBarLoading').style.display = 'none';

                if (!json.labels || json.labels.length === 0) {
                    document.getElementById('chartBarEmpty').style.display = 'flex';
                    return;
                }

                document.getElementById('chartPanenBulanan').style.display = 'block';
                const ctx = document.getElementById('chartPanenBulanan').getContext('2d');

                if (barChartInstance) barChartInstance.destroy();

                barChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: json.labels,
                        datasets: [{
                            label: 'Hasil Panen (kg)',
                            data: json.data,
                            backgroundColor: 'rgba(34,197,94,0.18)',
                            borderColor: '#16a34a',
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                            hoverBackgroundColor: 'rgba(34,197,94,0.35)',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        animation: { duration: 500 },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ' ' + ctx.parsed.y.toLocaleString('id-ID') + ' kg',
                                    afterLabel: (ctx) => {
                                        const cnt = json.counts[ctx.dataIndex];
                                        return cnt + ' kali panen';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: { font: { size: 11 }, callback: v => v.toLocaleString('id-ID') + ' kg' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11 } }
                            }
                        }
                    }
                });
            })
            .catch(() => {
                document.getElementById('chartBarLoading').style.display = 'none';
                document.getElementById('chartBarEmpty').style.display   = 'flex';
            });
    }

    // ── Doughnut Chart (server-side) ──────────────────────────────────
    <?php if (!empty($status_data)): ?>
    const ctxDoughnut  = document.getElementById('chartStatusTanaman').getContext('2d');
    const statusLabels = <?= json_encode($status_labels) ?>;
    const statusData   = <?= json_encode($status_data) ?>;
    const statusColors = <?= json_encode($status_colors) ?>;

    new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: statusColors,
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.parsed} tanaman`
                    }
                }
            }
        }
    });

    const legendEl = document.getElementById('statusLegend');
    statusLabels.forEach((lbl, i) => {
        legendEl.innerHTML += `<span style="display:flex;align-items:center;gap:5px;font-size:.78rem;font-weight:600;color:#374151;">
            <span style="width:10px;height:10px;border-radius:50%;background:${statusColors[i]};display:inline-block;"></span>${lbl} (${statusData[i]})
        </span>`;
    });
    <?php endif; ?>

    // Muat chart pertama kali sesuai nilai dropdown (default 6 bulan)
    document.addEventListener('DOMContentLoaded', () => loadChart(parseInt(document.getElementById('periodSelect').value)));
    </script>


</div><!-- /container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
