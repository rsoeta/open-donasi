<?php
include_once('../../includes/config.php');
include_once('../../includes/wa.php');
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

$q = mysqli_query($conn, "SELECT * FROM donasi_pending WHERE id = $pending_id LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    $msg = "Data donasi tidak ditemukan atau sudah diproses.";
    $icon = "error";
} else {
    $row = mysqli_fetch_assoc($q);

    $update = mysqli_query($conn, "
        UPDATE donasi_pending 
        SET status = 'ditolak' 
        WHERE id = $pending_id
        LIMIT 1
    ");

    if ($update) {
        $nama = htmlspecialchars($row['nama_donatur']);
        $jumlah = number_format($row['jumlah'], 0, ',', '.');

        // ========= KIRIM WA =========
        $wa = preg_replace('/\D/', '', $row['wa']);

        if ($wa) {
            $template = get_setting(
                'wa_msg_rejected',
                "Mohon maaf {nama}, donasi Anda tidak dapat diverifikasi."
            );

            $program = mysqli_fetch_assoc(
                mysqli_query($conn, "SELECT judul FROM donasi_post WHERE id={$row['id_donasi']} LIMIT 1")
            );
            $program_name = $program ? $program['judul'] : '-';

            $pesan = str_replace(
                ['{nama}', '{program}'],
                [$row['nama_donatur'], $program_name],
                $template
            );

            $template = get_setting('wa_msg_rejected');
            $pesan  = html_to_whatsapp($template);
            // replace placeholder
            $pesan = str_replace([
                '{{site_name}}',
                '{{nama}}',
                '{{program}}',
            ], [
                get_setting('site_name', 'Open Donasi'),
                $row['nama_donatur'],
                $program_name,
            ], $pesan);

            sendWhatsAppMessage($wa, $pesan);
        }

        $msg  = "Donasi dari <b>$nama</b> sebesar <b>Rp $jumlah</b> telah ditolak.";
        $icon = "warning";
    } else {
        $msg  = "Gagal memperbarui status donasi.<br>" . mysqli_error($conn);
        $icon = "error";
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
        Swal.fire({
            icon: '<?= $icon ?>',
            title: 'Konfirmasi Penolakan',
            html: `<?= addslashes($msg) ?>`,
            confirmButtonColor: '#e53e3e',
            timer: 3500,
            timerProgressBar: true
        }).then(() => {
            window.location.href = 'konfirmasi.php';
        });
    </script>
</body>

</html>