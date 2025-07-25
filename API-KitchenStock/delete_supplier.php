<?php
header('Content-Type: application/json');
require 'koneksi.php';

$response = array();

// Memeriksa apakah parameter supplier_id telah dikirim
if (isset($_POST['supplier_id'])) {
    $id = $_POST['supplier_id'];

    // Menyiapkan statement SQL DELETE
    $stmt = $conn->prepare("DELETE FROM supplier WHERE supplier_id = ?");
    // Mengikat parameter. "i" berarti integer
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['status'] = 'success';
            $response['message'] = 'Data berhasil dihapus';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'ID tidak ditemukan';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Gagal menghapus data: ' . $stmt->error;
    }
    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Parameter supplier_id tidak ditemukan';
}
$conn->close();
echo json_encode($response);
?>