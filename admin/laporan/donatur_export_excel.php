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
        t.nama_donatur,
        t.jumlah,
        t.metode,
        t.tanggal_transaksi,
        p.judul AS program
    FROM donasi_transaksi t
    LEFT JOIN donasi_post p ON p.id = t.id_donasi
    $where
    ORDER BY t.tanggal_transaksi DESC
");

// Hitung total
$total_nominal = 0;
$total_donatur = 0;

echo "<table border='1'>
<tr style='font-weight:bold; background:#d1fae5;'>
    <th>No</th>
    <th>Tanggal</th>
    <th>Nama Donatur</th>
    <th>Program</th>
    <th>Nominal</th>
    <th>Metode</th>
</tr>";

$no = 1;
while ($row = mysqli_fetch_assoc($q)) {
    $total_nominal += $row['jumlah'];
    $total_donatur++;

    echo "<tr>
        <td>{$no}</td>
        <td>" . date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])) . "</td>
        <td>{$row['nama_donatur']}</td>
        <td>{$row['program']}</td>
        <td>" . number_format($row['jumlah'], 0, ',', '.') . "</td>
        <td>{$row['metode']}</td>
    </tr>";

    $no++;
}

// Baris TOTAL
echo "
<tr style='font-weight:bold; background:#e2e8f0;'>
    <td colspan='4' align='right'>TOTAL</td>
    <td>" . number_format($total_nominal, 0, ',', '.') . "</td>
    <td>{$total_donatur} Donatur</td>
</tr>
";

echo "</table>";
