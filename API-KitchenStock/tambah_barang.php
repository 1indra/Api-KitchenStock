<?php
include 'koneksi.php';
$response = ['status' => 0, 'message' => 'Terjadi kesalahan.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['barang_nama'] ?? '';
    $kategori = $_POST['barang_kategori'] ?? '';
    $satuan = $_POST['barang_satuan'] ?? '';
    $jumlah = (int)($_POST['barang_jumlah_stock'] ?? 0);
    $reorder = (int)($_POST['barang_reorder_level'] ?? 0);
    $tipe = $_POST['barang_tipe'] ?? '';

    $stmt = $conn->prepare("INSERT INTO barang (nama, kategori, satuan_barang, jumlah_stock, reorder_level, tipe_barang) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiis", $nama, $kategori, $satuan, $jumlah, $reorder, $tipe);

    if ($stmt->execute()) {
        $response = ['status' => 1, 'message' => 'Barang berhasil ditambahkan.'];
    } else {
        $response['message'] = 'Gagal menambahkan data: ' . $stmt->error;
    }
    $stmt->close();
}
echo json_encode($response);
$conn->close();
?>