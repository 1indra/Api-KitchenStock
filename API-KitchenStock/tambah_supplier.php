<?php
header('Content-Type: application/json');
require 'koneksi.php';

$response = array();

// DIUBAH: Memeriksa nama parameter sesuai dengan yang dikirim dari Java
if (isset($_POST['nama']) && isset($_POST['contact_person']) && isset($_POST['phone']) && isset($_POST['email']) && isset($_POST['address'])) {
    
    $nama = $_POST['nama'];
    $contact_person = $_POST['contact_person'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    // Query INSERT menggunakan nama kolom dari database Anda
    $stmt = $conn->prepare("INSERT INTO supplier (nama, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nama, $contact_person, $phone, $email, $address);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Pemasok baru berhasil ditambahkan';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Gagal menambahkan pemasok: ' . $stmt->error;
    }
    $stmt->close();

} else {
    $response['status'] = 'error';
    $response['message'] = 'Parameter tidak lengkap';
}
$conn->close();
echo json_encode($response);
?>