<?php
// Mengatur header agar output berupa JSON
header('Content-Type: application/json');
// Memanggil file koneksi database
require 'koneksi.php';

// Mengambil tanggal dari parameter GET. Jika tidak ada, gunakan tanggal hari ini.
$tanggal = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : date('Y-m-d');

$response = array();

// Validasi dasar untuk format tanggal (YYYY-MM-DD) demi keamanan
if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $tanggal)) {
    $response['status'] = 'error';
    $response['message'] = 'Format tanggal salah. Gunakan YYYY-MM-DD.';
    echo json_encode($response);
    exit(); // Hentikan eksekusi skrip
}

// --- PERBAIKAN UTAMA DI SINI (QUERY SQL) ---
// Kita menggunakan JOIN untuk menggabungkan tabel 'transaksi' dengan 'barang'
// agar bisa mendapatkan nama dan satuan barang.
$sql = "SELECT 
            b.nama AS nama_barang,      -- Mengambil kolom 'nama' dari tabel barang, dinamai 'nama_barang'
            t.quantity AS jumlah,       -- Mengambil kolom 'quantity' dari tabel transaksi, dinamai 'jumlah'
            b.satuan_barang AS satuan,  -- Mengambil kolom 'satuan_barang' dari tabel barang, dinamai 'satuan'
            t.transaksi_type AS tipe    -- Mengambil kolom 'transaksi_type' dari tabel transaksi, dinamai 'tipe'
        FROM 
            transaksi AS t              -- Tabel transaksi kita beri alias 't'
        JOIN 
            barang AS b ON t.barang_id = b.barang_id -- Digabungkan berdasarkan 'barang_id'
        WHERE 
            DATE(t.transaksi_date) = ?  -- Menggunakan kolom 'transaksi_date'
        ORDER BY 
            t.transaksi_id DESC";       

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    // Menambahkan debug jika prepare gagal
    $response['status'] = 'error';
    $response['message'] = 'Gagal mempersiapkan query: ' . $conn->error;
    echo json_encode($response);
    exit();
}

// 's' berarti parameter yang di-bind adalah sebuah string
$stmt->bind_param("s", $tanggal);
$stmt->execute();
$result = $stmt->get_result();

$data_transaksi = array();
while ($row = $result->fetch_assoc()) {
    // Memastikan data 'jumlah' adalah angka (integer) di JSON
    $row['jumlah'] = (int) $row['jumlah'];
    $data_transaksi[] = $row;
}

$response['status'] = 'success';
$response['data'] = $data_transaksi;

// Menutup statement dan koneksi
$stmt->close();
$conn->close();

// Mencetak hasil akhir dalam format JSON
echo json_encode($response);
?>
