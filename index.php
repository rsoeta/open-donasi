<?php
include_once('includes/config.php');

// Ambil semua artikel donasi aktif
$query = "
SELECT 
    p.id, 
    p.judul, 
    p.gambar, 
    p.deskripsi, 
    p.status_donasi,
    IFNULL(SUM(t.jumlah), 0) AS total_terkumpul
FROM donasi_post p
LEFT JOIN donasi_transaksi t ON p.id = t.id_donasi
GROUP BY p.id
ORDER BY p.id DESC
";
$result = mysqli_query($conn, $query);

// Ambil pengaturan dinamis
$site_name = get_setting('site_name', 'Open Donasi');
$site_logo = get_setting('site_logo', 'assets/images/logo.png');
$site_contact = get_setting('site_contact', 'info@example.com');

include_once('includes/header.php');
?>

<main style="max-width:1100px;margin:40px auto;padding:0 20px;">
    <h2 style="text-align:center;margin-bottom:30px;">Daftar Program Donasi</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?php
                $total = (float) $row['total_terkumpul']; // pastikan float
                ?>
                <div class="article-card">
                    <div class="image-container">
                        <img src="uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['judul']) ?>">
                        <div class="donasi-overlay">
                            💰 Terkumpul: Rp <?= number_format($total, 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="content">
                        <h3><?= htmlspecialchars($row['judul']) ?></h3>
                        <p><?= substr(strip_tags($row['deskripsi']), 0, 100) ?>...</p>
                        <!-- buat tombol donasi sekarang rata kanan -->
                        <div style="text-align:right;">
                            <a href="detail.php?id=<?= $row['id'] ?>" class="btn-add">Donasi Sekarang</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

        </div>
    <?php else: ?>
        <p style="text-align:center;color:#666;">Belum ada artikel donasi yang aktif saat ini.</p>
    <?php endif; ?>
</main>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const counters = document.querySelectorAll(".donasi-overlay");
        counters.forEach(counter => {
            const text = counter.textContent;
            const match = text.match(/Rp\s([\d.]+)/);
            if (!match) return;
            const target = parseInt(match[1].replace(/\./g, '')) || 0;
            let current = 0;
            const increment = target / 50; // kecepatan naik
            const update = () => {
                current += increment;
                if (current >= target) current = target;
                counter.textContent = `💰 Terkumpul: Rp ${current.toLocaleString("id-ID")}`;
                if (current < target) requestAnimationFrame(update);
            };
            update();
        });
    });
</script>


<?php include_once('includes/footer.php'); ?>