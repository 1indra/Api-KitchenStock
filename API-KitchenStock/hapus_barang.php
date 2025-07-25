<?php
require_once 'koneksi.php'; 
header('Content-Type: application/json');
$response = array();
if (isset($_POST['barang_id'])) {
    $id = $_POST['barang_id'];
    $stmt = $conn->prepare("DELETE FROM barang WHERE barang_id = ?");
    
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['status'] = 1;
                $response['message'] = "Barang berhasil dihapus.";
            } else {
                $response['status'] = 0;
                $response['message'] = "Gagal menghapus: Barang dengan ID tersebut tidak ditemukan.";
            }
        } else {
            $response['status'] = 0;
            $response['message'] = "Gagal mengeksekusi query hapus.";
        }
        $stmt->close();
    } else {
        $response['status'] = 0;
        $response['message'] = "Gagal menyiapkan statement SQL.";
    }

} else {
    $response['status'] = 0;
    $response['message'] = "Parameter ID barang tidak diterima.";
}
$conn->close();
echo json_encode($response);
?>