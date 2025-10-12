<?php
session_start();
include_once('../../includes/config.php');
header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

$id = intval($_POST['id']);
$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'terima') {
    // Ambil data dari donasi_pending
    $q = mysqli_query($conn, "SELECT * FROM donasi_pending WHERE id=$id LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        $row = mysqli_fetch_assoc($q);

        // Pastikan semua field ada
        $id_donasi = intval($row['id_donasi']);
        $nama = mysqli_real_escape_string($conn, $row['nama_donatur']);
        $jumlah = floatval($row['jumlah']);
        $metode = mysqli_real_escape_string($conn, $row['metode']);

        // Pindahkan ke donasi_transaksi
        $insert = mysqli_query($conn, "
      INSERT INTO donasi_transaksi (id_donasi, nama_donatur, jumlah, metode, tanggal_transaksi)
      VALUES ($id_donasi, '$nama', $jumlah, '$metode', NOW())
    ");

        if ($insert) {
            // Update status & tanggal verifikasi
            mysqli_query($conn, "
        UPDATE donasi_pending 
        SET status='diterima', tanggal_verifikasi=NOW()
        WHERE id=$id
      ");

            echo json_encode([
                'success' => true,
                'message' => "Donasi dari {$row['nama_donatur']} sebesar Rp" . number_format($row['jumlah'], 0, ',', '.') . " telah dikonfirmasi!"
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memindahkan data: ' . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan.']);
    }
} elseif ($aksi === 'tolak') {
    $update = mysqli_query($conn, "
    UPDATE donasi_pending 
    SET status='ditolak', tanggal_verifikasi=NOW()
    WHERE id=$id
  ");
    echo json_encode(['success' => $update, 'message' => 'Donasi telah ditolak.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
}
