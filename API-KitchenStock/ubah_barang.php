<?php
require_once 'koneksi.php';
header('Content-Type: application/json');
$response = array();
if (isset($_POST['barang_id']) && isset($_POST['barang_nama']) && isset($_POST['barang_kategori']) && isset($_POST['barang_satuan']) && isset($_POST['barang_jumlah_stock']) && isset($_POST['barang_reorder_level']) && isset($_POST['barang_tipe'])) {
    $id = $_POST['barang_id'];
    $nama = $_POST['barang_nama'];
    $kategori = $_POST['barang_kategori'];
    $satuan = $_POST['barang_satuan'];
    $jumlah = $_POST['barang_jumlah_stock'];
    $reorder = $_POST['barang_reorder_level'];
    $tipe = $_POST['barang_tipe'];
    $stmt = $conn->prepare("UPDATE barang SET nama = ?, kategori = ?, satuan_barang = ?, jumlah_stock = ?, reorder_level = ?, tipe_barang = ? WHERE barang_id = ?");
    if ($stmt) {
        $stmt->bind_param("sssiisi", $nama, $kategori, $satuan, $jumlah, $reorder, $tipe, $id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['status'] = 1;
                $response['message'] = "Data barang berhasil diubah.";
            } else {
                $response['status'] = 0;
                $response['message'] = "Tidak ada perubahan data atau barang tidak ditemukan.";
            }
        } else {
            $response['status'] = 0;
            $response['message'] = "Gagal mengeksekusi query ubah.";
        }
        $stmt->close();
    } else {
        $response['status'] = 0;
        $response['message'] = "Gagal menyiapkan statement SQL. Cek koneksi.";
    }
} else {
    $response['status'] = 0;
    $response['message'] = "Parameter tidak lengkap.";
}
$conn->close();
echo json_encode($response);
?>