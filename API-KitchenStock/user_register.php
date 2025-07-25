<?php

// Selalu letakkan di baris paling atas untuk memastikan output adalah JSON
header('Content-Type: application/json');

// Memanggil file koneksi database eksternal
require 'koneksi.php';

// Cek koneksi, diasumsikan variabel $conn berasal dari koneksi.php
if ($conn->connect_error) {
    // Kirim respons error dalam format JSON jika koneksi gagal
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal: ' . $conn->connect_error]);
    exit(); // Hentikan eksekusi script
}

// Pastikan metode request adalah POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Ambil data dari POST, gunakan null coalescing operator untuk keamanan
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'staff'; // default role

    // Validasi input tidak boleh kosong
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        echo json_encode(["status" => "error", "message" => "Username, email, password, dan role wajib diisi!"]);
        exit();
    }

    // Cek apakah email sudah terdaftar
    $checkEmail = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email sudah terdaftar!"]);
        $checkEmail->close();
        exit();
    }
    $checkEmail->close();

    // Cek Apakah Username sudah terdaftar
    $checkUsername = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $checkUsername->bind_param("s", $username);
    $checkUsername->execute();
    $checkUsername->store_result();

    if ($checkUsername->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Username sudah terdaftar!"]);
        $checkUsername->close();
        exit();
    }
    $checkUsername->close();

    // Hash password sebelum disimpan ke database untuk keamanan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Siapkan nilai NULL untuk kolom foto saat user baru mendaftar
    $default_photo_url = null;

    // Siapkan query INSERT
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, foto_profil_url) VALUES (?, ?, ?, ?, ?)");

    // Ikat parameter ke query. 'sssss' berarti kelima variabel adalah string
    $stmt->bind_param("sssss", $username, $email, $hashed_password, $role, $default_photo_url);

    // Eksekusi query dan berikan respons
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Registrasi berhasil! Silakan login."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal mendaftar: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Metode request tidak valid, harus POST."]);
}

$conn->close();
?>
