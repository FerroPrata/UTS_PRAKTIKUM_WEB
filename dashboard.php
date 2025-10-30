<?php
require_once 'config.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    redirect('login.php');
}

// Ambil data user
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT nama_lengkap, email FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Ambil jumlah produk
$product_count = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];

// Ambil data produk
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$conn->close();

// Ambil flash message jika ada
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
</head>
<body>

<h1>Admin Gudang - Dashboard</h1>

User: <?php echo htmlspecialchars($user['nama_lengkap']); ?> | <a href="profile.php">Profil</a> | <a href="change_password.php">Ubah Password</a> | <a href="logout.php">Logout</a>

<hr>

<?php if ($flash): ?>
<?php echo $flash['type']; ?>: <?php echo $flash['message']; ?>
<hr>
<?php endif; ?>

<h2>Selamat Datang, <?php echo htmlspecialchars($user['nama_lengkap']); ?></h2>

<hr>

<h3>Statistik</h3>

Total Produk: <?php echo $product_count; ?>

<hr>

<h2>Kelola Produk</h2>

<a href="product_add.php">Tambah Produk Baru</a>

<hr>

<h3>Daftar Produk</h3>

<?php if (count($products) > 0): ?>
<table>
<tr>
<td>Kode Produk</td>
<td>Nama Produk</td>
<td>Kategori</td>
<td>Harga</td>
<td>Stok</td>
<td>Aksi</td>
</tr>
<?php foreach ($products as $product): ?>
<tr>
<td><?php echo htmlspecialchars($product['kode_produk']); ?></td>
<td><?php echo htmlspecialchars($product['nama_produk']); ?></td>
<td><?php echo htmlspecialchars($product['kategori']); ?></td>
<td>Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></td>
<td><?php echo $product['stok']; ?> unit</td>
<td>
<a href="product_edit.php?id=<?php echo $product['id']; ?>">Edit</a>
|
<a href="product_delete.php?id=<?php echo $product['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">Hapus</a>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
Belum ada produk. Silakan tambah produk baru.
<?php endif; ?>

</body>
</html>
