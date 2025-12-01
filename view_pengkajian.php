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

// ROUTER: Handle URL pattern untuk detail pengkajian
if (strpos($current_url, '/detail_pengkajian.php') !== false) {
    $query_start = strpos($current_url, '?');
    if ($query_start !== false) {
        $query_string = substr($current_url, $query_start + 1);
        parse_str($query_string, $params);
        displayDetailPengkajian($pdo, $params, $base_url);
        exit;
    }
}

// Ambil data pengkajian dari database
try {
    $sql = "SELECT DISTINCT 
            ph.no_rm,
            ph.nama_pasien as nama,
            ph.reg_no,
            MAX(ph.created_at) as last_assessment
        FROM pengkajian_hemodialisa ph 
        GROUP BY ph.no_rm, ph.nama_pasien, ph.reg_no 
        ORDER BY last_assessment DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pengkajian = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pengkajian = [];
    $_SESSION['notif'] = "Error mengambil data: " . $e->getMessage();
}

// Proses hapus data
if (isset($_GET['hapus'])) {
    try {
        $no_rm_hapus = $_GET['hapus'];
        
        // Hapus data pengkajian
        $sql_hapus = "DELETE FROM pengkajian_hemodialisa WHERE no_rm = :no_rm";
        $stmt_hapus = $pdo->prepare($sql_hapus);
        $stmt_hapus->execute([':no_rm' => $no_rm_hapus]);
        
        $_SESSION['notif'] = "Data pengkajian berhasil dihapus!";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        $_SESSION['notif'] = "Error menghapus data: " . $e->getMessage();
    }
}

