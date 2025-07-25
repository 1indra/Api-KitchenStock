<?php
header('Content-Type: application/json');
require 'koneksi.php'; // Panggil file koneksi Anda

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan yang tidak diketahui.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pastikan user_id, username, dan email dikirim
    if (isset($_POST['user_id'], $_POST['username'], $_POST['email'])) {
        
        $userId = $_POST['user_id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        
        $imagePathForDb = null; // Path gambar untuk disimpan di database

        // Cek apakah ada file gambar yang diupload
        // Kunci 'image' harus cocok dengan yang Anda definisikan di VolleyMultipartRequest
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            
            $uploadDir = 'uploads/'; // Pastikan folder ini ada dan writable
            
            // Buat direktori jika belum ada
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Dapatkan ekstensi file
            $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            // Buat nama file unik untuk menghindari konflik
            $uniqueFileName = 'profil_' . $userId . '_' . time() . '.' . $fileExtension;
            $targetPath = $uploadDir . $uniqueFileName;

            // Coba pindahkan file yang diupload
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePathForDb = $targetPath; // Jika berhasil, siapkan path untuk DB
            } else {
                $response['message'] = 'Gagal menyimpan file gambar. Periksa izin folder uploads.';
                echo json_encode($response);
                exit();
            }
        }

        // Siapkan query SQL
        if ($imagePathForDb !== null) {
            // JIKA ADA GAMBAR BARU: Update semua data termasuk foto
            $sql = "UPDATE users SET username = ?, email = ?, foto_profil_url = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $username, $email, $imagePathForDb, $userId);
        } else {
            // JIKA TIDAK ADA GAMBAR BARU: Hanya update data teks
            $sql = "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $username, $email, $userId);
        }

        // Eksekusi query
        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Profil berhasil diperbarui!';
        } else {
            $response['message'] = 'Gagal memperbarui data di database: ' . $stmt->error;
        }
        $stmt->close();
        
    } else {
        $response['message'] = 'Data tidak lengkap. user_id, username, dan email wajib dikirim.';
    }
} else {
    $response['message'] = 'Metode request harus POST.';
}

$conn->close();
echo json_encode($response);
?>