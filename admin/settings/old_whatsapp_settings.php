<?php
session_start();
include_once('../../includes/config.php');

if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

// Ambil setting
$api_key = get_setting('alatwa_api_key', '');
$device_id = get_setting('alatwa_device_id', '');
$admin_wa = get_setting('admin_whatsapp', '');

$site_logo = get_setting('site_logo', 'assets/images/logo.png');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pengaturan WhatsApp - Open Donasi</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL . htmlspecialchars($site_logo) ?>">

    <style>
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.05);
        }

        label {
            font-weight: 600;
            margin-top: 10px;
            display: block;
        }

        input[type="text"] {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .btn-submit {
            background: #2c7a7b;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            margin-top: 20px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background: #205f5f;
        }

        .alert {
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
        }

        .alert-success {
            background: #c6f6d5;
            color: #22543d;
        }

        .alert-error {
            background: #fed7d7;
            color: #742a2a;
        }
    </style>
</head>

<body>

    <header>
        <div class="container" style="display:flex;justify-content:space-between;align-items:center;">
            <div class="logo">
                <a href="/"><img src="<?= BASE_URL . htmlspecialchars($site_logo) ?>" height="40"></a>
            </div>
            <nav>
                <a href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a> |
                <a href="<?= BASE_URL ?>logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="form-container">
        <h2 style="text-align:center;color:#2c7a7b;">Pengaturan WhatsApp (alatwa.com)</h2>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Pengaturan berhasil disimpan!</div>
        <?php endif; ?>

        <form action="whatsapp_save.php" method="POST">

            <label>API Key alatwa</label>
            <input type="text" name="api_key" value="<?= htmlspecialchars($api_key) ?>" required>

            <label>Device ID alatwa</label>
            <input type="text" name="device_id" value="<?= htmlspecialchars($device_id) ?>" required>

            <label>Nomor WhatsApp Admin</label>
            <input type="text" name="admin_wa" value="<?= htmlspecialchars($admin_wa) ?>" placeholder="Contoh: 6281234567890" required>

            <button type="submit" class="btn-submit">Simpan Pengaturan</button>

            <!-- <br><br> -->

            <!-- <button type="button" id="btnTestWA"
                style="background:#d69e2e;color:white;padding:10px 18px;border:none;border-radius:6px;cursor:pointer;">
                🔍 Test Kirim WhatsApp
            </button>

            <br><br>

            <button type="button" id="btnTestMedia"
                style="background:#3182ce;color:white;padding:10px 18px;border:none;border-radius:6px;cursor:pointer;">
                🖼️ Test Kirim Media (Gambar)
            </button> -->

            <script>
                document.getElementById('btnTestMedia').onclick = function() {

                    const btn = this;
                    btn.disabled = true;
                    btn.innerText = "Mengirim...";

                    fetch("whatsapp_test_media.php")
                        .then(res => res.json())
                        .then(data => {
                            btn.disabled = false;
                            btn.innerText = "🖼️ Test Kirim Media (Gambar)";

                            if (data.status === "success") {
                                alert("✔ Media berhasil dikirim!\n\n" + data.message);
                            } else {
                                alert("❌ Gagal mengirim media:\n\n" + data.error);
                            }
                        })
                        .catch(err => {
                            btn.disabled = false;
                            btn.innerText = "🖼️ Test Kirim Media (Gambar)";
                            alert("⚠ Error AJAX: " + err);
                        });
                };
            </script>

            <div id="waTestResult" style="margin-top:15px;"></div>

            <script>
                document.getElementById('btnTestWA').onclick = function() {

                    const btn = this;
                    btn.disabled = true;
                    btn.innerText = "Mengirim...";

                    fetch("whatsapp_test.php")
                        .then(res => res.json())
                        .then(data => {
                            btn.disabled = false;
                            btn.innerText = "🔍 Test Kirim WhatsApp";

                            if (data.status === "success") {
                                alert("✔ Pesan berhasil dikirim!\n\n" + data.message);
                            } else {
                                alert("❌ Gagal mengirim WhatsApp:\n\n" + data.error);
                            }
                        })
                        .catch(err => {
                            btn.disabled = false;
                            btn.innerText = "🔍 Test Kirim WhatsApp";
                            alert("⚠ Error AJAX: " + err);
                        });
                };
            </script>

        </form>
    </div>

</body>

</html>