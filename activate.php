<?php
require_once 'config.php';

$message = '';
$message_type = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $conn = getDBConnection();
    
    // Cari user dengan token aktivasi
    $stmt = $conn->prepare("SELECT id, email, is_active FROM users WHERE activation_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['is_active'] == 1) {
            $message = "Akun Anda sudah aktif sebelumnya. Silakan login.";
            $message_type = "info";
        } else {
            // Aktifkan akun dan hapus token
            $update_stmt = $conn->prepare("UPDATE users SET is_active = 1, activation_token = NULL WHERE id = ?");
            $update_stmt->bind_param("i", $row['id']);
            
            if ($update_stmt->execute()) {
                $message = "Selamat! Akun Anda berhasil diaktifkan. Silakan login untuk melanjutkan.";
                $message_type = "success";
            } else {
                $message = "Terjadi kesalahan saat mengaktifkan akun. Silakan hubungi administrator.";
                $message_type = "error";
            }
            
            $update_stmt->close();
        }
    } else {
        $message = "Token aktivasi tidak valid atau sudah digunakan.";
        $message_type = "error";
    }
    
    $stmt->close();
    $conn->close();
} else {
    $message = "Token aktivasi tidak ditemukan.";
    $message_type = "error";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Aktivasi Akun</title>
</head>
<body>

<h1>
<?php 
if ($message_type == 'success') {
    echo 'Aktivasi Berhasil';
} elseif ($message_type == 'error') {
    echo 'Aktivasi Gagal';
} else {
    echo 'Informasi';
}
?>
</h1>

<hr>

<?php echo $message; ?>

<hr>

<?php if ($message_type == 'success' || $message_type == 'info'): ?>
<a href="login.php">Login Sekarang</a>
<?php else: ?>
<a href="register.php">Registrasi Ulang</a> | <a href="login.php">Ke Halaman Login</a>
<?php endif; ?>

</body>
</html>
