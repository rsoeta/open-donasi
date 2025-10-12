<?php
include_once('../../includes/config.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: ../../login.php');
    exit;
}

$pending_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($pending_id <= 0) {
    header('Location: konfirmasi.php');
    exit;
}

// --- Ambil data pending
$q = mysqli_query($conn, "SELECT * FROM donasi_pending WHERE id = $pending_id LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    $msg = "Data donasi pending tidak ditemukan atau sudah dikonfirmasi.";
    $icon = "error";
} else {
    $p = mysqli_fetch_assoc($q);

    // --- Cegah duplikasi
    $check = mysqli_query($conn, "
        SELECT id FROM donasi_transaksi 
        WHERE id_donasi = {$p['id_donasi']} 
          AND nama_donatur = '" . mysqli_real_escape_string($conn, $p['nama_donatur']) . "'
          AND jumlah = {$p['jumlah']}
        LIMIT 1
    ");

    if ($check && mysqli_num_rows($check) > 0) {
        $msg  = "Donasi ini sudah pernah dikonfirmasi sebelumnya.";
        $icon = "info";
    } else {
        // --- Pindahkan ke donasi_transaksi (metode transfer)
        $id_donasi = intval($p['id_donasi']);
        $nama_donatur = mysqli_real_escape_string($conn, $p['nama_donatur']);
        $jumlah = floatval($p['jumlah']);
        $tanggal = date('Y-m-d H:i:s');
        $metode = 'transfer';

        $insert = mysqli_query($conn, "
            INSERT INTO donasi_transaksi (id_donasi, nama_donatur, jumlah, tanggal_transaksi, metode)
            VALUES ($id_donasi, '$nama_donatur', $jumlah, '$tanggal', '$metode')
        ");

        if ($insert) {
            // Hapus data pending agar tidak double
            mysqli_query($conn, "DELETE FROM donasi_pending WHERE id = $pending_id LIMIT 1");
            $msg  = "Donasi dari <b>$nama_donatur</b> sebesar <b>Rp " . number_format($jumlah, 0, ',', '.') . "</b> berhasil dikonfirmasi.";
            $icon = "success";
        } else {
            $msg  = "Gagal memindahkan data ke tabel transaksi.<br>" . mysqli_error($conn);
            $icon = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            Swal.fire({
                icon: '<?= $icon ?>',
                title: 'Konfirmasi Donasi',
                html: `<?= addslashes($msg) ?>`,
                confirmButtonColor: '#2c7a7b',
                confirmButtonText: 'OK',
                timer: 3500,
                timerProgressBar: true
            }).then(() => {
                window.location.href = 'konfirmasi.php';
            });
        });
    </script>
</body>

</html>