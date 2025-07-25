<?php
// Mengatur header agar output berupa format JSON
header('Content-Type: application/json');

// Memanggil file koneksi database
require 'koneksi.php';

// Menyiapkan array untuk respons akhir
$response = array();

// Query untuk mengambil semua kolom yang relevan dari tabel pemasok Anda
// Diurutkan berdasarkan nama dari A-Z
$sql = "SELECT supplier_id, nama, contact_person, phone, email, address FROM supplier ORDER BY nama ASC";

// Eksekusi query
$result = $conn->query($sql);

if ($result) {
    // Jika query berhasil
    $response['status'] = 'success';
    $data_pemasok = array();
    while ($row = $result->fetch_assoc()) {
        $data_pemasok[] = $row;
    }
    $response['data'] = $data_pemasok;
} else {
    // Jika query gagal
    $response['status'] = 'error';
    $response['message'] = 'Query Gagal: ' . $conn->error;
}

// Tutup koneksi
$conn->close();

// Kembalikan respons dalam format JSON
echo json_encode($response);
?>