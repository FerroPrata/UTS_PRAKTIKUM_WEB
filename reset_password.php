<?php
// .
require_once 'config.php';

$error = '';
$success = '';
$valid_token = false;
$token = '';

// Cek apakah token ada di URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $conn = getDBConnection();
    
    // Cek apakah token valid dan belum expired
    $stmt = $conn->prepare("SELECT id, email, nama_lengkap, reset_token_expiry FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Cek apakah token sudah expired
        if (strtotime($row['reset_token_expiry']) > time()) {
            $valid_token = true;
            $user_id = $row['id'];
            $user_email = $row['email'];
            $user_name = $row['nama_lengkap'];
        } else {
            $error = "Link reset password sudah kadaluarsa. Silakan kirim ulang permintaan reset password.";
        }
    } else {
        $error = "Link reset password tidak valid atau sudah digunakan.";
    }
    
    $stmt->close();
    $conn->close();
} else {
    $error = "Token tidak ditemukan.";
}

// Proses reset password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Semua field harus diisi!";
    } elseif ($new_password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        $conn = getDBConnection();
        
        // Hash password baru
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password dan hapus reset token
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $success = "Password berhasil direset! Silakan login dengan password baru Anda.";
            $valid_token = false; // Disable form
        } else {
            $error = "Terjadi kesalahan saat mereset password.";
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>
</head>
<body>

<h1>Reset Password</h1>

Buat password baru untuk akun Anda

<hr>

<?php if ($error): ?>
<?php echo $error; ?>
<hr>
<?php endif; ?>

<?php if ($success): ?>
<?php echo $success; ?>
<br><br>
<a href="login.php">Login Sekarang</a>
<?php elseif ($valid_token): ?>

Reset password untuk: <?php echo htmlspecialchars($user_email); ?>

<hr>

<form method="POST" action="">

Password Baru
<br>
<input type="password" name="new_password" required>

<br><br>

Konfirmasi Password Baru
<br>
<input type="password" name="confirm_password" required>

<br><br>

<button type="submit">Reset Password</button>

</form>

<?php else: ?>

<a href="forgot_password.php">Kirim Ulang Link Reset</a> | <a href="login.php">Kembali ke Login</a>

<?php endif; ?>

</body>
</html>
