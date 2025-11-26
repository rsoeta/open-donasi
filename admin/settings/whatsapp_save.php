<?php
session_start();
include_once('../../includes/config.php');

if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

$api_key   = trim($_POST['api_key'] ?? '');
$device_id = trim($_POST['device_id'] ?? '');
$admin_wa  = trim($_POST['admin_wa'] ?? '');

// Validasi dasar
$errors = [];

if ($api_key === '') $errors[] = "API Key tidak boleh kosong.";
if ($device_id === '') $errors[] = "Device ID tidak boleh kosong.";

if (!preg_match('/^[0-9]+$/', $admin_wa)) {
    $errors[] = "Nomor WhatsApp hanya boleh angka.";
}

if (strpos($admin_wa, '62') !== 0) {
    $errors[] = "Nomor WhatsApp harus diawali 62 (tanpa +).";
}

// Jika ada error → kembali ke halaman
if (!empty($errors)) {
    $msg = urlencode(implode("<br>", $errors));
    header("Location: whatsapp_settings.php?error=$msg");
    exit;
}

// Simpan setting
set_setting('alatwa_api_key', $api_key);
set_setting('alatwa_device_id', $device_id);
set_setting('admin_whatsapp', $admin_wa);

header("Location: whatsapp_settings.php?success=1");
exit;
