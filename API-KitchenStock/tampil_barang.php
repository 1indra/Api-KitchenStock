<?php
require_once 'koneksi.php';
header('Content-Type: application/json');

$response = array();
$sql = "SELECT barang_id, nama, kategori, satuan_barang, jumlah_stock, reorder_level FROM barang";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $response['status'] = 1;
    $response['message'] = "Data barang berhasil ditemukan.";
    
    $response['data'] = array();
    while ($row = $result->fetch_assoc()) {
        $item = array(
            'id' => $row['barang_id'],
            'nama' => $row['nama'],
            'kategori' => $row['kategori'],
            'stok' => $row['jumlah_stock'],
            'satuan' => $row['satuan_barang'],
            'minimal_stok' => $row['reorder_level']
        );
        array_push($response['data'], $item);
    }
} else {
    $response['status'] = 0;
    $response['message'] = "Data barang tidak ditemukan.";
}

$conn->close();

echo json_encode($response);

?>