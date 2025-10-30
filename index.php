<?php
require_once 'config.php';

// Redirect ke halaman login jika belum login.
if (isLoggedIn()) {
    redirect('dashboard.php');
} else {
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Gudang</title>
</head>
<body>

<h1>Admin Gudang</h1>

Sistem Manajemen Pengguna dan Produk Gudang

<p>
Kelola inventaris dengan mudah dan efisien
</p>

<hr>

<a href="login.php">Login</a>
|
<a href="register.php">Registrasi</a>

</body>
</html>
