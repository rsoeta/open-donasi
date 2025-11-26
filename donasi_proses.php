<?php
// die("DEBUG: Ini file donasi_proses.php yang sedang dijalankan");
include_once('includes/config.php');
require_once 'includes/wa.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id_donasi = intval($_POST['id_donasi']);
$nama      = mysqli_real_escape_string($conn, $_POST['nama_donatur'] ?? '');
$wa        = mysqli_real_escape_string($conn, $_POST['wa'] ?? '');
$catatan   = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');

// opsi donasi
$opt_transfer = isset($_POST['opt_transfer']);
$opt_goods    = isset($_POST['opt_goods']);

// tentukan tipe
if ($opt_transfer && $opt_goods) $type = 'mixed';
elseif ($opt_goods) $type = 'goods';
else $type = 'transfer';

// ====== DATA TRANSFER ======
$jumlah = 0;
$metode = 'Transfer';
$bukti_filename = null;

if ($opt_transfer) {

    $jumlah = floatval($_POST['jumlah'] ?? 0);
    $metode = $_POST['metode'] ?? 'Transfer';

    if (!empty($_FILES['bukti']['name'])) {
        $targetDir = "uploads/bukti/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['bukti']['name']));
        $targetFilePath = $targetDir . $fileName;

        $ext = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allow = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

        if (in_array($ext, $allow) && move_uploaded_file($_FILES['bukti']['tmp_name'], $targetFilePath)) {
            $bukti_filename = $fileName;
        }
    }
}

// ====== DATA PENGIRIMAN BARANG ======
$pengiriman = 'none';
$alamat     = '';

if ($opt_goods) {
    $pengiriman = mysqli_real_escape_string($conn, $_POST['pengiriman'] ?? 'none');
    $alamat     = mysqli_real_escape_string($conn, $_POST['alamat'] ?? '');
}

// ====== SIMPAN KE donasi_pending (VERSI BARU) ======
$sql = "INSERT INTO donasi_pending 
        (id_donasi, nama_donatur, wa, alamat, pengiriman, jumlah, metode, bukti, catatan, status, type)
        VALUES (
            $id_donasi,
            '$nama',
            '$wa',
            " . ($alamat ? "'$alamat'" : "NULL") . ",
            '$pengiriman',
            $jumlah,
            '$metode',
            " . ($bukti_filename ? "'$bukti_filename'" : "NULL") . ",
            '$catatan',
            'menunggu',
            '$type'
        )";

if (!mysqli_query($conn, $sql)) {
    die("❌ QUERY ERROR:<br><br>$sql<br><br>" . mysqli_error($conn));
}

$pending_id = mysqli_insert_id($conn);

// ====== SIMPAN BARANG ======
if ($opt_goods && !empty($_POST['item_id']) && !empty($_POST['item_qty'])) {

    $item_ids = $_POST['item_id'];
    $qtys     = $_POST['item_qty'];

    $stmt = $conn->prepare("INSERT INTO donasi_pending_items (pending_id, item_id, qty) VALUES (?, ?, ?)");

    for ($i = 0; $i < count($item_ids); $i++) {
        $iid = intval($item_ids[$i]);
        $qty = intval($qtys[$i]);
        if ($qty < 1) continue;

        $stmt->bind_param("iii", $pending_id, $iid, $qty);
        $stmt->execute();
    }

    $stmt->close();
}

// ====== NOTIFIKASI WA ADMIN (FORMAT BARU) ======
$program = mysqli_fetch_assoc(mysqli_query($conn, "SELECT judul FROM donasi_post WHERE id = $id_donasi"));
$prog_n  = $program ? $program['judul'] : '-';

// Link maps masjid
$masjid_lat = get_setting('masjid_lat', '-');
$masjid_lng = get_setting('masjid_lng', '-');
$maps = ($masjid_lat !== '-' && $masjid_lng !== '-') ?  "https://www.google.com/maps?q=$masjid_lat,$masjid_lng" : '-';

// $maps = get_setting('masjid_maps', '-');

// Kop pesan
$msg = "*" . get_setting('site_name', 'Open Donasi') . " - Notifikasi*\n\n";
$msg .= "📌 Donasi Baru Masuk (Pending)\n\n";
$msg .= "* Program: *$prog_n*\n";
$msg .= "* Nama Donatur: $nama\n\n";

if ($opt_transfer) {
    $msg .= "* Donasi Transfer:\n  Rp " . number_format($jumlah, 0, ',', '.') . "\n\n";
}

if ($opt_goods) {

    $msg .= "* Barang & Jumlah:\n";

    foreach ($_POST['item_id'] as $idx => $iid) {
        $qty = intval($_POST['item_qty'][$idx]);
        if ($qty < 1) continue;

        $name = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM donasi_items WHERE id=$iid LIMIT 1"))['name'];
        $msg .= "  - $qty × $name\n";
    }
    $msg .= "\n";

    // pengiriman
    if ($pengiriman === 'dikirim') {
        $msg .= "* Pengiriman: Dikirim\n";
        $msg .= "📍 Lokasi Masjid: $maps\n\n";
    } else {
        $msg .= "* Pengiriman: Dijemput\n";
        $msg .= "📍 Alamat: " . ($alamat ?: '-') . "\n\n";
    }
}

// WA + Catatan selalu tampil
$msg .= "* Nomor WA: $wa\n";
$msg .= "* Catatan: " . ($catatan ?: '-') . "\n";

notify_admin($msg);

// ====== REDIRECT ======
header("Location: thanks.php?tid=$pending_id");
exit;
