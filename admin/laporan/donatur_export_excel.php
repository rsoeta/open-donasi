<?php
include_once('../../includes/config.php');

// Header Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Daftar_Donatur_" . date('Ymd_His') . ".xls");

// Filter tanggal
$start = $_GET['start'] ?? '';
$end   = $_GET['end'] ?? '';

$where = "";
if ($start && $end) {
    $where = "WHERE t.tanggal_transaksi BETWEEN '$start 00:00:00' AND '$end 23:59:59'";
}

// Query daftar donatur
$q = mysqli_query($conn, "
    SELECT 
        t.id,
        t.nama_donatur,
        t.jumlah,
        t.metode,
        t.tanggal_transaksi,
        p.judul AS program
    FROM donasi_transaksi t
    LEFT JOIN donasi_post p ON p.id = t.id_donasi
    $where
    ORDER BY t.tanggal_transaksi ASC
");

// Hitung total
$total_nominal = 0;
$total_barang  = 0;
$total_semua   = 0;
$total_donatur = 0;

echo "<table border='1'>
<tr style='font-weight:bold; background:#d1fae5;'>
    <th>No</th>
    <th>Tanggal</th>
    <th>Nama Donatur</th>
    <th>Program</th>
    <th>Nominal Transfer</th>
    <th>Metode</th>
    <th>Nilai Barang</th>
    <th>Total Donasi</th>
</tr>";

$no = 1;
while ($row = mysqli_fetch_assoc($q)) {

    // Hitung nilai barang
    $nilai_barang = 0;
    $bq = mysqli_query($conn, "
        SELECT di.qty, i.harga_per_unit
        FROM donation_items di
        JOIN donasi_items i ON di.item_id = i.id
        WHERE di.donation_id = {$row['id']}
    ");

    while ($r = mysqli_fetch_assoc($bq)) {
        $nilai_barang += $r['qty'] * $r['harga_per_unit'];
    }

    // Total donasi (mixed atau barang-only atau transfer)
    $total_donasi = $row['jumlah'] + $nilai_barang;

    // Akumulasi total keseluruhan
    $total_nominal += $row['jumlah'];
    $total_barang  += $nilai_barang;
    $total_semua   += $total_donasi;
    $total_donatur++;

    echo "<tr>
        <td>{$no}</td>
        <td>" . date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])) . "</td>
        <td>{$row['nama_donatur']}</td>
        <td>{$row['program']}</td>
        <td>" . number_format($row['jumlah'], 0, ',', '.') . "</td>
        <td>{$row['metode']}</td>
        <td>" . number_format($nilai_barang, 0, ',', '.') . "</td>
        <td>" . number_format($total_donasi, 0, ',', '.') . "</td>
    </tr>";

    $no++;
}

// Baris TOTAL
echo "
<tr style='font-weight:bold; background:#e2e8f0;'>
    <td colspan='4' align='right'>TOTAL TRANSFER</td>
    <td>" . number_format($total_nominal, 0, ',', '.') . "</td>
    <td></td>
    <td>" . number_format($total_barang, 0, ',', '.') . "</td>
    <td>" . number_format($total_semua, 0, ',', '.') . "</td>
</tr>

<tr style='font-weight:bold; background:#edf2f7;'>
    <td colspan='8' align='left'>TOTAL DONATUR: {$total_donatur}</td>
</tr>";

echo "</table>";
