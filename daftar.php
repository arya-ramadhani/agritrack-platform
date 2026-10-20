<?php
session_start();
require_once 'config/koneksi.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$errors = [];
$success = '';

// Proses pendaftaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = escape($_POST['username']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $nama_lengkap = escape($_POST['nama_lengkap']);
    $email = escape($_POST['email']);

    // Validasi
    if (empty($username)) {
        $errors[] = 'Username harus diisi';
    }

    if (empty($password)) {
        $errors[] = 'Password harus diisi';
    }

    if ($password !== $password_confirm) {
        $errors[] = 'Konfirmasi password tidak sesuai';
    }

    if (empty($nama_lengkap)) {
        $errors[] = 'Nama lengkap harus diisi';
    }


    // Cek apakah username sudah ada
    $check_query = "SELECT * FROM tbl_users WHERE username = '$username'";
    $check_result = $conn->query($check_query);
    if ($check_result->num_rows > 0) {
        $errors[] = 'Username sudah digunakan';
    }

    // Cek email jika diisi
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    }

    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        $query = "INSERT INTO tbl_users (username, password, nama_lengkap, email) 
                  VALUES ('$username', '$password', '$nama_lengkap', '$email')";

        if ($conn->query($query)) {
            header('Location: ' . BASE_URL . '/login.php?success=1');
            exit;
        } else {
            $errors[] = 'Terjadi kesalahan: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - AgriTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/daftar.css">
</head>

<body>
    <div class="container-fluid">
        <div class="auth-container">
            <div class="auth-card">
                <div class="row g-0">
                    <!-- Bagian Kiri: Form Daftar -->
                    <div class="col-lg-6">
                        <div class="form-section h-100">
                            <div class="form-header">
                                <h1 class="form-title">DAFTAR</h1>
                                <p class="form-subtitle">Buat akun baru untuk mulai menggunakan sistem</p>
                            </div>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <div>
                                        <?php foreach ($errors as $error): ?>
                                            <div><?= $error ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="form-grid">
                                    <!-- Nama Lengkap -->
                                    <div class="form-group">
                                        <label class="form-label field-required">Nama Lengkap</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-user-tag"></i>
                                            </span>
                                            <input type="text" name="nama_lengkap" class="form-control" required
                                                placeholder="Masukkan nama lengkap">
                                        </div>
                                    </div>

                                    <!-- Username -->
                                    <div class="form-group">
                                        <label class="form-label field-required">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <input type="text" name="username" class="form-control" required
                                                placeholder="Masukkan username">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-grid">
                                    <!-- Password -->
                                    <div class="form-group">
                                        <label class="form-label field-required">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" name="password" id="password" class="form-control" required
                                                placeholder="Buat password">
                                            <button type="button" class="btn password-toggle" onclick="togglePassword('password')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div id="passwordStrength" class="password-strength"></div>
                                        <div class="password-rules">
                                            <small>Kombinasi huruf, angka, dan simbol</small>
                                        </div>
                                    </div>

                                    <!-- Confirm Password -->
                                    <div class="form-group">
                                        <label class="form-label field-required">Ulangi Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" name="password_confirm" id="passwordConfirm" class="form-control" required
                                                placeholder="Konfirmasi password">
                                            <button type="button" class="btn password-toggle" onclick="togglePassword('passwordConfirm')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email (full width) -->
                                <div class="form-group form-full-width">
                                    <label class="form-label field-required">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" name="email" class="form-control" required
                                            placeholder="contoh@email.com">
                                    </div>
                                </div>

                                

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-signup">
                                        <i class="fas fa-user-plus me-2"></i>DAFTAR
                                    </button>
                                </div>
                            </form>

                            <div class="login-link">
                                <span>Sudah punya akun?</span>
                                <a href="login.php">MASUK</a>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian Kanan: Logo dan Deskripsi -->
                    <div class="col-lg-6">
                        <div class="logo-section h-100">
                            <i class="fas fa-seedling logo-icon"></i>
                            <div class="logo-text">AgriTrack</div>
                            <p class="logo-description">
                                Yuk, bergabung dengan AgriTrack dan mulai kelola pertanian Anda dengan sistem digital yang mudah, aman, dan terpercaya.
                            </p>

                            <div class="features-list">
                                <div class="feature-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Gratis untuk selamanya</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Keamanan data terjamin</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-mobile-alt"></i>
                                    <span>Akses dari perangkat apapun</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleBtn = document.querySelector(`[onclick="togglePassword('${inputId}')"] i`);

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

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthText = document.getElementById('passwordStrength');

            let strength = 0;
            let text = '';
            let className = '';

            if (password.length === 0) {
                text = '';
            } else if (password.length < 6) {
                text = 'Password lemah';
                className = 'strength-weak';
            } else if (password.length < 8) {
                text = 'Password cukup';
                className = 'strength-medium';
            } else {
                // Check for character variety
                const hasUpper = /[A-Z]/.test(password);
                const hasLower = /[a-z]/.test(password);
                const hasNumbers = /\d/.test(password);
                const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);

                const criteriaMet = [hasUpper, hasLower, hasNumbers, hasSpecial].filter(Boolean).length;

                if (criteriaMet >= 3) {
                    text = 'Password kuat';
                    className = 'strength-strong';
                } else if (criteriaMet >= 2) {
                    text = 'Password cukup';
                    className = 'strength-medium';
                } else {
                    text = 'Password lemah';
                    className = 'strength-weak';
                }
            }

            strengthText.textContent = text;
            strengthText.className = `password-strength ${className}`;
        });
    </script>
</body>

</html>