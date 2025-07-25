<?php
header('Content-Type: application/json');
require 'koneksi.php';

$response = array();

// Memeriksa apakah semua parameter yang dibutuhkan telah dikirim
if (isset($_POST['supplier_id']) && isset($_POST['nama']) && isset($_POST['contact_person']) && isset($_POST['phone']) && isset($_POST['email']) && isset($_POST['address'])) {
    
    $id = $_POST['supplier_id'];
    $nama = $_POST['nama'];
    $contact_person = $_POST['contact_person'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    // Menyiapkan statement SQL UPDATE
    $stmt = $conn->prepare("UPDATE supplier SET nama = ?, contact_person = ?, phone = ?, email = ?, address = ? WHERE supplier_id = ?");
    // Mengikat parameter. "sssssi" berarti: string, string, string, string, string, integer
    $stmt->bind_param("sssssi", $nama, $contact_person, $phone, $email, $address, $id);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Data pemasok berhasil diupdate';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Gagal mengupdate data: ' . $stmt->error;
    }
    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Parameter tidak lengkap';
}
$conn->close();
echo json_encode($response);
?>