<?php
// Ganti 'koneksi.php' dengan nama file koneksi Anda jika berbeda
require 'koneksi.php'; 

header('Content-Type: text/html'); // Mengatur header untuk output HTML

echo "<h1>Hasil Tes Koneksi Database</h1>";

// Memeriksa status koneksi
if ($conn && !$conn->connect_error) {
    echo "<p style='color:green; font-weight:bold;'>BERHASIL!</p>";
    echo "<p>Skrip PHP berhasil terhubung ke database MySQL Anda.</p>";
} else {
    echo "<p style='color:red; font-weight:bold;'>GAGAL!</p>";
    // Memberikan pesan error yang spesifik
    echo "<p>Pesan Error: " . ($conn->connect_error ?? 'Tidak dapat membuat objek koneksi. Periksa file koneksi.php') . "</p>";
    echo "<p>Mohon periksa kembali detail di dalam file <strong>koneksi.php</strong> Anda (nama host, username, password, nama database).</p>";
}

// Menutup koneksi jika berhasil dibuat
if ($conn) {
    $conn->close();
}
?>