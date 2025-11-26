<?php
// include_once('../../includes/config.php');
// include_once('../includes/header.php');

session_start();
include_once('../../includes/config.php');
if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.php");
    exit;
}

// Ambil semua barang
$q = mysqli_query($conn, "
    SELECT id, name, unit, harga_per_unit
    FROM donasi_items
    ORDER BY name ASC
");

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
            <img src="<?= BASE_URL . htmlspecialchars($site_logo) ?>" height="40">
            <nav>
                <a href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a> |
                <a href="<?= BASE_URL ?>logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container mt-4 mb-5 p-4 bg-white shadow rounded">

        <div class="container mt-4">
            <h2 class="mb-3">Kelola Harga Barang</h2>

            <table class="table table-bordered table-striped">
                <thead class="table-success">
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Satuan</th>
                        <th>Harga / Unit (Rp)</th>
                        <th>Simpan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    while ($row = mysqli_fetch_assoc($q)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['unit']) ?></td>
                            <td>
                                <?php
                                $harga_tampil = number_format($row['harga_per_unit'], 0, ',', '.');
                                ?>
                                <input type="text"
                                    class="form-control"
                                    id="harga_<?= $row['id'] ?>"
                                    value="<?= $harga_tampil ?>"
                                    oninput="formatRibuan(this)">
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm"
                                    onclick="saveHarga(<?= $row['id'] ?>)">
                                    Simpan
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            function saveHarga(id) {
                let harga = document.getElementById('harga_' + id).value;

                if (harga < 0 || harga === "") {
                    Swal.fire('Oops!', 'Harga tidak valid.', 'error');
                    return;
                }

                fetch("update_harga.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            id: id,
                            harga: harga
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.fire({
                            icon: data.success ? 'success' : 'error',
                            title: data.message
                        });
                    });
            }

            // Format angka ribuan
            function formatRibuan(input) {
                let angka = input.value.replace(/\D/g, '');
                input.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
        </script>
    </div>
</body>

</html>