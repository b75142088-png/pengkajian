<?php
session_start();

// Koneksi ke database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'rsud_sanjiwani';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

function h($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// Base URL untuk menghindari masalah path
$base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$current_url = $_SERVER['REQUEST_URI'];

// ROUTER: Handle URL pattern untuk detail observasi
if (strpos($current_url, '/detail_observasi.php') !== false) {
    $query_start = strpos($current_url, '?');
    if ($query_start !== false) {
        $query_string = substr($current_url, $query_start + 1);
        parse_str($query_string, $params);
        displayDetailObservasi($pdo, $params, $base_url);
        exit;
    }
}

// Ambil data observasi dari database
try {
    $sql = "SELECT DISTINCT 
            doh.no_rm_pasien as no_rm,
            doh.nama_pasien as nama,
            doh.reg_no,
            MAX(doh.created_at) as last_observation
        FROM data_on_hd doh 
        GROUP BY doh.no_rm_pasien, doh.nama_pasien, doh.reg_no 
        ORDER BY last_observation DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $observasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $observasi = [];
    $_SESSION['notif'] = "Error mengambil data: " . $e->getMessage();
}

// Proses hapus data
if (isset($_GET['hapus'])) {
    try {
        $no_rm_hapus = $_GET['hapus'];
        
        // Hapus data post HD terkait dulu
        $sql_hapus_post = "DELETE dp FROM data_post_hd dp 
                          INNER JOIN data_on_hd doh ON dp.on_hd_id = doh.id 
                          WHERE doh.no_rm_pasien = :no_rm";
        $stmt_hapus_post = $pdo->prepare($sql_hapus_post);
        $stmt_hapus_post->execute([':no_rm' => $no_rm_hapus]);
        
        // Hapus data on HD
        $sql_hapus_on = "DELETE FROM data_on_hd WHERE no_rm_pasien = :no_rm";
        $stmt_hapus_on = $pdo->prepare($sql_hapus_on);
        $stmt_hapus_on->execute([':no_rm' => $no_rm_hapus]);
        
        $_SESSION['notif'] = "Data observasi berhasil dihapus!";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        $_SESSION['notif'] = "Error menghapus data: " . $e->getMessage();
    }
}

// Function untuk display detail observasi
function displayDetailObservasi($pdo, $params, $base_url) {
    $nama_url = $params['nama'] ?? '';
    $tgl_lahir_url = $params['tgl_lahir'] ?? '';
    $no_rm_url = $params['no_rm'] ?? '';
    $reg_no_url = $params['reg_no'] ?? '';

    // Cari data observasi
    $data_on_hd = null;
    $data_post_hd = null;

    if ($no_rm_url) {
        try {
            // Cari data ON-HD terbaru
            $sql_on_hd = "SELECT * FROM data_on_hd WHERE no_rm_pasien = :no_rm ORDER BY created_at DESC LIMIT 1";
            $stmt_on_hd = $pdo->prepare($sql_on_hd);
            $stmt_on_hd->execute([':no_rm' => $no_rm_url]);
            $data_on_hd = $stmt_on_hd->fetch(PDO::FETCH_ASSOC);
            
            if ($data_on_hd) {
                // Cari data POST HD terkait
                $sql_post_hd = "SELECT * FROM data_post_hd WHERE on_hd_id = :on_hd_id ORDER BY created_at DESC LIMIT 1";
                $stmt_post_hd = $pdo->prepare($sql_post_hd);
                $stmt_post_hd->execute([':on_hd_id' => $data_on_hd['id']]);
                $data_post_hd = $stmt_post_hd->fetch(PDO::FETCH_ASSOC);
            }
            
        } catch (PDOException $e) {
            showErrorPage("Error mengambil data: " . $e->getMessage(), $base_url);
            return;
        }
    }
    
    showDetailObservasiPage($data_on_hd, $data_post_hd, $params, $base_url);
}

function showErrorPage($message, $base_url) {
    ?>
<!DOCTYPE html>
<html>

<head>
    <title>Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="alert alert-danger">
            <h4>Error</h4>
            <p><?= h($message) ?></p>
            <a href="<?= $base_url ?>/view_observasi.php" class="btn btn-primary">Kembali ke Daftar Observasi</a>
        </div>
    </div>
</body>

</html>
<?php
}

function showDetailObservasiPage($data_on_hd, $data_post_hd, $params, $base_url) {
    $nama = h($params['nama'] ?? '');
    $tgl_lahir = h($params['tgl_lahir'] ?? '');
    $no_rm = h($params['no_rm'] ?? '');
    $reg_no = h($params['reg_no'] ?? '');
    ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Observasi Program Dialisis - <?= $nama ?></title>
    <style>
    @page {
        size: A4;
        margin: 15mm;
    }

    @media print {

        button,
        .no-print {
            display: none;
        }

        body {
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .sheet {
            margin: 0;
            padding: 0;
            border: none;
            box-shadow: none;
        }
    }

    @media screen {
        body {
            background: #efefef;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .sheet {
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }
    }

    body {
        font-family: "Times New Roman", Times, serif;
        color: #000;
        margin: 0;
        padding: 0;
        font-size: 14px;
        line-height: 1.4;
    }

    .sheet {
        width: 250mm;
        min-height: 297mm;
        padding: 20mm;
        box-sizing: border-box;
    }

    .kop {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid #000;
        padding: 8px 12px;
        margin-bottom: 8px;
        font-size: 13px;
    }

    .kop .left {
        font-weight: bold;
    }

    .kop .right {
        text-align: right;
        font-size: 12px;
    }

    h2 {
        text-align: center;
        text-transform: uppercase;
        margin-bottom: 20px;
        font-size: 18px;
    }

    table {
        table-layout: fixed;
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 25px;
        font-size: 15px;
        margin: 40px;
        transform: scale(0.9);
        transform-origin: top left;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 2px;
        word-wrap: break-word;
    }

    th {
        background-color: #e9ecef;
        font-weight: bold;
    }

    .section-title {
        font-weight: bold;
        margin: 20px 0 10px 0;
        font-size: 14px;
        text-decoration: underline;
    }

    .data-display {
        min-width: 60px;
        padding: 2px 4px;
        margin: 0 2px;
    }

    .data-post-section {
        margin: 15px 0;
    }

    .cairan-section {
        margin: 15px 0;
    }

    .inline-group {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .spacer {
        margin: 10px 0;
    }

    .divider {
        border-top: 1px solid #000;
        margin: 15px 0;
    }

    .info {
        border: 1px solid #000;
        padding: 8px 10px;
        margin-bottom: 10px;
        font-size: 13px;
        display: flex;
        gap: 10px;
        justify-content: space-between;
    }

    .info .item {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .btn-back {
        background-color: #27ff59;
        color: black;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        cursor: pointer;
        margin: 0 10px;
        font-size: 16px;
        text-decoration: none;
        display: inline-block;
    }

    .btn-print {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        cursor: pointer;
        margin: 0 10px;
        font-size: 16px;
        text-decoration: none;
        display: inline-block;
    }

    .action-buttons {
        text-align: center;
        margin: 30px 0;
    }

    .grid-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }

    .line {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
    }

    .line label {
        width: 120px;
        display: inline-block;
        margin-left: 40px;
    }

    .checkbox-line {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .checkbox-item {
        display: inline-block;
        margin-right: 20px;
    }

    hr {
        margin: 10px 0;
        width: 200px;
        border: 0;
        border-top: 1px solid #000;
    }

    .no-data {
        text-align: center;
        color: #666;
        font-style: italic;
        padding: 20px;
        border: 1px dashed #ccc;
        margin: 10px 0;
    }

    .data {
        padding: 0px 10px 0px 10px;
        border-bottom: 1px solid #000;
    }
    </style>
</head>

<body>
    <div class="sheet">
        <div class="kop">
            <div class="left">BLUD RSUD SANJIWANI GIANYAR</div>
            <div class="right">RM. 01.06.B/ 2015</div>
        </div>

        <!-- Info Pasien -->
        <div class="info">
            <div>Nama: <?= $nama ?></div>
            <div>Tgl. Lahir: <?= $tgl_lahir ?></div>
            <div>No. RM: <?= $no_rm ?></div>
            <div>Reg. No: <?= $reg_no ?></div>
        </div>

        <h2>Hasil Observasi Program Dialisis</h2>

        <?php if ($data_on_hd): ?>
        <!-- DATA ON-HD -->
        <div class="section-title">DATA ON-HD</div>

        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 10%;">Jam</th>
                    <th colspan="2">PASIEN</th>
                    <th colspan="5">MESIN</th>
                    <th rowspan="2" style="width: 20%;">Masalah /<br>Tindakan</th>
                    <th rowspan="2">Petugas</th>
                </tr>
                <tr>
                    <th style="width: 5%;">TD</th>
                    <th style="width: 5%;">Nadi</th>
                    <th style="width: 5%;">QB</th>
                    <th style="width: 5%;">Vena<br>Presure</th>
                    <th style="width: 5%;">UF<br>Goal</th>
                    <th style="width: 5%;">UF<br>Reuse</th>
                    <th style="width: 5%;">RF<br>Removed</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <?php if (!empty($data_on_hd["jam$i"]) || !empty($data_on_hd["td$i"]) || !empty($data_on_hd["nadi$i"])): ?>
                <tr>
                    <td style="text-align: center;"><?= h($data_on_hd["jam$i"] ?? '') ?></td>
                    <td style="text-align: center;"><?= h($data_on_hd["td$i"] ?? '') ?></td>
                    <td style="text-align: center;"><?= h($data_on_hd["nadi$i"] ?? '') ?></td>
                    <td style="text-align: center;"><?= h($data_on_hd["qb$i"] ?? '') ?></td>
                    <td style="text-align: center;"><?= h($data_on_hd["vena_presure$i"] ?? '') ?></td>
                    <td style="text-align: center;"><?= h($data_on_hd["uf_goal$i"] ?? '') ?></td>
                    <td style="text-align: center;"><?= h($data_on_hd["uf_reuse$i"] ?? '') ?></td>
                    <td style="text-align: center;"><?= h($data_on_hd["rf_removed$i"] ?? '') ?></td>
                    <td style="text-align: center;"><?= h($data_on_hd["masalah$i"] ?? '') ?></td>
                    <td style="text-align: center;"><?= h($data_on_hd["petugas$i"] ?? '') ?></td>
                </tr>
                <?php endif; ?>
                <?php endfor; ?>
            </tbody>
            <tbody>
                <tr>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td>
                        <div style="text-align: left; margin-left: 10px;">
                            <div>TBT (M) : <?= h($data_on_hd['treated_blood_time'] ?? '') ?></div>
                            <div>TBV (L) : <?= h($data_on_hd['treated_blood_volume'] ?? '') ?></div>
                            <div>Dialisate (L) : <?= h($data_on_hd['dialisate'] ?? '') ?></div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div>
            <p>Note : TBT (Treated Blood Time)</p>
            <p style="margin-left: 5%;"> TBV (Treated Blood Volume)</p>
        </div>

        <div class="divider"></div>

        <!-- DATA POST HD -->
        <?php if ($data_post_hd): ?>
        <div class="section-title">DATA POST HD</div>

        <div class="data-post-section">
            <div class="form-line" style="margin-bottom: 5px;">
                <label>Keluhan : <strong class="data"><?= h($data_post_hd['keluhan'] ?? '') ?></strong></label>
            </div>

            <div class="form-line">
                <div class="inline-group">
                    <label>Keadaan umum : <strong class="data"><?= h($data_post_hd['keadaan_umum'] ?? '') ?></strong></label>
                    <label>Tekanan darah : <strong class="data"><?= h($data_post_hd['tekanan_darah'] ?? '') ?></strong> mmHg</label>
                    <label>Nadi : <strong class="data"><?= h($data_post_hd['nadi_post'] ?? '') ?></strong> X/mmt</label>
                </div>
            </div>

            <div class="form-line">
                <div class="inline-group">
                    <label>Respirasi : <strong class="data"><?= h($data_post_hd['respirasi'] ?? '') ?></strong> X/mmt</label>
                    <label>BB-Post : <strong class="data"><?= h($data_post_hd['bb_post'] ?? '') ?></strong> kg</label>
                </div>
            </div>

            <div class="form-line">
                <div class="inline-group">
                    <label>Lama HD : <strong class="data"><?= h($data_post_hd['lama_hd'] ?? '') ?></strong> jam</label>
                    <label>UF removed : <strong class="data"><?= h($data_post_hd['uf_removed'] ?? '') ?></strong></label>
                </div>
            </div>
        </div>

        <div class="spacer"></div>

        <h3>Cairan yang masuk selama HD</h3>

        <div class="cairan-section">
            <div class="grid-container">
                <!-- Kolom Kiri -->
                <div class="left-column">
                    <div class="line">
                        <label>Sisa priming :</label>
                        <span class="data-display"><?= h($data_post_hd['sisa_priming'] ?? '0') ?></span> mL
                    </div>

                    <div class="line">
                        <label>Transfusi :</label>
                        <span class="data-display"><?= h($data_post_hd['transfusi'] ?? '0') ?></span> mL
                    </div>

                    <div class="line">
                        <label>Wash out :</label>
                        <span class="data-display"><?= h($data_post_hd['wash_out'] ?? '0') ?></span> mL
                    </div>

                    <div class="line">
                        <label>Minum :</label>
                        <span class="data-display"><?= h($data_post_hd['minum'] ?? '0') ?></span> mL
                    </div>

                    <hr style="width: 60%; margin-left: 35px;">

                    <div class="line total">
                        <label>Jumlah :</label>
                        <span class="data-display"><?= h($data_post_hd['jumlah'] ?? '0') ?></span> mL
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="right-column">
                    <div class="checkbox-line">
                        <label>Dializer :</label>
                        <span class="checkbox-item"><?= $data_post_hd['reuse'] ? '☑ Rause' : '☒ Rause' ?></span>
                        <span
                            class="checkbox-item"><?= $data_post_hd['tidak_reuse'] ? '☑ Tidak reuse' : '☒ Tidak reuse' ?></span>
                    </div>

                    <div class="checkbox-line" style="margin-left: 40%;">
                        <span
                            class="checkbox-item"><?= $data_post_hd['beku_bocor1'] ? '☑ beku/bocor' : '☒ beku/bocor' ?></span>
                    </div>

                    <div class="checkbox-line" style="margin-left: 40%;">
                        <span
                            class="checkbox-item"><?= $data_post_hd['single_use'] ? '☑ single use' : '☒ single use' ?></span>
                    </div>

                    <div class="checkbox-line" style="margin-left: 40%;">
                        <span
                            class="checkbox-item"><?= $data_post_hd['pakai_8x'] ? '☑ >8 X pakai' : '☒ >8 X pakai' ?></span>
                    </div>

                    <div class="checkbox-line" style="margin-left: 40%;">
                        <span
                            class="checkbox-item"><?= $data_post_hd['penyakit_menular'] ? '☑ Penyakit menular' : '☒ Penyakit menular' ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="no-data">
            Data POST HD tidak tersedia untuk observasi ini.
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="no-data">
            Data observasi tidak ditemukan untuk pasien ini.
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="action-buttons no-print">
            <a href="<?= $base_url ?>/view_observasi.php" class="btn-back">KEMBALI KE DAFTAR</a>
        </div>
    </div>
</body>

</html>
<?php
}
?>

<!-- TAMPILAN TABEL DATA OBSERVASI -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Observasi Dialisis</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        padding-top: 20px;
    }

    .header {
        background-color: #0d6efd;
        color: white;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .table-container {
        background-color: white;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }

    .btn-action {
        margin-right: 5px;
    }

    .table th {
        background-color: #e9ecef;
    }

    .last-observation {
        font-size: 12px;
        color: #6c757d;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="header text-center">
            <h2>Data Observasi Program Dialisis</h2>
            <p>RSUD Sanjiwani Gianyar</p>
        </div>

        <div class="d-flex justify-content-between mb-3">
            <a href="form_observasi.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Observasi Baru
            </a>
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="table-container">
            <?php if(isset($_SESSION['notif'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?= $_SESSION['notif'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['notif']); ?>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nomor RM</th>
                            <th scope="col">Nama Pasien</th>
                            <th scope="col">Reg. No</th>
                            <th scope="col" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($observasi) > 0): ?>
                        <?php $no = 1; ?>
                        <?php foreach($observasi as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= h($row['no_rm']) ?></td>
                            <td><?= h($row['nama']) ?></td>
                            <td><?= h($row['reg_no']) ?></td>
                            <td class="text-center">
                                <button onclick="viewObservasi(
                                            '<?= addslashes($row['nama']) ?>',
                                            '<?= $row['last_observation'] ? date('Y-m-d', strtotime($row['last_observation'])) : '' ?>',
                                            '<?= $row['no_rm'] ?>',
                                            '<?= addslashes($row['reg_no']) ?>'
                                        )" class="btn btn-info btn-sm btn-action" title="Tampil Observasi">
                                    <i class="bi bi-eye"></i> Tampil
                                </button>

                                <a href="?hapus=<?= $row['no_rm'] ?>" class="btn btn-danger btn-sm btn-action"
                                    title="Hapus Observasi"
                                    onclick="return confirm('Yakin ingin menghapus semua data observasi untuk pasien <?= h($row['nama']) ?>?')">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data observasi.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function viewObservasi(nama, tgl_lahir, no_rm, reg_no) {
        const encodedNama = encodeURIComponent(nama);
        const encodedTglLahir = encodeURIComponent(tgl_lahir);
        const encodedNoRm = encodeURIComponent(no_rm);
        const encodedRegNo = encodeURIComponent(reg_no);

        const url =
            `view_observasi.php/detail_observasi.php?nama=${encodedNama}&tgl_lahir=${encodedTglLahir}&no_rm=${encodedNoRm}&reg_no=${encodedRegNo}`;

        window.location.href = url;
    }
    </script>
</body>

</html>