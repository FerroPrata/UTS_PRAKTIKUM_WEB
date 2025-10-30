<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = sanitize($_POST['nama_lengkap']);
    
    // Validasi input
    if (empty($email) || empty($password) || empty($confirm_password) || empty($nama_lengkap)) {
        $error = "Semua field wajib diisi!";
    } elseif (!validateEmail($email)) {
        $error = "Format email tidak valid!";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        $conn = getDBConnection();
        
        // Cek apakah email sudah terdaftar
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email sudah terdaftar! Silakan gunakan email lain.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Generate activation token
            $activation_token = generateToken();
            
            // Insert user baru
            $stmt = $conn->prepare("INSERT INTO users (email, password, nama_lengkap, activation_token) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $email, $hashed_password, $nama_lengkap, $activation_token);
            
            if ($stmt->execute()) {
                // Kirim email aktivasi
                $activation_link = BASE_URL . "activate.php?token=" . $activation_token;
                
                $email_subject = "Aktivasi Akun Admin Gudang";
                $email_message = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
                            .content { background-color: #f9f9f9; padding: 20px; }
                            .button { display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                            .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2>Aktivasi Akun Admin Gudang</h2>
                            </div>
                            <div class='content'>
                                <p>Halo <strong>{$nama_lengkap}</strong>,</p>
                                <p>Terima kasih telah mendaftar sebagai Admin Gudang. Untuk mengaktifkan akun Anda, silakan klik tombol di bawah ini:</p>
                                <center>
                                    <a href='{$activation_link}' class='button'>Aktivasi Akun</a>
                                </center>
                                <p>Atau copy dan paste link berikut di browser Anda:</p>
                                <p><a href='{$activation_link}'>{$activation_link}</a></p>
                                <p>Link aktivasi ini berlaku untuk satu kali penggunaan.</p>
                                <p>Jika Anda tidak melakukan registrasi, abaikan email ini.</p>
                            </div>
                            <div class='footer'>
                                <p>&copy; 2025 Admin Gudang System. All rights reserved.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                
                if (sendEmail($email, $email_subject, $email_message)) {
                    $success = "Registrasi berhasil! Silakan cek email Anda untuk mengaktifkan akun.";
                    // Clear form
                    $_POST = array();
                } else {
                    $error = "Registrasi berhasil, tetapi gagal mengirim email aktivasi. Silakan hubungi administrator.";
                }
            } else {
                $error = "Terjadi kesalahan saat registrasi. Silakan coba lagi.";
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Registrasi</title>
</head>
<body>

<h2>Registrasi Admin Gudang</h2>

Daftar untuk mengelola sistem gudang

<hr>

<?php if ($error): ?>
<?php echo $error; ?>
<hr>
<?php endif; ?>

<?php if ($success): ?>
<?php echo $success; ?>
<hr>
<?php endif; ?>

<form method="POST" action="">

Email (Username)
<br>
<input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

<br><br>

Nama Lengkap
<br>
<input type="text" name="nama_lengkap" required value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>">

<br><br>

Password
<br>
<input type="password" name="password" required>

<br><br>

Konfirmasi Password
<br>
<input type="password" name="confirm_password" required>

<br><br>

<button type="submit">Daftar</button>

</form>

<hr>

Sudah punya akun? <a href="login.php">Login di sini</a>

</body>
</html>
