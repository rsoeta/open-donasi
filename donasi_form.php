<?php
include_once('includes/config.php');

$id_donasi = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = mysqli_query($conn, "SELECT * FROM donasi_post WHERE id=$id_donasi LIMIT 1");

if (!$query || mysqli_num_rows($query) == 0) {
    include_once('includes/header.php');
    echo '<main style="max-width:800px;margin:80px auto;text-align:center;">
            <h2>Donasi Tidak Ditemukan</h2>
            <p>Program donasi yang Anda pilih tidak tersedia.</p>
            <a href="' . BASE_URL . 'index.php" style="color:#2c7a7b;text-decoration:none;">← Kembali</a>
          </main>';
    include_once('includes/footer.php');
    exit;
}

$row = mysqli_fetch_assoc($query);
$site_name = get_setting('site_name', 'Open Donasi');
$site_logo = get_setting('site_logo', 'assets/images/logo.png');

include_once('includes/header.php');
?>

<main style="max-width:700px;margin:40px auto;background:#fff;padding:25px;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,0.05);">
    <h2 style="text-align:center;margin-bottom:25px;">Donasi untuk: <?= htmlspecialchars($row['judul']) ?></h2>

    <form action="donasi_proses.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_donasi" value="<?= $id_donasi ?>">

        <label>Nama Donatur <span style="color:red;">*</span></label>
        <input type="text" name="nama_donatur" required placeholder="Nama lengkap Anda" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-bottom:10px;">

        <label>Nominal Donasi (Rp) <span style="color:red;">*</span></label>
        <input
            type="text"
            id="jumlah"
            name="jumlah"
            required
            placeholder="Contoh: 150.000"
            inputmode="numeric"
            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-bottom:10px;"
            oninput="formatRibuan(this)">


        <label>Metode Pembayaran</label>
        <input type="text" name="metode" value="Transfer" readonly style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;background:#f8fafc;margin-bottom:10px;">

        <label>Upload Bukti Transfer <span style="color:red;">*</span></label>
        <input type="file" name="bukti" accept="image/*" required style="margin-bottom:10px;">

        <label>Catatan (opsional)</label>
        <textarea name="catatan" rows="3" placeholder="Tambahkan pesan atau keterangan..." style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;"></textarea>
        <div style="text-align:right;margin-top:10px;">
            <button type="submit" style="margin-top:15px;background:#2c7a7b;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">
                Kirim Donasi
            </button>
        </div>
    </form>
    <script>
        // Fungsi untuk menambahkan titik ribuan saat mengetik
        function formatRibuan(input) {
            // Hapus semua karakter non-digit
            let angka = input.value.replace(/\D/g, '');
            // Format ribuan pakai titik
            input.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Hapus titik sebelum dikirim ke server
        document.querySelector('form').addEventListener('submit', function() {
            const jumlah = document.getElementById('jumlah');
            jumlah.value = jumlah.value.replace(/\./g, ''); // hapus titik semua
        });
    </script>

</main>

<?php include_once('includes/footer.php'); ?>