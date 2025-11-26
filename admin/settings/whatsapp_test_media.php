<?php
session_start();
include_once('../../includes/config.php');
include_once('../../includes/wa.php');

header("Content-Type: application/json");

// admin wajib login
if (!isset($_SESSION['admin'])) {
    echo json_encode([
        "status" => "error",
        "error"  => "Session expired, silakan login kembali."
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

// URL gambar test (pakai logo Open-Donasi atau placeholder)
$testImage = BASE_URL . "assets/images/logo.png";

// caption
$caption  = "Test kirim media (gambar) dari Open-Donasi ✔\n";
$caption .= "Waktu: " . date("Y-m-d H:i:s");

// kirim media
$result = sendWhatsAppMedia($admin_wa, $caption, $testImage);

// cek hasil alatwa (status bisa 'ok' atau 'success')
if (!isset($result['status']) || !in_array($result['status'], ['ok', 'success'])) {
    echo json_encode([
        "status" => "error",
        "error"  => json_encode($result)
    ]);
    exit;
}

// sukses
echo json_encode([
    "status"  => "success",
    "message" => "Media berhasil dikirim ke " . $admin_wa
]);
exit;
