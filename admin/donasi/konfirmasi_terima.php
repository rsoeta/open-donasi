<?php
include_once('../../includes/config.php');
include_once('../../includes/wa.php'); // WA API
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: ../../login.php');
    exit;
}

$pending_id = intval($_GET['id'] ?? 0);
if ($pending_id <= 0) {
    header('Location: konfirmasi.php');
    exit;
}

// Ambil pending
$q = mysqli_query($conn, "SELECT * FROM donasi_pending WHERE id=$pending_id LIMIT 1");
if (!$q || mysqli_num_rows($q) == 0) {
    $msg  = "Data pending tidak ditemukan.";
    $icon = "error";
} else {

    $p = mysqli_fetch_assoc($q);

    $id_donasi = intval($p['id_donasi']);
    $nama      = mysqli_real_escape_string($conn, $p['nama_donatur']);
    $jumlah    = floatval($p['jumlah']);
    $metode    = mysqli_real_escape_string($conn, $p['metode']);
    $type      = mysqli_real_escape_string($conn, $p['type']);
    $tanggal   = date('Y-m-d H:i:s');

    // -----------------------------
    // CEGAH DUPLIKASI
    // -----------------------------
    $check = mysqli_query($conn, "
        SELECT id FROM donasi_transaksi
        WHERE id_donasi = $id_donasi
        AND nama_donatur = '$nama'
        AND jumlah = $jumlah
        LIMIT 1
    ");

    if ($check && mysqli_num_rows($check) > 0) {
        $msg  = "Donasi ini sudah pernah dikonfirmasi.";
        $icon = "info";
    } else {

        // -----------------------------
        // HITUNG NILAI BARANG
        // -----------------------------
        $itemQ = mysqli_query($conn, "
            SELECT di.qty, i.name, i.harga_per_unit
            FROM donasi_pending_items di
            JOIN donasi_items i ON di.item_id = i.id
            WHERE di.pending_id = $pending_id
        ");

        $nilai_barang = 0;
        $items_list = [];

        while ($it = mysqli_fetch_assoc($itemQ)) {
            $subtotal = $it['qty'] * $it['harga_per_unit'];
            $nilai_barang += $subtotal;
            $items_list[] = $it['qty'] . "× " . $it['name'];
        }

        $ringkasan_barang = empty($items_list) ? "-" : implode(", ", $items_list);

        // -----------------------------
        // INSERT TRANSAKSI
        // -----------------------------
        $insert = mysqli_query($conn, "
            INSERT INTO donasi_transaksi 
            (id_donasi, nama_donatur, jumlah, metode, tanggal_transaksi, type)
            VALUES ($id_donasi, '$nama', $jumlah, '$metode', '$tanggal', '$type')
        ");

        if ($insert) {

            $trx_id = mysqli_insert_id($conn);

            // -----------------------------
            // PINDAHKAN BARANG (IF ANY)
            // -----------------------------
            if ($type === 'goods' || $type === 'mixed') {

                $items = mysqli_query($conn, "
                    SELECT item_id, qty FROM donasi_pending_items WHERE pending_id = $pending_id
                ");

                $stmt = $conn->prepare("
                    INSERT INTO donation_items (donation_id, item_id, qty)
                    VALUES (?, ?, ?)
                ");

                while ($r = mysqli_fetch_assoc($items)) {
                    $iid = intval($r['item_id']);
                    $qty = intval($r['qty']);
                    $stmt->bind_param('iii', $trx_id, $iid, $qty);
                    $stmt->execute();
                }

                $stmt->close();
            }

            // -----------------------------
            // DELETE PENDING
            // -----------------------------
            mysqli_query($conn, "DELETE FROM donasi_pending WHERE id=$pending_id");
            mysqli_query($conn, "DELETE FROM donasi_pending_items WHERE pending_id=$pending_id");

            // ---------------------------------------------------
            // WHATSAPP NOTIFICATION TO DONATUR (FINAL VERSION)
            // ---------------------------------------------------

            // --- Sanitasi No. WhatsApp ---
            $wa = preg_replace('/\D/', '', $p['wa']);
            if (strpos($wa, '0') === 0) {
                $wa = '62' . substr($wa, 1); // ubah 08 → 628
            }

            // Lanjut hanya jika nomor valid
            if (!empty($wa)) {

                // --- Ambil Template Pesan (disetel admin) ---
                $template = get_setting(
                    'wa_msg_approved',
                    "Assalamu’alaikum {{nama}}.\nDonasi Anda untuk program {{program}} telah kami terima."
                );

                // --- Ambil Nama Program ---
                $prog = mysqli_fetch_assoc(mysqli_query(
                    $conn,
                    "SELECT judul FROM donasi_post WHERE id = $id_donasi LIMIT 1"
                ));

                $nama_program = $prog ? $prog['judul'] : "-";

                // --- Hitung nilai barang ---
                $nilai_barang = floatval($nilai_barang);
                $total        = $jumlah + $nilai_barang;

                // --- Siapkan daftar placeholder standar ---
                $replace = [
                    '{{site_name}}'    => get_setting('site_name', 'Open Donasi'),
                    '{{nama}}'         => $nama,
                    '{{program}}'      => $nama_program,
                    '{{type}}'         => ucfirst($type),
                    '{{jumlah}}'       => "Rp " . number_format($jumlah, 0, ',', '.'),
                    '{{nilai_barang}}' => "Rp " . number_format($nilai_barang, 0, ',', '.'),
                    '{{total}}'        => "Rp " . number_format($total, 0, ',', '.'),
                    '{{barang}}'       => isset($ringkasan_barang) ? $ringkasan_barang : '-'
                ];

                // --- Replace placeholder ---
                $message = strtr($template, $replace);

                // --- Hapus tag HTML dan konversi format WA ---
                $message = html_to_whatsapp($message);

                // --- Kirim WhatsApp ---
                sendWhatsAppMessage($wa, $message);
            }

            $msg  = "Donasi dari <b>{$p['nama_donatur']}</b> berhasil dikonfirmasi.";
            $icon = "success";
        } else {
            $msg  = "Gagal memindahkan donasi: " . mysqli_error($conn);
            $icon = "error";
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <script>
        Swal.fire({
            icon: '<?= $icon ?>',
            title: 'Konfirmasi Donasi',
            html: '<?= addslashes($msg) ?>',
            confirmButtonColor: '#2c7a7b',
            timer: 2500,
            timerProgressBar: true
        }).then(() => {
            window.location.href = 'konfirmasi.php';
        });
    </script>
</body>

</html>