<?php
include_once('../../includes/config.php');

$data = json_decode(file_get_contents("php://input"), true);

$id    = intval($data['id']);
// $harga = str_replace('.', '', $_POST['harga_per_unit']); // hilangkan . ribuan
// $harga = floatval($data['harga']);
// $harga = preg_replace('/\D/', '', $_POST['harga_per_unit']); // hilangkan semua non-digit

$harga_input = $data['harga'] ?? '0';

// buang titik ribuan
$harga_bersih = str_replace('.', '', $harga_input);

// pastikan angka
$harga_final = floatval($harga_bersih);



if ($id < 1) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

// simpan ke DB
$sql = "UPDATE donasi_items SET harga_per_unit = $harga_final WHERE id = $id";
$q = mysqli_query(
    $conn,
    $sql
);

echo json_encode([
    'success' => $q,
    'message' => $q ? 'Harga berhasil diperbarui!' : 'Gagal memperbarui harga.'
]);
