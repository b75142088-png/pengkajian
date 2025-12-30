<?php
// --- 1. KONEKSI DATABASE ---
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_rsud_sedasi";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// --- 2. INISIALISASI VARIABEL ---
$no_rm = $nama = $tgl_lahir = $jk = $regno = "";
$tgl_input = date('Y-m-d');
$jam_input = date('');
$ruangan = $tindakan = $diagnosa = "";
$bb = $tb = $td = $nadi = $nafas = $spo2 = "";
$iv_line_tempat = $iv_line_cairan = $lab = $rencana_mulai = $rencana_selesai = "";
$asa_score = $gcs_e = $gcs_v = $gcs_m = "";
$skala_nyeri = 0;

$jalan_nafas_arr = [];
$mallampati_check = "";
$leher_arr = [];
$riwayat_alergi = "";
$puasa_arr = [];

$mon_obat_nama = ["", "", ""];
$mon_obat_grid = [];
$mon_waktu = $mon_o2 = $mon_spo2 = $mon_nyeri = $mon_kesadaran = array_fill(0, 16, "");
$chart_grid_data = [];

$status_simpan = "";
$pesan_simpan = "";

// --- 3. LOGIKA GET (LOAD DATA) ---
if (isset($_GET['regno']) && !empty($_GET['regno'])) {
    $regno_cari = $conn->real_escape_string($_GET['regno']);
    $sql_cek = "SELECT * FROM status_sedasi WHERE regno = '$regno_cari'";
    $result = $conn->query($sql_cek);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $no_rm = $row['no_rm'];
        $nama = $row['nama_pasien'];
        $tgl_lahir = $row['tgl_lahir'];
        $jk = $row['jenis_kelamin'];
        $regno = $row['regno'];
        $waktu_split = explode(" ", $row['waktu_input']);
        $tgl_input = $waktu_split[0] ?? date('Y-m-d');
        $jam_input = $waktu_split[1] ?? date('H:i');
        $ruangan = $row['ruangan'];
        $tindakan = $row['tindakan'];
        $diagnosa = $row['diagnosa'];
        $bb = $row['bb'];
        $tb = $row['tb'];
        $td = $row['td'];
        $nadi = $row['nadi'];
        $nafas = $row['nafas'];
        $spo2 = $row['spo2'];
        $jalan_nafas_arr = json_decode($row['jalan_nafas'], true) ?? [];
        $mall_temp = json_decode($row['mallampati'], true);
        $mallampati_check = is_array($mall_temp) ? ($mall_temp[0] ?? "") : $mall_temp;
        $leher_arr = json_decode($row['leher'], true) ?? [];
        $riwayat_alergi = $row['riwayat_alergi'];
        $skala_nyeri = $row['skala_nyeri'];
        $iv_line_tempat = $row['iv_line_tempat'];
        $iv_line_cairan = $row['iv_line_cairan'];
        $puasa_arr = explode(", ", $row['status_puasa']);
        $lab = $row['lab_penunjang'];
        $asa_score = $row['asa_score'];
        $gcs_e = $row['gcs_e'];
        $gcs_v = $row['gcs_v'];
        $gcs_m = $row['gcs_m'];
        $rencana_mulai = $row['rencana_mulai'];
        $rencana_selesai = $row['rencana_selesai'];
        $mon_json = json_decode($row['monitoring_data'], true);
        if ($mon_json) {
            $mon_obat_nama = $mon_json['obat_nama'] ?? ["", "", ""];
            $mon_obat_grid = $mon_json['obat_grid'] ?? [];
            $mon_waktu = $mon_json['waktu'] ?? [];
            $mon_o2 = $mon_json['o2'] ?? [];
            $mon_spo2 = $mon_json['spo2'] ?? [];
            $mon_nyeri = $mon_json['nyeri'] ?? [];
            $mon_kesadaran = $mon_json['kesadaran'] ?? [];
            $chart_grid_data = $mon_json['chart_grid'] ?? [];
        }
    } else {
        $regno = $_GET['regno'];
        $nama = $_GET['nama'] ?? '';
        $no_rm = $_GET['no_rm'] ?? '';
        $tgl_lahir_url = $_GET['tgl_lahir'] ?? '';
        if (strpos($tgl_lahir_url, '-') !== false) {
            $tgl_lahir = $tgl_lahir_url;
        }
    }
}

