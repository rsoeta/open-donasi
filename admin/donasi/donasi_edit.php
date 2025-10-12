<?php
include_once('../../includes/config.php');
session_start();

// Ambil nilai saat ini
$current_name = get_setting('site_name', 'Open Donasi');
$current_contact = get_setting('site_contact', 'info@example.com');
$site_logo = get_setting('site_logo', 'assets/images/logo.png');


if (!isset($_SESSION['admin'])) {
    header('Location: ../../login.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$result = mysqli_query($conn, "SELECT * FROM donasi_post WHERE id = $id");
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Donasi</title>
    <!-- Favicon (multi-browser support) -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>assets/images/logo.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>assets/images/logo.png">
    <meta name="theme-color" content="#2c7a7b">

    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <div class="container">
        <h2>Edit Artikel Donasi</h2>
        <form action="donasi_update.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">

            <label>Judul</label>
            <input type="text" name="judul" value="<?= htmlspecialchars($row['judul']) ?>" required>

            <label for="gambar">Gambar Utama</label>
            <?php if (!empty($row['gambar'])): ?>
                <img src="../../uploads/<?= $row['gambar'] ?>" alt="Gambar Utama" style="width:200px;display:block;margin-bottom:10px;border-radius:6px;">
            <?php endif; ?>
            <input type="file" name="gambar" accept="image/*">

            <label>Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi"><?= htmlspecialchars($row['deskripsi']) ?></textarea>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;">
                <button type="submit" class="btn-submit">Simpan Perubahan</button>
                <a href="donasi.php" class="btn-back" style="margin:0">Batal</a>
            </div>
        </form>
    </div>
    <script>
        tinymce.init({
            selector: '#deskripsi',
            height: 400,
            menubar: false,
            branding: false,
            plugins: 'lists link image table code',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code',
            images_upload_url: 'upload_image.php',
        });
    </script>
</body>

</html>