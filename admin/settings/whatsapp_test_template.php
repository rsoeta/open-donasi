<?php
include_once('../../includes/config.php');
require_once('../../includes/wa.php');

header('Content-Type: application/json');

// Ambil nomor WA admin
$admin_wa = get_setting('admin_whatsapp', '');

if (!$admin_wa) {
    echo json_encode([
        "status" => "error",
        "error" => "Nomor admin belum diset."
    ]);
    exit;
}

// Ambil template pesan
$template = get_setting('wa_msg_approved', "Pesan uji: WA template bekerja dengan baik.");

// Kirim WA
$result = sendWhatsAppMessage($admin_wa, $template);

// --- Normalisasi Response ---

if (isset($result['status']) && ($result['status'] === 'ok' || $result['status'] === 'success')) {
    // sukses
    echo json_encode([
        "status" => "ok",
        "message" => "Pesan uji berhasil dikirim ke <b>$admin_wa</b>.<br>Message ID: " . ($result['message_id'] ?? '-'),
        "raw" => $result
    ]);
} else {
    // gagal
    echo json_encode([
        "status" => "error",
        "error" => $result['error'] ?? "Gagal mengirim pesan.",
        "raw" => $result
    ]);
}
exit;
