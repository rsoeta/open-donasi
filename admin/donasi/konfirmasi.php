<?php
session_start();
include_once('../../includes/config.php');

if (!isset($_SESSION['admin'])) {
    header('Location: ../../login.php');
    exit;
}

// Ambil pending + program + type
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

    <link rel="icon" href="<?= BASE_URL . htmlspecialchars($site_logo) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
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
    <style>
        .badge-transfer {
            background: #3182ce;
        }

        .badge-goods {
            background: #38a169;
        }

        .badge-mixed {
            background: #805ad5;
        }
    </style>
</head>

<body>

    <header>
        <div class="container d-flex justify-content-between align-items-center">
            <img src="<?= BASE_URL . htmlspecialchars($site_logo) ?>" height="40">
            <nav>
                <a href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a> |
                <a href="<?= BASE_URL ?>logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container mt-4 mb-5 p-4 bg-white shadow rounded">

        <h2 class="text-center mb-4">Daftar Donasi Pending</h2>

        <?php if (mysqli_num_rows($query) > 0): ?>
            <table id="konfirmasiTable" class="table table-bordered table-striped nowrap w-100">
                <thead class="table-success">
                    <tr>
                        <th>No</th>
                        <th>Program</th>
                        <th>Donatur</th>
                        <th>Tipe</th>
                        <th>Transfer (Rp)</th>
                        <th>Barang</th>
                        <th>Bukti</th>
                        <th>Tanggal</th>
                        <th>Nilai Barang</th>
                        <th>Total Donasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($query)):

                        // ambil barang untuk pending ini
                        $itemQ = mysqli_query($conn, "
                            SELECT di.qty, i.name, i.harga_per_unit
                            FROM donasi_pending_items di
                            JOIN donasi_items i ON di.item_id = i.id
                            WHERE di.pending_id = {$row['id']}
                        ");

                        $nilai_barang = 0;
                        $items_list  = [];
                        $items_text  = "-";

                        // Proses barang
                        while ($it = mysqli_fetch_assoc($itemQ)) {

                            $subtotal = $it['qty'] * $it['harga_per_unit'];
                            $nilai_barang += $subtotal;

                            $items_list[] = [
                                'name'     => $it['name'],
                                'qty'      => intval($it['qty']),
                                'harga'    => $it['harga_per_unit'],
                                'subtotal' => $subtotal
                            ];

                            // Buat ringkasan teks
                            $summary[] = $it['qty'] . " × " . $it['name'];
                        }

                        if (!empty($items_list)) {
                            $items_text = implode(", ", $summary);
                        }


                        // $items_text = "-";
                        // $items_list = [];

                        // if ($row['type'] == 'goods' || $row['type'] == 'mixed') {
                        //     $items = [];
                        //     while ($it = mysqli_fetch_assoc($itemQ)) {
                        //         $items[] = $it['qty'] . " x " . $it['name'];
                        //         $items_list[] = [
                        //             'name' => $it['name'],
                        //             'qty'  => intval($it['qty'])
                        //         ];
                        //     }
                        //     if (!empty($items)) {
                        //         $items_text = implode(", ", $items);
                        //     }
                        // }

                        // badge tipe
                        $badge = "<span class='badge badge-transfer'>Transfer</span>";
                        if ($row['type'] == 'goods') {
                            $badge = "<span class='badge badge-goods'>Barang</span>";
                        }
                        if ($row['type'] == 'mixed') {
                            $badge = "<span class='badge badge-mixed'>Mixed</span>";
                        }

                    ?>

                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_program']) ?></td>
                            <td><?= htmlspecialchars($row['nama_donatur']) ?></td>
                            <td><?= $badge ?></td>
                            <td><?= number_format($row['jumlah'], 0, ',', '.') ?></td>

                            <!-- ringkasan barang -->
                            <td>
                                <?php if ($row['type'] == 'goods' || $row['type'] == 'mixed'): ?>
                                    <?= htmlspecialchars($items_text) ?>
                                    <br>
                                    <button
                                        class="btn btn-sm btn-outline-primary mt-1 btn-detail-barang"
                                        data-items='<?= json_encode($items_list) ?>'
                                        data-donor="<?= htmlspecialchars($row['nama_donatur']) ?>">
                                        Detail
                                    </button>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>

                            <!-- bukti -->
                            <td>
                                <?php if (!empty($row['bukti']) && file_exists('../../uploads/bukti/' . $row['bukti'])): ?>
                                    <a href="<?= BASE_URL ?>uploads/bukti/<?= $row['bukti'] ?>" target="_blank">
                                        <img src="<?= BASE_URL ?>uploads/bukti/<?= $row['bukti'] ?>" width="60" class="rounded">
                                    </a>
                                <?php else: ?>
                                    <em>-</em>
                                <?php endif; ?>
                            </td>

                            <td><?= date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])) ?></td>
                            <td>
                                Rp <?= number_format($nilai_barang, 0, ',', '.') ?>
                            </td>

                            <td>
                                <?php
                                $total = $row['jumlah'] + $nilai_barang;
                                echo "Rp " . number_format($total, 0, ',', '.');
                                ?>
                            </td>

                            <td>
                                <a href="konfirmasi_terima.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm">
                                    ✓ Terima
                                </a>
                                <a href="konfirmasi_tolak.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">
                                    ✗ Tolak
                                </a>
                            </td>
                        </tr>

                    <?php endwhile; ?>
                </tbody>
            </table>

        <?php else: ?>
            <p class="text-center mt-3">Belum ada donasi pending.</p>
        <?php endif; ?>
        <!-- UNIVERSAL MODAL DETAIL BARANG -->
        <div class="modal fade" id="modalDetailBarang" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Detail Barang Donasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p id="detailDonatur" class="fw-bold mb-2"></p>

                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th width="80">Qty</th>
                                </tr>
                            </thead>
                            <tbody id="detailBarangBody">
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(() => {
            $('#konfirmasiTable').DataTable({
                responsive: true
            });
        });
    </script>
    <script>
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("btn-detail-barang")) {

                // Ambil data barang dari atribut tombol
                let items = JSON.parse(e.target.getAttribute("data-items"));
                let donor = e.target.getAttribute("data-donor");

                // Set nama donor
                document.getElementById("detailDonatur").innerText =
                    "Donatur: " + donor;

                // Render baris tabel
                let tbody = document.getElementById("detailBarangBody");
                tbody.innerHTML = ""; // reset

                items.forEach(it => {
                    tbody.innerHTML += `
                <tr>
                    <td>${it.name}</td>
                    <td>${it.qty}</td>
                </tr>
            `;
                });

                // Tampilkan modal
                let modal = new bootstrap.Modal(document.getElementById("modalDetailBarang"));
                modal.show();
            }
        });
    </script>

</body>

</html>