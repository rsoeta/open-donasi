<?php
$autoloadPath = realpath(__DIR__ . '/../../vendor/autoload.php');
if (!file_exists($autoloadPath)) {
  $autoloadPath = realpath(__DIR__ . '/../../includes/dompdf/autoload.inc.php');
}
if (!$autoloadPath || !file_exists($autoloadPath)) {
  die('❌ DomPDF tidak ditemukan.');
}
require $autoloadPath;

include_once('../../includes/config.php');

use Dompdf\Dompdf;

// Set timezone Jakarta
date_default_timezone_set('Asia/Jakarta');
function tanggal_indo($tanggal)
{
  $bulan = [
    1 => 'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
  ];
  $pecah = explode('-', date('Y-m-d', strtotime($tanggal)));
  return $pecah[2] . ' ' . $bulan[(int)$pecah[1]] . ' ' . $pecah[0];
}

$start = $_GET['start'] ?? '';
$end   = $_GET['end'] ?? '';
$where = '';

if ($start && $end) {
  $where = "AND t.tanggal_transaksi BETWEEN '$start 00:00:00' AND '$end 23:59:59'";
}

// Ambil pengaturan lembaga
$site_name    = get_setting('site_name', 'Open Donasi');
$site_owner   = get_setting('site_owner', 'Komunitas Sahabat Al Hilal');
$current_logo = get_setting('site_logo', 'assets/images/logo.png');
$site_city = get_setting('site_city', 'Bandung');


// Embed logo ke Base64
$logo_path = realpath(__DIR__ . '/../../' . $current_logo);
$logo_base64 = '';
if ($logo_path && file_exists($logo_path)) {
  $type = pathinfo($logo_path, PATHINFO_EXTENSION);
  $data = file_get_contents($logo_path);
  $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

// Ambil data laporan
$query = mysqli_query($conn, "
  SELECT d.judul, d.status_donasi, d.tanggal_ditutup,
  COUNT(DISTINCT t.id) AS jumlah_donatur,
  COALESCE(SUM(t.jumlah),0) AS total_donasi
  FROM donasi_post d
  LEFT JOIN donasi_transaksi t ON d.id = t.id_donasi $where
  GROUP BY d.id, d.judul, d.status_donasi, d.tanggal_ditutup
");

$html = '
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
  .kop { text-align: center; margin-bottom: 12px; }
  .kop img { height: 70px; display:block; margin:0 auto 8px auto; }
  .kop h1 { margin: 0; font-size: 18px; font-weight: bold; text-transform: uppercase; }
  .kop p { margin: 2px 0; font-size: 13px; }
  hr { border: 1px solid #000; margin-top: 8px; margin-bottom: 10px; }
  table { border-collapse: collapse; width: 100%; margin-top: 15px; }
  th, td { border: 1px solid #555; padding: 6px; }
  th { background-color: #f2f2f2; }
  .center { text-align: center; }
  .right { text-align: right; }
  .ttd { margin-top:50px; width:100%; text-align:center; }
  .ttd td { border:none; }
  .footer { text-align:center; font-size:11px; margin-top:30px; color:#555; border-top:1px solid #aaa; padding-top:4px; }
</style>

<div class="kop">
';

if ($logo_base64 !== '') {
  $html .= '<img src="' . $logo_base64 . '" alt="Logo Lembaga">';
}

$html .= '
  <h1>' . htmlspecialchars($site_name) . '</h1>
  <p><strong>' . htmlspecialchars($site_owner) . '</strong></p>
  <hr>
  <h3 style="margin-top:12px;">LAPORAN DONASI</h3>
';

if ($start && $end) {
  $html .= '<p>Periode: ' . date('d M Y', strtotime($start)) . ' s/d ' . date('d M Y', strtotime($end)) . '</p>';
}

$html .= '</div>

<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Program Donasi</th>
      <th>Status Donasi</th>
      <th>Tanggal Ditutup</th>
      <th>Jumlah Donatur</th>
      <th>Total Donasi (Rp)</th>
    </tr>
  </thead>
  <tbody>
';

$no = 1;
while ($row = mysqli_fetch_assoc($query)) {
  $tanggal = $row['tanggal_ditutup'] ? date('d M Y', strtotime($row['tanggal_ditutup'])) : '-';
  $html .= '
      <tr>
        <td class="center">' . $no . '</td>
        <td>' . htmlspecialchars($row['judul']) . '</td>
        <td class="center">' . ucfirst($row['status_donasi']) . '</td>
        <td class="center">' . $tanggal . '</td>
        <td class="center">' . $row['jumlah_donatur'] . '</td>
        <td class="right">' . number_format($row['total_donasi'], 0, ',', '.') . '</td>
      </tr>';
  $no++;
}

$html .= '
  </tbody>
</table>

<!-- Blok tanda tangan lembaga -->
<table style="width:100%; margin-top:10px; border:none">
  <tr>
    <td style="width:50%; border:none"></td>
    <td style="width:50%; text-align:center; border:none">
    ' . htmlspecialchars($site_city) . ', ' . tanggal_indo(date('Y-m-d')) . '<br>
      Mengetahui,<br>
      Ketua Lembaga<br><br><br><br><br>
      <span style="display:inline-block; border-top:1px solid #000; padding-top:3px; font-weight:bold;">
    ' . htmlspecialchars($site_name) . '
      </span>
    </td>
  </tr>
</table>

<div class="footer">
  Dokumen ini dicetak otomatis oleh sistem <strong>' . htmlspecialchars($site_name) . '</strong>.
</div>
';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Laporan_Donasi_" . date('Ymd_His') . ".pdf", ["Attachment" => true]);
exit;
