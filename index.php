<?php
session_start();
require_once 'config/koneksi.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
} else {
    header('Location: ' . BASE_URL . '/login.php');
}
exit;
?>