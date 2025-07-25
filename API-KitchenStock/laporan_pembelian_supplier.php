<?php
header('Content-Type: application/json');
require 'koneksi.php';

$response = array();

// --- PERBAIKAN UTAMA (QUERY SQL) ---
// Menggunakan JOIN untuk menggabungkan 3 tabel: transaksi, barang, dan supplier.
$sql = "SELECT 
            b.nama AS nama_barang,
            t.quantity AS jumlah,
            b.satuan_barang AS satuan,
            s.contact_person AS nama_pemasok, -- PERBAIKAN DI SINI: ganti 'nama_pemasok' dengan nama kolom yang benar di tabel supplier Anda
            DATE_FORMAT(t.transaksi_date, '%d-%m-%Y') AS tanggal_pembelian 
        FROM 
            transaksi AS t
        JOIN 
            barang AS b ON t.barang_id = b.barang_id
        LEFT JOIN 
            supplier AS s ON t.supplier_id = s.supplier_id 
        WHERE 
            t.transaksi_type = 'in' 
        ORDER BY 
            t.transaksi_date DESC, t.transaksi_id DESC";

$result = $conn->query($sql);

if ($result) {
    $response['status'] = 'success';
    $data_pembelian = array();
    while ($row = $result->fetch_assoc()) {
        $row['jumlah'] = (int) $row['jumlah'];
        
        // Menangani jika supplier_id adalah NULL
        if ($row['nama_pemasok'] === null) {
            $row['nama_pemasok'] = 'Stok Awal / Lainnya';
        }

        $data_pembelian[] = $row;
    }
    $response['data'] = $data_pembelian;
} else {
    $response['status'] = 'error';
    $response['message'] = 'Query Gagal: ' . $conn->error;
}

$conn->close();
echo json_encode($response);
?>
