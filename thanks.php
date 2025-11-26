<?php
include_once('includes/config.php');

// Ambil ID transaksi
$tid = isset($_GET['tid']) ? intval($_GET['tid']) : 0;

// Ambil data transaksi
$q = mysqli_query($conn, "SELECT * FROM donasi_pending WHERE id = $tid LIMIT 1");

if (!$q || mysqli_num_rows($q) == 0) {
    die("Transaksi tidak ditemukan.");
}
$trx = mysqli_fetch_assoc($q);

// Ambil data program
$prog_q = mysqli_query($conn, "SELECT judul FROM donasi_post WHERE id = {$trx['id_donasi']} LIMIT 1");
$program = mysqli_fetch_assoc($prog_q);
$program_name = $program ? $program['judul'] : '-';

// Ambil detail barang (jika mixed / goods)
$items = [];
if ($trx['type'] != 'transfer') {
    $iq = mysqli_query($conn, "
    SELECT pi.qty, i.name, i.unit
    FROM donasi_pending_items pi
    JOIN donasi_items i ON pi.item_id = i.id
    WHERE pi.pending_id = $tid
    ");
    while ($r = mysqli_fetch_assoc($iq)) {
        $items[] = $r;
    }
}

// nomor admin
$admin_wa = get_setting('admin_whatsapp', '');
$wa_link = "";
if ($admin_wa) {
    $message = urlencode("Halo admin, saya ingin mengkonfirmasi donasi saya.\n\nID Transaksi: #$tid\nProgram: $program_name\nNama: {$trx['nama_donatur']}");
    $wa_link = "https://wa.me/{$admin_wa}?text={$message}";
}

include_once('includes/header.php');
?>

<style>
    .thanks-box {
        max-width: 700px;
        margin: 40px auto;
        background: #fff;
        padding: 30px 35px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .thanks-box h2 {
        color: #2c7a7b;
        margin-bottom: 10px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .item-list {
        margin-top: 10px;
        background: #f8fafc;
        padding: 10px;
        border-radius: 6px;
    }

    .wa-button {
        display: inline-block;
        padding: 12px 20px;
        background: #25D366;
        color: #fff;
        border-radius: 6px;
        font-weight: bold;
        text-decoration: none;
        margin-top: 20px;
    }

    .wa-button:hover {
        background: #1eae59;
    }
</style>

<main class="thanks-box">
    <h2>Terima Kasih 🙏</h2>
    <p style="color:#444; font-size:16px;">
        Donasi Anda telah berhasil kami terima.
    </p>

    <hr style="margin:20px 0;">

    <h3 style="color:#2c7a7b;">📄 Ringkasan Donasi</h3>

    <div class="summary-row">
        <span>Nama Donatur</span>
        <strong><?= htmlspecialchars($trx['nama_donatur']) ?></strong>
    </div>

    <div class="summary-row">
        <span>Program</span>
        <strong><?= htmlspecialchars($program_name) ?></strong>
    </div>

    <div class="summary-row">
        <span>Jenis Donasi</span>
        <strong><?= htmlspecialchars(ucfirst($trx['type'])) ?></strong>
    </div>

    <?php if ($trx['type'] !== 'goods'): ?>
        <div class="summary-row">
            <span>Nominal Transfer</span>
            <strong>Rp <?= number_format($trx['jumlah'], 0, ',', '.') ?></strong>
        </div>
    <?php endif; ?>

    <?php if ($trx['type'] !== 'transfer'): ?>
        <div style="margin-top:15px;">
            <strong>📦 Barang yang Didonasikan:</strong>

            <div class="item-list">
                <?php foreach ($items as $it): ?>
                    <div>• <?= $it['qty'] ?> × <?= htmlspecialchars($it['name']) ?> (<?= $it['unit'] ?>)</div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="summary-row" style="margin-top:10px;">
        <span>Tanggal</span>
        <strong><?= $trx['tanggal_pengajuan'] ?></strong>
    </div>

    <div class="summary-row">
        <span>Catatan</span>
        <strong><?= htmlspecialchars($trx['catatan'] ?? '-') ?></strong>
    </div>

    <!-- <?php if ($wa_link): ?>
        <a href="<?= $wa_link ?>" class="wa-button" target="_blank">📲 Konfirmasi via WhatsApp</a>
    <?php endif; ?> -->

    <br><br>
    <a href="<?= BASE_URL ?>index.php" style="text-decoration:none;color:#3182ce;">← Kembali ke Beranda</a>
</main>

<?php include_once('includes/footer.php'); ?>