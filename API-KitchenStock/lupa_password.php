<?php
header('Content-Type: application/json');
require 'koneksi.php'; // Pastikan file koneksi Anda benar

// Hanya izinkan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan']);
    exit();
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Format email tidak valid']);
    exit();
}

// Cek apakah email terdaftar di database
$sql = "SELECT user_id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // --- LOGIKA RESET PASSWORD SEBENARNYA DI SINI ---
    // 1. Buat token reset yang unik (misal: bin2hex(random_bytes(32)))
    // 2. Simpan token tersebut di tabel 'users' atau tabel 'password_resets' beserta timestamp kapan token kedaluwarsa.
    // 3. Kirim email ke pengguna yang berisi link: https://domainanda.com/reset_password.php?token=...
    // 4. Di halaman reset_password.php, validasi token, lalu izinkan pengguna memasukkan password baru.

    // Untuk sekarang, kita hanya kirim pesan sukses
    echo json_encode(['status' => 'success', 'message' => 'Jika email terdaftar, link reset password telah dikirim.']);

} else {
    // Kita tetap kirim pesan sukses untuk keamanan, agar orang tidak bisa menebak email mana yang terdaftar.
    echo json_encode(['status' => 'success', 'message' => 'Jika email terdaftar, link reset password telah dikirim.']);
}

$stmt->close();
$conn->close();
?>
