<?php
header('Content-Type: application/json');
require 'koneksi.php';

// Ambil parameter dari GET request
$tipe = isset($_GET['tipe']) ? $_GET['tipe'] : 'semua';
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : null;

$response = array();

// --- Membangun Query SQL secara Dinamis ---
$sql = "SELECT 
            t.transaksi_id,
            b.nama AS nama_barang,
            t.quantity AS jumlah,
            b.satuan_barang AS satuan,
            t.transaksi_type AS tipe,
            -- PERBAIKAN 1: Mengambil kolom 'nama' dari tabel 'users'
            COALESCE(s.contact_person, u.username, 'Sistem') AS keterangan, 
            DATE_FORMAT(t.transaksi_date, '%d-%m-%Y %H:%i') AS tanggal
        FROM 
            transaksi AS t
        JOIN 
            barang AS b ON t.barang_id = b.barang_id
        LEFT JOIN 
            supplier AS s ON t.supplier_id = s.supplier_id
        -- PERBAIKAN 2: Menggunakan nama kolom yang benar untuk JOIN
        LEFT JOIN
            users AS u ON t.user_id = u.user_id"; // Asumsi primary key di tabel users adalah 'user_id'

$whereClauses = [];
$params = [];
$types = "";

// Tambahkan filter Tipe Transaksi
if ($tipe === 'in' || $tipe === 'out') {
    $whereClauses[] = "t.transaksi_type = ?";
    $params[] = $tipe;
    $types .= "s";
}

// Tambahkan filter Tanggal
if ($tanggal !== null && preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $tanggal)) {
    $whereClauses[] = "DATE(t.transaksi_date) = ?";
    $params[] = $tanggal;
    $types .= "s";
}

// Gabungkan semua klausa WHERE
if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

$sql .= " ORDER BY t.transaksi_id DESC LIMIT 100"; 

// Eksekusi query
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    // Menambahkan penanganan error jika prepare gagal
    if ($stmt === false) {
        $response['status'] = 'error';
        $response['message'] = 'Gagal mempersiapkan query: ' . $conn->error;
        echo json_encode($response);
        exit();
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}


if ($result) {
    $response['status'] = 'success';
    $data_riwayat = array();
    while ($row = $result->fetch_assoc()) {
        $row['jumlah'] = (int) $row['jumlah'];
        $data_riwayat[] = $row;
    }
    $response['data'] = $data_riwayat;
} else {
    $response['status'] = 'error';
    $response['message'] = 'Query Gagal: ' . $conn->error;
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
echo json_encode($response);
?>
