<?php
include_once('../../includes/config.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: ../../login.php');
    exit;
}

// Ambil ID donasi pending dari URL
$pending_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($pending_id <= 0) {
    header('Location: konfirmasi.php');
    exit;
}

// 🔍 Cek apakah data masih ada
$q = mysqli_query($conn, "SELECT * FROM donasi_pending WHERE id = $pending_id LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    $msg = "Data donasi tidak ditemukan atau sudah diproses.";
    $icon = "error";
} else {
    $row = mysqli_fetch_assoc($q);

    // 🔁 Update status menjadi 'ditolak'
    $update = mysqli_query($conn, "
        UPDATE donasi_pending 
        SET status = 'ditolak' 
        WHERE id = $pending_id
        LIMIT 1
    ");

    if ($update) {
        $nama = htmlspecialchars($row['nama_donatur']);
        $jumlah = number_format($row['jumlah'], 0, ',', '.');
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
        document.addEventListener("DOMContentLoaded", () => {
            Swal.fire({
                icon: '<?= $icon ?>',
                title: 'Konfirmasi Penolakan',
                html: `<?= addslashes($msg) ?>`,
                confirmButtonColor: '#e53e3e',
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