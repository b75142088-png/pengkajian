<?php
// Masukkan file koneksi
require_once 'db_connection.php';

$pesan_sukses = "";
$error_message = "";
$signature_preview = "";

// Helper function
function getValue($field) {
    return isset($_POST[$field]) ? htmlspecialchars($_POST[$field]) : '';
}

function getChecked($field, $val) {
    return (isset($_POST[$field]) && $_POST[$field] === $val) ? 'checked' : '';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Ambil data TTD
        $signature_image = $_POST['signature_image'] ?? '';
        if (!empty($signature_image)) {
            $signature_preview = $signature_image;
        }

        // Query INSERT
        $sql = "INSERT INTO asesmen_pra_anestesi (
            no_rm, nama_pasien, tgl_lahir, jk_header, regno, ruangan, tgl_asesmen, jam_asesmen,
            umur, jk_penata, menikah, pekerjaan,
            rokok, rokok_jumlah, kopi, kopi_jumlah, alkohol, alkohol_jumlah, olahraga, olahraga_jumlah,
            obat_resep, obat_bebas, obat_bebas_ket, aspirin, aspirin_dosis, painkiller, painkiller_dosis,
            steroid, steroid_ket, alergi_obat, alergi_obat_ket,
            alergi_lateks, alergi_plester, alergi_makanan,
            rk_perdarahan_abnormal, rk_pembekuan_abnormal, rk_masalah_pembiusan, rk_jantung_koroner, rk_diabetes,
            rk_serangan_jantung, rk_hipertensi, rk_tbc, rk_penyakit_berat_lain, rk_penjelasan_ya,
            bahasa_indo, bahasa_lain, bahasa_lain_ket, kom_mata, kom_telinga, kom_bicara,
            rp_perdarahan_abnormal, rp_pembekuan_abnormal, rp_maag, rp_anemia, rp_sesak, rp_asma, rp_pingsan,
            rp_nyeri_dada, rp_hepatitis, rp_hipertensi, rp_ngorok, rp_penyakit_berat_lain, rp_diabetes, rp_penjelasan_ya,
            transfusi, transfusi_tahun, hiv_check, hiv_tahun, hiv_res,
            lensa_kontak, kacamata, alat_bantu_dengar, gigi_palsu,
            op_lokal_ket, op_regional_ket, op_umum_ket,
            terakhir_periksa_tgl, terakhir_periksa_tempat, terakhir_periksa_penyakit,
            jml_hamil, jml_anak, menstruasi, menyusui,
            anamnesis,
            dok_hilang_gigi, dok_masalah_leher, dok_leher_pendek, dok_batuk, dok_sesak, dok_infeksi_nafas, dok_mens_abnormal, dok_stroke,
            dok_sakit_dada, dok_jantung_abnormal, dok_muntah, dok_susah_kencing, dok_kejang, dok_hamil, dok_pingsan, dok_obesitas,
            dok_keterangan,
            ku_kesadaran, ku_visus, ku_faring, ku_gigi_palsu, ku_keterangan,
            fisik_tinggi, fisik_berat, fisik_td, fisik_nadi, fisik_rr, fisik_suhu,
            fisik_paru, fisik_jantung, fisik_abdomen, fisik_ekstrimitas, fisik_neurologi, fisik_lain,
            lab_hb_ht, lab_pt_aptt, lab_kehamilan, lab_kalium, lab_ureum, lab_keterangan,
            lab_rontgen, lab_ekg, lab_nacl, lab_co2, lab_lain,
            masalah, asa, saran,
            ane_umum, au_iv, au_sm, au_lma, au_ett,
            ane_reg, ar_sab, ar_epi, ar_cse, ar_pnb,
            ane_umum_reg,
            puasa_jam, puasa_tgl,
            signature_image, nama_dokter_ttd
        ) VALUES (
            :no_rm, :nama_pasien, :tgl_lahir, :jk_header, :regno, :ruangan, :tgl_asesmen, :jam_asesmen,
            :umur, :jk_penata, :menikah, :pekerjaan,
            :rokok, :rokok_jumlah, :kopi, :kopi_jumlah, :alkohol, :alkohol_jumlah, :olahraga, :olahraga_jumlah,
            :obat_resep, :obat_bebas, :obat_bebas_ket, :aspirin, :aspirin_dosis, :painkiller, :painkiller_dosis,
            :steroid, :steroid_ket, :alergi_obat, :alergi_obat_ket,
            :alergi_lateks, :alergi_plester, :alergi_makanan,
            :rk_perdarahan_abnormal, :rk_pembekuan_abnormal, :rk_masalah_pembiusan, :rk_jantung_koroner, :rk_diabetes,
            :rk_serangan_jantung, :rk_hipertensi, :rk_tbc, :rk_penyakit_berat_lain, :rk_penjelasan_ya,
            :bahasa_indo, :bahasa_lain, :bahasa_lain_ket, :kom_mata, :kom_telinga, :kom_bicara,
            :rp_perdarahan_abnormal, :rp_pembekuan_abnormal, :rp_maag, :rp_anemia, :rp_sesak, :rp_asma, :rp_pingsan,
            :rp_nyeri_dada, :rp_hepatitis, :rp_hipertensi, :rp_ngorok, :rp_penyakit_berat_lain, :rp_diabetes, :rp_penjelasan_ya,
            :transfusi, :transfusi_tahun, :hiv_check, :hiv_tahun, :hiv_res,
            :lensa_kontak, :kacamata, :alat_bantu_dengar, :gigi_palsu,
            :op_lokal_ket, :op_regional_ket, :op_umum_ket,
            :terakhir_periksa_tgl, :terakhir_periksa_tempat, :terakhir_periksa_penyakit,
            :jml_hamil, :jml_anak, :menstruasi, :menyusui,
            :anamnesis,
            :dok_hilang_gigi, :dok_masalah_leher, :dok_leher_pendek, :dok_batuk, :dok_sesak, :dok_infeksi_nafas, :dok_mens_abnormal, :dok_stroke,
            :dok_sakit_dada, :dok_jantung_abnormal, :dok_muntah, :dok_susah_kencing, :dok_kejang, :dok_hamil, :dok_pingsan, :dok_obesitas,
            :dok_keterangan,
            :ku_kesadaran, :ku_visus, :ku_faring, :ku_gigi_palsu, :ku_keterangan,
            :fisik_tinggi, :fisik_berat, :fisik_td, :fisik_nadi, :fisik_rr, :fisik_suhu,
            :fisik_paru, :fisik_jantung, :fisik_abdomen, :fisik_ekstrimitas, :fisik_neurologi, :fisik_lain,
            :lab_hb_ht, :lab_pt_aptt, :lab_kehamilan, :lab_kalium, :lab_ureum, :lab_keterangan,
            :lab_rontgen, :lab_ekg, :lab_nacl, :lab_co2, :lab_lain,
            :masalah, :asa, :saran,
            :ane_umum, :au_iv, :au_sm, :au_lma, :au_ett,
            :ane_reg, :ar_sab, :ar_epi, :ar_cse, :ar_pnb,
            :ane_umum_reg,
            :puasa_jam, :puasa_tgl,
            :signature_image, :nama_dokter_ttd
        )";

        $stmt = $pdo->prepare($sql);
        
        $params = [
            ':no_rm' => $_POST['no_rm'] ?? '', ':nama_pasien' => $_POST['nama_pasien'] ?? '', ':tgl_lahir' => $_POST['tgl_lahir'] ?? null,
            ':jk_header' => $_POST['jk_header'] ?? '', ':regno' => $_POST['regno'] ?? '', ':ruangan' => $_POST['ruangan'] ?? '',
            ':tgl_asesmen' => $_POST['tgl_asesmen'] ?? null, ':jam_asesmen' => $_POST['jam_asesmen'] ?? null,
            ':umur' => $_POST['umur'] ?? 0, ':jk_penata' => $_POST['jk_penata'] ?? '', ':menikah' => $_POST['menikah'] ?? '', ':pekerjaan' => $_POST['pekerjaan'] ?? '',
            ':rokok' => $_POST['rokok'] ?? '', ':rokok_jumlah' => $_POST['rokok_jumlah'] ?? '', ':kopi' => $_POST['kopi'] ?? '', ':kopi_jumlah' => $_POST['kopi_jumlah'] ?? '',
            ':alkohol' => $_POST['alkohol'] ?? '', ':alkohol_jumlah' => $_POST['alkohol_jumlah'] ?? '', ':olahraga' => $_POST['olahraga'] ?? '', ':olahraga_jumlah' => $_POST['olahraga_jumlah'] ?? '',
            ':obat_resep' => $_POST['obat_resep'] ?? '', ':obat_bebas' => $_POST['obat_bebas'] ?? '', ':obat_bebas_ket' => $_POST['obat_bebas_ket'] ?? '',
            ':aspirin' => $_POST['aspirin'] ?? '', ':aspirin_dosis' => $_POST['aspirin_dosis'] ?? '', ':painkiller' => $_POST['painkiller'] ?? '', ':painkiller_dosis' => $_POST['painkiller_dosis'] ?? '',
            ':steroid' => $_POST['steroid'] ?? '', ':steroid_ket' => $_POST['steroid_ket'] ?? '', ':alergi_obat' => $_POST['alergi_obat'] ?? '', ':alergi_obat_ket' => $_POST['alergi_obat_ket'] ?? '',
            ':alergi_lateks' => $_POST['alt'] ?? '', ':alergi_plester' => $_POST['alp'] ?? '', ':alergi_makanan' => $_POST['alm'] ?? '',
            ':rk_perdarahan_abnormal' => $_POST['rk_Perdarahanyangtidaknormal'] ?? '', ':rk_pembekuan_abnormal' => $_POST['rk_Pembekuandarahtidaknormal'] ?? '', ':rk_masalah_pembiusan' => $_POST['rk_Permasalahandalampembiusan'] ?? '',
            ':rk_jantung_koroner' => $_POST['rk_Operasijantungkoroner'] ?? '', ':rk_diabetes' => $_POST['rk_Diabetes'] ?? '', ':rk_serangan_jantung' => $_POST['rk_Seranganjantung'] ?? '',
            ':rk_hipertensi' => $_POST['rk_Hipertensi'] ?? '', ':rk_tbc' => $_POST['rk_Tuberkulosis'] ?? '', ':rk_penyakit_berat_lain' => $_POST['rk_Penyakitberatlainnya'] ?? '', ':rk_penjelasan_ya' => $_POST['rk_penjelasan_ya'] ?? '',
            ':bahasa_indo' => $_POST['bahasa_indo'] ?? '', ':bahasa_lain' => $_POST['bahasa_lain'] ?? '', ':bahasa_lain_ket' => $_POST['bahasa_lain_ket'] ?? '',
            ':kom_mata' => $_POST['kom_mata'] ?? '', ':kom_telinga' => $_POST['kom_telinga'] ?? '', ':kom_bicara' => $_POST['kom_bicara'] ?? '',
            ':rp_perdarahan_abnormal' => $_POST['rp_Perdarahantidaknormal'] ?? '', ':rp_pembekuan_abnormal' => $_POST['rp_Pembekuandarahtidaknormal'] ?? '', ':rp_maag' => $_POST['rp_Sakitmaag'] ?? '',
            ':rp_anemia' => $_POST['rp_Anemia'] ?? '', ':rp_sesak' => $_POST['rp_Sesaknapas'] ?? '', ':rp_asma' => $_POST['rp_Asma'] ?? '', ':rp_pingsan' => $_POST['rp_Pingsan'] ?? '',
            ':rp_nyeri_dada' => $_POST['rp_Seranganjantung/Nyeridada'] ?? '', ':rp_hepatitis' => $_POST['rp_Hepatitis/sakitkuning'] ?? '', ':rp_hipertensi' => $_POST['rp_Hipertensi'] ?? '',
            ':rp_ngorok' => $_POST['rp_SumbatanjalannafassaatTidur/Mengorok'] ?? '', ':rp_penyakit_berat_lain' => $_POST['rp_Penyakitberatlainnya'] ?? '', ':rp_diabetes' => $_POST['rp_Diabetes'] ?? '', ':rp_penjelasan_ya' => $_POST['rp_penjelasan_ya'] ?? '',
            ':transfusi' => $_POST['transfusi'] ?? '', ':transfusi_tahun' => $_POST['transfusi_tahun'] ?? '', ':hiv_check' => $_POST['hiv_check'] ?? '', ':hiv_tahun' => $_POST['hiv_tahun'] ?? '', ':hiv_res' => $_POST['hiv_res'] ?? '',
            ':lensa_kontak' => $_POST['lk'] ?? '', ':kacamata' => $_POST['km'] ?? '', ':alat_bantu_dengar' => $_POST['abd'] ?? '', ':gigi_palsu' => $_POST['gp'] ?? '',
            ':op_lokal_ket' => $_POST['op_lokal_ket'] ?? '', ':op_regional_ket' => $_POST['op_regional_ket'] ?? '', ':op_umum_ket' => $_POST['op_umum_ket'] ?? '',
            ':terakhir_periksa_tgl' => $_POST['terakhir_periksa_tgl'] ?? null, ':terakhir_periksa_tempat' => $_POST['terakhir_periksa_tempat'] ?? '', ':terakhir_periksa_penyakit' => $_POST['terakhir_periksa_penyakit'] ?? '',
            ':jml_hamil' => $_POST['jml_hamil'] ?? '', ':jml_anak' => $_POST['jml_anak'] ?? '', ':menstruasi' => $_POST['menstruasi'] ?? '', ':menyusui' => $_POST['menyusui'] ?? '',
            ':anamnesis' => $_POST['anamnesis'] ?? '',
            ':dok_hilang_gigi' => $_POST['dok_Hilangnyagigi'] ?? '', ':dok_masalah_leher' => $_POST['dok_Masalahmobilisasileher'] ?? '', ':dok_leher_pendek' => $_POST['dok_Leherpendek'] ?? '',
            ':dok_batuk' => $_POST['dok_Batuk'] ?? '', ':dok_sesak' => $_POST['dok_Sesaknafas'] ?? '', ':dok_infeksi_nafas' => $_POST['dok_Barusajamenderitainfeksisalurannafasatas'] ?? '',
            ':dok_mens_abnormal' => $_POST['dok_Periodemenstruasitidaknormal'] ?? '', ':dok_stroke' => $_POST['dok_Stroke'] ?? '',
            ':dok_sakit_dada' => $_POST['dok_Sakitdada'] ?? '', ':dok_jantung_abnormal' => $_POST['dok_Denyutjantungtidaknormal'] ?? '', ':dok_muntah' => $_POST['dok_Muntah'] ?? '',
            ':dok_susah_kencing' => $_POST['dok_Susahkencing'] ?? '', ':dok_kejang' => $_POST['dok_Kejang'] ?? '', ':dok_hamil' => $_POST['dok_Sedanghamil'] ?? '',
            ':dok_pingsan' => $_POST['dok_Pingsan'] ?? '', ':dok_obesitas' => $_POST['dok_Obesitas'] ?? '',
            ':dok_keterangan' => $_POST['dok_keterangan'] ?? '',
            ':ku_kesadaran' => $_POST['ku_kesadaran'] ?? '', ':ku_visus' => $_POST['ku_visus'] ?? '', ':ku_faring' => $_POST['ku_faring'] ?? '', ':ku_gigi_palsu' => $_POST['ku_gigi_palsu'] ?? '', ':ku_keterangan' => $_POST['ku_keterangan'] ?? '',
            ':fisik_tinggi' => $_POST['fisik_tinggi'] ?? '', ':fisik_berat' => $_POST['fisik_berat'] ?? '', ':fisik_td' => $_POST['fisik_td'] ?? '', ':fisik_nadi' => $_POST['fisik_nadi'] ?? '', ':fisik_rr' => $_POST['fisik_rr'] ?? '', ':fisik_suhu' => $_POST['fisik_suhu'] ?? '',
            ':fisik_paru' => $_POST['fisik_paru'] ?? '', ':fisik_jantung' => $_POST['fisik_jantung'] ?? '', ':fisik_abdomen' => $_POST['fisik_abdomen'] ?? '', ':fisik_ekstrimitas' => $_POST['fisik_ekstrimitas'] ?? '', ':fisik_neurologi' => $_POST['fisik_neurologi'] ?? '', ':fisik_lain' => $_POST['fisik_lain'] ?? '',
            ':lab_hb_ht' => $_POST['lab_hb_ht'] ?? '', ':lab_pt_aptt' => $_POST['lab_pt_aptt'] ?? '', ':lab_kehamilan' => $_POST['lab_kehamilan'] ?? '', ':lab_kalium' => $_POST['lab_kalium'] ?? '', ':lab_ureum' => $_POST['lab_ureum'] ?? '', ':lab_keterangan' => $_POST['lab_keterangan'] ?? '',
            ':lab_rontgen' => $_POST['lab_rontgen'] ?? '', ':lab_ekg' => $_POST['lab_ekg'] ?? '', ':lab_nacl' => $_POST['lab_nacl'] ?? '', ':lab_co2' => $_POST['lab_co2'] ?? '', ':lab_lain' => $_POST['lab_lain'] ?? '',
            ':masalah' => $_POST['masalah'] ?? '', ':asa' => $_POST['asa'] ?? '', ':saran' => $_POST['saran'] ?? '',
            ':ane_umum' => $_POST['ane_umum'] ?? '', ':au_iv' => $_POST['au_iv'] ?? '', ':au_sm' => $_POST['au_sm'] ?? '', ':au_lma' => $_POST['au_lma'] ?? '', ':au_ett' => $_POST['au_ett'] ?? '',
            ':ane_reg' => $_POST['ane_reg'] ?? '', ':ar_sab' => $_POST['ar_sab'] ?? '', ':ar_epi' => $_POST['ar_epi'] ?? '', ':ar_cse' => $_POST['ar_cse'] ?? '', ':ar_pnb' => $_POST['ar_pnb'] ?? '',
            ':ane_umum_reg' => $_POST['ane_umum_reg'] ?? '',
            ':puasa_jam' => $_POST['puasa_jam'] ?? null, ':puasa_tgl' => $_POST['puasa_tgl'] ?? null,
            ':signature_image' => $signature_image, ':nama_dokter_ttd' => $_POST['nama_dokter_ttd'] ?? ''
        ];

        $stmt->execute($params);
        $pesan_sukses = "Data Asesmen Pra Anestesi & Tanda Tangan berhasil disimpan ke Database.";
        
    } catch(PDOException $e) {
        $error_message = "Gagal menyimpan data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RM. 05.103/2025 - Asesmen Pra Anestesi</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

    <style>
        :root {
            --header-green: #00FFCC;
            --border-color: #000;
        }

        body {
            background-color: #525659;
            font-family: "Times New Roman", Times, serif;
            font-size: 11px;
            color: #000;
        }

        .page-a4 {
            background: white;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 10mm;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            border: 1px solid #ccc;
            box-sizing: border-box;
            position: relative;
        }

        .input-line {
            border: none;
            border-bottom: 1px dotted #000;
            background: transparent;
            font-family: inherit;
            font-size: inherit;
            padding: 0 2px;
            height: 16px;
            outline: none;
            width: 100%;
        }

        .input-line:focus {
            border-bottom: 1px solid blue;
        }

        input[type=date].input-line,
        input[type=time].input-line {
            width: 90px;
        }

        textarea.flat-textarea {
            width: 100%;
            border: none;
            resize: none;
            font-family: inherit;
            font-size: inherit;
            padding: 2px;
            background: transparent;
            overflow: hidden;
            min-height: 100%;
        }

        .header-green-bar {
            background-color: var(--header-green);
            border: 1px solid var(--border-color);
            font-weight: bold;
            padding: 4px 10px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }

        .border-box {
            border: 1px solid var(--border-color);
        }

        .section-title {
            font-weight: bold;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        table.dense-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.dense-table td {
            padding: 0 2px; /* Super compact padding */
            vertical-align: top;
        }

        input[type="radio"],
        input[type="checkbox"] {
            margin: 0 2px 0 5px;
            vertical-align: middle;
            transform: scale(0.85);
        }

        .yt-label {
            margin-right: 8px;
        }

        .signature-wrapper {
            position: relative;
            width: 100%;
            height: 70px; /* Reduced height to save space */
            border: 2px dashed #999;
            background-color: #fcfcfc;
            margin-top: 2px;
            cursor: crosshair;
        }

        .signature-wrapper canvas {
            display: block;
            width: 100%;
            height: 100%;
        }

        /* --- CSS KHUSUS PRINT --- */
        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }

            html, body {
                width: 210mm;
                height: 297mm;
            }

            body {
                background: none;
                margin: 0;
                padding: 0;
            }

            .page-a4 {
                margin: 0;
                border: none;
                box-shadow: none;
                padding: 0;
                width: 100%;
                /* ZOOM 90% AGAR MUAT PAS 2 HALAMAN */
                zoom: 1.01; 
                page-break-after: always;
                break-after: page;
            }

            .page-a4:last-of-type {
                page-break-after: auto;
                break-after: auto;
            }

            .no-print {
                display: none !important;
            }

            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .input-line {
                border-bottom: none;
            }

            /* HILANGKAN BORDER TTD SAAT PRINT */
            .signature-wrapper {
                border: none !important;
                background: transparent !important;
                box-shadow: none !important;
            }
            
            textarea.flat-textarea {
                height: auto !important;
                display: block !important;
                overflow: visible !important;
                white-space: pre-wrap;
            }

            .border-box[style*="height"] {
                height: auto !important;
                min-height: 40px;
            }
            
            /* Tighten spacing specifically for print */
            .mb-1, .mb-2, .mb-3 {
                margin-bottom: 2px !important;
            }
        }
    </style>
