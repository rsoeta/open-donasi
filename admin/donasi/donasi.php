<?php
session_start();
include_once('../../includes/config.php');

if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

$site_logo = get_setting('site_logo', 'assets/images/logo.png');
$result = mysqli_query($conn, "SELECT * FROM donasi_post ORDER BY id DESC");

// Ambil nilai saat ini
$site_name = get_setting('site_name', 'Open Donasi');
$current_contact = get_setting('site_contact', 'info@example.com');
$site_logo = get_setting('site_logo', 'assets/images/logo.png');

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Donasi - <?= htmlspecialchars($site_name) ?></title>
    <!-- Favicon (multi-browser support) -->
    <link rel="icon" type="image/png" sizes="32x32" href=" <?= BASE_URL . htmlspecialchars($site_logo) ?>">
    <link rel="apple-touch-icon" href="<?= BASE_URL . htmlspecialchars($site_logo) ?>">
    <meta name="theme-color" content="#2c7a7b">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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
        <div class="container d-flex justify-content-between align-items-center">
            <div class="logo">
                <a href="/"><img src="<?= BASE_URL . htmlspecialchars($site_logo) ?>" alt="<?= htmlspecialchars($site_name) ?>" height="40"></a>
            </div>
            <nav>
                <a href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a>
                <a href="<?= BASE_URL ?>logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container mt-4">
        <h2>Daftar Program Donasi</h2>
        <div class="text-end mb-3">
            <a href="donasi_add.php" class="btn btn-primary">+ Tambah Artikel Donasi</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered display nowrap dataTable dtr-inline collapsed" style="width:100%">
                <thead>
                    <tr>
                        <!-- <th></th> Kolom collapsible -->
                        <th>No</th>
                        <th>Gambar</th>
                        <th>Judul</th>
                        <th>Tanggal</th>
                        <!-- <th>Status</th> -->
                        <th>Status Donasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        // echo "<td></td>"; // kolom collapse
                        echo "<td>{$no}</td>";

                        // Kolom gambar
                        echo "<td>";
                        if (!empty($row['gambar'])) {
                            echo "<img src='../../uploads/{$row['gambar']}' alt='Gambar' style='width:90px;border-radius:6px;'>";
                        } else {
                            echo "Tidak ada gambar";
                        }
                        echo "</td>";

                        // Kolom judul
                        echo "<td>" . htmlspecialchars($row['judul']) . "</td>";

                        // Kolom tanggal
                        echo "<td>" . date('d M Y', strtotime($row['tanggal_post'])) . "</td>";

                        // Kolom status
                        // echo "<td><span class='badge bg-success'>" . htmlspecialchars($row['status']) . "</span></td>";

                        // Kolom status donasi
                        $status_donasi = strtolower($row['status_donasi']);
                        if ($status_donasi === 'aktif') {
                            echo "<td><span class='text-success fw-bold'>Aktif</span></td>";
                        } else {
                            echo "<td><span class='text-danger fw-bold'>Ditutup</span></td>";
                        }

                        // Kolom aksi
                        echo "<td class='text-center'>";

                        // Tombol Edit
                        echo "<a href='donasi_edit.php?id={$row['id']}' class='btn btn-sm btn-warning me-1'>
                    <i class='bi bi-pencil-square'></i> Edit
                  </a>";

                        // Tombol Hapus
                        echo "<button onclick='deleteDonasi({$row['id']})' class='btn btn-sm btn-danger me-1'>
                    <i class='bi bi-trash'></i> Hapus
                  </button>";

                        // Tombol Buka / Tutup Donasi
                        if ($status_donasi === 'aktif') {
                            echo "<a href='donasi_status.php?id={$row['id']}&aksi=close' 
                        class='btn btn-sm btn-outline-danger'>
                        <i class='bi bi-lock'></i> Tutup
                      </a>";
                        } else {
                            echo "<a href='donasi_status.php?id={$row['id']}&aksi=open' 
                        class='btn btn-sm btn-outline-success'>
                        <i class='bi bi-unlock'></i> Buka
                      </a>";
                        }

                        // Tombol Share
                        echo "<button onclick=\"shareDonasi('" . htmlspecialchars($row['judul']) . "', '" . BASE_URL . "detail.php?id=" . $row['id'] . "')\" class=\"btn btn-sm btn-success\"><i class='bi bi-share'></i> Bagikan</button>";

                        echo "</td>";
                        echo "</tr>";

                        $no++;
                    }
                    ?>
                </tbody>
            </table>


        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- DataTables Bootstrap 5 JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <!-- File global inisialisasi DataTables -->
    <script src="<?= BASE_URL ?>assets/js/datatables-init.js"></script>
    <script src="<?= BASE_URL ?>assets/js/share.js"></script>


    <script>
        function deleteDonasi(id) {
            Swal.fire({
                title: 'Hapus Donasi?',
                text: 'Data yang dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e53e3e',
                cancelButtonColor: '#718096',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'donasi_delete.php?id=' + id;
                }
            });
        }
    </script>
</body>

</html>