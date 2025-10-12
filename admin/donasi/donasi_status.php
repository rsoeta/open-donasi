<?php
session_start();
include_once('../../includes/config.php');

if (!isset($_SESSION['admin'])) {
  header('Location: ../../login.php');
  exit;
}

$id = intval($_GET['id']);
$aksi = $_GET['aksi'] ?? '';

if ($id && in_array($aksi, ['open', 'close'])) {
  if ($aksi === 'close') {
    $status = 'ditutup';
    $update = mysqli_query($conn, "UPDATE donasi_post SET status_donasi='$status', tanggal_ditutup=NOW() WHERE id=$id");
    $pesan = 'Program donasi telah ditutup dan tidak lagi menerima donasi.';
  } else {
    $status = 'aktif';
    $update = mysqli_query($conn, "UPDATE donasi_post SET status_donasi='$status', tanggal_ditutup=NULL WHERE id=$id");
    $pesan = 'Program donasi telah dibuka kembali.';
  }

  if ($update) {
    header('Location: donasi.php?msg=success&info=' . urlencode($pesan));
  } else {
    header('Location: donasi.php?msg=error&info=' . urlencode('Gagal memperbarui status.'));
  }
  exit;
} else {
  header('Location: donasi.php');
  exit;
}
