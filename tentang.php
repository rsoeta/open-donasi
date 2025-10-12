<?php
include_once('includes/config.php');
$site_name = get_setting('site_name', 'Open Donasi');
$site_about = get_setting('site_about', '<p>Belum ada informasi tentang lembaga ini.</p>');
$site_logo = get_setting('site_logo', 'assets/images/logo.png');
include_once('includes/header.php');
?>

<main style="max-width:900px;margin:40px auto;background:#fff;padding:30px;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,0.05);">
    <h2 style="text-align:center;margin-bottom:20px;">Tentang <?= htmlspecialchars($site_name) ?></h2>
    <div><?= $site_about ?></div>
</main>

<?php include_once('includes/footer.php'); ?>