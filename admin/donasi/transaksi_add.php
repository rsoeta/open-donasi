<?php
include_once('../../includes/config.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: ../../login.php');
    exit;
}

date_default_timezone_set('Asia/Jakarta');

// Ambil daftar artikel donasi untuk dropdown
$donasiList = mysqli_query($conn, "SELECT id, judul FROM donasi_post ORDER BY judul ASC");
$site_logo = get_setting('site_logo', 'assets/images/logo.png');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Transaksi Donasi</title>

    <!-- Favicon (multi-browser support) -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL . htmlspecialchars($site_logo) ?>">
    <link rel="apple-touch-icon" href="<?= BASE_URL . htmlspecialchars($site_logo) ?>">
    <meta name="theme-color" content="#2c7a7b">

    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* --- Body & Base --- */
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            color: #333;
        }

        /* --- Header (versi asli sederhana) --- */
        header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 0;
        }

        header .container {
            width: 90%;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header img {
            height: 40px;
        }

        header nav a {
            color: #2c7a7b;
            text-decoration: none;
            margin-left: 14px;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        header nav a:hover {
            color: #205f5f;
            text-decoration: underline;
        }

        /* --- Container utama --- */
        .container {
            max-width: 700px;
            background: #fff;
            margin: 60px auto;
            padding: 35px 40px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
        }

        /* --- Judul halaman --- */
        h2 {
            text-align: center;
            color: #2c7a7b;
            margin-bottom: 25px;
            letter-spacing: 0.3px;
        }

        /* --- Form Layout --- */
        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 18px;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 6px;
            color: #2d3748;
        }

        .form-group input,
        .form-group select {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #cbd5e0;
            transition: all 0.3s ease;
            font-size: 14px;
            background: #f9fafb;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2c7a7b;
            box-shadow: 0 0 5px rgba(44, 122, 123, 0.4);
            background: #fff;
        }

        /* --- Tombol Form --- */
        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-save,
        .btn-cancel {
            flex: 1;
            border: none;
            border-radius: 6px;
            padding: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s ease;
            font-size: 14px;
        }

        /* Tombol simpan */
        .btn-save {
            background-color: #2c7a7b;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-save:hover {
            background-color: #205f5f;
        }

        /* Tombol batal */
        .btn-cancel {
            background-color: #e2e8f0;
            color: #2d3748;
            text-decoration: none;
            text-align: center;
            line-height: 37px;
        }

        .btn-cancel:hover {
            background-color: #cbd5e1;
        }

        /* --- Responsif Mobile --- */
        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 30px 10px;
            }

            header .container {
                flex-direction: column;
                text-align: center;
            }

            header nav {
                margin-top: 8px;
            }

            header nav a {
                font-size: 13px;
            }

            h2 {
                font-size: 20px;
            }
        }

        /* --- Animasi kecil tombol --- */
        .btn-save:active,
        .btn-cancel:active {
            transform: scale(0.98);
        }
    </style>
</head>

<body>

    <header>
        <div class="container">
            <img src="<?= BASE_URL . htmlspecialchars($site_logo) ?>" alt="Logo" height="40">
            <nav>
                <a href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a>
                <a href="<?= BASE_URL ?>logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2>Tambah Transaksi Donasi</h2>

        <form action="transaksi_save.php" method="POST">
            <!-- field lain seperti id_donasi, nama_donatur, jumlah, tanggal_transaksi -->
            <input type="hidden" name="metode" value="cash">
            <!-- atau jika mau select:
            <select name="metode" required>
                <option value="cash" selected>Cash (Tunai)</option>
                <option value="transfer">Transfer / Bank</option>
            </select>
            -->
            <div class="form-group">
                <label for="id_donasi">Penerima Donasi</label>
                <select name="id_donasi" id="id_donasi" required>
                    <option value="">-- Pilih Penerima Donasi --</option>
                    <?php while ($row = mysqli_fetch_assoc($donasiList)): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['judul']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="nama_donatur">Nama Donatur</label>
                <input type="text" name="nama_donatur" id="nama_donatur" placeholder="Contoh: Hamba Allah" required>
            </div>

            <div class="form-group">
                <label for="jumlah">Jumlah Donasi (Rp)</label>
                <input
                    type="text"
                    id="jumlah"
                    name="jumlah"
                    required
                    placeholder="Contoh: 150.000"
                    inputmode="numeric"
                    oninput="formatRibuan(this)">
            </div>

            <div class="form-group">
                <label for="tanggal_transaksi">Tanggal Transaksi</label>
                <input type="datetime-local" name="tanggal_transaksi" id="tanggal_transaksi" required value="<?= date('Y-m-d\TH:i') ?>">
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn-save">💾 Simpan Transaksi</button>
                <a href="../laporan/laporan.php" class="btn-cancel">Kembali</a>
            </div>
        </form>
    </div>

    <script>
        // Format ribuan saat mengetik
        function formatRibuan(input) {
            let angka = input.value.replace(/\D/g, '');
            input.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Hapus titik sebelum kirim ke server
        document.querySelector('form').addEventListener('submit', function() {
            const jumlah = document.getElementById('jumlah');
            jumlah.value = jumlah.value.replace(/\./g, '');
        });
    </script>

    <script>
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= urldecode($_GET['info']) ?>',
                confirmButtonColor: '#2c7a7b',
                confirmButtonText: 'Lihat Laporan',
                timer: 3000,
                timerProgressBar: true,
                didOpen: () => {
                    const content = Swal.getHtmlContainer();
                    const timerEl = document.createElement('div');
                    timerEl.style.marginTop = '10px';
                    timerEl.style.fontSize = '13px';
                    timerEl.style.color = '#555';
                    timerEl.innerText = 'Anda akan dialihkan otomatis dalam 3 detik...';
                    content.appendChild(timerEl);
                }
            }).then((result) => {
                if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                    window.location.href = "<?= BASE_URL ?>admin/laporan/laporan.php";
                }
            });
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'error'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '<?= urldecode($_GET['info']) ?>',
                confirmButtonColor: '#e53e3e'
            });
        <?php endif; ?>
    </script>

</body>

</html>