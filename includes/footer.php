<?php
// includes/footer.php
if (!function_exists('get_setting')) {
    include_once __DIR__ . '/config.php';
}
$site_contact = get_setting('site_contact', 'info@example.com');
$site_bank    = get_setting('site_bank', 'Bank BCA - 1234567890');
$site_owner   = get_setting('site_owner', 'Komunitas Sahabat Al Hilal');
$site_name    = get_setting('site_name', 'Open Donasi');
?>

<footer>
    <div class="container">
        <div style="margin-bottom:8px;">
            <strong>Kontak:</strong> <?= htmlspecialchars($site_contact) ?>
        </div>
        <div style="margin-bottom:8px;">
            <strong>Rekening Bank:</strong> <?= htmlspecialchars($site_bank) ?>
        </div>
        <div style="font-size:13px; margin-top:6px;">
            &copy; <?= date('Y') ?> <?= htmlspecialchars($site_name) ?> | <?= htmlspecialchars($site_owner) ?>
        </div>
    </div>
</footer>
</body>

</html>