<?php
header('Content-Type: application/json');
require 'koneksi.php';

$response = array();

$sql = "SELECT barang_id, nama, jumlah_stock, satuan_barang FROM barang ORDER BY nama ASC";
$result = $conn->query($sql);

if ($result) {
    $response['status'] = 'success';
    $data_stok = array();
    while ($row = $result->fetch_assoc()) {
        // WAJIB: Casting ke integer agar dibaca sebagai angka di Android (.getInt)
        $row['barang_id'] = (int) $row['barang_id'];
        $row['jumlah_stock'] = (int) $row['jumlah_stock'];
        $data_stok[] = $row;
    }
    $response['data'] = $data_stok;
} else {
    $response['status'] = 'error';
    $response['message'] = 'Query Gagal: ' . $conn->error;
}

$conn->close();
echo json_encode($response);
?>