<?php
// .
require_once 'config.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

// Ambil data user
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT email, nama_lengkap FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = sanitize($_POST['nama_lengkap']);
    
    if (empty($nama_lengkap)) {
        $error = "Nama lengkap harus diisi!";
    } else {
        $stmt = $conn->prepare("UPDATE users SET nama_lengkap = ? WHERE id = ?");
        $stmt->bind_param("si", $nama_lengkap, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            setFlashMessage('success', 'Profil berhasil diperbarui!');
            redirect('dashboard.php');
        } else {
            $error = "Terjadi kesalahan saat memperbarui profil.";
        }
        
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<title>Profil Saya</title>
</head>
<body>

<h1>Admin Gudang</h1>

<a href="dashboard.php">Dashboard</a> | <a href="products.php">Produk</a> | <a href="logout.php">Logout</a>

<hr>

Profil Saya

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
<input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
<br>
Email tidak dapat diubah

<br><br>

Nama Lengkap
<br>
<input type="text" name="nama_lengkap" required value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>">

<br><br>

<button type="submit">Update Profil</button>
<a href="dashboard.php">Kembali</a>

</form>

</body>
</html>
