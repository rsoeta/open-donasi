# 🌿 Open Donasi — Aplikasi Donasi Berbasis Web

**Open Donasi** adalah aplikasi web untuk mengelola program donasi secara terbuka, transparan, dan fleksibel.  
Dikembangkan menggunakan **PHP, MySQL, JavaScript, dan Google Apps Script-style UI**, aplikasi ini memungkinkan lembaga, masjid, atau organisasi sosial untuk:

- Membuat & mempublikasikan program donasi
- Menerima dan mengelola transaksi donasi (cash & transfer)
- Memverifikasi donasi online
- Menampilkan laporan transparan & grafik donasi per program
- Mengirim notifikasi WhatsApp ke donatur (via API pihak ketiga)
- Menyajikan tampilan modern, responsif, dan user-friendly

---

## 📸 Tampilan Aplikasi

| Halaman                                             | Deskripsi                                                                                             |
| --------------------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| 🏠 **Beranda (index.php)**                          | Menampilkan daftar program donasi & total donasi terkumpul.                                           |
| 📄 **Detail Donasi (detail.php)**                   | Informasi lengkap tiap program, gambar, deskripsi, dan tombol _Donasi Sekarang_ serta _Bagikan Link_. |
| 💚 **Form Donasi (donasi_form.php)**                | Pengunjung mengisi data & bukti transfer donasi.                                                      |
| 🧩 **Dashboard Admin (admin/dashboard.php)**        | Panel utama admin dengan navigasi cepat ke Data Donasi, Laporan, dan Pengaturan.                      |
| 📊 **Laporan (admin/laporan/laporan.php)**          | Rekap total donasi per program + grafik Chart.js + fitur ekspor Excel & PDF.                          |
| ⚙️ **Pengaturan (admin/pengaturan/pengaturan.php)** | Mengelola nama lembaga, logo, kontak, dan teks halaman _Tentang_.                                     |

---

## 🧠 Fitur Utama

✅ **Manajemen Program Donasi**  
Admin dapat membuat, mengedit, menutup, dan membuka kembali program donasi kapan saja.

✅ **Dua Jenis Donasi (Transfer & Cash)**

- Donasi _Transfer_ dikirim oleh pengguna melalui form.
- Donasi _Cash_ dimasukkan manual oleh admin via “Tambah Transaksi”.

✅ **Konfirmasi Donasi Otomatis (Admin Panel)**  
Admin bisa meninjau donasi yang masuk, menolak atau menerima (langsung masuk ke transaksi terverifikasi).

✅ **Laporan Transparan & Filter Periode**  
Filter laporan berdasarkan rentang tanggal, dengan tampilan grafik & tabel dinamis.

✅ **Ekspor Excel & PDF Resmi**  
Laporan dapat diekspor langsung dengan kop lembaga & tanda tangan otomatis.

✅ **Fitur Share Link Donasi (Web Share API)**  
Tombol “🔗 Bagikan” di setiap program untuk menyalin atau membagikan link ke media sosial.

✅ **Desain Modern & Responsif**  
Tampilan berbasis Bootstrap 5 + CSS toska lembut khas Open Donasi, nyaman di desktop & mobile.

---

## 🧱 Struktur Folder Proyek
