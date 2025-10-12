<?php
include_once('../../includes/config.php');
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Donasi_" . date('Ymd_His') . ".xls");

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$where = '';
if ($start && $end) $where = "AND t.tanggal_transaksi BETWEEN '$start 00:00:00' AND '$end 23:59:59'";

$query = mysqli_query($conn, "
  SELECT d.judul, d.status_donasi, d.tanggal_ditutup,
  COUNT(DISTINCT t.id) AS jumlah_donatur,
  COALESCE(SUM(t.jumlah),0) AS total_donasi
  FROM donasi_post d
  LEFT JOIN donasi_transaksi t ON d.id = t.id_donasi $where
  GROUP BY d.id, d.judul, d.status_donasi, d.tanggal_ditutup
");

echo "<table border='1'>
<tr>
  <th>No</th><th>Program Donasi</th><th>Status Donasi</th><th>Tanggal Ditutup</th><th>Jumlah Donatur</th><th>Total Donasi (Rp)</th>
</tr>";
$no=1;
while ($row = mysqli_fetch_assoc($query)) {
  echo "<tr>
    <td>$no</td>
    <td>{$row['judul']}</td>
    <td>{$row['status_donasi']}</td>
    <td>{$row['tanggal_ditutup']}</td>
    <td>{$row['jumlah_donatur']}</td>
    <td>" . number_format($row['total_donasi'],0,',','.') . "</td>
  </tr>";
  $no++;
}
echo "</table>";