// --- 4. LOGIKA SIMPAN (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $no_rm = $_POST['no_rm'];
    $nama = $_POST['nama'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $jk = $_POST['jk'] ?? '';
    $regno = $_POST['regno'];
    $waktu_input = $_POST['tgl_input'] . ' ' . $_POST['jam_input'];
    $ruangan = $_POST['ruangan'];
    $tindakan = $_POST['tindakan'];
    $diagnosa = $_POST['diagnosa'];
    $bb = $_POST['bb'];
    $tb = $_POST['tb'];
    $td = $_POST['td'];
    $nadi = $_POST['nadi'];
    $nafas = $_POST['nafas'];
    $spo2 = $_POST['spo2'];
    $jalan_nafas = json_encode($_POST['jalan_nafas'] ?? []);
    $mallampati_json = json_encode($_POST['mallampati'] ?? "");
    $leher = json_encode($_POST['leher'] ?? []);
    $alergi_check = isset($_POST['alergi_check']) ? "Ya: " . $_POST['alergi_text'] : "Tidak";
    $skala_nyeri = ($_POST['skala_nyeri_manual'] !== '') ? $_POST['skala_nyeri_manual'] : ($_POST['skala_nyeri_radio'] ?? 0);
    $iv_line_tempat = $_POST['iv_line_tempat'];
    $iv_line_cairan = $_POST['iv_line_cairan'];
    $puasa = [];
    if (isset($_POST['puasa_makan'])) $puasa[] = 'Makan';
    if (isset($_POST['puasa_minum'])) $puasa[] = 'Minum';
    $status_puasa = implode(", ", $puasa);
    $lab = $_POST['lab'];
    $asa_score = $_POST['asa'] ?? '';
    $gcs_e = $_POST['gcs_e'];
    $gcs_v = $_POST['gcs_v'];
    $gcs_m = $_POST['gcs_m'];
    $rencana_mulai = $_POST['rencana_mulai'];
    $rencana_selesai = $_POST['rencana_selesai'];

    // Grid Obat
    $obat_grid_final = [];
    $raw_obat_grid = $_POST['mon_obat_grid'] ?? [];
    for ($r = 0; $r < 3; $r++) {
        $row_data = [];
        for ($c = 0; $c < 16; $c++) {
            $row_data[] = isset($raw_obat_grid[$r][$c]) ? "✓" : "";
        }
        $obat_grid_final[] = $row_data;
    }
    // Grid Chart
    $chart_data_final = [];
    $raw_chart = $_POST['chart_data'] ?? [];
    for ($r = 0; $r < 23; $r++) {
        $row_chart = [];
        for ($c = 0; $c < 16; $c++) {
            $row_chart[] = $raw_chart[$r][$c] ?? "";
        }
        $chart_data_final[] = $row_chart;
    }
    $monitoring_data = [
        'obat_nama' => $_POST['mon_obat_nama'] ?? [],
        'obat_grid' => $obat_grid_final,
        'waktu' => $_POST['mon_waktu'] ?? [],
        'o2' => $_POST['mon_o2'] ?? [],
        'spo2' => $_POST['mon_spo2'] ?? [],
        'nyeri' => $_POST['mon_nyeri'] ?? [],
        'kesadaran' => $_POST['mon_kesadaran'] ?? [],
        'chart_grid' => $chart_data_final
    ];
    $monitoring_json = json_encode($monitoring_data);

    // CEK DB UNTUK INSERT/UPDATE
    $cek_sql = "SELECT id FROM status_sedasi WHERE regno = '$regno'";
    $cek_res = $conn->query($cek_sql);

    if ($cek_res->num_rows > 0) {
        // --- UPDATE ---
        $sql = "UPDATE status_sedasi SET 
            no_rm=?, nama_pasien=?, tgl_lahir=?, jenis_kelamin=?, waktu_input=?,
            ruangan=?, tindakan=?, diagnosa=?, bb=?, tb=?, td=?, nadi=?, nafas=?, spo2=?,
            jalan_nafas=?, mallampati=?, leher=?, riwayat_alergi=?, skala_nyeri=?,
            iv_line_tempat=?, iv_line_cairan=?, status_puasa=?, lab_penunjang=?,
            asa_score=?, gcs_e=?, gcs_v=?, gcs_m=?, rencana_mulai=?, rencana_selesai=?, monitoring_data=?
            WHERE regno=?";

        $stmt = $conn->prepare($sql);

        // PERBAIKAN: JUMLAH STRING TIPE DATA SEKARANG 31 (30 data + 1 regno)
        $stmt->bind_param(
            "ssssssssddsiissssisisssiiisssss",
            $no_rm,
            $nama,
            $tgl_lahir,
            $jk,
            $waktu_input,
            $ruangan,
            $tindakan,
            $diagnosa,
            $bb,
            $tb,
            $td,
            $nadi,
            $nafas,
            $spo2,
            $jalan_nafas,
            $mallampati_json,
            $leher,
            $alergi_check,
            $skala_nyeri,
            $iv_line_tempat,
            $iv_line_cairan,
            $status_puasa,
            $lab,
            $asa_score,
            $gcs_e,
            $gcs_v,
            $gcs_m,
            $rencana_mulai,
            $rencana_selesai,
            $monitoring_json,
            $regno
        );
        $pesan_sukses = "Data Berhasil Diperbarui!";
    } else {
        // --- INSERT ---
        $sql = "INSERT INTO status_sedasi (
            no_rm, nama_pasien, tgl_lahir, jenis_kelamin, regno, waktu_input,
            ruangan, tindakan, diagnosa, bb, tb, td, nadi, nafas, spo2,
            jalan_nafas, mallampati, leher, riwayat_alergi, skala_nyeri,
            iv_line_tempat, iv_line_cairan, status_puasa, lab_penunjang,
            asa_score, gcs_e, gcs_v, gcs_m, rencana_mulai, rencana_selesai, monitoring_data
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        // INSERT: 31 data (termasuk regno di urutan ke-5)
        $stmt->bind_param(
            "sssssssssddsiissssisisssiiissss",
            $no_rm,
            $nama,
            $tgl_lahir,
            $jk,
            $regno,
            $waktu_input,
            $ruangan,
            $tindakan,
            $diagnosa,
            $bb,
            $tb,
            $td,
            $nadi,
            $nafas,
            $spo2,
            $jalan_nafas,
            $mallampati_json,
            $leher,
            $alergi_check,
            $skala_nyeri,
            $iv_line_tempat,
            $iv_line_cairan,
            $status_puasa,
            $lab,
            $asa_score,
            $gcs_e,
            $gcs_v,
            $gcs_m,
            $rencana_mulai,
            $rencana_selesai,
            $monitoring_json
        );
        $pesan_sukses = "Data Baru Berhasil Disimpan!";
    }

    if ($stmt->execute()) {
        $status_simpan = "success";
        $pesan_simpan = $pesan_sukses;
    } else {
        $status_simpan = "error";
        $pesan_simpan = "Gagal: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Sedasi - RSUD Sanjiwani Gianyar</title>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            background-color: #f4f6f9;
        }

        .container {
            width: 210mm;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 4px;
            vertical-align: top;
        }

        .no-border {
            border: none;
        }

        .header-green {
            background: linear-gradient(90deg, #92D050, #7db342);
            font-weight: bold;
            text-align: center;
            padding: 8px;
            border: 1px solid black;
            color: #000;
        }

        .header-black {
            background: #2c3e50;
            color: white;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            padding: 5px;
        }

        input[type="text"],
        input[type="number"],
        input[type="time"],
        input[type="date"] {
            border: none;
            border-bottom: 1px dashed #aaa;
            width: 80%;
            background: transparent;
            font-family: inherit;
            font-size: inherit;
        }

        input:focus {
            outline: none;
            border-bottom: 1px solid #2980b9;
            background-color: #f0f8ff;
        }

        textarea {
            resize: vertical;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 98%;
            height: 40px;
        }

        .full-width {
            width: 98% !important;
        }

        .logo-section {
            display: flex;
            align-items: center;
        }

        .logo-img {
            width: 50px;
            height: 50px;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .title {
            text-align: center;
            flex-grow: 1;
        }

        .title h2 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
        }

        .mallampati-viz {
            display: flex;
            justify-content: space-around;
            margin-top: 5px;
        }

        .pain-scale-container {
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin: 5px 0;
        }

        .pain-option {
            text-align: center;
            cursor: pointer;
        }

        .pain-emoji {
            font-size: 22px;
            display: block;
            margin-bottom: 2px;
        }

        .monitoring-table td {
            height: 15px;
            padding: 0;
            text-align: center;
            vertical-align: middle;
        }

        .monitoring-table input[type="text"] {
            width: 100%;
            border: none;
            text-align: center;
        }

        .chart-grid td {
            height: 12px;
            border: 1px solid #ccc;
        }

        .chart-label {
            font-size: 9px;
            text-align: right;
            padding-right: 2px;
            border-right: 1px solid black;
            background: #eee;
        }

        .btn-container {
            text-align: center;
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .btn-modern {
            padding: 12px 30px;
            font-size: 14px;
            font-weight: bold;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-save {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.4);
        }

        .btn-print {
            background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
        }

        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }

            /* KUNCI AREA CETAK */
            html,
            body {
                width: 210mm;
                height: 297mm;
                overflow: hidden;
            }

            /* KECILKAN SELURUH ISI */
            .container {
                transform: scale(0.95);
                transform-origin: top;
                width: 100%;
            }

            body {
                background-color: white;
                margin: 0;
                padding: 0;
                transform: scale(0.95);
                transform-origin: top left;
                width: 105%;
            }

            .container {
                width: 100%;
                box-shadow: none;
                margin: 0;
                padding: 0;
                border: none;
            }

            .btn-container,
            .swal2-container,
            ::-webkit-scrollbar {
                display: none !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            td,
            th {
                padding: 1px 3px !important;
                font-size: 10px !important;
            }

            .chart-grid td {
                height: 10px !important;
            }

            .header-black {
                padding: 2px !important;
                font-size: 11px !important;
            }

            .header-green {
                padding: 4px !important;
            }

            input[type="text"],
            input[type="number"],
            input[type="time"],
            textarea {
                border: none !important;
                font-size: 10px !important;
            }

            textarea {
                height: 30px !important;
            }

            ::placeholder {
                color: transparent;
            }
        }
    </style>
</head>

<body>

    <form action="" method="post" id="mainForm">
        <div class="container">
            <!-- HEADER -->
            <div class="header-green" style="text-align: left; position: relative;">
                BLUD RSUD SANJIWANI GIANYAR
                <span style="float: right; font-weight: bold; padding: 2px 5px; background: rgba(255,255,255,0.3); border-radius: 4px;">RM.05.112/2025</span>
            </div>

            <table style="margin-top: 5px; border: none;">
                <tr style="border: none;">
                    <td width="60%">
                        <div class="logo-section">
                            <div class="logo-img"><img src="logo.png" alt="Logo" style="height: 60px; margin-left: 20px; margin-top: 10px;"></div>
                            <div class="title">
                                <h2 style="font-size: 16px;">STATUS SEDASI</h2>
                            </div>
                        </div>
                    </td>
                    <td width="40%" style="border: 1px solid #ccc; padding: 5px; border-radius: 4px;">
                        <table class="no-border" style="width: 100%; margin: 0;">
                            <tr>
                                <td class="no-border" width="30%">No. RM</td>
                                <td class="no-border">: <input type="text" name="no_rm" value="<?php echo htmlspecialchars($no_rm); ?>" required style="font-weight: bold;"></td>
                            </tr>
                            <tr>
                                <td class="no-border">Nama</td>
                                <td class="no-border">: <input type="text" name="nama" value="<?php echo htmlspecialchars($nama); ?>" required></td>
                            </tr>
                            <tr>
                                <td class="no-border">Tgl. Lahir</td>
                                <td class="no-border">: <input type="text" name="tgl_lahir" value="<?php echo htmlspecialchars($tgl_lahir); ?>" style="width: 100px;">
                                    <input type="radio" name="jk" value="L" <?php echo ($jk == 'L') ? 'checked' : ''; ?>> LK
                                    <input type="radio" name="jk" value="P" <?php echo ($jk == 'P') ? 'checked' : ''; ?>> PR
                                </td>
                            </tr>
                            <tr>
                                <td class="no-border">Regno</td>
                                <td class="no-border">: <input type="text" name="regno" value="<?php echo htmlspecialchars($regno); ?>" required style="font-weight: bold; color: #2c3e50;"></td>
                            </tr>
                            <tr>
                                <td class="no-border" colspan="2" style="border-top: 1px dashed #ccc !important; padding-top: 2px;">
                                    Tanggal: <input type="date" name="tgl_input" value="<?php echo htmlspecialchars($tgl_input); ?>" style="width: 100px;">
                                    Jam: <input type="time" name="jam_input" value="<?php echo htmlspecialchars($jam_input); ?>" style="width: 60px;"> Wita
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- DIAGNOSIS INFO -->
            <table style="margin-top: 5px;">
                <tr>
                    <td width="15%">Ruangan :</td>
                    <td width="35%"><input type="text" name="ruangan" value="<?php echo htmlspecialchars($ruangan); ?>" class="full-width"></td>
                    <td width="50%" rowspan="2" style="vertical-align: top;">
                        Tindakan : <br>
                        <textarea name="tindakan"><?php echo htmlspecialchars($tindakan); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td>Diagnosa :</td>
                    <td><input type="text" name="diagnosa" value="<?php echo htmlspecialchars($diagnosa); ?>" class="full-width"></td>
                </tr>
            </table>

            <!-- PENILAIAN PRA SEDASI -->
            <div class="header-black">PENILAIAN PRA SEDASI</div>
            <table>
                <tr>
                    <td>BB: <input type="number" step="0.1" name="bb" value="<?php echo htmlspecialchars($bb); ?>" style="width: 30px"> kg</td>
                    <td>TB: <input type="number" step="0.1" name="tb" value="<?php echo htmlspecialchars($tb); ?>" style="width: 30px"> cm</td>
                    <td colspan="2">TD: <input type="text" name="td" value="<?php echo htmlspecialchars($td); ?>" style="width: 40px"> mmHg</td>
                    <td>Nadi <input type="number" name="nadi" value="<?php echo htmlspecialchars($nadi); ?>" style="width: 30px"> x/mnt</td>
                    <td>Nafas <input type="number" name="nafas" value="<?php echo htmlspecialchars($nafas); ?>" style="width: 30px"> x/mnt</td>
                    <td>SpO2 <input type="number" name="spo2" value="<?php echo htmlspecialchars($spo2); ?>" style="width: 30px"> %</td>
                </tr>
                <tr>
                    <td colspan="2" width="30%">
                        <strong>Jalan Nafas</strong><br>
                        <?php
                        $opsi_nafas = ['Normal', 'Mulut Kecil', 'Gigi Prominen', 'Dagu Kecil'];
                        foreach ($opsi_nafas as $opsi) {
                            $checked = in_array($opsi, $jalan_nafas_arr) ? 'checked' : '';
                            echo "<input type='checkbox' name='jalan_nafas[]' value='$opsi' $checked> $opsi<br>";
                        }
                        ?>
                    </td>
                    <td colspan="3" width="35%">
                        <strong>Mallampati</strong>
                        <div class="mallampati-viz">
                            <div><img src="mallampati1.png" title="I" style="height: 30px;"></div>
                            <div><img src="mallampati2.png" title="II" style="height: 30px;"></div>
                            <div><img src="mallampati3.png" title="III" style="height: 30px;"></div>
                            <div><img src="mallampati4.png" title="IV" style="height: 30px;"></div>
                        </div>
                        <div style="justify-content: flex-start; margin-top: 2px;">
                            <input type="radio" name="mallampati" value="1" <?php echo ($mallampati_check == '1') ? 'checked' : ''; ?> style="margin-left: 27px;">
                            <input type="radio" name="mallampati" value="2" <?php echo ($mallampati_check == '2') ? 'checked' : ''; ?> style="margin-left: 48px;">
                            <input type="radio" name="mallampati" value="3" <?php echo ($mallampati_check == '3') ? 'checked' : ''; ?> style="margin-left: 47px;">
                            <input type="radio" name="mallampati" value="4" <?php echo ($mallampati_check == '4') ? 'checked' : ''; ?> style="margin-left: 49px;">
                        </div>
                    </td>
                    <td colspan="2">
                        <strong>Leher</strong><br>
                        <input type="checkbox" name="leher[]" value="Normal" <?php echo in_array('Normal', $leher_arr) ? 'checked' : ''; ?>> Normal<br>
                        <input type="checkbox" name="leher[]" value="Pendek" <?php echo in_array('Pendek', $leher_arr) ? 'checked' : ''; ?>> Leher pendek<br>
                        <input type="checkbox" name="leher[]" value="Terbatas" <?php echo in_array('Terbatas', $leher_arr) ? 'checked' : ''; ?>> Gerak leher terbatas
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        <strong>Riwayat Alergi :</strong><br>
                        <input type="checkbox" name="alergi_check" value="Ya" <?php echo (strpos($riwayat_alergi, 'Ya') !== false) ? 'checked' : ''; ?>> Ya, sebutkan:
                        <input type="text" name="alergi_text" value="<?php echo (strpos($riwayat_alergi, 'Ya') !== false) ? trim(str_replace("Ya: ", "", $riwayat_alergi)) : ''; ?>" style="width: 150px"><br>
                        <input type="checkbox" name="alergi_none" value="Tidak" <?php echo ($riwayat_alergi == 'Tidak') ? 'checked' : ''; ?>> Tidak
                    </td>
                    <td colspan="2">
                        <strong>Skala Nyeri (0-10):</strong>
                        <div class="pain-scale-container">
                            <label class="pain-option">
                                <span class="pain-emoji">🙂</span>
                                <input type="radio" name="skala_nyeri_radio" value="0" <?php echo ($skala_nyeri == 0) ? 'checked' : ''; ?>>
                                <div style="font-size:9px">0</div>
                            </label>
                            <label class="pain-option">
                                <span class="pain-emoji">😐</span>
                                <input type="radio" name="skala_nyeri_radio" value="2" <?php echo ($skala_nyeri >= 1 && $skala_nyeri <= 3) ? 'checked' : ''; ?>>
                                <div style="font-size:9px">1-3</div>
                            </label>
                            <label class="pain-option">
                                <span class="pain-emoji">☹️</span>
                                <input type="radio" name="skala_nyeri_radio" value="6" <?php echo ($skala_nyeri >= 4 && $skala_nyeri <= 6) ? 'checked' : ''; ?>>
                                <div style="font-size:9px">4-6</div>
                            </label>
                            <label class="pain-option">
                                <span class="pain-emoji">😭</span>
                                <input type="radio" name="skala_nyeri_radio" value="10" <?php echo ($skala_nyeri >= 7) ? 'checked' : ''; ?>>
                                <div style="font-size:9px">7-10</div>
                            </label>
                        </div>
                        <div style="text-align: center; margin-top: 5px; border-top: 1px dashed #ccc; padding-top:2px;">
                            Atau Isi Angka:
                            <input type="number" name="skala_nyeri_manual" value="<?php echo ($skala_nyeri > 0) ? $skala_nyeri : ''; ?>" style="width:40px; text-align: center;">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        IV Line &nbsp;&nbsp; Tempat : <input type="text" name="iv_line_tempat" value="<?php echo htmlspecialchars($iv_line_tempat); ?>" style="width: 80px"><br>
                        <div style="margin-left: 55px;">Cairan : <input type="number" name="iv_line_cairan" value="<?php echo htmlspecialchars($iv_line_cairan); ?>" style="width: 80px"> cc/jam</div>
                    </td>
                    <td colspan="2" rowspan="2">
                        <strong>Puasa</strong> <br>
                        <input type="checkbox" name="puasa_makan" value="1" <?php echo in_array('Makan', $puasa_arr) ? 'checked' : ''; ?>> Makan <br>
                        <input type="checkbox" name="puasa_minum" value="1" <?php echo in_array('Minum', $puasa_arr) ? 'checked' : ''; ?>> Minum
                    </td>
                </tr>
                <tr>
                    <td colspan="5">Laboratorium/Pemeriksaan Penunjang:<br><input type="text" name="lab" value="<?php echo htmlspecialchars($lab); ?>" class="full-width"></td>
                </tr>
                <tr>
                    <td colspan="4">
                        <strong>ASA</strong>
                        <input type="radio" name="asa" value="1" <?php echo ($asa_score == '1') ? 'checked' : ''; ?>> 1
                        <input type="radio" name="asa" value="2" <?php echo ($asa_score == '2') ? 'checked' : ''; ?>> 2
                        <input type="radio" name="asa" value="3" <?php echo ($asa_score == '3') ? 'checked' : ''; ?>> 3
                        <input type="radio" name="asa" value="4" <?php echo ($asa_score == '4') ? 'checked' : ''; ?>> 4
                        <input type="radio" name="asa" value="5" <?php echo ($asa_score == '5') ? 'checked' : ''; ?>> 5
                        <input type="radio" name="asa" value="E" <?php echo ($asa_score == 'E') ? 'checked' : ''; ?>> E <br>
                        Tk. Kesadaran: E <input type="number" name="gcs_e" value="<?php echo htmlspecialchars($gcs_e); ?>" style="width: 30px;"> V <input type="number" name="gcs_v" value="<?php echo htmlspecialchars($gcs_v); ?>" style="width: 30px;"> M <input type="number" name="gcs_m" value="<?php echo htmlspecialchars($gcs_m); ?>" style="width: 30px;">
                    </td>
                    <td colspan="1"><strong>Rencana Sedasi</strong><textarea name="rencana_sedasi" style="height: 30px;"></textarea></td>
                    <td colspan="2">
                        <table class="no-border" width="100%">
                            <tr>
                                <td colspan="2" class="no-border" style="text-align: center; border-bottom: 1px solid black !important;">Tindakan</td>
                            </tr>
                            <tr>
                                <td class="no-border" style="border-right: 1px solid black !important;">Mulai</td>
                                <td class="no-border">Selesai</td>
                            </tr>
                            <tr>
                                <td class="no-border" style="border-right: 1px solid black !important;"><input type="time" name="rencana_mulai" value="<?php echo htmlspecialchars($rencana_mulai); ?>" size="5"></td>
                                <td class="no-border"><input type="time" name="rencana_selesai" value="<?php echo htmlspecialchars($rencana_selesai); ?>" size="5"></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- MONITORING SELAMA SEDASI -->
            <div class="header-black">MONITORING SELAMA SEDASI</div>
            <table class="monitoring-table">
                <tr>
                    <td width="20%" style="text-align: left; padding-left: 5px;"><strong>Obat & Rute <br> Pemberian</strong></td>
                    <?php for ($i = 0; $i < 16; $i++): ?>
                        <td width="5%"></td>
                    <?php endfor; ?>
                </tr>

                <?php for ($r = 0; $r < 3; $r++): ?>
                    <tr>
                        <td><input type="text" name="mon_obat_nama[]" value="<?php echo htmlspecialchars($mon_obat_nama[$r] ?? ''); ?>" style="text-align: left;" placeholder="Nama Obat..."></td>
                        <?php for ($i = 0; $i < 16; $i++):
                            $isChecked = ($mon_obat_grid[$r][$i] ?? "") == "✓";
                        ?>
                            <td><input type="checkbox" name="mon_obat_grid[<?php echo $r; ?>][<?php echo $i; ?>]" value="1" <?php echo $isChecked ? 'checked' : ''; ?>></td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>

                <tr>
                    <td style="text-align: left; padding-left: 5px;">Waktu</td>
                    <?php for ($i = 0; $i < 16; $i++): echo "<td><input type='text' name='mon_waktu[]' value='" . htmlspecialchars($mon_waktu[$i] ?? '') . "'></td>";
                    endfor; ?>
                </tr>
                <tr>
                    <td style="text-align: left; padding-left: 5px;">O2 L/menit</td>
                    <?php for ($i = 0; $i < 16; $i++): echo "<td><input type='text' name='mon_o2[]' value='" . htmlspecialchars($mon_o2[$i] ?? '') . "'></td>";
                    endfor; ?>
                </tr>
                <tr>
                    <td style="text-align: left; padding-left: 5px;">SpO2</td>
                    <?php for ($i = 0; $i < 16; $i++): echo "<td><input type='text' name='mon_spo2[]' value='" . htmlspecialchars($mon_spo2[$i] ?? '') . "'></td>";
                    endfor; ?>
                </tr>
                <tr>
                    <td style="text-align: left; padding-left: 5px;">Skala Nyeri</td>
                    <?php for ($i = 0; $i < 16; $i++): echo "<td><input type='text' name='mon_nyeri[]' value='" . htmlspecialchars($mon_nyeri[$i] ?? '') . "'></td>";
                    endfor; ?>
                </tr>
                <tr>
                    <td style="text-align: left; padding-left: 5px;">Tk. Kesadaran</td>
                    <?php for ($i = 0; $i < 16; $i++): echo "<td><input type='text' name='mon_kesadaran[]' value='" . htmlspecialchars($mon_kesadaran[$i] ?? '') . "'></td>";
                    endfor; ?>
                </tr>
            </table>

            <!-- Chart Grid -->
            <table class="chart-grid" style="margin-top: -1px;">
                <tr style="background: #e9ecef;">
                    <td width="5%"></td>
                    <td width="5%" style="text-align: center;"><strong>R</strong></td>
                    <td width="5%" style="text-align: center;"><strong>N</strong></td>
                    <td width="5%" style="text-align: center;"><strong>TD</strong></td>
                    <?php for ($i = 0; $i < 16; $i++): ?>
                        <td width="5%" style="background: #f8f9fa;"></td>
                    <?php endfor; ?>
                </tr>

                <?php
                $labels = [
                    ['', '60', '', '220', ''],
                    ['', '', '', '', ''],
                    ['', '55', '', '200', ''],
                    ['', '', '', '', ''],
                    ['', '50', '', '180', ''],
                    ['', '', '', '', ''],
                    ['N ●', '45', '', '160', '●'],
                    ['', '', '', '', ''],
                    ['Sis ▼', '40', '180', '140', '▼'],
                    ['', '', '', '', ''],
                    ['Dis ▲', '35', '160', '120', '▲'],
                    ['', '', '', '', ''],
                    ['R +', '30', '140', '100', '+'],
                    ['', '', '', '', ''],
                    ['', '25', '120', '80', ''],
                    ['', '', '', '', ''],
                    ['', '20', '100', '60', ''],
                    ['', '', '', '', ''],
                    ['', '15', '80', '40', ''],
                    ['', '', '', '', ''],
                    ['', '10', '60', '20', ''],
                    ['', '', '', '', ''],
                    ['', '5', '', '0', '']
                ];

                foreach ($labels as $rIndex => $row):
                    $symbol = !empty($row[4]) ? $row[4] : '•';
                ?>
                    <tr>
                        <td class="chart-label" style="text-align: center;"><?php echo $row[0]; ?></td>
                        <td class="chart-label" style="text-align: center;"><?php echo $row[1]; ?></td>
                        <td class="chart-label" style="text-align: center;"><?php echo $row[2]; ?></td>
                        <td class="chart-label" style="text-align: center;"><?php echo $row[3]; ?></td>

                        <?php for ($i = 0; $i < 16; $i++):
                            $savedSymbol = $chart_grid_data[$rIndex][$i] ?? "";
                        ?>
                            <td onclick="toggleSymbol(this, '<?php echo $symbol; ?>')" style="cursor:pointer; user-select: none; font-weight: bold; color: blue;">
                                <?php echo $savedSymbol; ?>
                                <input type="hidden" name="chart_data[<?php echo $rIndex; ?>][<?php echo $i; ?>]" value="<?php echo $savedSymbol; ?>">
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>
            </table>

            <div style="font-size: 11px; margin-top: 5px; text-align: center; color: #555;">
                Mulai sedasi x &rightarrow; &nbsp;&nbsp;&nbsp;
                Selesai sedasi x &leftarrow; &nbsp;&nbsp;&nbsp;
                Mulai prosedur o &rightarrow; &nbsp;&nbsp;&nbsp;
                Selesai prosedur &leftarrow; o
            </div>

            <br>

            <div class="btn-container">
                <button type="button" class="btn-modern btn-print" onclick="window.print()">
                    <i class="fa fa-print"></i> PRINT
                </button>
                <button type="submit" id="btnSimpan" class="btn-modern btn-save">
                    <i class="fa fa-save"></i> SIMPAN & LANJUT
                </button>
            </div>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function toggleSymbol(cell, symbol) {
            var input = cell.querySelector('input');
            if (cell.innerHTML.includes(symbol)) {
                cell.innerHTML = '<input type="hidden" name="' + input.name + '" value="">';
            } else {
                cell.innerHTML = symbol + '<input type="hidden" name="' + input.name + '" value="' + symbol + '">';
            }
        }

        document.getElementById('mainForm').addEventListener('submit', function() {
            var btn = document.getElementById('btnSimpan');
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> MENYIMPAN...';
            btn.disabled = true;
            btn.style.opacity = '0.7';
        });

        var statusSimpan = "<?php echo $status_simpan; ?>";
        var pesanSimpan = "<?php echo $pesan_simpan; ?>";

        if (statusSimpan === 'success') {
            Swal.fire({
                title: 'Berhasil!',
                text: pesanSimpan,
                icon: 'success',
                showConfirmButton: false,
                timer: 2000
            }).then(function() {
                var regno = document.querySelector('input[name="regno"]').value;
                window.location.href = 'catatan.php?regno=' + regno;
            });
        } else if (statusSimpan === 'error') {
            Swal.fire({
                title: 'Gagal!',
                text: pesanSimpan,
                icon: 'error',
                confirmButtonText: 'Tutup'
            });
            var btn = document.getElementById('btnSimpan');
            btn.innerHTML = '<i class="fa fa-save"></i> SIMPAN & LANJUT';
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    </script>

</body>

</html>