<?php
session_start();
include_once('../../includes/config.php');

if (!isset($_SESSION['admin'])) {
    header('Location: ../../login.php');
    exit;
}

$success = '';
$error = '';

// ========== HANDLE POST ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // TAB UMUM
    if ($_POST['tab'] === 'umum') {
        set_setting('site_name', trim($_POST['site_name']));
        set_setting('site_contact', trim($_POST['site_contact']));
        set_setting('site_bank', trim($_POST['site_bank']));
        set_setting('site_owner', trim($_POST['site_owner']));
        set_setting('site_city', trim($_POST['site_city']));

        // Upload Logo
        if (!empty($_FILES['site_logo']['name'])) {
            $allowed = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];
            $file = $_FILES['site_logo'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $dir = '../../assets/images/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);

                $newName = 'logo_' . time() . '.' . $ext;
                $path = $dir . $newName;

                if (move_uploaded_file($file['tmp_name'], $path)) {
                    set_setting('site_logo', 'assets/images/' . $newName);
                }
            }
        }

        $success = "Pengaturan umum berhasil disimpan.";
    }

    // TAB TENTANG
    if ($_POST['tab'] === 'tentang') {
        set_setting('site_about', $_POST['site_about']);
        $success = "Halaman Tentang telah diperbarui.";
    }

    // TAB WHATSAPP & LOKASI
    if ($_POST['tab'] === 'wa') {

        set_setting('alatwa_api_key', trim($_POST['api_key']));
        set_setting('alatwa_device_id', trim($_POST['device_id']));
        set_setting('admin_whatsapp', trim($_POST['admin_wa']));

        set_setting('masjid_lat', trim($_POST['masjid_lat']));
        set_setting('masjid_lng', trim($_POST['masjid_lng']));

        set_setting('wa_msg_approved', $_POST['wa_msg_approved']);
        set_setting('wa_msg_rejected', $_POST['wa_msg_rejected']);

        $success = "Pengaturan WhatsApp & Lokasi berhasil disimpan.";
    }
}

// ========== LOAD SETTINGS ==========
$current_logo = get_setting('site_logo', 'assets/images/logo.png');

$current_name = get_setting('site_name', 'Open Donasi');
$current_contact = get_setting('site_contact', '');
$current_bank = get_setting('site_bank', '');
$current_owner = get_setting('site_owner', '');
$current_about = get_setting('site_about', '');

$current_city = get_setting('site_city', 'Bandung');

// WA CONFIG
$api_key = get_setting('alatwa_api_key', '');
$device_id = get_setting('alatwa_device_id', '');
$admin_wa = get_setting('admin_whatsapp', '');

// Lokasi
$lat = get_setting('masjid_lat', '-6.123456');
$lng = get_setting('masjid_lng', '107.123456');

// Template WA
$wa_ok = get_setting(
    'wa_msg_approved',
    "Assalamu'alaikum {{nama}},\n
Terima kasih telah berdonasi sebesar Rp {{jumlah}} untuk program {{program}}.\n
Semoga Allah membalas kebaikan Anda."
);

$wa_no = get_setting(
    'wa_msg_rejected',
    "Assalamu'alaikum {{nama}},\n
Mohon maaf donasi Anda tidak dapat diproses.\n
Silakan hubungi admin."
);

?>
<!DOCTYPE html>
<html lang='id'>

