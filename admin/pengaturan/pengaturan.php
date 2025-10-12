<?php
session_start();
include_once('../../includes/config.php');

if (!isset($_SESSION['admin'])) {
    header('Location: ../../login.php');
    exit;
}

$success = '';
$error = '';

// handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['tab'] === 'umum') {
        $site_name = trim($_POST['site_name']);
        $site_contact = trim($_POST['site_contact']);
        $site_bank = trim($_POST['site_bank']);
        $site_owner = trim($_POST['site_owner']);
        set_setting('site_name', $site_name);
        set_setting('site_contact', $site_contact);
        set_setting('site_bank', $site_bank);
        set_setting('site_owner', $site_owner);
        set_setting('site_city', $_POST['site_city']);

        // handle upload logo
        if (!empty($_FILES['site_logo']['name'])) {
            $allowed = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];
            $file = $_FILES['site_logo'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $targetDir = '../../assets/images/';
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                $newName = 'logo_' . time() . '.' . $ext;
                $targetPath = $targetDir . $newName;
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $dbPath = 'assets/images/' . $newName;
                    set_setting('site_logo', $dbPath);
                }
            }
        }
        $success = 'Pengaturan umum berhasil disimpan.';
    }

    if ($_POST['tab'] === 'tentang') {
        $site_about = $_POST['site_about'];
        set_setting('site_about', $site_about);
        $success = 'Halaman Tentang berhasil diperbarui.';
    }
}

// ambil data
$current_name = get_setting('site_name', 'Open Donasi');
$current_contact = get_setting('site_contact', 'info@example.com');
$current_bank = get_setting('site_bank', 'Bank BCA - 1234567890');
$current_owner = get_setting('site_owner', 'Saat kita memberi, kita sedang menyalakan harapan.');
$current_logo = get_setting('site_logo', 'assets/images/logo.png');
$current_about = get_setting('site_about', '<p>Tuliskan profil lembaga Anda di sini...</p>');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pengaturan Situs</title>
    <!-- Favicon (multi-browser support) -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>assets/images/logo.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>assets/images/logo.png">
    <meta name="theme-color" content="#2c7a7b">

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- <script src="../../assets/js/tinymce/tinymce.min.js"></script> -->
    <script src="<?= BASE_URL ?>assets/js/tinymce/tinymce.min.js"></script>

    <style>
        .settings-card {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            display: flex;
            border-bottom: 1px solid #eee;
        }

        .card-header button {
            flex: 1;
            padding: 12px;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .card-header button.active {
            background: #2c7a7b;
            color: white;
        }

        .card-body {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="file"],
        input[type="email"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .btn {
            padding: 10px 16px;
            background: #2c7a7b;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .logo-preview img {
            max-height: 80px;
            margin-top: 10px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

    <header>
        <div class="container" style="display:flex;justify-content:space-between;align-items:center;">
            <img src="<?= BASE_URL . htmlspecialchars($current_logo) ?>" alt="Logo" height="40">
            <nav>
                <a href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a> |
                <a href="<?= BASE_URL ?>logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="settings-card">
        <div class="card-header">
            <button class="tab-btn active" data-tab="umum">Umum</button>
            <button class="tab-btn" data-tab="tentang">Tentang</button>
        </div>
        <div class="card-body">

            <!-- TAB UMUM -->
            <div class="tab-content" id="tab-umum">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="tab" value="umum">
                    <div class="form-group">
                        <label>Nama Lembaga</label>
                        <input type="text" name="site_name" value="<?= htmlspecialchars($current_name) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Kontak (Email/WA)</label>
                        <input type="text" name="site_contact" value="<?= htmlspecialchars($current_contact) ?>">
                    </div>
                    <div class="form-group">
                        <label>Rekening Bank</label>
                        <input type="text" name="site_bank" value="<?= htmlspecialchars($current_bank) ?>">
                    </div>
                    <div class="form-group">
                        <label>Footer</label>
                        <input type="text" name="site_owner" value="<?= htmlspecialchars($current_owner) ?>">
                    </div>
                    <div class="form-group">
                        <label for="site_city">Kab/Kota</label>
                        <input type="text" id="site_city" name="site_city"
                            value="<?= htmlspecialchars(get_setting('site_city', 'Bandung')) ?>"
                            placeholder="Contoh: Bandung, Garut, Tasikmalaya" required>
                    </div>
                    <div class="form-group">
                        <label>Logo Lembaga</label>
                        <input type="file" name="site_logo" accept="image/*">
                        <div class="logo-preview">
                            <img src="../../<?= htmlspecialchars($current_logo) ?>" alt="Logo Saat Ini">
                        </div>
                    </div>
                    <button type="submit" class="btn">Simpan Pengaturan</button>
                </form>
            </div>

            <!-- TAB TENTANG -->
            <div class="tab-content" id="tab-tentang" style="display:none;">
                <form method="post">
                    <input type="hidden" name="tab" value="tentang">
                    <label>Isi Halaman Tentang</label>
                    <textarea id="site_about" name="site_about"><?= htmlspecialchars($current_about) ?></textarea>
                    <button type="submit" class="btn" style="margin-top:10px;">Simpan Halaman Tentang</button>
                </form>
            </div>

        </div>
    </div>

    <script>
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
                this.classList.add('active');
                document.getElementById('tab-' + this.dataset.tab).style.display = 'block';
            });
        });

        // TinyMCE untuk Tab Tentang
        tinymce.init({
            selector: '#site_about',
            height: 400,
            menubar: false,
            plugins: 'lists link image table code',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code',
            branding: false
        });
    </script>

    <?php if ($success): ?>
        <script>
            Swal.fire('Berhasil!', '<?= addslashes($success) ?>', 'success');
        </script>
    <?php endif; ?>

</body>

</html>