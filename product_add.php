<?php
// .
require_once 'config.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_produk = sanitize($_POST['kode_produk']);
    $nama_produk = sanitize($_POST['nama_produk']);
    $kategori = sanitize($_POST['kategori']);
    $harga = sanitize($_POST['harga']);
    $stok = sanitize($_POST['stok']);
    $deskripsi = sanitize($_POST['deskripsi']);
    
    // Validasi input
    if (empty($kode_produk) || empty($nama_produk) || empty($kategori) || empty($harga) || empty($stok)) {
        $error = "Semua field wajib diisi kecuali deskripsi!";
    } elseif (!is_numeric($harga) || $harga < 0) {
        $error = "Harga harus berupa angka positif!";
    } elseif (!is_numeric($stok) || $stok < 0) {
        $error = "Stok harus berupa angka positif!";
    } else {
        $conn = getDBConnection();
        
        // Cek apakah kode produk sudah ada
        $stmt = $conn->prepare("SELECT id FROM products WHERE kode_produk = ?");
        $stmt->bind_param("s", $kode_produk);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Kode produk sudah digunakan! Gunakan kode lain.";
        } else {
            // Insert produk baru
            $stmt = $conn->prepare("INSERT INTO products (kode_produk, nama_produk, kategori, harga, stok, deskripsi, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdisi", $kode_produk, $nama_produk, $kategori, $harga, $stok, $deskripsi, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                setFlashMessage('success', 'Produk berhasil ditambahkan!');
                redirect('dashboard.php');
            } else {
                $error = "Terjadi kesalahan saat menambahkan produk.";
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
<title>Tambah Produk</title>
</head>
<body>

<h1>Admin Gudang</h1>

<a href="dashboard.php">Dashboard</a> | <a href="products.php">Produk</a> | <a href="logout.php">Logout</a>

<hr>

Tambah Produk Baru

<hr>

<?php if ($error): ?>
<?php echo $error; ?>
<hr>
<?php endif; ?>

<form method="POST" action="">

Kode Produk
<br>
<input type="text" name="kode_produk" required value="<?php echo isset($_POST['kode_produk']) ? htmlspecialchars($_POST['kode_produk']) : ''; ?>">

<br><br>

Kategori
<br>
<select name="kategori" required>
<option value="">Pilih Kategori</option>
<option value="Elektronik" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Elektronik') ? 'selected' : ''; ?>>Elektronik</option>
<option value="Pakaian" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Pakaian') ? 'selected' : ''; ?>>Pakaian</option>
<option value="Makanan" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Makanan') ? 'selected' : ''; ?>>Makanan</option>
<option value="Minuman" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Minuman') ? 'selected' : ''; ?>>Minuman</option>
<option value="Alat Tulis" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Alat Tulis') ? 'selected' : ''; ?>>Alat Tulis</option>
<option value="Furniture" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Furniture') ? 'selected' : ''; ?>>Furniture</option>
<option value="Lainnya" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
</select>

<br><br>

Nama Produk
<br>
<input type="text" name="nama_produk" required value="<?php echo isset($_POST['nama_produk']) ? htmlspecialchars($_POST['nama_produk']) : ''; ?>">

<br><br>

Harga (Rp)
<br>
<input type="number" name="harga" required min="0" step="0.01" value="<?php echo isset($_POST['harga']) ? htmlspecialchars($_POST['harga']) : ''; ?>">

<br><br>

Stok
<br>
<input type="number" name="stok" required min="0" value="<?php echo isset($_POST['stok']) ? htmlspecialchars($_POST['stok']) : ''; ?>">

<br><br>

Deskripsi
<br>
<textarea name="deskripsi" rows="5" cols="50"><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>

<br><br>

<button type="submit">Simpan Produk</button>
<a href="products.php">Batal</a>

</form>

</body>
</html>
