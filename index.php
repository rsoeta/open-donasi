<?php
include_once('includes/config.php');

// ==========================================
//  Ambil semua program donasi (aktif saja)
// ==========================================
$query = "
SELECT 
    p.id,
    p.judul,
    p.gambar,
    p.deskripsi,
    p.target_donasi,
    p.status_donasi,
    IFNULL(SUM(t.jumlah), 0) AS total_terkumpul
FROM donasi_post p
LEFT JOIN donasi_transaksi t ON p.id = t.id_donasi
WHERE p.status_donasi = 'aktif'
GROUP BY p.id
ORDER BY p.id DESC
";

$result = mysqli_query($conn, $query);

// Ambil pengaturan dinamis
$site_name    = get_setting('site_name', 'Open Donasi');
$site_logo    = get_setting('site_logo', 'assets/images/logo.png');
$site_contact = get_setting('site_contact', 'info@example.com');

include_once('includes/header.php');
?>

<main style="max-width:1100px;margin:40px auto;padding:0 20px;">
    <h2 style="text-align:center;margin-bottom:30px;">Daftar Program Donasi</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;">

            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                <?php
                $total  = (float) $row['total_terkumpul'];
                $target = (float) $row['target_donasi'];

                // Hitung persentase
                $persen = 0;
                if ($target > 0) {
                    $persen = min(100, round(($total / $target) * 100));
                }

                // Format nilai rupiah
                $total_fmt  = number_format($total, 0, ',', '.');
                $persen_fmt = $persen . '%';
                ?>

                <div class="article-card">
                    <div class="image-container">

                        <img src="uploads/<?= htmlspecialchars($row['gambar']) ?>"
                            alt="<?= htmlspecialchars($row['judul']) ?>">

                        <!-- Overlay jumlah + progress -->
                        <div class="donasi-overlay">
                            💰 Terkumpul: Rp <?= $total_fmt ?><br>
                            🎯 <?= $persen_fmt ?> dari target
                        </div>
                    </div>

                    <div class="content">
                        <h3><?= htmlspecialchars($row['judul']) ?></h3>

                        <p><?= substr(strip_tags($row['deskripsi']), 0, 100) ?>...</p>

                        <div style="text-align:right;">
                            <a href="detail.php?id=<?= $row['id'] ?>" class="btn-add">
                                Donasi Sekarang
                            </a>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>
        <p style="text-align:center;color:#666;">Belum ada program donasi yang aktif saat ini.</p>
    <?php endif; ?>
</main>

<?php include_once('includes/footer.php'); ?>