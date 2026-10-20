<?php
session_start();
require_once '../config/koneksi.php';
requireLogin();

// Data kota di Kepulauan Bangka Belitung dengan kode BMKG
$kotaBabel = [
    "Pangkal Pinang" => ["display" => "Pangkal Pinang", "bmkg_id" => "501190"],
    "Bangka" => ["display" => "Bangka", "bmkg_id" => "501196"],
    "Bangka Barat" => ["display" => "Bangka Barat", "bmkg_id" => "501197"],
    "Bangka Selatan" => ["display" => "Bangka Selatan", "bmkg_id" => "501198"],
    "Bangka Tengah" => ["display" => "Bangka Tengah", "bmkg_id" => "501199"],
    "Belitung" => ["display" => "Belitung", "bmkg_id" => "501200"],
    "Belitung Timur" => ["display" => "Belitung Timur", "bmkg_id" => "501201"]
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Info Cuaca - AgriTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .weather-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }

        .weather-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .current-weather {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border-radius: 15px;
        }

        .weather-icon-lg {
            font-size: 5rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .temp-display {
            font-size: 4rem;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .info-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }

        .info-card i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .search-btn {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.4);
        }

        .bmkg-badge {
            background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
    </style>
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
                    <li class="nav-item"><a class="nav-link active" href="cuaca.php"><i class="fas fa-cloud-sun"></i> Info Cuaca</a></li>
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

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3>
                    <i class="fas fa-cloud-sun text-primary me-2"></i> Informasi Cuaca
                </h3>
                <p class="text-muted mb-0">Kepulauan Bangka Belitung</p>
            </div>
            <span class="bmkg-badge">
                <i class="fas fa-satellite-dish me-1"></i> Data BMKG
            </span>
        </div>

        <!-- Info Alert -->
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Info:</strong> Data cuaca diambil langsung dari BMKG (Badan Meteorologi, Klimatologi, dan Geofisika). Data diperbarui setiap 3 jam.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Location Selector -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-map-marker-alt text-primary me-2"></i>Pilih Kabupaten/Kota
                </h5>
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Kabupaten/Kota</label>
                        <select id="kotaSelect" class="form-select form-select-lg">
                            <option value="">-- Pilih Kabupaten/Kota --</option>
                            <?php foreach ($kotaBabel as $key => $data): ?>
                                <option value="<?= $key ?>" data-bmkg-id="<?= $data['bmkg_id'] ?>">
                                    <?= $data['display'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button id="loadCuacaBtn" class="btn search-btn w-100" onclick="loadCuaca()" disabled>
                            <i class="fas fa-search me-2"></i> Tampilkan Cuaca
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="text-center my-5" style="display:none;">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 fw-bold">Mengambil data dari BMKG...</p>
            <div class="progress mt-2" style="height: 5px; max-width: 300px; margin: 0 auto;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)"></div>
            </div>
        </div>

        <!-- Error Alert -->
        <div id="errorAlert" class="alert alert-danger alert-dismissible fade" style="display:none;">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <span id="errorMessage"></span>
            <button type="button" class="btn-close" onclick="hideError()"></button>
        </div>

        <!-- Weather Display -->
        <div id="weatherContainer" style="display:none;">
            <!-- Current Weather -->
            <div class="card current-weather mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h4 class="mb-0">
                                    <i class="fas fa-location-dot me-2"></i>
                                    <span id="cityName">-</span>
                                </h4>
                                <button class="btn btn-light btn-sm" onclick="loadCuaca()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <div id="weatherIcon" class="weather-icon-lg me-4"></div>
                                <div>
                                    <h1 id="suhuDisplay" class="temp-display mb-0">-°C</h1>
                                    <h5 id="kondisiCuaca" class="mb-1">-</h5>
                                    <small class="opacity-75">Terasa seperti <span id="feelsLike">-</span>°C</small>
                                </div>
                            </div>
                            <div class="opacity-75">
                                <i class="fas fa-clock me-1"></i>
                                <span id="waktuUpdate">-</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="info-card">
                                        <i class="fas fa-tint text-primary"></i>
                                        <h6 class="text-dark">Kelembapan</h6>
                                        <h4 class="mb-0 text-dark" id="kelembapan">-</h4>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-card">
                                        <i class="fas fa-wind text-success"></i>
                                        <h6 class="text-dark">Kec. Angin</h6>
                                        <h4 class="mb-0 text-dark" id="kecepatanAngin">-</h4>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-card">
                                        <i class="fas fa-compass text-warning"></i>
                                        <h6 class="text-dark">Arah Angin</h6>
                                        <h4 class="mb-0 text-dark" id="arahAngin">-</h4>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-card">
                                        <i class="fas fa-gauge text-danger"></i>
                                        <h6 class="text-dark">Tekanan</h6>
                                        <h4 class="mb-0 text-dark" id="tekanan">-</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Forecast -->
            <div class="card mb-4">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                    <i class="fas fa-calendar-alt me-2"></i> Prakiraan 5 Hari
                </div>
                <div class="card-body">
                    <div class="row g-3" id="forecastContainer"></div>
                </div>
            </div>

            <!-- Agricultural Tips -->
            <div class="card">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                    <i class="fas fa-leaf me-2"></i> Rekomendasi Pertanian
                </div>
                <div class="card-body">
                    <div id="weatherTips"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const kotaSelect = document.getElementById('kotaSelect');
        const loadBtn = document.getElementById('loadCuacaBtn');

        kotaSelect.addEventListener('change', function() {
            loadBtn.disabled = !this.value;
        });

        async function loadCuaca() {
            const selectedOption = kotaSelect.options[kotaSelect.selectedIndex];
            const kotaKey = selectedOption.value;
            const kotaName = selectedOption.textContent;

            if (!kotaKey) {
                showError('Silakan pilih kabupaten/kota terlebih dahulu');
                return;
            }

            document.getElementById('loadingIndicator').style.display = 'block';
            document.getElementById('weatherContainer').style.display = 'none';
            hideError();

            try {
                const apiUrl = `../api/cuaca_proxy.php?kota=${encodeURIComponent(kotaKey)}`;

                const response = await fetch(apiUrl);

                if (!response.ok) {
                    throw new Error(`HTTP error ${response.status}`);
                }

                const responseData = await response.json();

                if (!responseData.success) {
                    throw new Error(responseData.error || 'Data tidak ditemukan');
                }

                displayWeatherData(responseData, kotaName);

            } catch (error) {
                console.error('Error:', error);
                showError(error.message || 'Gagal memuat data cuaca. Menggunakan data simulasi.');

                // Gunakan fallback data
                useFallbackData(kotaName);
            } finally {
                document.getElementById('loadingIndicator').style.display = 'none';
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Fungsi fallback jika API gagal
        function useFallbackData(cityName) {
            const fallbackData = {
                current: {
                    temp: Math.floor(Math.random() * (32 - 28 + 1)) + 28,
                    feels_like: Math.floor(Math.random() * (34 - 30 + 1)) + 30,
                    humidity: Math.floor(Math.random() * (85 - 70 + 1)) + 70,
                    condition: ['Cerah', 'Cerah Berawan', 'Berawan'][Math.floor(Math.random() * 3)],
                    wind_speed: Math.floor(Math.random() * (15 - 5 + 1)) + 5,
                    wind_dir: ['T', 'TL', 'TG', 'S'][Math.floor(Math.random() * 4)],
                    pressure: Math.floor(Math.random() * (1015 - 1008 + 1)) + 1008
                },
                forecast: []
            };

            for (let i = 0; i < 5; i++) {
                fallbackData.forecast.push({
                    date: new Date(Date.now() + i * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
                    condition: ['Cerah', 'Cerah Berawan', 'Berawan', 'Hujan Ringan'][Math.floor(Math.random() * 4)],
                    temp_max: Math.floor(Math.random() * (34 - 31 + 1)) + 31,
                    temp_min: Math.floor(Math.random() * (26 - 23 + 1)) + 23,
                    humidity: Math.floor(Math.random() * (90 - 65 + 1)) + 65
                });
            }

            displayWeatherData(fallbackData, cityName);
        }

        function displayWeatherData(data, cityName) {
            // Update dengan nama kota dari data jika tersedia
            const displayCity = data.city || cityName;
            document.getElementById('cityName').textContent = displayCity;

            // Data current
            document.getElementById('suhuDisplay').textContent = data.current.temp + '°C';
            document.getElementById('kondisiCuaca').textContent = data.current.condition;
            document.getElementById('feelsLike').textContent = data.current.feels_like;
            document.getElementById('kelembapan').textContent = data.current.humidity + '%';
            document.getElementById('kecepatanAngin').textContent = data.current.wind_speed + ' km/j';
            document.getElementById('arahAngin').textContent = data.current.wind_dir;
            document.getElementById('tekanan').textContent = data.current.pressure + ' hPa';

            // Waktu update
            const now = new Date();
            document.getElementById('waktuUpdate').textContent =
                now.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

            setWeatherIcon(data.current.condition);
            displayForecast(data.forecast);
            displayTips(data.current);

            document.getElementById('weatherContainer').style.display = 'block';
        }

        function setWeatherIcon(condition) {
            let iconClass = 'fa-cloud';
            let colorClass = 'text-white';

            if (condition.includes('Cerah') && !condition.includes('Berawan')) {
                iconClass = 'fa-sun';
                colorClass = 'text-warning';
            } else if (condition.includes('Cerah Berawan')) {
                iconClass = 'fa-cloud-sun';
                colorClass = 'text-warning';
            } else if (condition.includes('Berawan')) {
                iconClass = 'fa-cloud';
            } else if (condition.includes('Hujan Lebat') || condition.includes('Petir')) {
                iconClass = 'fa-cloud-bolt';
            } else if (condition.includes('Hujan')) {
                iconClass = 'fa-cloud-rain';
            }

            document.getElementById('weatherIcon').innerHTML =
                `<i class="fas ${iconClass} ${colorClass}"></i>`;
        }

        function displayForecast(forecast) {
            const container = document.getElementById('forecastContainer');
            container.innerHTML = '';

            forecast.forEach((day, index) => {
                const dateObj = new Date(day.date);
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const dayName = index === 0 ? 'Hari Ini' : days[dateObj.getDay()];

                const icon = getWeatherIcon(day.condition);

                container.innerHTML += `
                    <div class="col">
                        <div class="card weather-card h-100">
                            <div class="card-body text-center p-3">
                                <h6 class="mb-1">${dayName}</h6>
                                <small class="text-muted">${dateObj.getDate()}/${dateObj.getMonth() + 1}</small>
                                <div class="my-3">
                                    <i class="fas ${icon.icon} ${icon.color}" style="font-size: 2.5rem;"></i>
                                </div>
                                <p class="mb-2 fw-bold">${day.condition}</p>
                                <div class="d-flex justify-content-center gap-2 mb-2">
                                    <span class="badge bg-danger">${day.temp_max}°C</span>
                                    <span class="badge bg-primary">${day.temp_min}°C</span>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-tint"></i> ${day.humidity}%
                                </small>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        function getWeatherIcon(condition) {
            if (condition.includes('Cerah') && !condition.includes('Berawan')) {
                return {
                    icon: 'fa-sun',
                    color: 'text-warning'
                };
            } else if (condition.includes('Cerah Berawan')) {
                return {
                    icon: 'fa-cloud-sun',
                    color: 'text-warning'
                };
            } else if (condition.includes('Hujan Lebat') || condition.includes('Petir')) {
                return {
                    icon: 'fa-cloud-bolt',
                    color: 'text-danger'
                };
            } else if (condition.includes('Hujan')) {
                return {
                    icon: 'fa-cloud-rain',
                    color: 'text-primary'
                };
            }
            return {
                icon: 'fa-cloud',
                color: 'text-secondary'
            };
        }

        function displayTips(data) {
            const tips = [];

            if (data.condition.includes('Hujan')) {
                tips.push('🌧️ Tunda penyemprotan pestisida');
                tips.push('💧 Periksa saluran drainase');
                tips.push('🍄 Waspadai serangan jamur');
            } else if (data.condition.includes('Cerah')) {
                tips.push('☀️ Ideal untuk penyemprotan');
                tips.push('💦 Lakukan penyiraman rutin');
                tips.push('🌱 Pantau kelembaban tanah');
            } else {
                tips.push('⛅ Kondisi normal untuk aktivitas');
                tips.push('📋 Lakukan perawatan rutin');
            }

            if (data.temp > 32) {
                tips.push('🔥 Tambah frekuensi penyiraman');
                tips.push('🌳 Berikan naungan pada tanaman');
            }

            if (data.humidity > 80) {
                tips.push('💧 Tingkatkan sirkulasi udara');
                tips.push('🦠 Gunakan fungisida preventif');
            }

            document.getElementById('weatherTips').innerHTML =
                tips.map(tip => `<div class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>${tip}</div>`).join('');
        }

        function showError(message) {
            const alert = document.getElementById('errorAlert');
            document.getElementById('errorMessage').textContent = message;
            alert.style.display = 'block';
            alert.classList.add('show');
        }

        function hideError() {
            const alert = document.getElementById('errorAlert');
            alert.style.display = 'none';
            alert.classList.remove('show');
        }
    </script>
</body>

</html>