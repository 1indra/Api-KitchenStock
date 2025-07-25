<?php
header('Content-Type: application/json');
require 'koneksi.php';

$response = array();

try {
    // Query untuk total transaksi masuk bulan ini
    $sql_in = "SELECT COUNT(*) as total FROM transaksi WHERE transaksi_type = 'in' AND MONTH(transaksi_date) = MONTH(CURDATE()) AND YEAR(transaksi_date) = YEAR(CURDATE())";
    $result_in = $conn->query($sql_in);
    $total_masuk = $result_in->fetch_assoc()['total'];

    // Query untuk total transaksi keluar bulan ini
    $sql_out = "SELECT COUNT(*) as total FROM transaksi WHERE transaksi_type = 'out' AND MONTH(transaksi_date) = MONTH(CURDATE()) AND YEAR(transaksi_date) = YEAR(CURDATE())";
    $result_out = $conn->query($sql_out);
    $total_keluar = $result_out->fetch_assoc()['total'];

    $response['status'] = 'success';
    $response['data'] = [
        'total_transaksi_masuk' => $total_masuk,
        'total_transaksi_keluar' => $total_keluar
    ];

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Query Gagal: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>