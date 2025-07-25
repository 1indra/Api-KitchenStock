<?php
// Mengatur header agar output berupa format JSON
header('Content-Type: application/json; charset=utf-8');

// Memanggil file koneksi database
require 'koneksi.php';

// Menyiapkan array untuk respons akhir
$response = array();

// Pastikan koneksi berhasil
if ($conn->connect_error) {
    $response['status'] = 'error';
    $response['message'] = 'Koneksi Database Gagal: ' . $conn->connect_error;
    echo json_encode($response);
    exit();
}

// Query yang sudah dibersihkan dari karakter tidak terlihat
$sql = "SELECT 
            t.transaksi_id,
            t.transaksi_type,
            t.quantity,
            t.keterangan,
            t.transaksi_date,
            b.nama AS nama_barang,
            u.username AS nama_user,
            s.nama AS nama_pemasok
        FROM 
            transaksi t
        JOIN 
            barang b ON t.barang_id = b.barang_id
        LEFT JOIN 
            users u ON t.user_id = u.user_id
        LEFT JOIN 
            supplier s ON t.supplier_id = s.supplier_id
        ORDER BY 
            t.transaksi_date DESC, t.transaksi_id DESC"; // Tambahan urutan by ID untuk konsistensi

// Eksekusi query
$result = $conn->query($sql);

if ($result) {
    // Jika query berhasil
    $response['status'] = 'success';
    $data_transaksi = array();
    while ($row = $result->fetch_assoc()) {
        // Pastikan data NULL diubah menjadi string kosong atau penanda lain jika perlu
        $row['nama_pemasok'] = $row['nama_pemasok'] ?? '-';
        $row['nama_user'] = $row['nama_user'] ?? 'N/A';
        $row['keterangan'] = $row['keterangan'] ?? '';

        $data_transaksi[] = $row;
    }
    $response['data'] = $data_transaksi;
} else {
    // Jika query gagal, berikan pesan error yang spesifik dari database
    $response['status'] = 'error';
    $response['message'] = 'Query Gagal: ' . $conn->error;
}

// Tutup koneksi
$conn->close();

// Kembalikan respons dalam format JSON
echo json_encode($response);
?>