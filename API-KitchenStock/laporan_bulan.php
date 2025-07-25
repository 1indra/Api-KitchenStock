<?php
header('Content-Type: application/json');
require 'koneksi.php';

// Mengambil parameter periode bulan, contoh: ?bulan=2025-06
$periode = isset($_GET['bulan']) ? trim($_GET['bulan']) : date('Y-m');
list($tahun, $bulan) = explode('-', $periode);

$response = array();
$data_laporan = array();

// --- Query 1: Mengambil Ringkasan Total Transaksi (Masuk & Keluar) ---
// PERBAIKAN: Menggunakan nama tabel dan kolom yang benar
$sql_summary = "SELECT 
                    COUNT(CASE WHEN transaksi_type = 'in' THEN 1 END) AS total_transaksi_masuk,
                    COUNT(CASE WHEN transaksi_type = 'out' THEN 1 END) AS total_transaksi_keluar
                FROM transaksi 
                WHERE YEAR(transaksi_date) = ? AND MONTH(transaksi_date) = ?";

$stmt_summary = $conn->prepare($sql_summary);
// 'i' berarti parameter adalah integer
$stmt_summary->bind_param("ii", $tahun, $bulan);
$stmt_summary->execute();
$result_summary = $stmt_summary->get_result()->fetch_assoc();
$data_laporan['ringkasan'] = [
    'total_transaksi_masuk' => $result_summary['total_transaksi_masuk'] ?? "0",
    'total_transaksi_keluar' => $result_summary['total_transaksi_keluar'] ?? "0"
];
$stmt_summary->close();


// --- Query 2: Mengambil Rekapitulasi Jumlah per Barang ---
// PERBAIKAN: Menggunakan JOIN dan nama kolom yang benar
$sql_rekap = "SELECT 
                   b.nama AS nama_barang, 
                   SUM(CASE WHEN t.transaksi_type = 'in' THEN t.quantity ELSE 0 END) AS total_jumlah_masuk,
                   SUM(CASE WHEN t.transaksi_type = 'out' THEN t.quantity ELSE 0 END) AS total_jumlah_keluar
               FROM 
                   transaksi AS t
               JOIN 
                   barang AS b ON t.barang_id = b.barang_id
               WHERE 
                   YEAR(t.transaksi_date) = ? AND MONTH(t.transaksi_date) = ?
               GROUP BY 
                   b.nama 
               ORDER BY 
                   b.nama ASC";

$stmt_rekap = $conn->prepare($sql_rekap);
$stmt_rekap->bind_param("ii", $tahun, $bulan);
$stmt_rekap->execute();
$result_rekap = $stmt_rekap->get_result();

$rekap_data = [];
while ($row = $result_rekap->fetch_assoc()) {
    // Casting ke integer
    $row['total_jumlah_masuk'] = (int) $row['total_jumlah_masuk'];
    $row['total_jumlah_keluar'] = (int) $row['total_jumlah_keluar'];
    $rekap_data[] = $row;
}
$data_laporan['rekap_per_barang'] = $rekap_data;
$stmt_rekap->close();


// --- Finalisasi Respons ---
$response['status'] = 'success';
$response['data'] = $data_laporan;

$conn->close();
echo json_encode($response);
?>
