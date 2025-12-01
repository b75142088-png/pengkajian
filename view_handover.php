<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "rumah_sakit";

// Membuat koneksi
$conn = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Query untuk mengambil data
$sql = "SELECT * FROM handover_jaga ORDER BY tanggal_handover DESC, kamar_pasien ASC";
$result = mysqli_query($conn, $sql);

// Group data by tanggal handover
$handover_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tanggal = $row['tanggal_handover'];
    if (!isset($handover_data[$tanggal])) {
        $handover_data[$tanggal] = [];
    }
    $handover_data[$tanggal][] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Handover Jaga</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #eef2f5;
        }
        .header-title {
            font-weight: bold;
            font-size: 30px;
            color: #2c3e50;
        }
        .handover-card {
            border-radius: 12px;
            overflow: hidden;
        }
        .handover-header {
            background: linear-gradient(90deg, #0D6EFD, #0dcaf0);
            color: white;
        }
        .timestamp {
            font-size: 13px;
            color: #6c757d;
        }
        .table thead {
            background: #1f2937;
            color: white;
        }
    </style>
</head>

<body>

<div class="container py-4">
    
    <!-- Header -->
    <div class="text-center mb-4">
        <h1 class="header-title">DATA HANDOVER JAGA</h1>
        <p class="text-secondary">RSUD SANJIWANI GIANYAR - RUANG KELAS III</p>
    </div>

    <!-- Action Buttons -->
    <div class="text-center mb-4">
        <a href="handover_jaga.php" class="btn btn-primary btn-lg px-4">
            ⬅ Kembali ke Form
        </a>
    </div>

    <!-- Tampilkan pesan jika kosong -->
    <?php if (empty($handover_data)): ?>
        <div class="alert alert-warning text-center py-5 shadow">
            <h4 class="fw-bold">Belum ada data handover.</h4>
            <p>Silahkan input melalui halaman form.</p>
        </div>
    <?php else: ?>

        <!-- Looping data -->
        <?php foreach ($handover_data as $tanggal => $data): ?>
            <?php 
                $first_record = $data[0];
                $tanggal_formatted = date('d F Y', strtotime($tanggal));
            ?>

            <div class="card shadow mb-4 handover-card">
                <div class="card-header text-center p-4 handover-header">
                    <h4 class="mb-1">HANDOVER PAGI → SORE</h4>
                    <p class="mb-0">Ruang: <?= strtoupper($first_record['ruangan']); ?> — <?= $tanggal_formatted; ?></p>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered align-middle text-center mb-0">
                            <thead>
                                <tr>
                                    <th>Kamar</th>
                                    <th>Nama Pasien</th>
                                    <th>No. RM</th>
                                    <th>Kondisi Pagi</th>
                                    <th>Petugas Menyerahkan</th>
                                    <th>Petugas Menerima</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $record): ?>
                                    <?php if (!empty($record['nama_pasien']) || !empty($record['kamar_pasien'])): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($record['kamar_pasien']); ?></td>
                                            <td><?= htmlspecialchars($record['nama_pasien']); ?></td>
                                            <td><?= htmlspecialchars($record['no_rm']); ?></td>
                                            <td><?= htmlspecialchars($record['kondisi_pagi']); ?></td>
                                            <td><?= htmlspecialchars($record['petugas_menyerahkan']); ?></td>
                                            <td><?= htmlspecialchars($record['petugas_menerima']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light text-end timestamp">
                    Disimpan: <?= date('d/m/Y H:i', strtotime($first_record['created_at'])); ?>
                </div>
            </div>

        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php mysqli_close($conn); ?>
