<?php
// .
echo "<h1>Test PHPMailer Installation</h1>";
echo "<hr>";

// 1. Cek apakah folder PHPMailer ada
echo "<h2>1. Cek Folder PHPMailer</h2>";
if (file_exists('vendor/PHPMailer/PHPMailer.php')) {
    echo "✅ PHPMailer.php ditemukan<br>";
} else {
    echo "❌ PHPMailer.php TIDAK ditemukan<br>";
}

if (file_exists('vendor/PHPMailer/SMTP.php')) {
    echo "✅ SMTP.php ditemukan<br>";
} else {
    echo "❌ SMTP.php TIDAK ditemukan<br>";
}

if (file_exists('vendor/PHPMailer/Exception.php')) {
    echo "✅ Exception.php ditemukan<br>";
} else {
    echo "❌ Exception.php TIDAK ditemukan<br>";
}

echo "<hr>";

// 2. Coba load PHPMailer
echo "<h2>2. Coba Load PHPMailer Class</h2>";
try {
    require_once 'vendor/PHPMailer/PHPMailer.php';
    require_once 'vendor/PHPMailer/SMTP.php';
    require_once 'vendor/PHPMailer/Exception.php';
    
    echo "✅ Berhasil load file PHPMailer<br>";
    
    // 3. Coba buat instance PHPMailer
    echo "<hr>";
    echo "<h2>3. Coba Buat Instance PHPMailer</h2>";
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    echo "✅ Berhasil membuat instance PHPMailer<br>";
    
    // 4. Cek method-method penting
    echo "<hr>";
    echo "<h2>4. Cek Method PHPMailer</h2>";
    
    if (method_exists($mail, 'isSMTP')) {
        echo "✅ Method isSMTP() tersedia<br>";
    }
    
    if (method_exists($mail, 'setFrom')) {
        echo "✅ Method setFrom() tersedia<br>";
    }
    
    if (method_exists($mail, 'addAddress')) {
        echo "✅ Method addAddress() tersedia<br>";
    }
    
    if (method_exists($mail, 'send')) {
        echo "✅ Method send() tersedia<br>";
    }
    
    echo "<hr>";
    echo "<h2>✅ PHPMAILER TERINSTALL DENGAN BENAR!</h2>";
    echo "<p>PHPMailer siap digunakan untuk mengirim email.</p>";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>5. Konfigurasi SMTP di config.php</h2>";

require_once 'config.php';

echo "SMTP Host: " . SMTP_HOST . "<br>";
echo "SMTP Port: " . SMTP_PORT . "<br>";
echo "SMTP User: " . SMTP_USER . "<br>";
echo "SMTP From: " . SMTP_FROM . "<br>";
echo "SMTP From Name: " . SMTP_FROM_NAME . "<br>";

echo "<hr>";
echo "<p><b>Note:</b> Untuk test kirim email, gunakan halaman registrasi atau forgot password.</p>";
echo "<p><a href='index.php'>Kembali ke Home</a></p>";
?>
