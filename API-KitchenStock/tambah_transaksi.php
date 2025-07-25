<?php
header('Content-Type: application/json');
require 'koneksi.php';

$response = array();

// Memastikan koneksi database berhasil
if ($conn->connect_error) {
    $response['status'] = 'error';
    $response['message'] = 'Koneksi Database Gagal: ' . $conn->connect_error;
    echo json_encode($response);
    exit();
}

// Cek apakah parameter wajib sudah dikirim
if (isset($_POST['barang_id'], $_POST['transaksi_type'], $_POST['quantity'], $_POST['user_id'])) {
    
    // Ambil dan bersihkan data input
    $barang_id = (int)$_POST['barang_id'];
    $transaksi_type = $_POST['transaksi_type'];
    $quantity = (int)$_POST['quantity'];
    $user_id = (int)$_POST['user_id'];
    
    // Tangani parameter opsional dengan aman
    $supplier_id = isset($_POST['supplier_id']) && !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
    $keterangan = isset($_POST['keterangan']) ? $_POST['keterangan'] : '';
    
    // ================== PERBAIKAN 1: Menangkap transaksi_date ==================
    // Jika tanggal dikirim dari Android, gunakan itu. Jika tidak, biarkan NULL agar diisi default oleh DB (CURRENT_TIMESTAMP)
    $transaksi_date = isset($_POST['transaksi_date']) && !empty($_POST['transaksi_date']) ? $_POST['transaksi_date'] : null;

    // Validasi kuantitas
    if ($quantity <= 0) {
        $response['status'] = 'error';
        $response['message'] = 'Jumlah harus lebih dari 0';
        echo json_encode($response);
        exit();
    }
    
    // Cek stok jika transaksi keluar
    if ($transaksi_type == 'out') {
        $stmt_cek = $conn->prepare("SELECT jumlah_stock FROM barang WHERE barang_id = ?");
        $stmt_cek->bind_param("i", $barang_id);
        $stmt_cek->execute();
        $result_cek = $stmt_cek->get_result();
        if($result_cek->num_rows > 0) {
            $row = $result_cek->fetch_assoc();
            if ($row['jumlah_stock'] < $quantity) {
                $response['status'] = 'error';
                $response['message'] = 'Stok tidak mencukupi. Stok saat ini: ' . $row['jumlah_stock'];
                echo json_encode($response);
                exit();
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Barang tidak ditemukan.';
            echo json_encode($response);
            exit();
        }
        $stmt_cek->close();
    }

    // Memulai database transaction
    $conn->autocommit(FALSE);
    $error = false;
    $error_message = '';

    // Query untuk INSERT ke tabel transaksi
    // ================== PERBAIKAN 1 (lanjutan): Menambahkan transaksi_date ke query ==================
    $stmt_transaksi = $conn->prepare("INSERT INTO transaksi (barang_id, user_id, supplier_id, transaksi_type, quantity, keterangan, transaksi_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    // Tipe data untuk bind_param: i (int), s (string). supplier_id (bisa null) dan transaksi_date (bisa null) tetap di-bind.
    $stmt_transaksi->bind_param("iiisiss", $barang_id, $user_id, $supplier_id, $transaksi_type, $quantity, $keterangan, $transaksi_date);
    
    if (!$stmt_transaksi->execute()) {
        $error = true;
        // Memberikan pesan error yang lebih spesifik dari database
        $error_message = 'Gagal menyimpan transaksi: ' . $stmt_transaksi->error;
    }
    $stmt_transaksi->close();

    // Query untuk UPDATE stok di tabel barang
    if (!$error) {
        if ($transaksi_type == 'in') {
            $sql_update_stok = "UPDATE barang SET jumlah_stock = jumlah_stock + ? WHERE barang_id = ?";
        } else {
            $sql_update_stok = "UPDATE barang SET jumlah_stock = jumlah_stock - ? WHERE barang_id = ?";
        }
        
        $stmt_stok = $conn->prepare($sql_update_stok);
        $stmt_stok->bind_param("ii", $quantity, $barang_id);

        if (!$stmt_stok->execute()) {
            $error = true;
            $error_message = 'Gagal mengupdate stok: ' . $stmt_stok->error;
        }
        $stmt_stok->close();
    }

    // Finalisasi Transaction
    if ($error) {
        $conn->rollback();
        $response['status'] = 'error';
        $response['message'] = $error_message; // Tampilkan pesan error yang lebih detail
    } else {
        $conn->commit();
        $response['status'] = 'success';
        $response['message'] = 'Transaksi berhasil disimpan';
    }

} else {
    $response['status'] = 'error';
    $response['message'] = 'Parameter wajib tidak lengkap';
}

$conn->close();

echo json_encode($response);

?>