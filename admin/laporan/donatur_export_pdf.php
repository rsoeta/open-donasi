<?php
// ========================================================
//  DONATUR EXPORT PDF (FINAL FIX) — Tidak korup, Header OK
// ========================================================

ob_start(); // Buffer output untuk cegah PDF korup

// === Autoload DomPDF ===
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

// === Timezone ===
date_default_timezone_set('Asia/Jakarta');

// === Format Tanggal Indonesia ===
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
    $arr = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $arr[2] . ' ' . $bulan[(int)$arr[1]] . ' ' . $arr[0];
}

// === Filter ===
$start = $_GET['start'] ?? '';
$end   = $_GET['end'] ?? '';

$where = "";
if ($start && $end) {
    $where = "WHERE t.tanggal_transaksi BETWEEN '$start 00:00:00' AND '$end 23:59:59'";
}

// === Pengaturan lembaga ===
$site_name    = get_setting('site_name', 'Open Donasi');
$site_owner   = get_setting('site_owner', 'Lembaga');
$current_logo = get_setting('site_logo', 'assets/images/logo.png');
$site_city    = get_setting('site_city', 'Bandung');

// === Load Logo (Base64) ===
$logo_file = realpath(__DIR__ . '/../../' . $current_logo);
$logo_base64 = "";

if ($logo_file && file_exists($logo_file)) {
    $ext  = pathinfo($logo_file, PATHINFO_EXTENSION);
    $data = file_get_contents($logo_file);
    $logo_base64 = "data:image/{$ext};base64," . base64_encode($data);
}

// ====================================
//  Ambil Data DONATUR (Bukan per program)
// ====================================
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

// === Hitung total ===
$rows = [];
$total_donatur = 0;
$total_nominal = 0;

while ($d = mysqli_fetch_assoc($q)) {
    $rows[] = $d;
    $total_donatur++;
    $total_nominal += $d['jumlah'];
}

// ==========================
//  HTML PDF
// ==========================
$html = '
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
table { border-collapse: collapse; width: 100%; margin-top: 10px; }
th, td { border: 1px solid #555; padding: 6px; }
th { background: #f2f2f2; font-weight:bold; }
.center { text-align:center; }
.right { text-align:right; }
.footer { text-align:center; margin-top:20px; font-size:11px; border-top:1px solid #aaa; padding-top:5px; }
</style>

<div style="text-align:center;">';

if ($logo_base64 !== "") {
    $html .= '<img src="' . $logo_base64 . '" style="height:70px;margin-bottom:10px;">';
}

$html .= '
<h2 style="margin:0;">' . $site_name . '</h2>
<p><strong>' . $site_owner . '</strong></p>
<hr>
<h3 style="margin-top:10px;">LAPORAN DAFTAR DONATUR</h3>';

if ($start && $end) {
    $html .= '<p>Periode: ' . tanggal_indo($start) . ' - ' . tanggal_indo($end) . '</p>';
}

$html .= '</div>

<table>
<thead>
<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>Nama Donatur</th>
    <th>Program</th>
    <th>Nominal</th>
    <th>Metode</th>
    <th>Nilai Barang</th>
    <th>Total Donasi</th>
</tr>
</thead>
<tbody>
';

$no = 1;
foreach ($rows as $r) {
    $bq = mysqli_query($conn, "
        SELECT di.qty, i.harga_per_unit
        FROM donation_items di
        JOIN donasi_items i ON di.item_id = i.id
        WHERE di.donation_id = {$r['id']}
    ");

    $nilai_barang = 0;

    while ($x = mysqli_fetch_assoc($bq)) {
        $nilai_barang += $x['qty'] * $x['harga_per_unit'];
    }

    $total_donasi = $nilai_barang + $r['jumlah'];

    $html .= '
<tr>
    <td class="center">' . $no++ . '</td>
    <td>' . date('d M Y H:i', strtotime($r['tanggal_transaksi'])) . '</td>
    <td>' . htmlspecialchars($r['nama_donatur']) . '</td>
    <td>' . htmlspecialchars($r['program']) . '</td>
    <td class="right">Rp ' . number_format($r['jumlah'], 0, ',', '.') . '</td>
    <td>' . htmlspecialchars($r['metode']) . '</td>
    <td class="right">Rp ' . number_format($nilai_barang, 0, ',', '.') . '</td>
    <td class="right">Rp ' . number_format($total_donasi, 0, ',', '.') . '</td>
</tr>';
}

// === Baris TOTAL ===
// Hitung total nilai barang & total donasi
$total_barang = 0;
foreach ($rows as $r) {
    $bq = mysqli_query($conn, "
        SELECT di.qty, i.harga_per_unit
        FROM donation_items di
        JOIN donasi_items i ON di.item_id = i.id
        WHERE di.donation_id = {$r['id']}
    ");
    while ($x = mysqli_fetch_assoc($bq)) {
        $total_barang += $x['qty'] * $x['harga_per_unit'];
    }
}

$total_semua = $total_nominal + $total_barang;

// FOOTER TOTAL
$html .= '
<tr style="background:#e2e8f0; font-weight:bold;">
    <td colspan="4" class="right">TOTAL</td>
    <td class="right">Rp ' . number_format($total_nominal, 0, ',', '.') . '</td>
    <td class="center">' . $total_donatur . ' Donatur</td>
    <td class="right">Rp ' . number_format($total_barang, 0, ',', '.') . '</td>
    <td class="right">Rp ' . number_format($total_semua, 0, ',', '.') . '</td>
</tr>';

$html .= '
</tbody>
</table>

<div class="footer">
Dicetak otomatis oleh sistem <strong>' . $site_name . '</strong>.
</div>
';

// ==========================
// === Render PDF
// ==========================
$dompdf = new Dompdf([
    "isRemoteEnabled" => true,
    "isHtml5ParserEnabled" => true
]);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();

$pdfOutput = $dompdf->output();

// === Kirim header PDF ===
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"Daftar_Donatur_" . date('Ymd_His') . ".pdf\"");
header("Cache-Control: private, max-age=0, must-revalidate");
header("Pragma: public");

// === Buang output liar ===
ob_end_clean();

// === Tampilkan PDF ===
echo $pdfOutput;
exit;
