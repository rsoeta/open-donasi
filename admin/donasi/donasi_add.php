<?php
session_start();
include_once('../../includes/config.php');

// Cek login
if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $target_donasi = floatval($_POST['target_donasi']);
    $status = $_POST['status'];

    // Upload gambar (jika ada)
    $gambar = null;
    if (!empty($_FILES['gambar']['name'])) {
        $targetDir = "../../uploads/";
        $fileName = time() . '_' . basename($_FILES['gambar']['name']);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFilePath)) {
                $gambar = $fileName;
            }
        }
    }

    // Simpan ke database
    $query = "INSERT INTO donasi_post (judul, gambar, deskripsi, target_donasi, status)
              VALUES ('$judul', '$gambar', '$deskripsi', '$target_donasi', '$status')";
    if (mysqli_query($conn, $query)) {
        header("Location: donasi.php?success=1");
        exit;
    } else {
        $error = "Gagal menyimpan data: " . mysqli_error($conn);
    }
}
$site_logo = get_setting('site_logo', 'assets/images/logo.png');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Artikel Donasi</title>
    <!-- Favicon (multi-browser support) -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>assets/images/logo.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>assets/images/logo.png">
    <meta name="theme-color" content="#2c7a7b">

    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">

    <style>
        .form-container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .form-container h2 {
            text-align: center;
            color: #2c7a7b;
            margin-bottom: 20px;
        }

        form label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        form input,
        form textarea,
        form select {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Open Sans', sans-serif;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .btn-submit {
            background: #2c7a7b;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-submit:hover {
            background: #205f5f;
        }

        .btn-back {
            display: inline-block;
            background: #718096;
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .btn-back:hover {
            background: #4a5568;
        }

        .alert {
            padding: 10px;
            background: #fed7d7;
            color: #c53030;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 13px;
        }
    </style>

    <!-- TinyMCE Editor -->
    <script src="../../assets/js/tinymce/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#deskripsi',
            height: 400,
            menubar: false,
            branding: false,
            plugins: 'lists link image table code',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code',

            /* -------------------- */
            /* KONFIGURASI UPLOAD GAMBAR */
            /* -------------------- */
            images_upload_url: 'upload_image.php',

            automatic_uploads: true,
            file_picker_types: 'image',

            file_picker_callback: function(callback, value, meta) {
                if (meta.filetype === 'image') {
                    var input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');

                    input.onchange = function() {
                        var file = this.files[0];
                        var formData = new FormData();
                        formData.append('file', file);

                        fetch('upload_image.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.location) {
                                    callback(data.location, {
                                        alt: file.name
                                    });
                                } else {
                                    alert('Upload gagal: ' + (data.error || 'Unknown error'));
                                }
                            })
                            .catch(err => alert('Gagal mengupload gambar: ' + err));
                    };

                    input.click();
                }
            }
        });
    </script>
</head>

<body>

    <header>
        <div class="container" style="display:flex;justify-content:space-between;align-items:center;">
            <div class="logo">
                <a href="/"><img src="<?= BASE_URL . htmlspecialchars($site_logo) ?>" alt="<?= htmlspecialchars($site_name) ?>" height="40"></a>
            </div>
            <nav>
                <a href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a> |
                <a href="<?= BASE_URL ?>logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="form-container">
        <a href="donasi.php" class="btn-back">← Kembali ke Daftar Donasi</a>
        <h2>Tambah Artikel Donasi</h2>

        <?php if (!empty($error)): ?>
            <div class="alert"><?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <label for="judul">Judul Donasi</label>
            <input type="text" name="judul" required>

            <label for="gambar">Gambar (opsional)</label>
            <input type="file" name="gambar" accept="image/*">

            <label for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi"></textarea>

            <label for="target_donasi">Target Donasi (Rp)</label>
            <!-- <input type="number" name="target_donasi" placeholder="contoh: 5000000"> -->
            <!-- style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-bottom:10px;" -->
            <input
                type="text"
                id="jumlah"
                name="target_donasi"
                required
                step="1000"
                placeholder="Contoh: 150.000"
                inputmode="numeric"
                oninput="formatRibuan(this)">


            <label for="status">Status</label>
            <select name="status">
                <option value="aktif">Aktif</option>
                <option value="ditutup">Ditutup</option>
            </select>

            <button type="submit" class="btn-submit">Simpan</button>
        </form>
        <script>
            // Fungsi untuk menambahkan titik ribuan saat mengetik
            function formatRibuan(input) {
                // Hapus semua karakter non-digit
                let angka = input.value.replace(/\D/g, '');
                // Format ribuan pakai titik
                input.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            // Hapus titik sebelum dikirim ke server
            document.querySelector('form').addEventListener('submit', function() {
                const jumlah = document.getElementById('jumlah');
                jumlah.value = jumlah.value.replace(/\./g, ''); // hapus titik semua
            });
        </script>
    </div>

</body>

</html>