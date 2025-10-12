<?php
session_start();
include_once('../../includes/config.php');

if (!isset($_SESSION['admin'])) {
    header('Location: ../../login.php');
    exit;
}

// Ambil donasi pending
$query = mysqli_query($conn, "
  SELECT p.*, d.judul AS nama_program
  FROM donasi_pending p
  LEFT JOIN donasi_post d ON p.id_donasi = d.id
  ORDER BY p.tanggal_pengajuan DESC
");

$site_logo = get_setting('site_logo', 'assets/images/logo.png');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Donasi - Open Donasi</title>

    <!-- Favicon (multi-browser support) -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL . htmlspecialchars($site_logo) ?>">
    <link rel="apple-touch-icon" href="<?= BASE_URL . htmlspecialchars($site_logo) ?>">
    <meta name="theme-color" content="#2c7a7b">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background: #f5f6fa;
        }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #2c7a7b;
            color: white;
        }

        td img {
            border-radius: 8px;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-accept {
            background: #38a169;
            color: white;
        }

        .btn-reject {
            background: #e53e3e;
            color: white;
        }

        .status {
            font-weight: bold;
            text-transform: capitalize;
        }
    </style>
</head>

<body>

    <header>
        <div class="container" style="display:flex;justify-content:space-between;align-items:center;">
            <img src="<?= BASE_URL . htmlspecialchars($site_logo) ?>" alt="Logo" height="40">
            <nav>
                <a href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a> |
                <a href="<?= BASE_URL ?>logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2 style="text-align:center;margin-bottom:20px;">Daftar Donasi Pending</h2>

        <?php if (mysqli_num_rows($query) > 0): ?>
            <table id="konfirmasiTable" class="table table-striped table-bordered display nowrap dataTable dtr-inline collapsed" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Program</th>
                        <th>Nama Donatur</th>
                        <th>Jumlah (Rp)</th>
                        <th>Bukti Transfer</th>
                        <th>Catatan</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_program']) ?></td>
                            <td><?= htmlspecialchars($row['nama_donatur']) ?></td>
                            <td><?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                            <td>
                                <?php if (!empty($row['bukti']) && file_exists('../../uploads/bukti/' . $row['bukti'])): ?>
                                    <a href="<?= BASE_URL ?>uploads/bukti/<?= htmlspecialchars($row['bukti']) ?>" target="_blank">
                                        <img src="<?= BASE_URL ?>uploads/bukti/<?= htmlspecialchars($row['bukti']) ?>" alt="Bukti" width="60">
                                    </a>
                                <?php else: ?>
                                    <em>Tidak ada</em>
                                <?php endif; ?>
                            </td>
                            <td><?= nl2br(htmlspecialchars($row['catatan'])) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])) ?></td>
                            <td><span class="status" style="color:#d69e2e;"><?= $row['status'] ?></span></td>
                            <td>
                                <?php if ($row['status'] == 'menunggu'): ?>
                                    <a href="konfirmasi_terima.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-circle"></i> Terima
                                    </a>
                                    <a href="konfirmasi_tolak.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger">
                                        <i class="bi bi-x-circle"></i> Tolak
                                    </a>
                                <?php else: ?>
                                    <em>Selesai</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center;">Belum ada donasi pending.</p>
        <?php endif; ?>
    </div>

    <!-- DataTables Bootstrap 5 JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <!-- File global inisialisasi DataTables -->
    <script src="<?= BASE_URL ?>assets/js/datatables-init.js"></script>

    <script>
        function verifikasiDonasi(id, aksi) {
            let text = aksi === 'terima' ? 'Konfirmasi donasi ini sebagai valid?' : 'Tolak donasi ini?';
            Swal.fire({
                title: 'Yakin?',
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: aksi === 'terima' ? 'Ya, Terima' : 'Ya, Tolak',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('verifikasi_proses.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'id=' + id + '&aksi=' + aksi
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Berhasil!', data.message, 'success')
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('Gagal', data.message, 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Gagal memproses permintaan.', 'error'));
                }
            });
        }
    </script>

</body>

</html>