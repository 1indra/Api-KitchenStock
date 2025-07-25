<?php
header('Content-Type: application/json');
require 'koneksi.php'; // Pastikan file koneksi Anda benar

// Hanya izinkan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan']);
    exit();
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Email dan password wajib diisi.']);
    exit();
}

// --- PERBAIKAN DI SINI ---
// Menggunakan 'user_id' sebagai nama kolom primary key
$sql = "SELECT user_id, username, email, password, role FROM users WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // Pengguna ditemukan, sekarang verifikasi password
    $user = $result->fetch_assoc();
    
    // Memverifikasi password yang dikirim dengan hash di database
    if (password_verify($password, $user['password'])) {
        // Password benar, login berhasil
        
        // --- PERBAIKAN DI SINI ---
        // Buat array data pengguna untuk dikirim kembali
        $userData = [
            'user_id' => (int)$user['user_id'], // Mengirim 'user_id'
            'username' => $user['username'],
            'role' => $user['role']
        ];
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => $userData
        ]);

    } else {
        // Password salah
        echo json_encode(['status' => 'error', 'message' => 'Email atau password salah.']);
    }

} else {
    // Pengguna dengan email tersebut tidak ditemukan
    echo json_encode(['status' => 'error', 'message' => 'Email atau password salah.']);
}

$stmt->close();
$conn->close();
?>
