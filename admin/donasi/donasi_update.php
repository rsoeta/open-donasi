<?php
include_once('../../includes/config.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: ../../login.php');
    exit;
}

$id = intval($_POST['id']);
$judul = mysqli_real_escape_string($conn, $_POST['judul']);
$deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

// Ambil gambar lama
$result = mysqli_query($conn, "SELECT gambar FROM donasi_post WHERE id=$id");
$old = mysqli_fetch_assoc($result);
$gambar = $old['gambar'];

// Jika admin upload gambar baru
if (!empty($_FILES['gambar']['name'])) {
    $targetDir = "../../uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = time() . '_' . basename($_FILES['gambar']['name']);
    $targetFile = $targetDir . $fileName;
    move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFile);

    // Hapus gambar lama (jika ada)
    if (!empty($gambar) && file_exists($targetDir . $gambar)) {
        unlink($targetDir . $gambar);
    }

    $gambar = $fileName;
}

$query = "UPDATE donasi_post 
          SET judul='$judul', deskripsi='$deskripsi', gambar='$gambar', tanggal_post=NOW()
          WHERE id=$id";

if (mysqli_query($conn, $query)) {
    header('Location: donasi.php?msg=updated');
} else {
    echo "Error: " . mysqli_error($conn);
}
