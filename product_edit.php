<?php
require_once 'config.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

// Ambil ID produk dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'ID produk tidak valid!');
    redirect('products.php');
}

$product_id = $_GET['id'];
$conn = getDBConnection();

// Ambil data produk
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    setFlashMessage('error', 'Produk tidak ditemukan!');
    redirect('products.php');
}

$product = $result->fetch_assoc();
$stmt->close();

// Proses update
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
        // Cek apakah kode produk sudah digunakan oleh produk lain
        $stmt = $conn->prepare("SELECT id FROM products WHERE kode_produk = ? AND id != ?");
        $stmt->bind_param("si", $kode_produk, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Kode produk sudah digunakan! Gunakan kode lain.";
        } else {
            // Update produk
            $stmt = $conn->prepare("UPDATE products SET kode_produk = ?, nama_produk = ?, kategori = ?, harga = ?, stok = ?, deskripsi = ? WHERE id = ?");
            $stmt->bind_param("sssdisi", $kode_produk, $nama_produk, $kategori, $harga, $stok, $deskripsi, $product_id);
            
            if ($stmt->execute()) {
                setFlashMessage('success', 'Produk berhasil diperbarui!');
                redirect('dashboard.php');
            } else {
                $error = "Terjadi kesalahan saat memperbarui produk.";
            }
        }
        
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit Produk</title>
</head>
<body>

<h1>Admin Gudang</h1>

<a href="dashboard.php">Dashboard</a> | <a href="products.php">Produk</a> | <a href="logout.php">Logout</a>

<hr>

Edit Produk

<hr>

<?php if ($error): ?>
<?php echo $error; ?>
<hr>
<?php endif; ?>

<form method="POST" action="">

Kode Produk
<br>
<input type="text" name="kode_produk" required value="<?php echo htmlspecialchars($product['kode_produk']); ?>">

<br><br>

Kategori
<br>
<select name="kategori" required>
<option value="">Pilih Kategori</option>
<option value="Elektronik" <?php echo ($product['kategori'] == 'Elektronik') ? 'selected' : ''; ?>>Elektronik</option>
<option value="Pakaian" <?php echo ($product['kategori'] == 'Pakaian') ? 'selected' : ''; ?>>Pakaian</option>
<option value="Makanan" <?php echo ($product['kategori'] == 'Makanan') ? 'selected' : ''; ?>>Makanan</option>
<option value="Minuman" <?php echo ($product['kategori'] == 'Minuman') ? 'selected' : ''; ?>>Minuman</option>
<option value="Alat Tulis" <?php echo ($product['kategori'] == 'Alat Tulis') ? 'selected' : ''; ?>>Alat Tulis</option>
<option value="Furniture" <?php echo ($product['kategori'] == 'Furniture') ? 'selected' : ''; ?>>Furniture</option>
<option value="Lainnya" <?php echo ($product['kategori'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
</select>

<br><br>

Nama Produk
<br>
<input type="text" name="nama_produk" required value="<?php echo htmlspecialchars($product['nama_produk']); ?>">

<br><br>

Harga (Rp)
<br>
<input type="number" name="harga" required min="0" step="0.01" value="<?php echo $product['harga']; ?>">

<br><br>

Stok
<br>
<input type="number" name="stok" required min="0" value="<?php echo $product['stok']; ?>">

<br><br>

Deskripsi
<br>
<textarea name="deskripsi" rows="5" cols="50"><?php echo htmlspecialchars($product['deskripsi']); ?></textarea>

<br><br>

<button type="submit">Update Produk</button>
<a href="products.php">Batal</a>

</form>

</body>
</html>
