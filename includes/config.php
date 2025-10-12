<?php

/** -------------------------------------------------------------------
 * Konfigurasi Koneksi Database
 * ------------------------------------------------------------------- */
$host = "localhost";
$user = "root";
$pass = "";
$db   = "open_donasi";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

/** -------------------------------------------------------------------
 * Konfigurasi URL Dasar (Base URL)
 * ------------------------------------------------------------------- */

/**
 * BASE_URL otomatis mendeteksi root URL
 * — bekerja untuk Laragon (open-donasi.test) dan hosting biasa.
 */
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

$hostName = $_SERVER['HTTP_HOST'];

// Ambil direktori proyek dari file config.php
$baseDir = str_replace('\\', '/', realpath(__DIR__ . '/../'));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));

// Jika Laragon .test, DOCUMENT_ROOT dan baseDir bisa sama, jadi abaikan perbedaan
$subFolder = str_replace($docRoot, '', $baseDir);
if ($subFolder == '' || $subFolder == '/') {
    $subFolder = ''; // berarti domain langsung ke root proyek
}

// Pastikan format akhir: http://open-donasi.test/
define('BASE_URL', rtrim($protocol . $hostName . $subFolder, '/') . '/');


/** -------------------------------------------------------------------
 * Fungsi Helper: Setting Situs
 * ------------------------------------------------------------------- */

/**
 * Ambil setting berdasarkan key (mengembalikan default jika tidak ada)
 */
function get_setting($key, $default = '')
{
    global $conn;
    $key = mysqli_real_escape_string($conn, $key);
    $q = mysqli_query($conn, "SELECT svalue FROM settings WHERE skey = '$key' LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        $r = mysqli_fetch_assoc($q);
        return $r['svalue'];
    }
    return $default;
}

/**
 * Simpan atau perbarui setting situs.
 */
function set_setting($key, $value)
{
    global $conn;
    $key = mysqli_real_escape_string($conn, $key);
    $value = mysqli_real_escape_string($conn, $value);

    $q = mysqli_query($conn, "SELECT id FROM settings WHERE skey = '$key' LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        return mysqli_query($conn, "UPDATE settings SET svalue = '$value' WHERE skey = '$key' LIMIT 1");
    } else {
        return mysqli_query($conn, "INSERT INTO settings (skey, svalue) VALUES ('$key', '$value')");
    }
}
