<?php
include_once('../../includes/config.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: ../../login.php');
    exit;
}

// === FILTER TANGGAL ===
$startDate = isset($_GET['start']) ? $_GET['start'] : '';
$endDate   = isset($_GET['end']) ? $_GET['end'] : '';

$filterTrans = ''; // untuk tabel donasi_transaksi
$filterPending = ''; // untuk tabel donasi_pending

if (!empty($startDate) && !empty($endDate)) {
    $start = date('Y-m-d', strtotime($startDate));
    $end   = date('Y-m-d', strtotime($endDate));
    $filterTrans   = "AND (tt.tanggal_transaksi BETWEEN '$start' AND '$end')";
    $filterPending = "AND (pp.tanggal_pengajuan BETWEEN '$start' AND '$end')";
}

// === QUERY LAPORAN ===
$queryStr = "
SELECT
  d.id,
  d.judul AS penerima,

  (
    COALESCE((
      SELECT COUNT(DISTINCT tt.nama_donatur)
      FROM donasi_transaksi tt
      WHERE tt.id_donasi = d.id $filterTrans
    ), 0)
    +
    COALESCE((
      SELECT COUNT(DISTINCT pp.nama_donatur)
      FROM donasi_pending pp
      WHERE pp.id_donasi = d.id AND pp.status IN ('menunggu','ditolak') $filterPending
    ), 0)
  ) AS jumlah_donatur,

  -- Donasi diterima hanya dari transaksi (cash & transfer)
  COALESCE((
    SELECT SUM(tt.jumlah)
    FROM donasi_transaksi tt
    WHERE tt.id_donasi = d.id $filterTrans
  ), 0) AS donasi_diterima,

  -- Donasi ditolak (hanya dari pending)
  COALESCE((
    SELECT SUM(pp.jumlah)
    FROM donasi_pending pp
    WHERE pp.id_donasi = d.id AND pp.status = 'ditolak' $filterPending
  ), 0) AS donasi_ditolak,

  -- Pending count
  COALESCE((
    SELECT COUNT(*)
    FROM donasi_pending pp
    WHERE pp.id_donasi = d.id AND pp.status = 'menunggu' $filterPending
  ), 0) AS pending_donasi,

  -- Total donasi = diterima + ditolak + pending
  (
    COALESCE((
      SELECT SUM(tt.jumlah) FROM donasi_transaksi tt WHERE tt.id_donasi = d.id $filterTrans
    ), 0)
    +
    COALESCE((
      SELECT SUM(pp.jumlah) FROM donasi_pending pp WHERE pp.id_donasi = d.id AND pp.status IN ('menunggu','ditolak') $filterPending
    ), 0)
  ) AS total_donasi,

  -- Metode Donasi
  CASE
    WHEN EXISTS (SELECT 1 FROM donasi_transaksi tt WHERE tt.id_donasi = d.id AND tt.metode = 'cash')
         AND EXISTS (SELECT 1 FROM donasi_transaksi tt2 WHERE tt2.id_donasi = d.id AND tt2.metode = 'transfer')
      THEN 'Cash & Transfer'
    WHEN EXISTS (SELECT 1 FROM donasi_transaksi tt WHERE tt.id_donasi = d.id AND tt.metode = 'cash')
      THEN 'Cash'
    WHEN EXISTS (SELECT 1 FROM donasi_transaksi tt WHERE tt.id_donasi = d.id AND tt.metode = 'transfer')
      THEN 'Transfer'
    ELSE '-'
  END AS metode_donasi,

  d.status_donasi,
  d.tanggal_ditutup

FROM donasi_post d
ORDER BY donasi_diterima DESC
";

