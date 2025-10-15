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

// Ambil nomor rekening dari tabel settings
$site_bank = get_setting('site_bank', 'BRI - 000000000000000'); // default jika kosong
preg_match('/\d{6,}/', $site_bank, $matches);
$bank_number = $matches[0] ?? '0000000000';
$bank_name = trim(preg_replace('/[-–—]?\s*\d.*$/', '', $site_bank));

include_once('includes/header.php');
?>

<main style="max-width:700px;margin:40px auto;background:#fff;padding:25px;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,0.05);">
    <h2 style="text-align:center;margin-bottom:25px;">Donasi untuk: <?= htmlspecialchars($row['judul']) ?></h2>

    <form action="donasi_proses.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_donasi" value="<?= $id_donasi ?>">

        <!-- Nama Donatur -->
        <label>Nama Donatur <span style="color:red;">*</span></label>
        <input type="text" name="nama_donatur" required placeholder="Nama lengkap Anda"
            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-bottom:10px;">

        <!-- Nominal Donasi -->
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

        <!-- Metode & Nomor Rekening -->
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
            <div style="flex:1;">
                <label>Metode</label>
                <input type="text" name="metode" value="Transfer" readonly
                    style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;background:#f8fafc;">
            </div>
            <div style="flex:1;min-width:200px;">
                <label>Nomor Rekening</label>
                <div style="display:flex;align-items:center;gap:6px;">
                    <input type="text" id="noRekening" value="<?= htmlspecialchars($site_bank) ?>" readonly
                        style="flex:1;padding:10px;border:1px solid #ddd;border-radius:6px;background:#f8fafc;">
                    <button type="button" class="copy-btn" onclick="copyRekening('<?= $bank_number ?>')"
                        style="border:none;background:#2c7a7b;color:white;padding:9px 12px;border-radius:6px;cursor:pointer;">📋</button>
                </div>
                <small style="color:#666;">Salin nomor rekening untuk transfer</small>
            </div>
        </div>

        <!-- Upload Bukti Transfer -->
        <label>Upload Bukti Transfer <span style="color:red;">*</span></label>
        <input type="file" name="bukti" accept="image/*" required style="margin-bottom:10px;">

        <!-- Catatan -->
        <label>Catatan (opsional)</label>
        <textarea name="catatan" rows="3" placeholder="Tambahkan pesan atau keterangan..."
            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;"></textarea>

        <div style="text-align:right;margin-top:10px;">
            <button type="submit"
                style="margin-top:15px;background:#2c7a7b;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">
                Kirim Donasi
            </button>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Format angka ribuan
        function formatRibuan(input) {
            let angka = input.value.replace(/\D/g, '');
            input.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Hapus titik sebelum submit
        document.querySelector('form').addEventListener('submit', function() {
            const jumlah = document.getElementById('jumlah');
            jumlah.value = jumlah.value.replace(/\./g, '');
        });

        // Fungsi salin nomor rekening dengan fallback
        function copyRekening(noRek) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                // ✅ Browser mendukung Clipboard API
                navigator.clipboard.writeText(noRek)
                    .then(() => showCopyAlert(noRek))
                    .catch(err => fallbackCopy(noRek));
            } else {
                // ❌ Clipboard API tidak tersedia
                fallbackCopy(noRek);
            }
        }

        // Fallback manual (membuat elemen input temporer)
        function fallbackCopy(noRek) {
            const tempInput = document.createElement('input');
            tempInput.value = noRek;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            showCopyAlert(noRek);
        }

        // Notifikasi SweetAlert
        function showCopyAlert(noRek) {
            Swal.fire({
                icon: 'success',
                title: 'Nomor Rekening Disalin!',
                html: `<strong>${noRek}</strong>`,
                showConfirmButton: false,
                timer: 2000,
                background: '#f8fafc',
                color: '#2c7a7b'
            });
        }
    </script>
</main>

<?php include_once('includes/footer.php'); ?>