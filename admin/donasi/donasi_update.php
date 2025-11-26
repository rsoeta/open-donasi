<?php
session_start();
include_once('../../includes/config.php');

if (!isset($_SESSION['admin'])) {
    header('Location: ../../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: donasi.php');
    exit;
}

$id = intval($_POST['id']);
$judul = mysqli_real_escape_string($conn, $_POST['judul']);
$deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
$target = floatval(str_replace('.', '', $_POST['target_donasi']));
$status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'aktif');

$accepts_goods = isset($_POST['accepts_goods']) ? 1 : 0;
$goods_note = mysqli_real_escape_string($conn, $_POST['goods_note'] ?? "");

// ===========================================
// HANDLE UPLOAD GAMBAR (AMAN)
// ===========================================

$gambar_sql = "";
if (!empty($_FILES['gambar']['name'])) {

    $targetDir = "../../uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

    $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "_", basename($_FILES['gambar']['name']));
    $targetFile = $targetDir . $fileName;

    $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($ext, $allowed)) {
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFile)) {

            // hapus gambar lama
            $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT gambar FROM donasi_post WHERE id=$id"));
            if ($old && $old['gambar'] && file_exists("../../uploads/" . $old['gambar'])) {
                unlink("../../uploads/" . $old['gambar']);
            }

            $gambar_sql = ", gambar='$fileName'";
        }
    }
}

// ===========================================
// UPDATE TABEL donasi_post
// ===========================================

$q1 = mysqli_query($conn, "
    UPDATE donasi_post SET
        judul = '$judul',
        deskripsi = '$deskripsi',
        target_donasi = $target,
        status = '$status',
        accepts_goods = $accepts_goods,
        goods_note = '$goods_note'
        $gambar_sql
    WHERE id = $id
");

if (!$q1) {
    die("Gagal update post: " . mysqli_error($conn));
}

// ===========================================
// UPDATE BARANG (ANTI FK ERROR)
// ===========================================
// Sistem:
// - Tidak DELETE donasi_items (karena dipakai FK)
// - Hanya nonaktifkan item lama
// - Item baru → INSERT
// - Item lama yang masih ada → tetap aktif
// ===========================================

if ($accepts_goods) {

    // Ambil item lama
    $resultOld = mysqli_query($conn, "
        SELECT id, name, unit
        FROM donasi_items
        WHERE donasi_post_id = $id AND is_active = 1
    ");

    $old_items = [];
    while ($r = mysqli_fetch_assoc($resultOld)) {
        $old_items[$r['id']] = $r;
    }

    $updated_ids = [];
    $items_json = $_POST['items_json'] ?? '[]';
    $items_new = json_decode($items_json, true);

    if (is_array($items_new)) {

        foreach ($items_new as $it) {

            $name = mysqli_real_escape_string($conn, trim($it['name'] ?? ''));
            $unit = mysqli_real_escape_string($conn, trim($it['unit'] ?? 'pcs'));

            if ($name === '') continue;

            // cek apakah ada item yang sama
            $found = null;
            foreach ($old_items as $oid => $old) {
                if ($old['name'] === $name && $old['unit'] === $unit) {
                    $found = $oid;
                    break;
                }
            }

            if ($found) {
                // tetap aktif
                $updated_ids[] = $found;
            } else {
                // insert baru
                mysqli_query($conn, "
                    INSERT INTO donasi_items (donasi_post_id, name, unit, is_active)
                    VALUES ($id, '$name', '$unit', 1)
                ");
                $updated_ids[] = mysqli_insert_id($conn);
            }
        }
    }

    // nonaktifkan item lama yang tidak ada di daftar baru
    foreach ($old_items as $oid => $old) {
        if (!in_array($oid, $updated_ids)) {
            mysqli_query($conn, "
                UPDATE donasi_items SET is_active = 0 WHERE id = $oid
            ");
        }
    }
} else {
    // Jika program tidak menerima barang, nonaktifkan semua
    mysqli_query($conn, "
        UPDATE donasi_items SET is_active = 0 WHERE donasi_post_id = $id
    ");
}

header("Location: donasi.php?updated=1");
exit;