function displayDetailPengkajian($pdo, $params, $base_url) {
    $nama_url = $params['nama'] ?? '';
    $tgl_lahir_url = $params['tgl_lahir'] ?? '';
    $no_rm_url = $params['no_rm'] ?? '';
    $reg_no_url = $params['reg_no'] ?? '';
    $id = $params['id'] ?? 0;

    // Cari data pengkajian
    $data_pengkajian = null;

    if ($id) {
        try {
            // Cari data pengkajian berdasarkan ID
            $sql = "SELECT * FROM pengkajian_hemodialisa WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $data_pengkajian = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            showErrorPage("Error mengambil data: " . $e->getMessage(), $base_url);
            return;
        }
    } elseif ($no_rm_url) {
        try {
            $sql = "SELECT * FROM pengkajian_hemodialisa WHERE no_rm = :no_rm ORDER BY created_at DESC LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':no_rm' => $no_rm_url]);
            $data_pengkajian = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            showErrorPage("Error mengambil data: " . $e->getMessage(), $base_url);
            return;
        }
    }
    
    showDetailPengkajianPage($data_pengkajian, $params, $base_url);
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
                <a href="<?= $base_url ?>/view_pengkajian.php" class="btn btn-primary">Kembali ke Daftar Pengkajian</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// Fungsi untuk memeriksa checkbox
function isChecked($value) {
    return $value ? 'checked' : '';
}

// Fungsi untuk memeriksa keadaan umum
function isKeadaanUmumChecked($keadaan_umum, $value) {
    if (empty($keadaan_umum)) return '';
    $items = explode(',', $keadaan_umum);
    return in_array($value, $items) ? 'checked' : '';
}

function showDetailPengkajianPage($data_pengkajian, $params, $base_url) {
    ?>
    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="utf-8">
        <title>Detail Pengkajian Hemodialisa</title>
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <style>
            @page {
                size: A4;
                margin: 15mm;
            }

            body {
                margin: 0;
                padding: 0;
                background: #efefef;
                font-family: "Times New Roman", Times, serif;
            }

            .sheet {
                width: 210mm;
                min-height: 297mm;
                margin: 12mm auto;
                background: #fff;
                padding: 18mm;
                box-sizing: border-box;
                border: 1px solid #bbb;
                position: relative;
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

            .title {
                text-align: center;
                border: 1px solid #000;
                padding: 10px 6px;
                font-weight: bold;
                margin-bottom: 10px;
                font-size: 15px;
            }

            .info {
                border: 1px solid #000;
                padding: 8px 10px;
                margin-bottom: 10px;
                font-size: 13px;
                display: flex;
                gap: 10px;
                justify-content: flex-end;
            }

            .info .item {
                min-width: 120px;
                white-space: nowrap;
            }

            .section-title {
                background: #e8e8e8;
                border: 1px solid #000;
                border-bottom: none;
                padding: 4px 6px;
                font-weight: bold;
                margin-top: 8px;
                font-size: 13px;
            }

            .section-body {
                border: 1px solid #000;
                border-top: 1px solid #000;
                padding: 10px;
                font-size: 12px;
                line-height: 2.5;
            }

            input, textarea {
                background: transparent;
                border: none;
                border-bottom: 1px solid #000;
                padding: 2px 4px;
                text-align: left;
                margin: 0 5px;
            }

            input[readonly], textarea[readonly] {
                background: #f5f5f5;
                border-bottom: 1px solid #ccc;
                color: #333;
            }

            textarea {
                width: 100%;
                resize: vertical;
                min-height: 80px;
            }

            .two-col {
                display: flex;
                gap: 8px;
                margin-top: 8px;
            }

            .col {
                flex: 1;
                border: 1px solid #000;
                font-size: 13px;
            }

            .col .inner {
                min-height: 80px;
                padding: 8px;
            }

            .footer {
                display: flex;
                justify-content: space-between;
                margin-top: 30px;
                font-size: 13px;
            }

            .no-print {
                margin-top: 12px;
            }

            .lung-image-container {
                position: absolute;
                top: 75mm;
                right: 25mm;
                width: 40mm;
                height: 40mm;
                z-index: 100;
                cursor: pointer;
                transition: transform 0.3s ease;
                border: 1px solid #ddd;
                border-radius: 5px;
                background: #f9f9f9;
                padding: 3px;
            }

            .lung-image {
                width: 100%;
                height: 100%;
                object-fit: contain;
            }

            .lung-image-container:hover {
                transform: scale(1.05);
                border-color: #0077ff;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }

            .lung-image-container.active {
                transform: scale(1.5);
                z-index: 100;
                box-shadow: 0 0 25px rgba(0,0,0,0.6);
                background: white;
                padding: 10px;
                border-radius: 8px;
                border: 2px solid #0077ff;
            }

            .lung-label {
                position: absolute;
                bottom: -20px;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 10px;
                color: #666;
                font-style: italic;
            }

            /* Gaya untuk tanda tangan */
            .signature-container {
                margin-top: 20px;
                text-align: center;
            }

            .signature-display {
                border: 1px solid #ccc;
                border-radius: 5px;
                background: white;
                margin: 10px auto;
                min-height: 80px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #666;
            }

            .signature-display img {
                max-width: 100%;
                max-height: 80px;
            }

            .message {
                padding: 10px;
                margin: 10px 0;
                border-radius: 5px;
                text-align: center;
            }

            .success {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }

            .error {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }

            .submit-btn {
                text-align: center;
                margin: 20px 0;
            }

            .submit-btn button {
                padding: 12px 24px;
                font-size: 16px;
                background: #0077ff;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                margin: 0 10px;
            }

            .submit-btn button:hover {
                background: #0055cc;
            }

            .submit-btn .print-btn {
                background: #28a745;
            }

            .submit-btn .print-btn:hover {
                background: #218838;
            }

            .action-buttons {
                text-align: center;
                margin: 20px 0;
            }

            .btn {
                padding: 10px 20px;
                margin: 0 10px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
                text-decoration: none;
                display: inline-block;
            }

            .btn-primary {
                background: #0077ff;
                color: white;
            }

            .btn-secondary {
                background: #6c757d;
                color: white;
            }

            .btn-success {
                background: #28a745;
                color: white;
            }

            @media print {
                body {
                    background: #fff;
                }

                .no-print {
                    display: none;
                }

                .sheet {
                    border: none;
                    box-shadow: none;
                    margin: 0;
                    padding: 10mm;
                }

                .lung-image-container {
                    display: none;
                }

                .message {
                    display: none;
                }

                .action-buttons {
                    display: none;
                }
            }

            /* Gaya untuk checkbox yang disabled */
            input[type="checkbox"]:disabled {
                opacity: 1;
                cursor: not-allowed;
            }

            input[type="checkbox"]:disabled:checked::before {
                content: "✓";
                display: inline-block;
                width: 14px;
                height: 14px;
                background: #0077ff;
                color: white;
                text-align: center;
                line-height: 14px;
                font-size: 12px;
                border-radius: 2px;
            }

            input[type="checkbox"]:disabled:not(:checked)::before {
                content: "✗";
                display: inline-block;
                width: 14px;
                height: 14px;
                background: #f8f9fa;
                color: #6c757d;
                text-align: center;
                line-height: 14px;
                font-size: 12px;
                border-radius: 2px;
                border: 1px solid #ccc;
            }
        </style>
    </head>

    <body>

        <div class="sheet">
            <!-- Kontainer untuk gambar paru-paru -->
            <div class="lung-image-container" id="lungImageContainer" style="width: 15%; margin-top: 25%;">
                <img src="<?= $base_url ?>/paru.png" alt="Ilustrasi Paru-Paru" class="lung-image">
                <div class="lung-label">Klik untuk memperbesar</div>
                <div style="margin-top: 10%; margin-left: 15%; font-size: 15px;"><p>Gambar Paru</p></div>
            </div>

            <div class="kop">
                <div class="left">BLUD RSUD SANJIWANI GIANYAR</div>
                <div class="right">RM. 01.06.A/2015</div>
            </div>
            <div class="title">PENGKAJIAN MEDIS & KEPERAWATAN HEMODIALISA</div>
            <div class="info">
                <div class="item">Nama: <input type="text" value="<?= h($data_pengkajian['nama_pasien'] ?? '') ?>" readonly style="width: 80px;"></div>
                <div class="item">Tgl. Lahir: <input type="text" value="<?= h($data_pengkajian['tgl_lahir'] ?? '') ?>" readonly style="width: 80px;"></div>
                <div class="item">No. RM: <input type="text" value="<?= h($data_pengkajian['no_rm'] ?? '') ?>" readonly style="width: 50px;"></div>
                <div class="item">Reg. No: <input type="text" value="<?= h($data_pengkajian['reg_no'] ?? '') ?>" readonly style="width: 100px;"></div>
            </div>

            <?php if ($data_pengkajian && isset($data_pengkajian['id'])): ?>
                <!-- Data lengkap tersedia -->
                <div class="section-body" style="padding: 0%;">
                    <p style="font-size:13px; margin:6px 0;">
                        <strong style="margin-left:10px">Penilaian Nyeri</strong>
                        &nbsp;&nbsp; Lokasi: <input type="text" value="<?= h($data_pengkajian['nyeri_lokasi'] ?? '') ?>" readonly style="width: 60px;">
                        &nbsp;&nbsp; Intensitas (0–10): <strong><input type="text" value="<?= h($data_pengkajian['nyeri_intensitas'] ?? '') ?>" readonly style="width: 30px;"></strong>
                    </p>
                </div>

                <div class="section-title">TANDA-TANDA VITAL</div>
                <div class="section-body">
                    <div style="margin-bottom:6px;">
                        <strong style="margin-bottom: 10px;">Keadaan Umum: </strong>
                        <label><input type="checkbox" disabled <?= isKeadaanUmumChecked($data_pengkajian['keadaan_umum'] ?? '', 'Baik') ?>> Baik</label>
                        <label><input type="checkbox" disabled <?= isKeadaanUmumChecked($data_pengkajian['keadaan_umum'] ?? '', 'Sedang') ?>> Sedang</label>
                        <label><input type="checkbox" disabled <?= isKeadaanUmumChecked($data_pengkajian['keadaan_umum'] ?? '', 'Lemah') ?>> Lemah</label>
                        <label><input type="checkbox" disabled <?= isKeadaanUmumChecked($data_pengkajian['keadaan_umum'] ?? '', 'Buruk') ?>> Buruk</label>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; GCS: <strong>E: <input type="text" value="<?= h($data_pengkajian['gcs_e'] ?? '') ?>" readonly style="width: 30px;"> V: <input type="text" value="<?= h($data_pengkajian['gcs_v'] ?? '') ?>" readonly style="width: 30px"> M: <input type="text" value="<?= h($data_pengkajian['gcs_m'] ?? '') ?>" readonly style="width: 30px"></strong>
                    </div>
                    <div style="margin-top:6px;">
                        Tensi: <strong><input type="text" value="<?= h($data_pengkajian['tensi'] ?? '') ?>" readonly style="width: 40px;"></strong>
                        &nbsp;&nbsp;&nbsp;
                        Nadi: <strong><input type="text" value="<?= h($data_pengkajian['nadi'] ?? '') ?>" readonly style="width: 40px"></strong>
                        &nbsp;&nbsp;&nbsp;
                        Respirasi: <strong><input type="text" value="<?= h($data_pengkajian['respirasi'] ?? '') ?>" readonly style="width: 40px;"></strong>
                        &nbsp;&nbsp;&nbsp;
                        Suhu: <strong><input type="text" value="<?= h($data_pengkajian['suhu'] ?? '') ?>" readonly style="width: 40px">°C</strong>
                    </div>
                </div>

                <div class="section-title">PEMERIKSAAN FISIK</div>
                <div class="section-body">
                    <div style="margin-bottom:6px;"><strong>Mata:</strong>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['mata_anemi'] ?? 0) ?>> Anemi</label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['mata_ikterus'] ?? 0) ?>> Ikterus</label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['mata_reflex_pupil'] ?? 0) ?>> Reflex Pupil</label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['mata_oedema_palpebra'] ?? 0) ?>> Oedema Palpebra</label>
                    </div>

                    <div style="margin-bottom:6px;"><strong>THT:</strong>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['tht_tonsil'] ?? 0) ?>> Tonsil <input type="text" value="<?= h($data_pengkajian['tht_tonsil_keterangan'] ?? '') ?>" readonly style="width: 20px"></label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['tht_pharing'] ?? 0) ?>> Pharing <input type="text" value="<?= h($data_pengkajian['tht_pharing_keterangan'] ?? '') ?>" readonly style="width: 40px"></label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['tht_lidah'] ?? 0) ?>> Lidah <input type="text" value="<?= h($data_pengkajian['tht_lidah_keterangan'] ?? '') ?>" readonly style="width: 50px"></label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['tht_bibir'] ?? 0) ?>> Bibir <input type="text" value="<?= h($data_pengkajian['tht_bibir_keterangan'] ?? '') ?>" readonly style="width: 80px"></label>
                    </div>

                    <div style="margin-bottom:6px;"><strong>Leher:</strong>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['leher_jvp'] ?? 0) ?>> JVP <input type="text" value="<?= h($data_pengkajian['leher_jvp_keterangan'] ?? '') ?>" readonly style="width: 20px"></label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['leher_pembesar_kelenjar'] ?? 0) ?>> Pembesar Kelenjar <input type="text" value="<?= h($data_pengkajian['leher_pembesar_kelenjar_keterangan'] ?? '') ?>" readonly style="width: 20px"></label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['leher_kaku_kuduk'] ?? 0) ?>> Kaku Kuduk+/-</label>
                    </div>

                    <div style="margin-bottom:6px;"><strong>Thoraks:</strong>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['thoraks_simetris'] ?? 0) ?>> Simetris/ Asimetris <input type="text" value="<?= h($data_pengkajian['thoraks_simetris_keterangan'] ?? '') ?>" readonly></label>
                    </div>

                    <div style="margin-bottom:6px;"> &nbsp;&nbsp;&nbsp; -Cardiovaskuler:
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['cardiovaskuler_s1s2'] ?? 0) ?>> S1,S2 <input type="text" value="<?= h($data_pengkajian['cardiovaskuler_s1s2_keterangan'] ?? '') ?>" readonly style="width: 50px;"> reguler/Ireguler</label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['cardiovaskuler_murmur'] ?? 0) ?>> Murmur <input type="text" value="<?= h($data_pengkajian['cardiovaskuler_murmur_keterangan'] ?? '') ?>" readonly style="width: 80px;"></label><br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['cardiovaskuler_lain_lain'] ?? 0) ?>> Lain-lain <input type="text" value="<?= h($data_pengkajian['cardiovaskuler_lain_lain_keterangan'] ?? '') ?>" readonly></label>
                    </div>

                    <div style="margin-bottom:6px;"> &nbsp;&nbsp;&nbsp; -Pulmo:
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['pulmo_suara_nafas'] ?? 0) ?>> Suara Nafas <input type="text" value="<?= h($data_pengkajian['pulmo_suara_nafas_keterangan'] ?? '') ?>" readonly style="width: 50px;"></label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['pulmo_ronchi'] ?? 0) ?>> Ronchi <input type="text" value="<?= h($data_pengkajian['pulmo_ronchi_keterangan'] ?? '') ?>" readonly style="width: 50px;"></label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['pulmo_wheezing'] ?? 0) ?>> Wheezing <input type="text" value="<?= h($data_pengkajian['pulmo_wheezing_keterangan'] ?? '') ?>" readonly style="width: 50px;"></label> <br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><input type="checkbox" disabled <?= isChecked($data_pengkajian['pulmo_lain_lain'] ?? 0) ?>> Lain-lain <input type="text" value="<?= h($data_pengkajian['pulmo_lain_lain_keterangan'] ?? '') ?>" readonly></label>
                    </div>

                    <div style="margin-top:6px;"><strong>Abdomen:</strong>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['abdomen_distensi'] ?? 0) ?>> Distensi:+/-</label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['abdomen_meteorismus'] ?? 0) ?>> Meteorismus:+/-</label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['abdomen_peristaltic'] ?? 0) ?>> Peristaltic:</label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['abdomen_normal'] ?? 0) ?>> Normal</label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['abdomen_meningkat'] ?? 0) ?>> Meningkat</label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['abdomen_menurun'] ?? 0) ?>> Menurun</label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['abdomen_ascites'] ?? 0) ?>> Ascites:+/-</label> <br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><input type="checkbox" disabled <?= isChecked($data_pengkajian['abdomen_nyeri_tekan'] ?? 0) ?>> Nyeri tekan:+/- Lokasi <input type="text" value="<?= h($data_pengkajian['abdomen_nyeri_tekan_lokasi'] ?? '') ?>" readonly></label> <br>
                        <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - Hepar: <input type="text" value="<?= h($data_pengkajian['abdomen_hepar'] ?? '') ?>" readonly></label> <br>
                        <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Lien: &nbsp;&nbsp;&nbsp;<input type="text" value="<?= h($data_pengkajian['abdomen_lien'] ?? '') ?>" readonly></label>
                    </div>

                    <div><strong>Extremitas:</strong>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['extremitas_hangat_dingin'] ?? 0) ?>> Hangat/Dingin</label>
                        <label><input type="checkbox" disabled <?= isChecked($data_pengkajian['extremitas_edama'] ?? 0) ?>> Edama. pada <input type="text" value="<?= h($data_pengkajian['extremitas_edama_lokasi'] ?? '') ?>" readonly></label>
                    </div>
                </div>

                <div class="section-title">HASIL PEMERIKSAAN PENUNJANG</div>
                <div class="section-body">
                    <label>1. Laboratorium: <input type="text" value="<?= h($data_pengkajian['lab_laboratorium'] ?? '') ?>" readonly></label> <br>
                    <label>2. EKG: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" value="<?= h($data_pengkajian['lab_ekg'] ?? '') ?>" readonly></label> <br>
                    <label>3. X-Ray &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" value="<?= h($data_pengkajian['lab_xray'] ?? '') ?>" readonly></label>
                </div>

                <div style="display:flex; gap:8px; margin-top:8px;">
                    <div class="col">
                        <div class="section-title" style="margin:0;">DIAGNOSA KERJA / DIAGNOSA BANDING</div>
                        <div class="inner" style="padding:8px; min-height:100px;">
                            <textarea readonly placeholder="Tidak ada diagnosa kerja..." style="width: 95%;"><?= h($data_pengkajian['diagnosa_kerja'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="col">
                        <div class="section-title" style="margin:0;">TERAPI / TINDAKAN</div>
                        <div class="inner" style="padding:8px; min-height:100px;">
                            <textarea readonly placeholder="Tidak ada terapi/tindakan..." style="width: 95%;"><?= h($data_pengkajian['terapi_tindakan'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="footer">
                    <div class="signature-container">
                        <div>Mengetahui,</div>
                        <div><strong>Perawat Pelaksana</strong></div>
                        
                        <div style="margin-top: 90px;">( <input type="text" value="<?= h($data_pengkajian['perawat_nama'] ?? '') ?>" readonly style="width: 150px; text-align: center;"> )</div>
                    </div>

                    <div class="signature-container" style="text-align:right;">
                        <div>Gianyar, <input type="text" value="<?= h($data_pengkajian['tgl_kunjungan'] ?? '') ?>" readonly style="width: 40%;"></div>
                        <div><strong>Dokter Penanggung Jawab</strong></div>
                        
                        <div style="margin-top: 90px;">( <input type="text" value="<?= h($data_pengkajian['dokter_nama'] ?? '') ?>" readonly style="width: 150px; text-align: center;"> )</div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Data tidak tersedia -->
                <div class="section-body">
                    <div class="no-data" style="text-align: center; color: #666; font-style: italic; padding: 20px;">
                        Data pengkajian tidak ditemukan untuk pasien ini.
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tombol Aksi -->
            <div class="action-buttons no-print">
                <a href="<?= $base_url ?>/view_pengkajian.php" class="btn btn-secondary">Kembali ke Daftar</a>
            </div>
        </div>

        <script>
            // Animasi untuk gambar paru-paru
            document.getElementById('lungImageContainer').addEventListener('click', function() {
                this.classList.toggle('active');
            });

            document.addEventListener('click', function(event) {
                const lungContainer = document.getElementById('lungImageContainer');
                if (!lungContainer.contains(event.target) && lungContainer.classList.contains('active')) {
                    lungContainer.classList.remove('active');
                }
            });

            document.addEventListener('keydown', function(event) {
                const lungContainer = document.getElementById('lungImageContainer');
                if (event.key === 'Escape' && lungContainer.classList.contains('active')) {
                    lungContainer.classList.remove('active');
                }
            });

            // Fungsi untuk print
            function printForm() {
                window.print();
            }
        </script>

    </body>

    </html>
    <?php
}
?>

<!-- TAMPILAN TABEL DATA PENGKAJIAN -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengkajian Hemodialisa</title>
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
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .btn-action {
            margin-right: 5px;
        }
        .table th {
            background-color: #e9ecef;
        }
        .last-assessment {
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header text-center">
            <h2>Data Pengkajian Hemodialisa</h2>
            <p>RSUD Sanjiwani Gianyar</p>
        </div>
        
        <div class="d-flex justify-content-between mb-3">
            <a href="form_pengkajian.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Pengkajian Baru
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
                        <?php if(count($pengkajian) > 0): ?>
                            <?php $no = 1; ?>
                            <?php foreach($pengkajian as $row): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= h($row['no_rm']) ?></td>
                                    <td><?= h($row['nama']) ?></td>
                                    <td><?= h($row['reg_no']) ?></td>
                                    <td class="text-center">
                                        <button onclick="viewPengkajian(
                                            '<?= addslashes($row['nama']) ?>',
                                            '<?= $row['last_assessment'] ? date('Y-m-d', strtotime($row['last_assessment'])) : '' ?>',
                                            '<?= $row['no_rm'] ?>',
                                            '<?= addslashes($row['reg_no']) ?>'
                                        )" class="btn btn-info btn-sm btn-action" title="Tampil Pengkajian">
                                            <i class="bi bi-eye"></i> Tampil
                                        </button>
                                        
                                        <a href="?hapus=<?= $row['no_rm'] ?>" class="btn btn-danger btn-sm btn-action" title="Hapus Pengkajian" 
                                           onclick="return confirm('Yakin ingin menghapus semua data pengkajian untuk pasien <?= h($row['nama']) ?>?')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data pengkajian.</td>
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
    function viewPengkajian(nama, tgl_lahir, no_rm, reg_no) {
        const encodedNama = encodeURIComponent(nama);
        const encodedTglLahir = encodeURIComponent(tgl_lahir);
        const encodedNoRm = encodeURIComponent(no_rm);
        const encodedRegNo = encodeURIComponent(reg_no);
        
        const url = `view_pengkajian.php/detail_pengkajian.php?nama=${encodedNama}&tgl_lahir=${encodedTglLahir}&no_rm=${encodedNoRm}&reg_no=${encodedRegNo}`;
        
        window.location.href = url;
    }
    </script>
</body>
</html>