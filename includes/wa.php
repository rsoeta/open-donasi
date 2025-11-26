<?php

/**
 * Normalisasi nomor WA agar sesuai format internasional (62)
 * Contoh:
 * 0812345678  → 62812345678
 * +62812345678 → 62812345678
 * 812345678   → 62812345678
 */
function normalisasi_wa($phone)
{
    // Hilangkan semua karakter selain angka
    $phone = preg_replace('/\D/', '', $phone);

    // Jika diawali "0"
    if (substr($phone, 0, 1) === '0') {
        return '62' . substr($phone, 1);
    }

    // Jika sudah diawali 62 → langsung kembalikan
    if (substr($phone, 0, 2) === '62') {
        return $phone;
    }

    // Jika diawali +62
    if (substr($phone, 0, 3) === '062') {
        return substr($phone, 1);
    }

    // Jika hanya "81234..." → tambahkan 62
    if (substr($phone, 0, 1) !== '6') {
        return '62' . $phone;
    }

    return $phone;
}

/**
 * Konversi HTML ke format teks WhatsApp
 */
function html_to_whatsapp($html)
{
    // ubah <br> dan <p> menjadi newline
    $text = preg_replace('/<\s*br\s*\/?>/i', "\n", $html);
    $text = preg_replace('/<\s*p[^>]*>/i', "\n", $text);
    $text = preg_replace('/<\s*\/p\s*>/i', "\n", $text);

    // hapus semua tag html lainnya
    $text = strip_tags($text);

    // decode entitas HTML
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

    // bersihkan spasi berlebih
    $text = preg_replace("/\n\s*\n\s*\n+/", "\n\n", $text);
    $text = trim($text);

    return $text;
}

/**
 * get_setting() sudah ada di sistem kamu, jadi kita tinggal pakai saja.
 * Pastikan settings:
 * - alatwa_api_key
 * - alatwa_device_id
 * sudah ada di tabel settings.
 */

function alatwa_get_api_headers()
{
    $api_key = get_setting('alatwa_api_key', '');
    return [
        "Authorization: $api_key",
        "Content-Type: application/json"
    ];
}

function alatwa_get_device_id()
{
    return get_setting('alatwa_device_id', '');
}

/**
 * ==========================================================
 *     1) FUNGSI KIRIM TEKS
 * ==========================================================
 */
function sendWhatsAppMessage($receiver, $message)
{
    $receiver = normalisasi_wa($receiver); // <-- FIX PENTING

    $device_id = alatwa_get_device_id();
    $api_header = alatwa_get_api_headers();
    $url = "https://api.alatwa.com/send/text";

    $message = trim($message);
    $message = str_replace(["\r\n", "\n", "\r"], "\n", $message);

    $payload = [
        "device"  => $device_id,
        "phone"   => $receiver,
        "message" => $message
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $api_header);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        file_put_contents(__DIR__ . "/../wa_error.log", date("Y-m-d H:i:s") . " CURL ERROR: $err\n", FILE_APPEND);
        return ["status" => false, "error" => $err];
    }

    $result = json_decode($response, true);

    if (!$result) {
        file_put_contents(__DIR__ . "/../wa_error.log", date("Y-m-d H:i:s") . " INVALID JSON RESPONSE: $response\n", FILE_APPEND);
        return ["status" => false, "error" => "Invalid JSON response", "raw" => $response];
    }

    return $result;
}

/**
 * ==========================================================
 *     2) FUNGSI KIRIM MEDIA (Gambar / File)
 * ==========================================================
 * format alatwa untuk kirim media (GAMBAR / PDF / DOC)
 * endpoint:
 *    POST https://api.alatwa.com/send/media
 * body JSON:
 * {
 *    "device": "",
 *    "phone": "",
 *    "caption": "",
 *    "url": "URL_FILE"
 * }
 * ==========================================================
 */
function sendWhatsAppMedia($receiver, $caption, $fileUrl)
{

    $device_id = alatwa_get_device_id();
    $api_header = alatwa_get_api_headers();
    $url = "https://api.alatwa.com/send/media";

    // Bersihkan caption
    $caption = trim($caption);
    $caption = str_replace(["\r\n", "\n", "\r"], "\n", $caption);

    $payload = [
        "device"  => $device_id,
        "phone"   => $receiver,
        "caption" => $caption,
        "url"     => $fileUrl  // file harus bisa diakses publik (HTTP/HTTPS)
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $api_header);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        file_put_contents(__DIR__ . "/../wa_error.log", date("Y-m-d H:i:s") . " CURL ERROR (MEDIA): $err\n", FILE_APPEND);
        return ["status" => false, "error" => $err];
    }

    $result = json_decode($response, true);

    if (!$result) {
        file_put_contents(__DIR__ . "/../wa_error.log", date("Y-m-d H:i:s") . " INVALID JSON (MEDIA): $response\n", FILE_APPEND);
        return ["status" => false, "error" => "Invalid JSON response", "raw" => $response];
    }

    return $result;
}


/**
 * ==========================================================
 *     3) Notifikasi otomatis ke Admin
 * ==========================================================
 */
function notify_admin($message)
{

    // nomor admin dari table settings
    $admin_number = get_setting('admin_whatsapp', '');

    if (!$admin_number) {
        file_put_contents(__DIR__ . "/../wa_error.log", date("Y-m-d H:i:s") . " ADMIN NUMBER NOT FOUND\n", FILE_APPEND);
        return;
    }

    return sendWhatsAppMessage($admin_number, $message);
}