<head>
    <meta charset='UTF-8'>
    <title>Pengaturan Situs</title>

    <!-- Favicon (multi-browser support) -->
    <link rel="icon" type="image/png" sizes="32x32" href=" <?= BASE_URL . htmlspecialchars($site_logo) ?>">
    <link rel="apple-touch-icon" href="<?= BASE_URL . htmlspecialchars($site_logo) ?>">
    <meta name="theme-color" content="#2c7a7b">

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- <script src="../../assets/js/tinymce/tinymce.min.js"></script> -->

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

        <!-- TABS -->
        <div class="card-header">
            <button class="tab-btn active" data-tab="umum">Umum</button>
            <button class="tab-btn" data-tab="tentang">Tentang</button>
            <button class="tab-btn" data-tab="wa">WhatsApp & Lokasi</button>
        </div>

        <div class="card-body">

            <!-- ========================= -->
            <!-- TAB : UMUM                -->
            <!-- ========================= -->
            <div class="tab-content" id="tab-umum">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="tab" value="umum">

                    <div class="form-group">
                        <label>Nama Lembaga</label>
                        <input type="text" name="site_name" value="<?= htmlspecialchars($current_name) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Kontak (Email / WA)</label>
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
                        <label>Kab/Kota</label>
                        <input type="text" name="site_city" value="<?= htmlspecialchars($current_city) ?>">
                    </div>

                    <div class="form-group">
                        <label>Logo Lembaga</label>
                        <input type="file" name="site_logo" accept="image/*">
                        <div class="logo-preview">
                            <img src="../../<?= htmlspecialchars($current_logo) ?>" alt="Logo">
                        </div>
                    </div>

                    <button class="btn">Simpan Pengaturan</button>
                </form>
            </div>

            <!-- ========================= -->
            <!-- TAB : TENTANG             -->
            <!-- ========================= -->
            <div class="tab-content" id="tab-tentang" style="display:none;">
                <form method="post">
                    <input type="hidden" name="tab" value="tentang">

                    <label>Isi Halaman Tentang</label>
                    <textarea id="site_about" name="site_about"><?= htmlspecialchars($current_about) ?></textarea>

                    <button class="btn" style="margin-top:10px;">Simpan Halaman Tentang</button>
                </form>
            </div>

            <!-- ========================= -->
            <!-- TAB : WHATSAPP & LOKASI   -->
            <!-- ========================= -->
            <div class="tab-content" id="tab-wa" style="display:none;">
                <form method="post">
                    <input type="hidden" name="tab" value="wa">

                    <h4 style="color:#2c7a7b;">⚙️ Pengaturan WhatsApp (alatwa.com)</h4>
                    <hr>

                    <div class="form-group">
                        <label>API Key alatwa</label>
                        <input type="text" name="api_key" value="<?= htmlspecialchars($api_key) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Device ID alatwa</label>
                        <input type="text" name="device_id" value="<?= htmlspecialchars($device_id) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Nomor WA Admin</label>
                        <input type="text" name="admin_wa" value="<?= htmlspecialchars($admin_wa) ?>" placeholder="6281234567890" required>
                    </div>

                    <br>

                    <h4 style="color:#2c7a7b;">📍 Lokasi Masjid</h4>
                    <hr>

                    <div class="form-group">
                        <label>Latitude</label>
                        <input type="text" name="masjid_lat" value="<?= htmlspecialchars($lat) ?>">
                    </div>

                    <div class="form-group">
                        <label>Longitude</label>
                        <input type="text" name="masjid_lng" value="<?= htmlspecialchars($lng) ?>">
                    </div>

                    <p style="font-size:12px;color:#555;background:#f1f5f9;padding:8px;border-radius:6px;">
                        *Gunakan Google Maps → klik lokasi → copy angka Latitude & Longitude.
                    </p>

                    <br>

                    <h4 style="color:#2c7a7b;">💬 Template Pesan WA</h4>
                    <hr>

                    <div class="form-group">
                        <label>WA – Pesan Donasi Diterima</label>
                        <textarea name="wa_msg_approved" id="wa_msg_approved" rows="4"><?= htmlspecialchars($wa_ok) ?></textarea>
                        <small>Variabel yang bisa dipakai: <b>{{nama}}, {{jumlah}}, {{program}}</b></small>
                    </div>

                    <div class="form-group">
                        <label>WA – Pesan Donasi Ditolak</label>
                        <textarea name="wa_msg_rejected" id="wa_msg_rejected" rows="4"><?= htmlspecialchars($wa_no) ?></textarea>
                        <small>Variabel yang bisa dipakai: <b>{{nama}}, {{jumlah}}, {{program}}</b></small>
                    </div>

                    <button class="btn">Simpan Pengaturan</button>

                </form>
                <!-- TOMBOL TEST KIRIM WA -->
                <hr style="margin:25px 0">

                <h4>🔍 Test Kirim WhatsApp</h4>
                <p class="text-muted" style="margin-top:-10px;margin-bottom:10px;">
                    Kirim pesan uji ke nomor admin untuk memastikan integrasi alatwa.com berfungsi normal.
                </p>

                <button type="button" id="btnTestWA"
                    class="btn"
                    style="background:#d69e2e;color:white;">
                    🔍 Kirim Pesan Uji
                </button>

                <div id="testResult" style="margin-top:12px;"></div>

            </div> <!-- card-body -->
        </div>
    </div> <!-- settings-card -->


    <!-- ========================= -->
    <!-- BAGIAN C: JS, TinyMCE, UI -->
    <!-- ========================= -->
    <script src="<?= BASE_URL ?>assets/js/tinymce/tinymce.min.js"></script>

    <script>
        // Tab switching
        (function() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            function showTab(name) {
                tabButtons.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.style.display = 'none');

                const btn = document.querySelector('.tab-btn[data-tab="' + name + '"]');
                const content = document.getElementById('tab-' + name);
                if (btn) btn.classList.add('active');
                if (content) content.style.display = 'block';
            }

            tabButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    showTab(this.dataset.tab);
                    // update URL hash so reload stays on same tab
                    history.replaceState(null, '', '#' + this.dataset.tab);
                });
            });

            // On load: try to open tab from hash (e.g. #wa) or default to 'umum'
            const hash = (location.hash || '').replace('#', '');
            const initial = hash && document.querySelector('.tab-btn[data-tab="' + hash + '"]') ? hash : 'umum';
            showTab(initial);
        })();

        // TinyMCE untuk tab 'tentang'
        tinymce.init({
            selector: '#site_about',
            height: 400,
            menubar: false,
            plugins: 'lists link image table code',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code',
            branding: false
        });

        // TinyMCE untuk template WA
        tinymce.init({
            selector: '#wa_msg_approved, #wa_msg_rejected',
            height: 250,
            menubar: false,
            plugins: 'lists link image table code',
            toolbar: 'undo redo | bullist numlist',
            branding: false
        });

        // Optional: auto-formatting for latitude/longitude inputs (simple)
        document.querySelectorAll('input[name="masjid_lat"], input[name="masjid_lng"]').forEach(el => {
            el.addEventListener('blur', function() {
                this.value = this.value.trim();
            });
        });

        // Show success message if available (PHP variable)

        // Test Kirim WhatsApp
        document.addEventListener("DOMContentLoaded", () => {

            const btnTest = document.getElementById("btnTestWA");
            if (!btnTest) return;

            btnTest.addEventListener("click", () => {

                btnTest.disabled = true;
                btnTest.innerText = "Mengirim...";

                // fetch("whatsapp_test_template.php")
                fetch("<?= BASE_URL ?>admin/settings/whatsapp_test_template.php")

                    .then(res => res.json())
                    .then(data => {

                        btnTest.disabled = false;
                        btnTest.innerText = "🔍 Kirim Pesan Uji";

                        console.log("Response WA Test:", data);

                        if (data.status === "ok" || data.status === "success") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                html: data.message || "Pesan uji berhasil dikirim!",
                                confirmButtonColor: '#2c7a7b'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Mengirim',
                                html: data.error || JSON.stringify(data.raw),
                                confirmButtonColor: '#e53e3e'
                            });
                        }
                    })

                    .catch(err => {
                        btnTest.disabled = false;
                        btnTest.innerText = "🔍 Kirim Pesan Uji";

                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan',
                            html: "AJAX Error:<br>" + err,
                            confirmButtonColor: '#e53e3e'
                        });
                    });
            });
        });
    </script>

    <?php if (!empty($success)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    html: '<?= addslashes($success) ?>',
                    confirmButtonColor: '#2c7a7b',
                    timer: 2200,
                    timerProgressBar: true
                });
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: '<?= addslashes($error) ?>',
                    confirmButtonColor: '#e53e3e'
                });
            });
        </script>
    <?php endif; ?>

</body>

</html>