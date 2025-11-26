<?php
include_once('../../includes/config.php');
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: ../../login.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$result = mysqli_query($conn, "SELECT * FROM donasi_post WHERE id = $id");
$row = mysqli_fetch_assoc($result);

if (!$row) {
    header("Location: donasi.php");
    exit;
}

// ambil items terkait
$items = [];
$resItems = mysqli_query($conn, "SELECT * FROM donasi_items 
WHERE donasi_post_id = $id AND is_active = 1
ORDER BY id ASC
");
while ($r = mysqli_fetch_assoc($resItems)) $items[] = $r;

$site_logo = get_setting('site_logo', 'assets/images/logo.png');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Donasi</title>

    <!-- Favicon (multi-browser support) -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL . htmlspecialchars($site_logo) ?>">
    <link rel="apple-touch-icon" href="<?= BASE_URL . htmlspecialchars($site_logo) ?>">
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
    <style>
        .items-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-list td,
        .items-list th {
            padding: 8px;
            border: 1px solid #eee;
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
            <div class="logo"><a href="/"><img src="<?= BASE_URL . htmlspecialchars($site_logo) ?>" height="40"></a></div>
            <nav><a href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a> | <a href="<?= BASE_URL ?>logout.php">Logout</a></nav>
        </div>
    </header>

    <div class="container">
        <h2>Edit Artikel Donasi</h2>
        <form action="donasi_update.php" method="POST" enctype="multipart/form-data" id="formEdit">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">

            <label>Judul</label>
            <input type="text" name="judul" value="<?= htmlspecialchars($row['judul']) ?>" required>

            <label>Gambar Utama</label>
            <?php if (!empty($row['gambar'])): ?>
                <img src="../../uploads/<?= $row['gambar'] ?>" alt="Gambar Utama" style="width:200px;display:block;margin-bottom:10px;border-radius:6px;">
            <?php endif; ?>
            <input type="file" name="gambar" accept="image/*">

            <label>Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi"><?= htmlspecialchars($row['deskripsi']) ?></textarea>

            <label>Target Donasi (Rp)</label>
            <input type="text" id="target_donasi" name="target_donasi" value="<?= number_format($row['target_donasi'], 0, ',', '.') ?>" oninput="formatRibuan(this)" required>

            <label>Status</label>
            <select name="status">
                <option value="aktif" <?= $row['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="ditutup" <?= $row['status'] == 'ditutup' ? 'selected' : '' ?>>Ditutup</option>
            </select>

            <label><input type="checkbox" id="accepts_goods" name="accepts_goods" <?= $row['accepts_goods'] ? 'checked' : '' ?>> Program menerima donasi barang</label>

            <div id="goods_area" style="display:<?= $row['accepts_goods'] ? 'block' : 'none' ?>; margin-top:10px;">
                <label>Catatan Barang (instruksi untuk donor)</label>
                <textarea name="goods_note" rows="3"><?= htmlspecialchars($row['goods_note']) ?></textarea>

                <hr>
                <h4>Daftar Barang (Admin)</h4>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <input type="text" id="new_item_name" placeholder="Nama barang (mis: Beras)">
                    <input type="text" id="new_item_unit" placeholder="Unit (pcs/kg)" style="width:120px">
                    <button type="button" onclick="addItem()">Tambah</button>
                </div>

                <div class="items-list" id="items_list"></div>
                <input type="hidden" name="items_json" id="items_json">
                <p class="small">Perubahan daftar barang akan menggantikan daftar sebelumnya.</p>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;">
                <button type="submit" class="btn-submit">Simpan Perubahan</button>
                <a href="donasi.php" class="btn-back">Batal</a>
            </div>
        </form>
    </div>

    <script>
        function formatRibuan(input) {
            let angka = input.value.replace(/\D/g, '');
            input.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        let items = <?= json_encode($items) ?>.map(it => ({
            name: it.name,
            unit: it.unit
        }));

        function renderItems() {
            const wrap = document.getElementById('items_list');
            if (!items.length) {
                wrap.innerHTML = '<em>Belum ada barang. Tambahkan di atas.</em>';
            } else {
                let html = '<table><thead><tr><th>Nama</th><th>Unit</th><th>Aksi</th></tr></thead><tbody>';
                items.forEach((it, idx) => html += `<tr><td>${escapeHtml(it.name)}</td><td>${escapeHtml(it.unit)}</td><td><button type="button" onclick="removeItem(${idx})">Hapus</button></td></tr>`);
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

        document.getElementById('accepts_goods').addEventListener('change', function() {
            document.getElementById('goods_area').style.display = this.checked ? 'block' : 'none';
        });
        renderItems();

        // format target sebelum submit
        document.getElementById('formEdit').addEventListener('submit', function() {
            const t = document.getElementById('target_donasi');
            if (t) t.value = t.value.replace(/\./g, '');
        });
    </script>
</body>

</html>