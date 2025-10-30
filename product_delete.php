<?php
// .
require_once 'config.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    redirect('login.php');
}

// Ambil ID produk dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'ID produk tidak valid!');
    redirect('dashboard.php');
}

$product_id = $_GET['id'];
$conn = getDBConnection();

// Cek apakah produk ada
$stmt = $conn->prepare("SELECT nama_produk FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    setFlashMessage('error', 'Produk tidak ditemukan!');
    redirect('dashboard.php');
}

$product = $result->fetch_assoc();
$stmt->close();

// Hapus produk
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
    setFlashMessage('success', 'Produk "' . $product['nama_produk'] . '" berhasil dihapus!');
} else {
    setFlashMessage('error', 'Terjadi kesalahan saat menghapus produk.');
}

$stmt->close();
$conn->close();

redirect('dashboard.php');
?>
