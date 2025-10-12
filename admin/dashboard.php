<?php
session_start();
include_once('../includes/config.php');

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

$email = $_SESSION['admin'];

// Ambil nilai saat ini
$current_name = get_setting('site_name', 'Open Donasi');
$current_contact = get_setting('site_contact', 'info@example.com');
$site_logo = get_setting('site_logo', 'assets/images/logo.png');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - <?= htmlspecialchars($current_name) ?></title>
    <!-- Favicon (multi-browser support) -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>assets/images/logo.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>assets/images/logo.png">
    <meta name="theme-color" content="#2c7a7b">


    <!-- Sekarang BASE_URL aman dan pasti menunjuk ke root -->
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">

    <style>
        .dashboard-container {
            max-width: 1000px;
            margin: 60px auto;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .dashboard-header h2 {
            color: #2c7a7b;
            margin-bottom: 10px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .menu-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            text-align: center;
            padding: 25px 20px;
            transition: all 0.3s ease;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .menu-card h3 {
            color: #2c7a7b;
            margin-bottom: 10px;
        }

        .menu-card p {
            color: #555;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .menu-card a {
            text-decoration: none;
            background: #2c7a7b;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
        }

        .menu-card a:hover {
            background: #205f5f;
        }
    </style>
</head>

<body>

    <header>
        <div class="container" style="display:flex;justify-content:space-between;align-items:center;">
            <img src="<?= BASE_URL . htmlspecialchars($site_logo) ?>" alt="Logo" height="40">
            <nav>
                <a href="<?= BASE_URL ?>index.php">Home</a> |
                <a href="<?= BASE_URL ?>logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h2>Selamat Datang, <?= htmlspecialchars($email) ?></h2>
        </div>

        <div class="menu-grid">
            <?php
            $pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM donasi_pending WHERE status='menunggu'"))['jml'];
            ?>
            <div class="menu-card">
                <h3>Data Donasi</h3>
                <p>Kelola daftar donasi.</p>
                <a href="<?= BASE_URL ?>admin/donasi/donasi.php">Buka</a>
                <br><br>
                <a href="<?= BASE_URL ?>admin/donasi/konfirmasi.php" style="display:inline-block;margin-top:8px;background:#d69e2e;color:white;padding:6px 12px;border-radius:6px;text-decoration:none;font-size:14px;">
                    Konfirmasi Donasi <?= $pending_count > 0 ? '(' . $pending_count . ')' : '' ?>
                </a>
            </div>

            <div class="menu-card">
                <h3>Laporan</h3>
                <p>Lihat rekap data dan statistik donasi.</p>
                <a href="<?= BASE_URL ?>admin/laporan/laporan.php">Buka</a>
                <br><br>
                <a href="<?= BASE_URL ?>admin/donasi/transaksi_add.php"
                    style="display:inline-block;margin-top:8px;background:#2c7a7b;color:white;padding:6px 12px;border-radius:6px;text-decoration:none;font-size:14px;">
                    + Tambah Transaksi Manual
                </a>
            </div>

            <div class="menu-card">
                <h3>Pengaturan</h3>
                <p>Ubah informasi admin.</p>
                <a href="<?= BASE_URL ?>admin/pengaturan/pengaturan.php">Buka</a>
            </div>
        </div>
    </div>

</body>

</html>