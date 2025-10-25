<?php
include_once('includes/config.php');

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query artikel donasi berdasarkan ID
$query = mysqli_query($conn, "SELECT * FROM donasi_post WHERE id = $id LIMIT 1");

// Jika tidak ditemukan
if (!$query || mysqli_num_rows($query) == 0) {
    include_once('includes/header.php');
    echo '<main style="max-width:800px;margin:80px auto;text-align:center;">
            <h2>Artikel Tidak Ditemukan</h2>
            <p>Maaf, artikel donasi yang Anda cari tidak tersedia atau sudah dihapus.</p>
            <a href="' . BASE_URL . 'index.php" style="color:#2c7a7b;text-decoration:none;font-weight:bold;">← Kembali ke Beranda</a>
          </main>';
    include_once('includes/footer.php');
    exit;
}

// Ambil data
$row = mysqli_fetch_assoc($query);
$site_name = get_setting('site_name', 'Open Donasi');
$site_logo = get_setting('site_logo', 'assets/images/logo.png');
date_default_timezone_set('Asia/Jakarta');

include_once('includes/header.php');
?>

<main style="max-width:900px;margin:40px auto;padding:20px;background:#fff;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,0.05);">
    <div style="text-align:center;margin-bottom:30px;">
        <h2 style="margin-bottom:10px;"><?= htmlspecialchars($row['judul']) ?></h2>
        <small style="color:#777;">Diterbitkan: <?= date('d M Y', strtotime($row['tanggal_post'])) ?></small>
    </div>
    <!-- buat tombol rata kanan -->
    <div style="text-align:right;margin-bottom:5px;">
        <button
            class="btn btn-success share-btn"
            data-title="<?= htmlspecialchars($row['judul']) ?>"
            data-url="<?= BASE_URL ?>detail.php?id=<?= $row['id'] ?>">
            <i class='bi bi-share'></i> Bagikan
        </button>
    </div>
    <?php
    $gambarPath = BASE_URL . 'uploads/' . $row['gambar'];
    if (!empty($row['gambar']) && file_exists('uploads/' . $row['gambar'])):
    ?>
        <div style="text-align:center;margin-bottom:25px;">
            <img src="<?= $gambarPath ?>" alt="<?= htmlspecialchars($row['judul']) ?>" style="max-width:100%;border-radius:10px;">
        </div>
    <?php endif; ?>

    <div class="article-body" style="line-height:1.8;color:#333;font-size:15px;">
        <?= $row['deskripsi'] ?>
    </div>

    <!-- tambah simbol home di tombol kembali ke Beranda! -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;">
        <a href="<?= BASE_URL ?>index.php" style="padding:10px 20px;background:#3182ce;color:white;border-radius:6px;text-decoration:none;">Kembali ke Beranda</a>
        <?php if ($row['status_donasi'] === 'aktif'): ?>
            <a href="<?= BASE_URL ?>donasi_form.php?id=<?= $row['id'] ?>"
                style="background:#2c7a7b;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold;">
                💰 Donasi Sekarang
            </a>
        <?php else: ?>
            <span style="background:#ccc;color:#555;padding:10px 20px;border-radius:6px;font-weight:bold;">
                🚫 Donasi Ditutup
            </span>
        <?php endif; ?>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_URL ?>assets/js/share.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.share-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const title = btn.getAttribute('data-title');
                const url = btn.getAttribute('data-url');
                shareDonasi(title, url);
            });
        });
    });
</script>

<?php include_once('includes/footer.php'); ?>