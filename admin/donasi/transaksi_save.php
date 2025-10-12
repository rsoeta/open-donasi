<?php
include_once('../../includes/config.php');
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: ../../login.php');
    exit;
}

$id_donasi = intval($_POST['id_donasi']);
$nama_donatur = mysqli_real_escape_string($conn, $_POST['nama_donatur']);
$jumlah = floatval(str_replace('.', '', $_POST['jumlah'])); // jika format ribuan pake titik
$tanggal = mysqli_real_escape_string($conn, $_POST['tanggal_transaksi']);
$metode = isset($_POST['metode']) ? mysqli_real_escape_string($conn, $_POST['metode']) : 'cash';

$sql = "INSERT INTO donasi_transaksi (id_donasi, nama_donatur, jumlah, tanggal_transaksi, metode)
        VALUES ($id_donasi, '$nama_donatur', $jumlah, '$tanggal', '$metode')";

if (mysqli_query($conn, $sql)) {
    header("Location: transaksi_add.php?msg=added&info=" . urlencode("Transaksi tersimpan"));
    exit;
} else {
    header("Location: transaksi_add.php?msg=error&info=" . urlencode("Gagal: " . mysqli_error($conn)));
    exit;
}