</head>

<body>

    <?php if ($pesan_sukses): ?>
        <div class="alert alert-success text-center no-print m-3">
            <?php echo $pesan_sukses; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger text-center no-print m-3">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" id="medicalForm">

        <div class="page-a4">
            <div class="header-green-bar" style="border-bottom: none;">
                <span>BLUD RSUD SANJIWANI GIANYAR</span>
                <span>RM. 05.103/2025</span>
            </div>

            <div class="border-box d-flex">
                <div style="width: 100px; border-right: 1px solid black; padding: 10px 10px;">
                    <div style="width: 80px; height: 80px; background: #eee; display: flex; align-items: center; justify-content: center; font-size: 10px; text-align: center;"><img src="logo.png" style="height: 80px;"></div>
                </div>
                <div style="flex-grow: 1; text-align: center; border-right: 1px solid black; display: flex; align-items: center; justify-content: center;">
                    <div style="font-weight: bold; font-size: 16px;">
                        ASESMEN<br>PRA ANESTESI
                    </div>
                </div>
                <div style="width: 280px; padding: 5px; font-size: 10px;">
                    <table class="dense-table">
                        <tr>
                            <td width="80">No. RM</td>
                            <td>: <input type="text" name="no_rm" value="<?php echo getValue('no_rm'); ?>" class="input-line" style="width: 140px;"></td>
                        </tr>
                        <tr>
                            <td>Nama</td>
                            <td>: <input type="text" name="nama_pasien" value="<?php echo getValue('nama_pasien'); ?>" class="input-line" style="width: 140px;"></td>
                        </tr>
                        <tr>
                            <td>Tgl.Lahir</td>
                            <td>: <input type="text" name="tgl_lahir" value="<?php echo getValue('tgl_lahir'); ?>" class="input-line" style="width: 140px;"></td>
                        </tr>
                        <tr>
                            <td>Jenis Kelamin</td>
                            <td>:
                                <label><input type="radio" name="jk_header" value="L" <?php echo getChecked('jk_header', 'L'); ?>> L</label>
                                <label class="ms-3"><input type="radio" name="jk_header" value="P" <?php echo getChecked('jk_header', 'P'); ?>> P</label>
                            </td>
                        </tr>
                        <tr>
                            <td>Regno</td>
                            <td>: <input type="text" name="regno" value="<?php echo getValue('regno'); ?>" class="input-line" style="width: 140px;"></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="border-box d-flex p-1 justify-content-between" style="border-top: none; font-size: 11px;">
                <div>Ruangan : <input type="text" name="ruangan" value="<?php echo getValue('ruangan'); ?>" class="input-line" style="width: 150px;"></div>
                <div>
                    Tanggal : <input type="date" name="tgl_asesmen" value="<?php echo getValue('tgl_asesmen'); ?>" class="input-line">
                    Jam : <input type="time" name="jam_asesmen" value="<?php echo getValue('jam_asesmen'); ?>" class="input-line"> WITA
                </div>
            </div>
            <div style="border: 1px solid #000; padding: 5px;">
                <div style="font-weight: bold; border-bottom: 1px solid black; margin-top: 5px;">
                    I. Data Diisi Oleh Penata Anestesi
                </div>

                <div class="d-flex justify-content-between mt-2 mb-2" style="font-size: 11px;">
                    <div>Umur: <input type="text" name="umur" value="<?php echo getValue('umur'); ?>" class="input-line" style="width: 40px;"></div>
                    <div>Jenis Kelamin: <label><input type="radio" name="jk_penata" value="L" <?php echo getChecked('jk_penata', 'L'); ?>> L</label> <label class="ms-2"><input type="radio" name="jk_penata" value="P" <?php echo getChecked('jk_penata', 'P'); ?>> P</label></div>
                    <div>Menikah: <label><input type="radio" name="menikah" value="Y" <?php echo getChecked('menikah', 'Y'); ?>> Y</label> <label class="ms-2"><input type="radio" name="menikah" value="T" <?php echo getChecked('menikah', 'T'); ?>> T</label></div>
                    <div>Pekerjaan: <input type="text" name="pekerjaan" value="<?php echo getValue('pekerjaan'); ?>" class="input-line" style="width: 120px;"></div>
                </div>

                <div class="section-title">KEBIASAAN</div>
                <table class="dense-table mb-1">
                    <tr>
                        <td width="50%">Merokok: <label><input type="radio" name="rokok" value="Y" <?php echo getChecked('rokok', 'Y'); ?>> Y</label> <label class="yt-label"><input type="radio" name="rokok" value="T" <?php echo getChecked('rokok', 'T'); ?>> T</label> Sebanyak: <input type="text" name="rokok_jumlah" value="<?php echo getValue('rokok_jumlah'); ?>" class="input-line" style="width: 80px;"></td>
                        <td>Kopi/teh/soda: <label><input type="radio" name="kopi" value="Y" <?php echo getChecked('kopi', 'Y'); ?>> Y</label> <label class="yt-label"><input type="radio" name="kopi" value="T" <?php echo getChecked('kopi', 'T'); ?>> T</label> Sebanyak: <input type="text" name="kopi_jumlah" value="<?php echo getValue('kopi_jumlah'); ?>" class="input-line" style="width: 80px;"></td>
                    </tr>
                    <tr>
                        <td>Alkohol: <label><input type="radio" name="alkohol" value="Y" <?php echo getChecked('alkohol', 'Y'); ?>> Y</label> <label class="yt-label"><input type="radio" name="alkohol" value="T" <?php echo getChecked('alkohol', 'T'); ?>> T</label> Sebanyak: <input type="text" name="alkohol_jumlah" value="<?php echo getValue('alkohol_jumlah'); ?>" class="input-line" style="width: 80px;"></td>
                        <td>Olahraga rutin: <label><input type="radio" name="olahraga" value="Y" <?php echo getChecked('olahraga', 'Y'); ?>> Y</label> <label class="yt-label"><input type="radio" name="olahraga" value="T" <?php echo getChecked('olahraga', 'T'); ?>> T</label> Sebanyak: <input type="text" name="olahraga_jumlah" value="<?php echo getValue('olahraga_jumlah'); ?>" class="input-line" style="width: 80px;"></td>
                    </tr>
                </table>

                <div class="section-title" style="margin-bottom: 0;">PENGOBATAN: (Sebutkan dosis atau jumlah pil per hari)</div>
                <div class="mb-1">
                    <label class="me-4"><input type="checkbox" name="obat_resep" value="Y" <?php echo getChecked('obat_resep', 'Y'); ?>> Obat Resep</label>
                    <label><input type="checkbox" name="obat_bebas" value="Y" <?php echo getChecked('obat_bebas', 'Y'); ?>> Obat bebas (vitamin, herbal): <input type="text" name="obat_bebas_ket" value="<?php echo getValue('obat_bebas_ket'); ?>" class="input-line" style="width: 200px;"></label>
                </div>
                <table class="dense-table mb-1">
                    <tr>
                        <td width="200">Penggunaan Aspirin rutin</td>
                        <td>: <label><input type="radio" name="aspirin" value="Y" <?php echo getChecked('aspirin', 'Y'); ?>> Y</label> <label class="yt-label"><input type="radio" name="aspirin" value="T" <?php echo getChecked('aspirin', 'T'); ?>> T</label> Dosis dan frekuensi: <input type="text" name="aspirin_dosis" value="<?php echo getValue('aspirin_dosis'); ?>" class="input-line" style="width: 150px;"></td>
                    </tr>
                    <tr>
                        <td>Obat anti sakit</td>
                        <td>: <label><input type="radio" name="painkiller" value="Y" <?php echo getChecked('painkiller', 'Y'); ?>> Y</label> <label class="yt-label"><input type="radio" name="painkiller" value="T" <?php echo getChecked('painkiller', 'T'); ?>> T</label> Dosis dan frekuensi: <input type="text" name="painkiller_dosis" value="<?php echo getValue('painkiller_dosis'); ?>" class="input-line" style="width: 150px;"></td>
                    </tr>
                    <tr>
                        <td>Injeksi steroid tahun-tahun terakhir</td>
                        <td>: <label><input type="radio" name="steroid" value="Y" <?php echo getChecked('steroid', 'Y'); ?>> Y</label> <label class="yt-label"><input type="radio" name="steroid" value="T" <?php echo getChecked('steroid', 'T'); ?>> T</label> Tanggal dan lokasi injeksi: <input type="text" name="steroid_ket" value="<?php echo getValue('steroid_ket'); ?>" class="input-line" style="width: 150px;"></td>
                    </tr>
                    <tr>
                        <td>Alergi obat</td>
                        <td>: <label><input type="radio" name="alergi_obat" value="Y" <?php echo getChecked('alergi_obat', 'Y'); ?>> Y</label> <label class="yt-label"><input type="radio" name="alergi_obat" value="T" <?php echo getChecked('alergi_obat', 'T'); ?>> T</label> Daftar obat dan tipe reaksi: <input type="text" name="alergi_obat_ket" value="<?php echo getValue('alergi_obat_ket'); ?>" class="input-line" style="width: 150px;"></td>
                    </tr>
                </table>
                <div class="mb-2">
                    Alergi lateks: <label><input type="radio" name="alt" value="Y" <?php echo getChecked('alt', 'Y'); ?>> Y</label> <label class="yt-label me-4"><input type="radio" name="alt" value="T" <?php echo getChecked('alt', 'T'); ?>> T</label>
                    Alergi plester: <label><input type="radio" name="alp" value="Y" <?php echo getChecked('alp', 'Y'); ?>> Y</label> <label class="yt-label me-4"><input type="radio" name="alp" value="T" <?php echo getChecked('alp', 'T'); ?>> T</label>
                    Alergi makanan: <label><input type="radio" name="alm" value="Y" <?php echo getChecked('alm', 'Y'); ?>> Y</label> <label class="yt-label"><input type="radio" name="alm" value="T" <?php echo getChecked('alm', 'T'); ?>> T</label>
                </div>

                <div class="section-title">RIWAYAT KELUARGA (Apakah keluarga mendapat permasalahan seperti di bawah ini):</div>
                <div class="row g-0">
                    <div class="col-6">
                        <table class="dense-table">
                            <?php
                            $keluarga_kiri = ['Perdarahan yang tidak normal', 'Pembekuan darah tidak normal', 'Permasalahan dalam pembiusan', 'Operasi jantung koroner', 'Diabetes'];
                            foreach ($keluarga_kiri as $k) {
                                $name = 'rk_' . str_replace(' ', '', $k);
                                echo "<tr><td>$k</td><td>: <label><input type='radio' name='$name' value='Y' ".getChecked($name, 'Y')."> Y</label> <label><input type='radio' name='$name' value='T' ".getChecked($name, 'T')."> T</label></td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="dense-table ps-2">
                            <?php
                            $keluarga_kanan = ['Serangan jantung', 'Hipertensi', 'Tuberkulosis', 'Penyakit berat lainnya'];
                            foreach ($keluarga_kanan as $k) {
                                $name = 'rk_' . str_replace(' ', '', $k);
                                echo "<tr><td width='160'>$k</td><td>: <label><input type='radio' name='$name' value='Y' ".getChecked($name, 'Y')."> Y</label> <label><input type='radio' name='$name' value='T' ".getChecked($name, 'T')."> T</label></td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                </div>
                <div class="mb-1">Jelaskan penyakit keluarga apa bila dijawab "Ya": <input type="text" name="rk_penjelasan_ya" value="<?php echo getValue('rk_penjelasan_ya'); ?>" class="input-line" style="width: 50%;"></div>

                <div class="section-title">KOMUNIKASI</div>
                <div class="mb-1">
                    Bahasa: <label class="me-3"><input type="checkbox" name="bahasa_indo" value="Indonesia" <?php echo getChecked('bahasa_indo', 'Indonesia'); ?>> Indonesia</label>
                    <label><input type="checkbox" name="bahasa_lain" value="Lainnya" <?php echo getChecked('bahasa_lain', 'Lainnya'); ?>> Lainnya: <input type="text" name="bahasa_lain_ket" value="<?php echo getValue('bahasa_lain_ket'); ?>" class="input-line" style="width: 150px;"></label>
                </div>
                <table class="dense-table mb-2">
                    <tr>
                        <td width="180">Gangguan Penglihatan/Buta</td>
                        <td>: <label><input type="radio" name="kom_mata" value="Y" <?php echo getChecked('kom_mata', 'Y'); ?>> Y</label> <label><input type="radio" name="kom_mata" value="T" <?php echo getChecked('kom_mata', 'T'); ?>> T</label></td>
                    </tr>
                    <tr>
                        <td>Gangguan Pendengaran/Tuli</td>
                        <td>: <label><input type="radio" name="kom_telinga" value="Y" <?php echo getChecked('kom_telinga', 'Y'); ?>> Y</label> <label><input type="radio" name="kom_telinga" value="T" <?php echo getChecked('kom_telinga', 'T'); ?>> T</label></td>
                    </tr>
                    <tr>
                        <td>Gangguan Bicara</td>
                        <td>: <label><input type="radio" name="kom_bicara" value="Y" <?php echo getChecked('kom_bicara', 'Y'); ?>> Y</label> <label><input type="radio" name="kom_bicara" value="T" <?php echo getChecked('kom_bicara', 'T'); ?>> T</label></td>
                    </tr>
                </table>

                <div class="section-title">RIWAYAT PENYAKIT PASIEN: Apakah pasien pernah menderita penyakit di bawah ini?</div>
                <div class="row g-0">
                    <div class="col-6">
                        <table class="dense-table">
                            <?php
                            $pasien_kiri = ['Perdarahan tidak normal', 'Pembekuan darah tidak normal', 'Sakit maag', 'Anemia', 'Sesak napas', 'Asma', 'Pingsan'];
                            foreach ($pasien_kiri as $p) {
                                $name = 'rp_' . str_replace(' ', '', $p);
                                echo "<tr><td>$p</td><td>: <label><input type='radio' name='$name' value='Y' ".getChecked($name, 'Y')."> Y</label> <label><input type='radio' name='$name' value='T' ".getChecked($name, 'T')."> T</label></td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="dense-table ps-2">
                            <?php
                            $pasien_kanan = ['Serangan jantung/Nyeri dada', 'Hepatitis/sakit kuning', 'Hipertensi', 'Sumbatan jalan nafas saat Tidur/Mengorok', 'Penyakit berat lainnya', 'Diabetes'];
                            foreach ($pasien_kanan as $p) {
                                $name = 'rp_' . str_replace('/', '', str_replace(' ', '', $p));
                                echo "<tr><td width='220'>$p</td><td>: <label><input type='radio' name='$name' value='Y' ".getChecked($name, 'Y')."> Y</label> <label><input type='radio' name='$name' value='T' ".getChecked($name, 'T')."> T</label></td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                </div>
                <div class="mb-2">Jelaskan penyakit yang dijawab "Ya" : <input type="text" name="rp_penjelasan_ya" value="<?php echo getValue('rp_penjelasan_ya'); ?>" class="input-line" style="width: 50%;"></div>

                <table class="dense-table mb-1">
                    <tr>
                        <td>Apakah pasien pernah mendapatkan transfusi darah?</td>
                        <td><label><input type="radio" name="transfusi" value="Y" <?php echo getChecked('transfusi', 'Y'); ?>> Y</label> <label class="yt-label"><input type="radio" name="transfusi" value="T" <?php echo getChecked('transfusi', 'T'); ?>> T</label> Bila ya, tahun berapa? <input type="text" name="transfusi_tahun" value="<?php echo getValue('transfusi_tahun'); ?>" class="input-line" style="width: 80px;"></td>
                    </tr>
                    <tr>
                        <td>Apakah pasien pernah diperiksa untuk diagnosis HIV?</td>
                        <td><label><input type="radio" name="hiv_check" value="Y" <?php echo getChecked('hiv_check', 'Y'); ?>> Y</label> <label class="yt-label"><input type="radio" name="hiv_check" value="T" <?php echo getChecked('hiv_check', 'T'); ?>> T</label> Bila ya, tahun berapa? <input type="text" name="hiv_tahun" value="<?php echo getValue('hiv_tahun'); ?>" class="input-line" style="width: 80px;"></td>
                    </tr>
                    <tr>
                        <td>Hasil pemeriksaan HIV :</td>
                        <td><label class="me-3"><input type="radio" name="hiv_res" value="Positif" <?php echo getChecked('hiv_res', 'Positif'); ?>> Positif</label> <label><input type="radio" name="hiv_res" value="Negatif" <?php echo getChecked('hiv_res', 'Negatif'); ?>> Negatif</label></td>
                    </tr>
                </table>

                <div class="mb-2">
                    Apakah pasien memakai ? <br>
                    Lensa kontak : <label><input type="radio" name="lk" value="Y" <?php echo getChecked('lk', 'Y'); ?>> Y</label> <label class="yt-label me-3"><input type="radio" name="lk" value="T" <?php echo getChecked('lk', 'T'); ?>> T</label>
                    Kacamata : <label><input type="radio" name="km" value="Y" <?php echo getChecked('km', 'Y'); ?>> Y</label> <label class="yt-label me-3"><input type="radio" name="km" value="T" <?php echo getChecked('km', 'T'); ?>> T</label>
                    Alat bantu dengar : <label><input type="radio" name="abd" value="Y" <?php echo getChecked('abd', 'Y'); ?>> Y</label> <label class="yt-label me-3"><input type="radio" name="abd" value="T" <?php echo getChecked('abd', 'T'); ?>> T</label>
                    Gigi palsu : <label><input type="radio" name="gp" value="Y" <?php echo getChecked('gp', 'Y'); ?>> Y</label> <label class="yt-label"><input type="radio" name="gp" value="T" <?php echo getChecked('gp', 'T'); ?>> T</label>
                </div>

                <div class="section-title">Riwayat operasi, tahun dan jenis operasi:</div>
                <div class="mb-2">
                    Jenis anestesi yang digunakan dan sebutkan komplikasi/reaksi yang dialami:
                    <table class="dense-table ps-3">
                        <tr>
                            <td width="220">Anestesia lokal-komplikasi/reaksi</td>
                            <td>: <input type="text" name="op_lokal_ket" value="<?php echo getValue('op_lokal_ket'); ?>" class="input-line" style="width: 80%;"></td>
                        </tr>
                        <tr>
                            <td>Anestesia regional-komplikasi/reaksi</td>
                            <td>: <input type="text" name="op_regional_ket" value="<?php echo getValue('op_regional_ket'); ?>" class="input-line" style="width: 80%;"></td>
                        </tr>
                        <tr>
                            <td>Anestesia umum-komplikasi/reaksi</td>
                            <td>: <input type="text" name="op_umum_ket" value="<?php echo getValue('op_umum_ket'); ?>" class="input-line" style="width: 80%;"></td>
                        </tr>
                    </table>
                    Tanggal terakhir kali periksa kesehatan ke dokter: <input type="date" name="terakhir_periksa_tgl" value="<?php echo getValue('terakhir_periksa_tgl'); ?>" class="input-line"> dimana: <input type="text" name="terakhir_periksa_tempat" value="<?php echo getValue('terakhir_periksa_tempat'); ?>" class="input-line" style="width: 200px;"><br>
                    Untuk penyakit gangguan apa: <input type="text" name="terakhir_periksa_penyakit" value="<?php echo getValue('terakhir_periksa_penyakit'); ?>" class="input-line" style="width: 80%;">
                </div>

                <div class="section-title">KHUSUS PASIEN PEREMPUAN :</div>
                <div class="d-flex justify-content-between">
                    <div>Jumlah kehamilan: <input type="text" name="jml_hamil" value="<?php echo getValue('jml_hamil'); ?>" class="input-line" style="width: 50px;"></div>
                    <div>Jumlah anak: <input type="text" name="jml_anak" value="<?php echo getValue('jml_anak'); ?>" class="input-line" style="width: 50px;"></div>
                    <div>Menstruasi: <input type="text" name="menstruasi" value="<?php echo getValue('menstruasi'); ?>" class="input-line" style="width: 100px;"></div>
                    <div>Menyusui: <label><input type="radio" name="menyusui" value="Y" <?php echo getChecked('menyusui', 'Y'); ?>> Y</label> <label><input type="radio" name="menyusui" value="T" <?php echo getChecked('menyusui', 'T'); ?>> T</label></div>
                </div>
            </div>
        </div>
        
        <div class="page-a4">
            <div class="header-green-bar">
                <span>BLUD RSUD SANJIWANI GIANYAR</span>
                <span>RM. 05.103/2025</span>
            </div>
            <div style="font-weight: bold; border-bottom: 1px solid black;">
                II. Data Diisi Oleh Dokter Anestesi
            </div>
            <div style="border: 1px solid #000; padding: 5px;">
                <div class="section-title mt-0">KAJIAN SISTEM</div>
                <div class="mb-1">Anamnesis Singkat:</div>
                <div class="border-box mb-1" style="height: 60px;"> <textarea name="anamnesis" class="flat-textarea" rows="2"><?php echo getValue('anamnesis'); ?></textarea>
                </div>

                <div class="row g-0 mb-1">
                    <div class="col-6">
                        <table class="dense-table">
                            <?php
                            $dok_kiri = ['Hilangnya gigi', 'Masalah mobilisasi leher', 'Leher pendek', 'Batuk', 'Sesak nafas', 'Baru saja menderita infeksi saluran nafas atas', 'Periode menstruasi tidak normal', 'Stroke'];
                            foreach ($dok_kiri as $d) {
                                $name = 'dok_' . str_replace(' ', '', $d);
                                echo "<tr><td width='180'>$d</td><td>: <label><input type='radio' name='$name' value='Y' ".getChecked($name, 'Y')."> Y</label> <label><input type='radio' name='$name' value='T' ".getChecked($name, 'T')."> T</label></td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="dense-table ps-2">
                            <?php
                            $dok_kanan = ['Sakit dada', 'Denyut jantung tidak normal', 'Muntah', 'Susah kencing', 'Kejang', 'Sedang hamil', 'Pingsan', 'Obesitas'];
                            foreach ($dok_kanan as $d) {
                                $name = 'dok_' . str_replace(' ', '', $d);
                                echo "<tr><td width='160'>$d</td><td>: <label><input type='radio' name='$name' value='Y' ".getChecked($name, 'Y')."> Y</label> <label><input type='radio' name='$name' value='T' ".getChecked($name, 'T')."> T</label></td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                </div>
                <div class="mb-1">Keterangan: <input type="text" name="dok_keterangan" value="<?php echo getValue('dok_keterangan'); ?>" class="input-line" style="width: 80%;"></div>
                <hr style="border-top: 1px solid black; opacity: 1; margin: 2px 0;">

                <div class="section-title">KEADAAN UMUM</div>
                <div class="d-flex justify-content-between mb-1">
                    <div style="width: 25%;">Kesadaran: <input type="text" name="ku_kesadaran" value="<?php echo getValue('ku_kesadaran'); ?>" class="input-line" style="width: 60%;"></div>
                    <div style="width: 25%;">Visus: <input type="text" name="ku_visus" value="<?php echo getValue('ku_visus'); ?>" class="input-line" style="width: 60%;"></div>
                    <div style="width: 25%;">Faring: <input type="text" name="ku_faring" value="<?php echo getValue('ku_faring'); ?>" class="input-line" style="width: 60%;"></div>
                    <div style="width: 25%;">Gigi palsu: <input type="text" name="ku_gigi_palsu" value="<?php echo getValue('ku_gigi_palsu'); ?>" class="input-line" style="width: 60%;"></div>
                </div>
                <div class="mb-1">Keterangan: <input type="text" name="ku_keterangan" value="<?php echo getValue('ku_keterangan'); ?>" class="input-line" style="width: 80%;"></div>

                <div class="section-title">PEMERIKSAAN FISIK</div>
                <div class="mb-1">
                    Tinggi: <input type="text" name="fisik_tinggi" value="<?php echo getValue('fisik_tinggi'); ?>" class="input-line" style="width: 30px;"> cm &nbsp;
                    Berat: <input type="text" name="fisik_berat" value="<?php echo getValue('fisik_berat'); ?>" class="input-line" style="width: 30px;"> kg &nbsp;
                    TD: <input type="text" name="fisik_td" value="<?php echo getValue('fisik_td'); ?>" class="input-line" style="width: 60px;"> mmHg &nbsp;
                    Nadi: <input type="text" name="fisik_nadi" value="<?php echo getValue('fisik_nadi'); ?>" class="input-line" style="width: 40px;"> x/mnt &nbsp;
                    RR: <input type="text" name="fisik_rr" value="<?php echo getValue('fisik_rr'); ?>" class="input-line" style="width: 40px;"> x/mnt &nbsp;
                    Suhu: <input type="text" name="fisik_suhu" value="<?php echo getValue('fisik_suhu'); ?>" class="input-line" style="width: 40px;"> °C
                </div>
                <table class="dense-table mb-1">
                    <tr>
                        <td width="150">Paru-paru</td>
                        <td>: <input type="text" name="fisik_paru" value="<?php echo getValue('fisik_paru'); ?>" class="input-line" style="width: 80%;"></td>
                    </tr>
                    <tr>
                        <td>Jantung</td>
                        <td>: <input type="text" name="fisik_jantung" value="<?php echo getValue('fisik_jantung'); ?>" class="input-line" style="width: 80%;"></td>
                    </tr>
                    <tr>
                        <td>Abdomen</td>
                        <td>: <input type="text" name="fisik_abdomen" value="<?php echo getValue('fisik_abdomen'); ?>" class="input-line" style="width: 80%;"></td>
                    </tr>
                    <tr>
                        <td>Ekstrimitas</td>
                        <td>: <input type="text" name="fisik_ekstrimitas" value="<?php echo getValue('fisik_ekstrimitas'); ?>" class="input-line" style="width: 80%;"></td>
                    </tr>
                    <tr>
                        <td>Neurologi (bila ada)</td>
                        <td>: <input type="text" name="fisik_neurologi" value="<?php echo getValue('fisik_neurologi'); ?>" class="input-line" style="width: 80%;"></td>
                    </tr>
                    <tr>
                        <td>Keterangan lainnya</td>
                        <td>: <input type="text" name="fisik_lain" value="<?php echo getValue('fisik_lain'); ?>" class="input-line" style="width: 80%;"></td>
                    </tr>
                </table>

                <div class="section-title">LABORATORIUM (bila tersedia)</div>
                <div class="row g-0">
                    <div class="col-6">
                        <table class="dense-table">
                            <tr>
                                <td width="80">Hb/Ht</td>
                                <td>: <input type="text" name="lab_hb_ht" value="<?php echo getValue('lab_hb_ht'); ?>" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>PT/APTT</td>
                                <td>: <input type="text" name="lab_pt_aptt" value="<?php echo getValue('lab_pt_aptt'); ?>" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>Tes kehamilan</td>
                                <td>: <input type="text" name="lab_kehamilan" value="<?php echo getValue('lab_kehamilan'); ?>" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>Kalium</td>
                                <td>: <input type="text" name="lab_kalium" value="<?php echo getValue('lab_kalium'); ?>" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>Ureum</td>
                                <td>: <input type="text" name="lab_ureum" value="<?php echo getValue('lab_ureum'); ?>" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>Keterangan</td>
                                <td>: <input type="text" name="lab_keterangan" value="<?php echo getValue('lab_keterangan'); ?>" class="input-line" style="width: 80%;"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="dense-table ps-3">
                            <tr>
                                <td width="80">Rontgen dada</td>
                                <td>: <input type="text" name="lab_rontgen" value="<?php echo getValue('lab_rontgen'); ?>" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>EKG</td>
                                <td>: <input type="text" name="lab_ekg" value="<?php echo getValue('lab_ekg'); ?>" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>Na/Cl</td>
                                <td>: <input type="text" name="lab_nacl" value="<?php echo getValue('lab_nacl'); ?>" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>CO2</td>
                                <td>: <input type="text" name="lab_co2" value="<?php echo getValue('lab_co2'); ?>" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>Lain-lain</td>
                                <td>: <input type="text" name="lab_lain" value="<?php echo getValue('lab_lain'); ?>" class="input-line" style="width: 80%;"></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="section-title mt-2">DAFTAR MASALAH :</div>
                <div class="border-box mb-1" style="height: 40px;">
                    <textarea name="masalah" class="flat-textarea" rows="2"><?php echo getValue('masalah'); ?></textarea>
                </div>

                <div class="section-title">DIAGNOSIS :</div>
                <div class="mb-1">
                    Klasifikasi berdasarkan ASA :
                    <select name="asa" style="font-family: inherit; font-size: inherit; padding: 1px;">
                        <option value="">-- Pilih ASA --</option>
                        <option value="ASA 1" <?php echo getChecked('asa', 'ASA 1') == 'checked' ? 'selected' : ''; ?>>ASA 1</option>
                        <option value="ASA 2" <?php echo getChecked('asa', 'ASA 2') == 'checked' ? 'selected' : ''; ?>>ASA 2</option>
                        <option value="ASA 3" <?php echo getChecked('asa', 'ASA 3') == 'checked' ? 'selected' : ''; ?>>ASA 3</option>
                        <option value="ASA 4" <?php echo getChecked('asa', 'ASA 4') == 'checked' ? 'selected' : ''; ?>>ASA 4</option>
                        <option value="ASA 5" <?php echo getChecked('asa', 'ASA 5') == 'checked' ? 'selected' : ''; ?>>ASA 5</option>
                        <option value="E" <?php echo getChecked('asa', 'E') == 'checked' ? 'selected' : ''; ?>>E (Emergency)</option>
                    </select>
                </div>

                <div class="section-title">SARAN :</div>
                <div class="border-box mb-1" style="height: 40px;">
                    <textarea name="saran" class="flat-textarea" rows="2"><?php echo getValue('saran'); ?></textarea>
                </div>

                <div class="section-title">REKOMENDASI ANESTESI:</div>
                <table class="dense-table mb-2 ps-2">
                    <tr>
                        <td width="130"><label><input type="checkbox" name="ane_umum" value="Y" <?php echo getChecked('ane_umum', 'Y'); ?>> Anestesi Umum :</label></td>
                        <td>
                            <label class="me-3"><input type="checkbox" name="au_iv" value="Y" <?php echo getChecked('au_iv', 'Y'); ?>> Intravena</label>
                            <label class="me-3"><input type="checkbox" name="au_sm" value="Y" <?php echo getChecked('au_sm', 'Y'); ?>> Sungkup Muka</label>
                            <label class="me-3"><input type="checkbox" name="au_lma" value="Y" <?php echo getChecked('au_lma', 'Y'); ?>> LMA</label>
                            <label><input type="checkbox" name="au_ett" value="Y" <?php echo getChecked('au_ett', 'Y'); ?>> ETT</label>
                        </td>
                    </tr>
                    <tr>
                        <td><label><input type="checkbox" name="ane_reg" value="Y" <?php echo getChecked('ane_reg', 'Y'); ?>> Regional Anestesi :</label></td>
                        <td>
                            <label class="me-3"><input type="checkbox" name="ar_sab" value="Y" <?php echo getChecked('ar_sab', 'Y'); ?>> SAB</label>
                            <label class="me-3"><input type="checkbox" name="ar_epi" value="Y" <?php echo getChecked('ar_epi', 'Y'); ?>> Epidural</label>
                            <label class="me-3"><input type="checkbox" name="ar_cse" value="Y" <?php echo getChecked('ar_cse', 'Y'); ?>> CSE</label>
                            <label><input type="checkbox" name="ar_pnb" value="Y" <?php echo getChecked('ar_pnb', 'Y'); ?>> PNB</label>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><label><input type="checkbox" name="ane_umum_reg" value="Y" <?php echo getChecked('ane_umum_reg', 'Y'); ?>> Anestesi Umum + Regional Anestesi</label></td>
                    </tr>
                </table>

                <div class="mb-2">
                    Puasa mulai : Jam <input type="time" name="puasa_jam" value="<?php echo getValue('puasa_jam'); ?>" class="input-line"> Wita &nbsp;&nbsp;
                    Tanggal <input type="date" name="puasa_tgl" value="<?php echo getValue('puasa_tgl'); ?>" class="input-line">
                </div>

                <div class="row">
                    <div class="col-6 offset-6 text-center" style="width: 250px; margin-left: auto;">
                        <strong>Tanda Tangan Dokter</strong>

                        <div class="signature-wrapper">
                            <?php if (!empty($signature_preview)): ?>
                                <img src="<?php echo $signature_preview; ?>" style="width: 100%; height: 100%; object-fit: contain;">
                                <input type="hidden" name="signature_image" value="<?php echo $signature_preview; ?>">
                            <?php else: ?>
                                <canvas id="signature-pad"></canvas>
                                <input type="hidden" name="signature_image" id="signature-image-input">
                            <?php endif; ?>
                        </div>

                        <?php if (empty($signature_preview)): ?>
                            <button type="button" class="btn btn-outline-danger btn-sm py-0 mt-1 no-print" id="clear-signature" style="font-size: 10px;">Hapus / Ulangi</button>
                        <?php endif; ?>

                        <div class="mt-2">
                            ( <input type="text" name="nama_dokter_ttd" value="<?php echo getValue('nama_dokter_ttd'); ?>" class="input-line text-center" placeholder="Nama Dokter" style="width: 180px;"> )
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-2 fst-italic" style="font-size: 10px;">
                Catatan : isilah tanda (v) sesuai dengan pilihan dan coret yang tidak perlu pada tanda (*)
            </div>
        </div>

        <div class="text-center mb-5 no-print">
            <button type="submit" class="btn btn-primary btn-lg px-5">Simpan Data Rekam Medis</button>
            <?php if (!empty($pesan_sukses)): ?>
                <button type="button" onclick="window.print()" class="btn btn-success btn-lg px-5 ms-2">Cetak Formulir</button>
            <?php endif; ?>
        </div>

    </form>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var canvas = document.getElementById('signature-pad');
            if (canvas) {
                var signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255, 255, 255, 0)', 
                    penColor: 'rgb(0, 0, 0)'
                });

                function resizeCanvas() {
                    var ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);
                }
                window.addEventListener("resize", resizeCanvas);
                resizeCanvas();

                document.getElementById('clear-signature').addEventListener('click', function() {
                    signaturePad.clear();
                });

                document.getElementById('medicalForm').addEventListener('submit', function(e) {
                    if (!signaturePad.isEmpty()) {
                        document.getElementById('signature-image-input').value = signaturePad.toDataURL('image/png');
                    }
                });
            }

            const textareas = document.getElementsByTagName("textarea");
            for (let i = 0; i < textareas.length; i++) {
                textareas[i].setAttribute("style", "height:" + (textareas[i].scrollHeight) + "px;overflow-y:hidden;");
                textareas[i].addEventListener("input", OnInput, false);
            }

            function OnInput() {
                this.style.height = 0;
                this.style.height = (this.scrollHeight) + "px";
            }
        });
    </script>

</body>
</html>