$query = mysqli_query($conn, $queryStr);
if (!$query) {
    die("<div style='background:#fee2e2;color:#b91c1c;padding:15px;border-radius:6px;'>
        <b>❌ Query gagal:</b> " . mysqli_error($conn) . "
    </div>");
}

// === Data Grafik ===
$labels = [];
$values = [];
while ($r = mysqli_fetch_assoc($query)) {
    $labels[] = $r['penerima'];
    $values[] = $r['donasi_diterima'];
    $data[] = $r;
}
mysqli_data_seek($query, 0);

// Ambil nilai saat ini
$current_name = get_setting('site_name', 'Open Donasi');
$current_contact = get_setting('site_contact', 'info@example.com');
$site_logo = get_setting('site_logo', 'assets/images/logo.png');

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon (multi-browser support) -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL . htmlspecialchars($site_logo) ?>">
    <link rel="apple-touch-icon" href="<?= BASE_URL . htmlspecialchars($site_logo) ?>">
    <meta name="theme-color" content="#2c7a7b">

    <title>Laporan Donasi - Open Donasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <header class="p-3 bg-white border-bottom shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 text-success fw-bold">
                    <img src="<?= BASE_URL . htmlspecialchars($site_logo) ?>" alt="Logo" height="40">
                    Laporan Donasi
                </h4>
                <small class="text-muted">Pantau hasil donasi berdasarkan periode waktu</small>
            </div>
            <div>
                <a href="<?= BASE_URL ?>admin/donasi/transaksi_add.php" class="btn btn-success me-2">
                    ➕ Tambah Transaksi
                </a>
                <a href="<?= BASE_URL ?>admin/dashboard.php" class="btn btn-outline-secondary">← Kembali</a>
            </div>
        </div>
    </header>

    <div class="container mt-4">
        <!-- Filter -->
        <form method="GET" class="row g-3 mb-4 align-items-end">
            <div class="col-md-4">
                <label for="start" class="form-label">Tanggal Mulai</label>
                <input type="date" id="start" name="start" value="<?= htmlspecialchars($startDate) ?>" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="end" class="form-label">Tanggal Akhir</label>
                <input type="date" id="end" name="end" value="<?= htmlspecialchars($endDate) ?>" class="form-control">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success w-100">Tampilkan</button>
            </div>
        </form>

        <!-- Grafik -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white">Grafik Total Donasi per Program</div>
            <div class="card-body">
                <canvas id="donasiChart" height="100"></canvas>
            </div>
        </div>

        <!-- Tabel -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <span>Tabel Laporan Donasi</span>
                <div>
                    <a href="export_excel.php?<?= http_build_query($_GET) ?>" style="background:#38a169;color:white;padding:6px 12px;border-radius:6px;text-decoration:none;">
                        📊 Ekspor Excel
                    </a>
                    <a href="export_pdf.php?<?= http_build_query($_GET) ?>" style="background:#e53e3e;color:white;padding:6px 12px;border-radius:6px;text-decoration:none;">
                        📄 Ekspor PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="laporanTable" class="table table-striped table-bordered nowrap" style="width:100%">
                    <thead class="table-success">
                        <tr>
                            <th>No</th>
                            <th>Program Donasi</th>
                            <th>Jumlah Donatur</th>
                            <th>Donasi Diterima (Rp)</th>
                            <th>Donasi Ditolak (Rp)</th>
                            <!-- <th>Total Donasi (Rp)</th> -->
                            <th>Pending</th>
                            <th>Metode</th>
                            <th>Status Donasi</th>
                            <th>Tanggal Ditutup</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        mysqli_data_seek($query, 0);
                        while ($row = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['penerima']) ?></td>
                                <td><?= number_format($row['jumlah_donatur'], 0, ',', '.') ?></td>
                                <td><strong><?= number_format($row['donasi_diterima'], 0, ',', '.') ?></strong></td>
                                <td><?= number_format($row['donasi_ditolak'], 0, ',', '.') ?></td>
                                <!-- <td><strong><?= number_format($row['total_donasi'], 0, ',', '.') ?></strong></td> -->
                                <td><?= $row['pending_donasi'] ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($row['metode_donasi']) ?></span></td>
                                <td><?= strtolower($row['status_donasi']) === 'aktif'
                                        ? "<span class='text-success fw-bold'>Aktif</span>"
                                        : "<span class='text-danger fw-bold'>Ditutup</span>"; ?></td>
                                <td><?= !empty($row['tanggal_ditutup']) ? date('d M Y', strtotime($row['tanggal_ditutup'])) : '-' ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#laporanTable').DataTable({
                responsive: true,
                paging: true,
                searching: true,
                ordering: true,
                pageLength: 10,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                }
            });
        });

        // === Grafik Donasi ===
        const ctx = document.getElementById('donasiChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Total Donasi (Rp)',
                    data: <?= json_encode($values) ?>,
                    backgroundColor: 'rgba(44, 122, 123, 0.7)',
                    borderColor: '#2c7a7b',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>