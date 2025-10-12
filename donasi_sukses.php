<?php
include_once('includes/config.php');
include_once('includes/header.php');
?>

<main style="max-width:700px;margin:60px auto;background:#fff;padding:40px;text-align:center;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,0.05);">
    <h2 style="color:#2c7a7b;">Terima Kasih 🙏</h2>
    <p style="font-size:16px;color:#444;margin-top:10px;">
        Donasi Anda telah kami terima dan sedang dalam proses verifikasi.
    </p>
    <p style="font-size:15px;color:#666;margin-top:10px;">
        Mohon tunggu beberapa saat hingga admin kami mengkonfirmasi pembayaran Anda.
    </p>
    <a href="<?= BASE_URL ?>index.php" style="display:inline-block;margin-top:20px;padding:10px 20px;background:#3182ce;color:white;border-radius:6px;text-decoration:none;">Kembali ke Beranda</a>
</main>

<?php include_once('includes/footer.php'); ?>