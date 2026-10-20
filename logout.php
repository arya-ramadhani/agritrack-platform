<?php
session_start();
require_once 'config/koneksi.php';

session_destroy();
header('Location: ' . BASE_URL . '/login.php');
exit;
?>
