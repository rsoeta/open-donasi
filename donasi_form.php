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
$site_bank = get_setting('site_bank', 'BRI - 000000000000000');
preg_match('/\d{6,}/', $site_bank, $matches);
$bank_number = $matches[0] ?? '0000000000';
$bank_name = trim(preg_replace('/[-–—]?\s*\d.*$/', '', $site_bank));

// ambil daftar items jika accepts_goods
$items_available = [];
if ($row['accepts_goods']) {
    $res = mysqli_query($conn, "SELECT * FROM donasi_items WHERE donasi_post_id = {$row['id']} ORDER BY id ASC");
    while ($r = mysqli_fetch_assoc($res)) $items_available[] = $r;
}

include_once('includes/header.php');
?>

<main style="max-width:700px;margin:40px auto;background:#fff;padding:25px;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,0.05);">
    <h2 style="text-align:center;margin-bottom:25px;">Donasi untuk: <?= htmlspecialchars($row['judul']) ?></h2>

    <form action="donasi_proses.php" method="POST" enctype="multipart/form-data" id="donationForm">
        <input type="hidden" name="id_donasi" value="<?= $id_donasi ?>">

        <label>Nama Donatur <span style="color:red;">*</span></label>
        <input type="text" name="nama_donatur" required placeholder="Nama lengkap Anda" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-bottom:10px;">

        <!-- Nomor WA -->
        <label>Nomor WhatsApp <span style="color:red;">*</span></label>
        <input type="text" name="wa" id="wa" required placeholder="Contoh: 081234567890"
            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-bottom:10px;">

        <!-- Pilih jenis donasi (transfer / barang) -->
        <label>Jenis Donasi <span style="color:red">*</span></label>
        <div style="display:flex;gap:12px;align-items:center;margin-bottom:10px;">
            <label><input type="checkbox" name="opt_transfer" id="opt_transfer" checked> Transfer</label>
            <?php if ($row['accepts_goods']): ?>
                <label><input type="checkbox" name="opt_goods" id="opt_goods"> Barang</label>
            <?php endif; ?>
            <small class="small" style="margin-left:8px;color:#666;">(Pilih 1 atau lebih. Cash hanya bisa dimasukkan oleh admin.)</small>
        </div>

        <!-- Transfer area -->
        <div id="area_transfer" style="margin-bottom:12px;">
            <label>Nominal Donasi (Rp) <span style="color:red;">*</span></label>
            <input type="text" id="jumlah" name="jumlah" placeholder="Contoh: 150.000" inputmode="numeric" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-bottom:10px;" oninput="formatRibuan(this)">

            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
                <div style="flex:1;">
                    <label>Metode</label>
                    <input type="text" name="metode" value="Transfer" readonly style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;background:#f8fafc;">
                </div>
                <div style="flex:1;min-width:200px;">
                    <label>Nomor Rekening</label>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <input type="text" id="noRekening" value="<?= htmlspecialchars($site_bank) ?>" readonly style="flex:1;padding:10px;border:1px solid #ddd;border-radius:6px;background:#f8fafc;">
                        <button type="button" class="copy-btn" onclick="copyRekening('<?= $bank_number ?>')" style="border:none;background:#2c7a7b;color:white;padding:9px 12px;border-radius:6px;cursor:pointer;">📋</button>
                    </div>
                    <small style="color:#666;">Salin nomor rekening untuk transfer</small>
                </div>
            </div>

            <label>Upload Bukti Transfer <span style="color:red;">*</span></label>
            <input type="file" name="bukti" id="bukti" accept="image/*" style="margin-bottom:10px;">
        </div>

        <!-- Goods area -->
        <?php if ($row['accepts_goods']): ?>
            <div id="area_goods" style="display:none;margin-bottom:10px;">
                <?php if (!empty($row['goods_note'])): ?>
                    <div style="padding:10px;background:#f1f8f7;border-radius:6px;margin-bottom:8px;"><?= nl2br(htmlspecialchars($row['goods_note'])) ?></div>
                <?php endif; ?>

                <label>Pilih Barang</label>
                <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
                    <select id="select_item" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:6px;">
                        <option value="">-- Pilih barang --</option>
                        <?php foreach ($items_available as $it): ?>
                            <option value="<?= $it['id'] ?>" data-unit="<?= htmlspecialchars($it['unit']) ?>"><?= htmlspecialchars($it['name']) ?> (<?= htmlspecialchars($it['unit']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" id="qty_item" min="1" placeholder="Qty" style="width:90px;padding:8px;border:1px solid #ddd;border-radius:6px;">
                    <button type="button" onclick="addGoods()">Tambah</button>
                </div>

                <div id="goods_list">
                    <!-- daftar barang donor -->
                    <em>Belum ada barang yang dipilih.</em>
                </div>
            </div>
        <?php endif; ?>

        <label>Catatan (opsional)</label>
        <textarea name="catatan" rows="3" placeholder="Tambahkan pesan atau keterangan..." style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;"></textarea>

        <!-- NEW: PILIH METODE PENGIRIMAN UNTUK DONASI BARANG -->
        <div id="pengiriman_area" style="display:none;margin-bottom:15px;">

            <label>Metode Pengiriman Barang <span style="color:red;">*</span></label>
            <div style="display:flex;gap:25px;margin-bottom:8px;margin-top:6px;">
                <label><input type="radio" name="pengiriman" value="dikirim"> Dikirim ke masjid</label>
                <label><input type="radio" name="pengiriman" value="dijemput"> Dijemput oleh relawan</label>
            </div>

            <!-- Lokasi Masjid (hidden by default) -->
            <div id="maps_area" style="display:none; margin-top:10px;">
                <label>📍 Lokasi Masjid</label><br>
                <a href="<?= get_setting('masjid_maps') ?>"
                    target="_blank"
                    style="display:inline-block;background:#2c7a7b;color:#fff;padding:8px 12px;border-radius:6px;text-decoration:none;">
                    Buka Google Maps
                </a>
            </div>

            <!-- NEW: Alamat donatur → wajib jika dijemput -->
            <div id="alamat_wrap" style="display:none;">
                <label>Alamat Penjemputan <span style="color:red;">*</span></label>
                <textarea name="alamat" id="alamat" rows="2" placeholder="Alamat lengkap untuk penjemputan..."
                    style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-bottom:10px;"></textarea>
            </div>

        </div>

        <div style="text-align:right;margin-top:10px;">
            <button type="submit" style="margin-top:15px;background:#2c7a7b;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">Kirim Donasi</button>
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
        document.getElementById('donationForm').addEventListener('submit', function(e) {
            // determine options selected
            const transfer = document.getElementById('opt_transfer') ? document.getElementById('opt_transfer').checked : false;
            const goods = document.getElementById('opt_goods') ? document.getElementById('opt_goods').checked : false;

            if (!transfer && !goods) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih jenis donasi',
                    text: 'Silakan pilih Transfer atau Barang.'
                });
                return;
            }

            if (transfer) {
                const jumlah = document.getElementById('jumlah');
                if (!jumlah || jumlah.value.trim() === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nominal kosong',
                        text: 'Masukkan nominal donasi untuk transfer.'
                    });
                    return;
                }
                jumlah.value = jumlah.value.replace(/\./g, '');
                // jika transfer dipilih, bukti wajib
                const bukti = document.getElementById('bukti');
                if (!bukti || !bukti.value) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Bukti transfer kosong',
                        text: 'Mohon unggah bukti transfer.'
                    });
                    return;
                }
            }

            if (goods) {
                // pastikan minimal 1 barang di goods_list
                const goodsList = document.querySelectorAll('[data-goods-item]');
                if (!goodsList || goodsList.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Belum pilih barang',
                        text: 'Silakan tambahkan minimal 1 item barang.'
                    });
                    return;
                }
                // create hidden inputs for item_id[] and item_qty[]
                // bersihkan existing jika ada
                document.querySelectorAll('input[name="item_id[]"]').forEach(n => n.remove());
                document.querySelectorAll('input[name="item_qty[]"]').forEach(n => n.remove());
                goodsList.forEach(function(row) {
                    const id = row.getAttribute('data-goods-item');
                    const qty = row.querySelector('.g-qty').value;
                    const inpId = document.createElement('input');
                    inpId.type = 'hidden';
                    inpId.name = 'item_id[]';
                    inpId.value = id;
                    const inpQty = document.createElement('input');
                    inpQty.type = 'hidden';
                    inpQty.name = 'item_qty[]';
                    inpQty.value = qty;
                    document.getElementById('donationForm').appendChild(inpId);
                    document.getElementById('donationForm').appendChild(inpQty);
                });
            }
        });

        // Copy rekening
        function copyRekening(noRek) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(noRek).then(() => showCopyAlert(noRek)).catch(() => fallbackCopy(noRek));
            } else fallbackCopy(noRek);
        }

        function fallbackCopy(noRek) {
            const temp = document.createElement('input');
            temp.value = noRek;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
            showCopyAlert(noRek);
        }

        function showCopyAlert(noRek) {
            Swal.fire({
                icon: 'success',
                title: 'Nomor Rekening Disalin!',
                html: `<strong>${noRek}</strong>`,
                showConfirmButton: false,
                timer: 1500,
                background: '#f8fafc',
                color: '#2c7a7b'
            });
        }

        // goods UI
        <?php if ($row['accepts_goods']): ?>
            document.getElementById('opt_goods').addEventListener('change', function() {
                document.getElementById('area_goods').style.display = this.checked ? 'block' : 'none';
            });

            function addGoods() {
                const sel = document.getElementById('select_item');
                const id = sel.value;
                if (!id) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih barang dulu'
                    });
                    return;
                }
                const name = sel.options[sel.selectedIndex].text;
                const unit = sel.options[sel.selectedIndex].getAttribute('data-unit') || '';
                const qtyEl = document.getElementById('qty_item');
                let qty = parseInt(qtyEl.value || '0');
                if (!qty || qty < 1) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Qty minimal 1'
                    });
                    return;
                }

                // jika item sudah ada di list, gabungkan qty
                const existing = document.querySelector('[data-goods-item="' + id + '"]');
                if (existing) {
                    const inputQty = existing.querySelector('.g-qty');
                    inputQty.value = parseInt(inputQty.value) + qty;
                } else {
                    const div = document.createElement('div');
                    div.setAttribute('data-goods-item', id);
                    div.style = 'padding:8px;border:1px solid #eee;margin-bottom:6px;border-radius:6px;display:flex;gap:8px;align-items:center;';
                    div.innerHTML = `<div style="flex:1;"><strong>${escapeHtml(name)}</strong> <span class="small">(${escapeHtml(unit)})</span></div>
                             <div>Qty: <input class="g-qty" type="number" value="${qty}" min="1" style="width:80px;padding:6px;border:1px solid #ddd;border-radius:6px;"></div>
                             <div><button type="button" onclick="removeGoods(this)">Hapus</button></div>`;
                    document.getElementById('goods_list').appendChild(div);
                }
                // reset inputs
                qtyEl.value = '';
                sel.selectedIndex = 0;
            }

            function removeGoods(btn) {
                const parent = btn.closest('[data-goods-item]');
                if (!parent) return;
                parent.remove();
            }

            function escapeHtml(t) {
                return t.replace(/[&<>"']/g, function(m) {
                    return ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    })[m];
                });
            }
        <?php endif; ?>

        // NEW: Tampilkan area pengiriman ketika barang dicentang
        const optGoods = document.getElementById('opt_goods');
        const pengirimanArea = document.getElementById('pengiriman_area');
        const alamatWrap = document.getElementById('alamat_wrap');

        if (optGoods) {
            optGoods.addEventListener('change', function() {
                if (this.checked) {
                    pengirimanArea.style.display = 'block';
                } else {
                    pengirimanArea.style.display = 'none';
                    alamatWrap.style.display = 'none';
                }
            });
        }

        // NEW: Tampilkan textarea alamat hanya jika "dijemput"
        document.addEventListener('change', function(e) {
            if (e.target.name === 'pengiriman') {
                if (e.target.value === 'dijemput') {
                    alamatWrap.style.display = 'block';
                } else {
                    alamatWrap.style.display = 'none';
                }
            }
        });

        // NEW: Validasi WA + Pengiriman + Alamat
        document.getElementById('donationForm').addEventListener('submit', function(evt) {

            // WA wajib
            const wa = document.getElementById('wa').value.trim();
            if (wa === '') {
                evt.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Nomor WA wajib diisi',
                    text: 'Masukkan nomor WhatsApp yang bisa dihubungi.'
                });
                return;
            }

            // Jika barang dipilih → metode pengiriman wajib
            if (optGoods && optGoods.checked) {

                const pengiriman = document.querySelector('input[name="pengiriman"]:checked');
                if (!pengiriman) {
                    evt.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih metode pengiriman',
                        text: 'Silakan pilih dikirim ke masjid atau dijemput relawan.'
                    });
                    return;
                }

                // Jika dijemput → alamat wajib
                if (pengiriman.value === 'dijemput') {
                    const alamat = document.getElementById('alamat').value.trim();
                    if (alamat === '') {
                        evt.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Alamat wajib diisi',
                            text: 'Silakan isi alamat lengkap untuk penjemputan.'
                        });
                        return;
                    }
                }
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target.name === 'pengiriman') {
                if (e.target.value === 'dikirim') {
                    document.getElementById('maps_area').style.display = 'block';
                } else {
                    document.getElementById('maps_area').style.display = 'none';
                }
            }
        });
    </script>
</main>

<?php include_once('includes/footer.php'); ?>