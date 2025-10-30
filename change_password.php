<?php
require_once 'config.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

// Proses update password
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Semua field harus diisi!";
    } elseif ($new_password !== $confirm_password) {
        $error = "Password baru dan konfirmasi password tidak cocok!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password baru minimal 6 karakter!";
    } else {
        $conn = getDBConnection();
        
        // Ambil password saat ini dari database
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // Verifikasi password saat ini
        if (!password_verify($current_password, $user['password'])) {
            $error = "Password saat ini salah!";
        } else {
            // Hash password baru
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                setFlashMessage('success', 'Password berhasil diubah!');
                redirect('dashboard.php');
            } else {
                $error = "Terjadi kesalahan saat mengubah password.";
            }
            
            $stmt->close();
        }
        
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Ubah Password</title>
</head>
<body>

<h1>Admin Gudang</h1>

<a href="dashboard.php">Dashboard</a> | <a href="products.php">Produk</a> | <a href="logout.php">Logout</a>

<hr>

Ubah Password

<hr>

<?php if ($error): ?>
<?php echo $error; ?>
<hr>
<?php endif; ?>

<?php if ($success): ?>
<?php echo $success; ?>
<hr>
<?php endif; ?>

Informasi:
<br>
Password baru harus minimal 6 karakter
<br>
Gunakan kombinasi huruf, angka, dan simbol untuk keamanan lebih baik

<hr>

<form method="POST" action="">

Password Saat Ini
<br>
<input type="password" name="current_password" required>

<br><br>

Password Baru
<br>
<input type="password" name="new_password" required>

<br><br>

Konfirmasi Password Baru
<br>
<input type="password" name="confirm_password" required>

<br><br>

<button type="submit">Ubah Password</button>
<a href="dashboard.php">Batal</a>

</form>

</body>
</html>
