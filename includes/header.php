<?php
// di includes/header.php (pastikan file ini include config.php sebelumnya)
if (!function_exists('get_setting')) {
    include_once __DIR__ . '/config.php';
    date_default_timezone_set('Asia/Jakarta');
}
$site_name = get_setting('site_name', 'Open Donasi');
$site_contact = get_setting('site_contact', 'info@example.com');
$site_logo = get_setting('site_logo', 'assets/images/logo.png');

// Detect Preview Image Automatically
function get_meta_image()
{
    global $row; // digunakan di detail.php

    // Jika halaman detail dan ada gambar
    if (isset($row['gambar']) && !empty($row['gambar'])) {
        return BASE_URL . "uploads/" . $row['gambar'];
    }

    // Default logo situs
    $logo = get_setting('site_logo', 'assets/images/logo.png');
    return BASE_URL . $logo;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($site_name) ?></title>
    <!-- Open Graph (Facebook, WhatsApp, Instagram) -->
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?= htmlspecialchars($page_title ?? $site_name) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_desc ?? 'Program Donasi') ?>">
    <meta property="og:image" content="<?= get_meta_image() ?>">
    <meta property="og:url" content="<?= BASE_URL . ($_SERVER['REQUEST_URI'] ?? '') ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($site_name) ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($page_title ?? $site_name) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($page_desc ?? 'Program Donasi') ?>">
    <meta name="twitter:image" content="<?= get_meta_image() ?>">

    <meta property="og:image" content="<?= get_meta_image() . '?v=' . time() ?>">

    <!-- Favicon (multi-browser support) -->
    <link rel="icon" type="image/png" sizes="32x32" href=" <?= BASE_URL . htmlspecialchars($site_logo) ?>">
    <link rel="apple-touch-icon" href="<?= BASE_URL . htmlspecialchars($site_logo) ?>">
    <meta name="theme-color" content="#2c7a7b">

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="/"><img src="<?= BASE_URL . htmlspecialchars($site_logo) ?>" alt="<?= htmlspecialchars($site_name) ?>" height="40"></a>
            </div>

            <nav>
                <a href="/index.php">Home</a> |
                <a href="<?= BASE_URL ?>tentang.php">Tentang</a> |
                <a href="/login.php">Login</a>
            </nav>
        </div>
    </header>