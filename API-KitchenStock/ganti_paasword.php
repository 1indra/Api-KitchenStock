<?php

header('Content-Type: application/json');
require 'koneksi.php'; // Panggil file koneksi Anda

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pastikan semua data yang dibutuhkan dikirim
    if (isset($_POST['user_id'], $_POST['password_lama'], $_POST['password_baru'])) {
        
        $userId = $_POST['user_id'];
        $oldPassword = $_POST['password_lama'];
        $newPassword = $_POST['password_baru'];

        // Ambil password yang ter-hash dari database untuk user ini
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $hashedPasswordFromDb = $user['password'];

            // Verifikasi apakah password lama yang dimasukkan cocok dengan yang ada di database
            if (password_verify($oldPassword, $hashedPasswordFromDb)) {
                
                // Jika cocok, hash password baru
                $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update password di database dengan hash yang baru
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $updateStmt->bind_param("si", $newHashedPassword, $userId);

                if ($updateStmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Password berhasil diubah!';
                } else {
                    $response['message'] = 'Gagal memperbarui password di database.';
                }
                $updateStmt->close();

            } else {
                // Jika password lama tidak cocok
                $response['message'] = 'Password lama yang Anda masukkan salah.';
            }
        } else {
            $response['message'] = 'Pengguna tidak ditemukan.';
        }
        $stmt->close();
        
    } else {
        $response['message'] = 'Data tidak lengkap.';
    }
} else {
    $response['message'] = 'Metode request harus POST.';
}

$conn->close();
echo json_encode($response);
?>
