<?php
include_once('includes/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_donasi = intval($_POST['id_donasi']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_donatur']);
    $jumlah = floatval($_POST['jumlah']);
    $metode = 'Transfer';
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);

    $buktiFile = $_FILES['bukti'];
    $buktiName = '';

    if (!empty($buktiFile['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($buktiFile['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $targetDir = 'uploads/bukti/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $fileName = time() . '_' . basename($buktiFile['name']);
            $targetPath = $targetDir . $fileName;

            if (move_uploaded_file($buktiFile['tmp_name'], $targetPath)) {
                $buktiName = $fileName;
            }
        }
    }

    // Simpan ke tabel donasi_pending
    $query = "INSERT INTO donasi_pending (id_donasi, nama_donatur, jumlah, metode, bukti, catatan, status)
              VALUES ($id_donasi, '$nama', $jumlah, '$metode', '$buktiName', '$catatan', 'menunggu')";

    if (mysqli_query($conn, $query)) {
        header('Location: donasi_sukses.php');
        exit;
    } else {
        echo "Terjadi kesalahan: " . mysqli_error($conn);
    }
} else {
    header('Location: index.php');
    exit;
}
