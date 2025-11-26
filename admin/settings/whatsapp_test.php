<?php
session_start();
include_once('../../includes/config.php');
include_once('../../includes/wa.php');

header("Content-Type: application/json");

// admin harus login
if (!isset($_SESSION['admin'])) {
    echo json_encode([
        "status" => "error",
        "error"  => "Session expired. Silakan login kembali."
    ]);
    exit;
}

// ambil nomor admin
$admin_wa = get_setting('admin_whatsapp', '');
if (!$admin_wa) {
    echo json_encode([
        "status" => "error",
        "error"  => "Nomor WhatsApp admin belum diatur."
    ]);
    exit;
}

// buat pesan test
$message  = "Test koneksi WhatsApp dari Open-Donasi ✔\n";
$message .= "Waktu: " . date("Y-m-d H:i:s") . "\n";

// kirim WA
$result = sendWhatsAppMessage($admin_wa, $message);

// cek hasil alatwa
// cek hasil alatwa
if (!isset($result['status']) || !in_array($result['status'], ['success', 'ok'])) {
    echo json_encode([
        "status" => "error",
        "error"  => json_encode($result)
    ]);
    exit;
}

// sukses
echo json_encode([
    "status" => "success",
    "message" => "Pesan test berhasil dikirim ke " . $admin_wa
]);
exit;
