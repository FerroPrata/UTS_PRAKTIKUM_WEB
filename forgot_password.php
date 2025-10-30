<?php
// .
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    
    if (empty($email)) {
        $error = "Email harus diisi!";
    } elseif (!validateEmail($email)) {
        $error = "Format email tidak valid!";
    } else {
        $conn = getDBConnection();
        
        // Cek apakah email terdaftar
        $stmt = $conn->prepare("SELECT id, nama_lengkap, is_active FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if ($row['is_active'] != 1) {
                $error = "Akun Anda belum aktif. Silakan aktifkan akun terlebih dahulu.";
            } else {
                // Generate reset token
                $reset_token = generateToken();
                $reset_token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token berlaku 1 jam
                
                // Update token reset di database
                $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                $update_stmt->bind_param("ssi", $reset_token, $reset_token_expiry, $row['id']);
                
                if ($update_stmt->execute()) {
                    // Kirim email reset password
                    $reset_link = BASE_URL . "reset_password.php?token=" . $reset_token;
                    
                    $email_subject = "Reset Password - Admin Gudang";
                    $email_message = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                .header { background-color: #f44336; color: white; padding: 20px; text-align: center; }
                                .content { background-color: #f9f9f9; padding: 20px; }
                                .button { display: inline-block; padding: 12px 24px; background-color: #f44336; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                                .warning { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 15px 0; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h2>Reset Password</h2>
                                </div>
                                <div class='content'>
                                    <p>Halo <strong>{$row['nama_lengkap']}</strong>,</p>
                                    <p>Kami menerima permintaan untuk me-reset password akun Anda. Untuk melanjutkan, silakan klik tombol di bawah ini:</p>
                                    <center>
                                        <a href='{$reset_link}' class='button'>Reset Password</a>
                                    </center>
                                    <p>Atau copy dan paste link berikut di browser Anda:</p>
                                    <p><a href='{$reset_link}'>{$reset_link}</a></p>
                                    <div class='warning'>
                                        <p><strong>⚠️ Penting:</strong></p>
                                        <ul>
                                            <li>Link ini berlaku selama 1 jam</li>
                                            <li>Link ini hanya dapat digunakan satu kali</li>
                                            <li>Jika Anda tidak meminta reset password, abaikan email ini</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class='footer'>
                                    <p>&copy; 2025 Admin Gudang System. All rights reserved.</p>
                                </div>
                            </div>
                        </body>
                        </html>
                    ";
                    
                    if (sendEmail($email, $email_subject, $email_message)) {
                        $success = "Link reset password telah dikirim ke email Anda. Silakan cek inbox atau folder spam.";
                    } else {
                        $error = "Gagal mengirim email. Silakan coba lagi atau hubungi administrator.";
                    }
                } else {
                    $error = "Terjadi kesalahan. Silakan coba lagi.";
                }
                
                $update_stmt->close();
            }
        } else {
            // Untuk keamanan, tampilkan pesan yang sama meskipun email tidak terdaftar
            $success = "Jika email terdaftar, link reset password akan dikirim ke email Anda.";
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Lupa Password</title>
</head>
<body>

<h1>Lupa Password</h1>

Masukkan email Anda untuk menerima link reset password

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
Link reset password akan dikirim ke email Anda
<br>
Link berlaku selama 1 jam

<hr>

<form method="POST" action="">

Email
<br>
<input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

<br><br>

<button type="submit">Kirim Link Reset</button>

</form>

<hr>

<a href="login.php">Kembali ke Login</a>

</body>
</html>
