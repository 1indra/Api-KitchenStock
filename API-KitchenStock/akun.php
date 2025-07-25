<?php
header('Content-Type: application/json');
require 'koneksi.php'; // Panggil file koneksi Anda

$response = ['status' => 'error', 'message' => 'User ID tidak valid.'];

// Pastikan user_id dikirim melalui metode GET
if (isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];

    // --- PERBAIKAN 1: Tambahkan 'foto_profil_url' ke dalam query SELECT ---
    $stmt = $conn->prepare("SELECT user_id, username, email, role, foto_profil_url FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();

        // --- PERBAIKAN 2: Ubah path relatif menjadi URL absolut ---
        // Cek apakah ada path gambar di database
        if (!empty($user_data['foto_profil_url'])) {
            $baseUrl = 'http://192.168.1.20/db/'; // SESUAIKAN DENGAN SERVER ANDA
            
            // Gabungkan base URL dengan path dari database
            $user_data['foto_profil_url'] = $baseUrl . $user_data['foto_profil_url'];
        }
        // --- AKHIR PERBAIKAN ---

        $response['status'] = 'success';
        $response['message'] = 'Data berhasil diambil.';
        $response['data'] = $user_data; // Kirim semua data user

    } else {
        $response['message'] = 'Pengguna tidak ditemukan.';
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
