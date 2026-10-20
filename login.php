<?php
session_start();
require_once 'config/koneksi.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$error = '';
$success = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = escape($_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM tbl_users WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Username atau password salah!';
    }
}

// Tampilkan pesan sukses jika ada dari daftar
if (isset($_GET['success'])) {
    $success = 'Pendaftaran berhasil! Silakan login.';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AgriTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>
    <div class="container-fluid">
        <div class="auth-container">
            <div class="auth-card">
                <div class="row g-0">
                    <!-- Bagian Kiri: Logo dan Deskripsi -->
                    <div class="col-lg-6">
                        <div class="logo-section h-100">
                            <i class="fas fa-seedling logo-icon"></i>
                            <div class="logo-text">AgriTrack</div>
                            <p class="logo-description">
                                Sistem Manajemen Pertanian Modern yang Presisi dalam Monitoring, Pengelolaan, dan Hasil Panen.
                            </p>

                            <div class="features-list">
                                <div class="feature-item">
                                    <i class="fas fa-chart-line"></i>
                                    <span>Monitoring Pertumbuhan Tanaman</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-map-marked-alt"></i>
                                    <span>Manajemen Lahan Digital</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-box"></i>
                                    <span>Tracking Hasil Panen</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-cloud-sun"></i>
                                    <span>Prediksi Cuaca BMKG</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian Kanan: Form Login -->
                    <div class="col-lg-6">
                        <div class="form-section h-100">
                            <div class="form-header">
                                <h1 class="form-title">MASUK</h1>
                                <p class="form-subtitle">Masukkan kredensial Anda untuk mengakses sistem</p>
                            </div>

                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i><?= $success ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="mb-4">
                                    <label class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" name="username" class="form-control" required
                                            placeholder="Masukkan username Anda">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" name="password" id="password" class="form-control" required
                                            placeholder="Masukkan password Anda">
                                        <button type="button" class="btn password-toggle" onclick="togglePassword()">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-login">
                                        <i class="fas fa-sign-in-alt me-2"></i>MASUK
                                    </button>
                                </div>
                            </form>

                            <div class="register-link">
                                <span>Belum punya akun?</span>
                                <a href="daftar.php">DAFTAR</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.classList.remove('fa-eye');
                toggleBtn.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleBtn.classList.remove('fa-eye-slash');
                toggleBtn.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>