<?php
// .
require_once 'config.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi!";
    } elseif (!validateEmail($email)) {
        $error = "Format email tidak valid!";
    } else {
        $conn = getDBConnection();
        
        // Ambil data user berdasarkan email
        $stmt = $conn->prepare("SELECT id, email, password, nama_lengkap, is_active FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Cek apakah akun sudah aktif
            if ($row['is_active'] != 1) {
                $error = "Akun Anda belum aktif. Silakan cek email untuk aktivasi akun.";
            } else {
                // Verifikasi password
                if (password_verify($password, $row['password'])) {
                    // Login berhasil
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
                    
                    setFlashMessage('success', 'Login berhasil! Selamat datang, ' . $row['nama_lengkap']);
                    redirect('dashboard.php');
                } else {
                    $error = "Password salah!";
                }
            }
        } else {
            $error = "Email tidak terdaftar!";
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
</head>
<body>

<h1>Admin Gudang</h1>

Sistem Manajemen Gudang

<hr>

<?php if ($error): ?>
<?php echo $error; ?>
<hr>
<?php endif; ?>

<form method="POST" action="">

Email
<br>
<input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

<br><br>

Password
<br>
<input type="password" name="password" required>

<br><br>

<button type="submit">Login</button>

</form>

<hr>

<a href="forgot_password.php">Lupa Password?</a>

<p>
Belum punya akun? <a href="register.php">Daftar di sini</a>
</p>

</body>
</html>
