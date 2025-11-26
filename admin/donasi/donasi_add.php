<?php
session_start();
include_once('../../includes/config.php');

if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

$error = '';
$success = '';

// Proses POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    // target_donasi mungkin datang tanpa titik
    $target_donasi = isset($_POST['target_donasi']) ? floatval(str_replace('.', '', $_POST['target_donasi'])) : 0;
    $status = $_POST['status'] ?? 'aktif';
    $accepts_goods = isset($_POST['accepts_goods']) ? 1 : 0;
    $goods_note = isset($_POST['goods_note']) ? mysqli_real_escape_string($conn, $_POST['goods_note']) : null;

    // Upload gambar (jika ada)
    $gambar = null;
    if (!empty($_FILES['gambar']['name'])) {
        $targetDir = "../../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['gambar']['name']));
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFilePath)) {
                $gambar = $fileName;
            } else {
                $error = "Gagal mengunggah gambar.";
            }
        } else {
            $error = "Tipe file gambar tidak diizinkan.";
        }
    }

    if (!$error) {
        $sql = "INSERT INTO donasi_post (judul, gambar, deskripsi, target_donasi, status, accepts_goods, goods_note)
                VALUES ('$judul', " . ($gambar ? "'$gambar'" : "NULL") . ", '$deskripsi', $target_donasi, '$status', $accepts_goods, " . ($goods_note ? "'$goods_note'" : "NULL") . ")";
        if (mysqli_query($conn, $sql)) {
            $post_id = mysqli_insert_id($conn);

            // Simpan items bila ada (dikirim sebagai JSON string di hidden input 'items_json')
            if ($accepts_goods && !empty($_POST['items_json'])) {
                $items_json = $_POST['items_json'];
                $items = json_decode($items_json, true);
                if (is_array($items)) {
                    $stmt = $conn->prepare("INSERT INTO donasi_items (donasi_post_id, name, unit) VALUES (?, ?, ?)");
                    foreach ($items as $it) {
                        $name = trim($it['name'] ?? '');
                        $unit = trim($it['unit'] ?? 'pcs');
                        if ($name === '') continue;
                        $stmt->bind_param('iss', $post_id, $name, $unit);
                        $stmt->execute();
                    }
                    $stmt->close();
                }
            }

            header("Location: donasi.php?success=1");
            exit;
        } else {
            $error = "Gagal menyimpan data: " . mysqli_error($conn);
        }
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
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL . htmlspecialchars($site_logo) ?>">
    <link rel="apple-touch-icon" href="<?= BASE_URL . htmlspecialchars($site_logo) ?>">
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
    <style>
        /* minimal styling for items area */
        .items-list {
            margin-top: 10px;
        }

        .items-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-list td,
        .items-list th {
            padding: 8px;
            border: 1px solid #eee;
            text-align: left;
        }

        .small {
            font-size: 13px;
            color: #666;
        }
    </style>
    <script src="../../assets/js/tinymce/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#deskripsi',
            height: 300,
            menubar: false,
            plugins: 'lists link image table',
            toolbar: 'bold italic | bullist numlist | link image table'
        });
    </script>
</head>

<body>
    <header>
        <div class="container" style="display:flex;justify-content:space-between;align-items:center;">
            <div class="logo"><a href="/"><img src="<?= BASE_URL . htmlspecialchars($site_logo) ?>" alt="" height="40"></a></div>
            <nav><a href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a> | <a href="<?= BASE_URL ?>logout.php">Logout</a></nav>
        </div>
    </header>

    <div class="form-container">
        <a href="donasi.php" class="btn-back">← Kembali ke Daftar Donasi</a>
        <h2>Tambah Artikel Donasi</h2>

        <?php if (!empty($error)): ?><div class="alert"><?= $error ?></div><?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" id="formAdd">
            <label>Judul Donasi</label>
            <input type="text" name="judul" required>

            <label>Gambar (opsional)</label>
            <input type="file" name="gambar" accept="image/*">

            <label>Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi"></textarea>

            <label>Target Donasi (Rp)</label>
            <input type="text" id="jumlah" name="target_donasi" placeholder="Contoh: 150.000" inputmode="numeric" oninput="formatRibuan(this)">

            <label>Status</label>
            <select name="status">
                <option value="aktif">Aktif</option>
                <option value="ditutup">Ditutup</option>
            </select>

            <label><input type="checkbox" name="accepts_goods" id="accepts_goods"> Program menerima donasi barang</label>

            <div id="goods_area" style="display:none;margin-top:10px;">
                <label>Catatan Barang (instruksi untuk donor)</label>
                <textarea name="goods_note" rows="3" placeholder="Mis: Barang harus baru / ukuran tertentu..."></textarea>

                <hr>
                <h4>Daftar Barang (Admin)</h4>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <input type="text" id="new_item_name" placeholder="Nama barang (mis: Beras)">
                    <input type="text" id="new_item_unit" placeholder="Unit (pcs/kg)" style="width:120px">
                    <button type="button" onclick="addItem()">Tambah</button>
                </div>
                <div class="items-list" id="items_list">
                    <!-- items table muncul di sini -->
                </div>
                <input type="hidden" name="items_json" id="items_json">
                <p class="small">Daftar barang yang admin masukkan akan muncul pada form publik untuk dipilih donor.</p>
            </div>

            <button type="submit" class="btn-submit">Simpan</button>
        </form>
    </div>

    <script>
        function formatRibuan(input) {
            let angka = input.value.replace(/\D/g, '');
            input.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        document.getElementById('accepts_goods').addEventListener('change', function() {
            document.getElementById('goods_area').style.display = this.checked ? 'block' : 'none';
        });

        // manage items in JS array until submit
        let items = [];

        function renderItems() {
            const wrap = document.getElementById('items_list');
            if (items.length === 0) {
                wrap.innerHTML = '<em>Belum ada barang. Tambahkan di atas.</em>';
            } else {
                let html = '<table><thead><tr><th>Nama</th><th>Unit</th><th>Aksi</th></tr></thead><tbody>';
                items.forEach((it, idx) => {
                    html += `<tr><td>${escapeHtml(it.name)}</td><td>${escapeHtml(it.unit)}</td><td><button type="button" onclick="removeItem(${idx})">Hapus</button></td></tr>`;
                });
                html += '</tbody></table>';
                wrap.innerHTML = html;
            }
            document.getElementById('items_json').value = JSON.stringify(items);
        }

        function addItem() {
            const name = document.getElementById('new_item_name').value.trim();
            const unit = document.getElementById('new_item_unit').value.trim() || 'pcs';
            if (!name) {
                alert('Nama barang wajib');
                return;
            }
            items.push({
                name,
                unit
            });
            document.getElementById('new_item_name').value = '';
            document.getElementById('new_item_unit').value = '';
            renderItems();
        }

        function removeItem(idx) {
            if (!confirm('Hapus item?')) return;
            items.splice(idx, 1);
            renderItems();
        }

        function escapeHtml(text) {
            return text.replace(/[&<>"']/g, function(m) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                })[m];
            });
        }

        // init
        renderItems();

        // saat submit, hilangkan titik pada jumlah
        document.getElementById('formAdd').addEventListener('submit', function() {
            const jumlah = document.getElementById('jumlah');
            if (jumlah) jumlah.value = jumlah.value.replace(/\./g, '');
        });
    </script>
</body>

</html>