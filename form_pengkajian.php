<?php
session_start();
// Koneksi ke database utama (untuk form)
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

// Inisialisasi variabel
$success_message = '';
$error_message = '';
$data_pasien = null;

// Ambil data dari parameter URL jika ada
$nama_url = $_GET['nama'] ?? '';
$tgl_lahir_url = $_GET['tgl_lahir'] ?? '';
$no_rm_url = $_GET['no_rm'] ?? '';
$reg_no_url = $_GET['reg_no'] ?? '';

// Proses penyimpanan data ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Data Pasien - ambil dari input atau dari parameter URL
        $nama_pasien = $_POST['nama'] ?? $nama_url;
        $tgl_lahir = $_POST['tgl_lahir'] ?? $tgl_lahir_url;
        $no_rm = $_POST['no_rm'] ?? $no_rm_url;
        $reg_no = $_POST['reg_no'] ?? $reg_no_url;
        
        // Penilaian Nyeri
        $nyeri_lokasi = $_POST['lokasi'] ?? '';
        $nyeri_intensitas = $_POST['intensitas'] ?? '';
        
        // Tanda-tanda Vital
        $keadaan_umum = isset($_POST['keadaan_umum']) ? implode(',', (array)$_POST['keadaan_umum']) : '';
        $gcs_e = $_POST['E'] ?? '';
        $gcs_v = $_POST['V'] ?? '';
        $gcs_m = $_POST['M'] ?? '';
        $tensi = $_POST['tensi'] ?? '';
        $nadi = $_POST['nadi'] ?? '';
        $respirasi = $_POST['respirasi'] ?? '';
        $suhu = $_POST['suhu'] ?? '';
        
        // Pemeriksaan Fisik - Mata
        $mata_anemi = isset($_POST['anemi']) ? 1 : 0;
        $mata_ikterus = isset($_POST['ikterus']) ? 1 : 0;
        $mata_reflex_pupil = isset($_POST['reflex_pupil']) ? 1 : 0;
        $mata_oedema_palpebra = isset($_POST['oedema_palpebra']) ? 1 : 0;
        
        // Pemeriksaan Fisik - THT
        $tht_tonsil = isset($_POST['tonsil']) ? 1 : 0;
        $tht_tonsil_keterangan = $_POST['tonsil_keterangan'] ?? '';
        $tht_pharing = isset($_POST['pharing']) ? 1 : 0;
        $tht_pharing_keterangan = $_POST['pharing_keterangan'] ?? '';
        $tht_lidah = isset($_POST['lidah']) ? 1 : 0;
        $tht_lidah_keterangan = $_POST['lidah_keterangan'] ?? '';
        $tht_bibir = isset($_POST['bibir']) ? 1 : 0;
        $tht_bibir_keterangan = $_POST['bibir_keterangan'] ?? '';
        
        // Pemeriksaan Fisik - Leher
        $leher_jvp = isset($_POST['jvp']) ? 1 : 0;
        $leher_jvp_keterangan = $_POST['jvp_keterangan'] ?? '';
        $leher_pembesar_kelenjar = isset($_POST['pembesar_kelenjar']) ? 1 : 0;
        $leher_pembesar_kelenjar_keterangan = $_POST['pembesar_kelenjar_keterangan'] ?? '';
        $leher_kaku_kuduk = isset($_POST['kaku_kuduk']) ? 1 : 0;
        
        // Pemeriksaan Fisik - Thoraks
        $thoraks_simetris = isset($_POST['simetris']) ? 1 : 0;
        $thoraks_simetris_keterangan = $_POST['simetris_keterangan'] ?? '';
        
        // Cardiovaskuler
        $cardiovaskuler_s1s2 = isset($_POST['cardiovaskuler']) ? 1 : 0;
        $cardiovaskuler_s1s2_keterangan = $_POST['cardiovaskuler_keterangan'] ?? '';
        $cardiovaskuler_murmur = isset($_POST['murmur']) ? 1 : 0;
        $cardiovaskuler_murmur_keterangan = $_POST['murmur_keterangan'] ?? '';
        $cardiovaskuler_lain_lain = isset($_POST['c_lain-lain']) ? 1 : 0;
        $cardiovaskuler_lain_lain_keterangan = $_POST['lain-lain_keterangan_c'] ?? '';
        
        // Pulmo
        $pulmo_suara_nafas = isset($_POST['suara_nafas']) ? 1 : 0;
        $pulmo_suara_nafas_keterangan = $_POST['suara_nafas_keterangan'] ?? '';
        $pulmo_ronchi = isset($_POST['ronchi']) ? 1 : 0;
        $pulmo_ronchi_keterangan = $_POST['ronchi_keterangan'] ?? '';
        $pulmo_wheezing = isset($_POST['wheezing']) ? 1 : 0;
        $pulmo_wheezing_keterangan = $_POST['wheezing_keterangan'] ?? '';
        $pulmo_lain_lain = isset($_POST['lain-lain']) ? 1 : 0;
        $pulmo_lain_lain_keterangan = $_POST['lain-lain_keterangan'] ?? '';
        
        // Anatomi Paru-Paru
        $paru_trakea_keterangan = $_POST['paru_trakea'] ?? '';
        $paru_bronkus_keterangan = $_POST['paru_bronkus'] ?? '';
        $paru_bronkiolus_keterangan = $_POST['paru_bronkiolus'] ?? '';
        $paru_alveoli_keterangan = $_POST['paru_alveoli'] ?? '';
        $paru_pleura_keterangan = $_POST['paru_pleura'] ?? '';
        $paru_diafragma_keterangan = $_POST['paru_diafragma'] ?? '';
        
        // Abdomen
        $abdomen_distensi = isset($_POST['distensi']) ? 1 : 0;
        $abdomen_meteorismus = isset($_POST['meteorismus']) ? 1 : 0;
        $abdomen_peristaltic = isset($_POST['peristaltic']) ? 1 : 0;
        $abdomen_normal = isset($_POST['normal']) ? 1 : 0;
        $abdomen_meningkat = isset($_POST['meningkat']) ? 1 : 0;
        $abdomen_menurun = isset($_POST['menurun']) ? 1 : 0;
        $abdomen_ascites = isset($_POST['ascites']) ? 1 : 0;
        $abdomen_nyeri_tekan = isset($_POST['nyeri_tekan']) ? 1 : 0;
        $abdomen_nyeri_tekan_lokasi = $_POST['nyeri_tekan_lokasi'] ?? '';
        $abdomen_hepar = $_POST['hepar'] ?? '';
        $abdomen_lien = $_POST['lien'] ?? '';
        
        // Extremitas
        $extremitas_hangat_dingin = isset($_POST['hangat_dingin']) ? 1 : 0;
        $extremitas_edama = isset($_POST['edama']) ? 1 : 0;
        $extremitas_edama_lokasi = $_POST['edama_lokasi'] ?? '';
        
        // Hasil Pemeriksaan Penunjang
        $lab_laboratorium = $_POST['laboratorium'] ?? '';
        $lab_ekg = $_POST['ekg'] ?? '';
        $lab_xray = $_POST['x-ray'] ?? '';
        
        // Diagnosa dan Terapi
        $diagnosa_kerja = $_POST['diagnosa_kerja'] ?? '';
        $terapi_tindakan = $_POST['terapi_tindakan'] ?? '';
        
        // Tanda Tangan
        $perawat_nama = $_POST['perawat'] ?? '';
        $dokter_nama = $_POST['dokter'] ?? '';
        $tgl_kunjungan = $_POST['tgl'] ?? date('Y-m-d');
        
        // Query INSERT dengan named parameters
        $sql = "INSERT INTO pengkajian_hemodialisa (
            nama_pasien, tgl_lahir, no_rm, reg_no, nyeri_lokasi, nyeri_intensitas, keadaan_umum,
            gcs_e, gcs_v, gcs_m, tensi, nadi, respirasi, suhu,
            mata_anemi, mata_ikterus, mata_reflex_pupil, mata_oedema_palpebra,
            tht_tonsil, tht_tonsil_keterangan, tht_pharing, tht_pharing_keterangan,
            tht_lidah, tht_lidah_keterangan, tht_bibir, tht_bibir_keterangan,
            leher_jvp, leher_jvp_keterangan, leher_pembesar_kelenjar, leher_pembesar_kelenjar_keterangan, leher_kaku_kuduk,
            thoraks_simetris, thoraks_simetris_keterangan,
            cardiovaskuler_s1s2, cardiovaskuler_s1s2_keterangan, cardiovaskuler_murmur, cardiovaskuler_murmur_keterangan,
            cardiovaskuler_lain_lain, cardiovaskuler_lain_lain_keterangan,
            pulmo_suara_nafas, pulmo_suara_nafas_keterangan, pulmo_ronchi, pulmo_ronchi_keterangan,
            pulmo_wheezing, pulmo_wheezing_keterangan, pulmo_lain_lain, pulmo_lain_lain_keterangan,
            paru_trakea_keterangan, paru_bronkus_keterangan, paru_bronkiolus_keterangan,
            paru_alveoli_keterangan, paru_pleura_keterangan, paru_diafragma_keterangan,
            abdomen_distensi, abdomen_meteorismus, abdomen_peristaltic, abdomen_normal,
            abdomen_meningkat, abdomen_menurun, abdomen_ascites, abdomen_nyeri_tekan,
            abdomen_nyeri_tekan_lokasi, abdomen_hepar, abdomen_lien,
            extremitas_hangat_dingin, extremitas_edama, extremitas_edama_lokasi,
            lab_laboratorium, lab_ekg, lab_xray, diagnosa_kerja, terapi_tindakan,
            perawat_nama, dokter_nama, tgl_kunjungan
        ) VALUES (
            :nama_pasien, :tgl_lahir, :no_rm, :reg_no, :nyeri_lokasi, :nyeri_intensitas, :keadaan_umum,
            :gcs_e, :gcs_v, :gcs_m, :tensi, :nadi, :respirasi, :suhu,
            :mata_anemi, :mata_ikterus, :mata_reflex_pupil, :mata_oedema_palpebra,
            :tht_tonsil, :tht_tonsil_keterangan, :tht_pharing, :tht_pharing_keterangan,
            :tht_lidah, :tht_lidah_keterangan, :tht_bibir, :tht_bibir_keterangan,
            :leher_jvp, :leher_jvp_keterangan, :leher_pembesar_kelenjar, :leher_pembesar_kelenjar_keterangan, :leher_kaku_kuduk,
            :thoraks_simetris, :thoraks_simetris_keterangan,
            :cardiovaskuler_s1s2, :cardiovaskuler_s1s2_keterangan, :cardiovaskuler_murmur, :cardiovaskuler_murmur_keterangan,
            :cardiovaskuler_lain_lain, :cardiovaskuler_lain_lain_keterangan,
            :pulmo_suara_nafas, :pulmo_suara_nafas_keterangan, :pulmo_ronchi, :pulmo_ronchi_keterangan,
            :pulmo_wheezing, :pulmo_wheezing_keterangan, :pulmo_lain_lain, :pulmo_lain_lain_keterangan,
            :paru_trakea_keterangan, :paru_bronkus_keterangan, :paru_bronkiolus_keterangan,
            :paru_alveoli_keterangan, :paru_pleura_keterangan, :paru_diafragma_keterangan,
            :abdomen_distensi, :abdomen_meteorismus, :abdomen_peristaltic, :abdomen_normal,
            :abdomen_meningkat, :abdomen_menurun, :abdomen_ascites, :abdomen_nyeri_tekan,
            :abdomen_nyeri_tekan_lokasi, :abdomen_hepar, :abdomen_lien,
            :extremitas_hangat_dingin, :extremitas_edama, :extremitas_edama_lokasi,
            :lab_laboratorium, :lab_ekg, :lab_xray, :diagnosa_kerja, :terapi_tindakan,
            :perawat_nama, :dokter_nama, :tgl_kunjungan
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nama_pasien' => $nama_pasien,
            ':tgl_lahir' => $tgl_lahir,
            ':no_rm' => $no_rm,
            ':reg_no' => $reg_no,
            ':nyeri_lokasi' => $nyeri_lokasi,
            ':nyeri_intensitas' => $nyeri_intensitas,
            ':keadaan_umum' => $keadaan_umum,
            ':gcs_e' => $gcs_e,
            ':gcs_v' => $gcs_v,
            ':gcs_m' => $gcs_m,
            ':tensi' => $tensi,
            ':nadi' => $nadi,
            ':respirasi' => $respirasi,
            ':suhu' => $suhu,
            ':mata_anemi' => $mata_anemi,
            ':mata_ikterus' => $mata_ikterus,
            ':mata_reflex_pupil' => $mata_reflex_pupil,
            ':mata_oedema_palpebra' => $mata_oedema_palpebra,
            ':tht_tonsil' => $tht_tonsil,
            ':tht_tonsil_keterangan' => $tht_tonsil_keterangan,
            ':tht_pharing' => $tht_pharing,
            ':tht_pharing_keterangan' => $tht_pharing_keterangan,
            ':tht_lidah' => $tht_lidah,
            ':tht_lidah_keterangan' => $tht_lidah_keterangan,
            ':tht_bibir' => $tht_bibir,
            ':tht_bibir_keterangan' => $tht_bibir_keterangan,
            ':leher_jvp' => $leher_jvp,
            ':leher_jvp_keterangan' => $leher_jvp_keterangan,
            ':leher_pembesar_kelenjar' => $leher_pembesar_kelenjar,
            ':leher_pembesar_kelenjar_keterangan' => $leher_pembesar_kelenjar_keterangan,
            ':leher_kaku_kuduk' => $leher_kaku_kuduk,
            ':thoraks_simetris' => $thoraks_simetris,
            ':thoraks_simetris_keterangan' => $thoraks_simetris_keterangan,
            ':cardiovaskuler_s1s2' => $cardiovaskuler_s1s2,
            ':cardiovaskuler_s1s2_keterangan' => $cardiovaskuler_s1s2_keterangan,
            ':cardiovaskuler_murmur' => $cardiovaskuler_murmur,
            ':cardiovaskuler_murmur_keterangan' => $cardiovaskuler_murmur_keterangan,
            ':cardiovaskuler_lain_lain' => $cardiovaskuler_lain_lain,
            ':cardiovaskuler_lain_lain_keterangan' => $cardiovaskuler_lain_lain_keterangan,
            ':pulmo_suara_nafas' => $pulmo_suara_nafas,
            ':pulmo_suara_nafas_keterangan' => $pulmo_suara_nafas_keterangan,
            ':pulmo_ronchi' => $pulmo_ronchi,
            ':pulmo_ronchi_keterangan' => $pulmo_ronchi_keterangan,
            ':pulmo_wheezing' => $pulmo_wheezing,
            ':pulmo_wheezing_keterangan' => $pulmo_wheezing_keterangan,
            ':pulmo_lain_lain' => $pulmo_lain_lain,
            ':pulmo_lain_lain_keterangan' => $pulmo_lain_lain_keterangan,
            ':paru_trakea_keterangan' => $paru_trakea_keterangan,
            ':paru_bronkus_keterangan' => $paru_bronkus_keterangan,
            ':paru_bronkiolus_keterangan' => $paru_bronkiolus_keterangan,
            ':paru_alveoli_keterangan' => $paru_alveoli_keterangan,
            ':paru_pleura_keterangan' => $paru_pleura_keterangan,
            ':paru_diafragma_keterangan' => $paru_diafragma_keterangan,
            ':abdomen_distensi' => $abdomen_distensi,
            ':abdomen_meteorismus' => $abdomen_meteorismus,
            ':abdomen_peristaltic' => $abdomen_peristaltic,
            ':abdomen_normal' => $abdomen_normal,
            ':abdomen_meningkat' => $abdomen_meningkat,
            ':abdomen_menurun' => $abdomen_menurun,
            ':abdomen_ascites' => $abdomen_ascites,
            ':abdomen_nyeri_tekan' => $abdomen_nyeri_tekan,
            ':abdomen_nyeri_tekan_lokasi' => $abdomen_nyeri_tekan_lokasi,
            ':abdomen_hepar' => $abdomen_hepar,
            ':abdomen_lien' => $abdomen_lien,
            ':extremitas_hangat_dingin' => $extremitas_hangat_dingin,
            ':extremitas_edama' => $extremitas_edama,
            ':extremitas_edama_lokasi' => $extremitas_edama_lokasi,
            ':lab_laboratorium' => $lab_laboratorium,
            ':lab_ekg' => $lab_ekg,
            ':lab_xray' => $lab_xray,
            ':diagnosa_kerja' => $diagnosa_kerja,
            ':terapi_tindakan' => $terapi_tindakan,
            ':perawat_nama' => $perawat_nama,
            ':dokter_nama' => $dokter_nama,
            ':tgl_kunjungan' => $tgl_kunjungan
        ]);

        $_SESSION['success_message'] = "Data berhasil disimpan!";

          header("Location: form_pengkajian.php");
        exit;
        
        
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Variabel untuk form - prioritaskan dari POST, lalu dari URL, lalu kosong
$nama = h($_POST['nama'] ?? $nama_url ?? '');
$tgl_lahir = h($_POST['tgl_lahir'] ?? $tgl_lahir_url ?? '');
$no_rm = h($_POST['no_rm'] ?? $no_rm_url ?? '');
$reg_no = h($_POST['reg_no'] ?? $reg_no_url ?? '');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Form Pengkajian Hemodialisa</title>
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
        justify-content: space-between;
    }

    .info .item {
        display: flex;
        align-items: center;
        gap: 5px;
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

    input,
    textarea {
        background: transparent;
        border: none;
        border-bottom: 1px solid #000;
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

    /* Gaya untuk gambar paru-paru interaktif */
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
        transform: scale(1.1);
        border-color: #0077ff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
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

    /* Modal untuk gambar paru-paru yang diperbesar */
    .lung-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .lung-modal.active {
        display: flex;
    }

    .lung-modal-content {
        background: white;
        border-radius: 10px;
        width: 90%;
        max-width: 1000px;
        max-height: 90vh;
        overflow: auto;
        padding: 20px;
        position: relative;
    }

    .lung-modal-close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 24px;
        cursor: pointer;
        color: #666;
        background: none;
        border: none;
    }

    .lung-modal-close:hover {
        color: #000;
    }

    .lung-detail-container {
        display: flex;
        gap: 20px;
        margin-top: 15px;
    }

    .lung-detail-image {
        flex: 1;
        min-width: 400px;
        position: relative;
    }

    .lung-detail-image img {
        width: 100%;
        height: auto;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .lung-detail-info {
        flex: 1;
    }

    .lung-parts {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
        margin-top: 15px;
    }

    .lung-part {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .lung-part:hover {
        background: #f0f8ff;
        border-color: #0077ff;
    }

    .lung-part.active {
        background: #e6f7ff;
        border-color: #0077ff;
        font-weight: bold;
    }

    .lung-part h4 {
        margin: 0 0 5px 0;
        font-size: 14px;
        color: #333;
    }

    .lung-part p {
        margin: 0;
        font-size: 12px;
        color: #666;
    }

    /* Gaya untuk titik-titik anatomi paru */
    .lung-point {
        position: absolute;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        cursor: pointer;
        transform: translate(-50%, -50%);
        transition: all 0.3s ease;
        z-index: 10;
    }

    .lung-point:hover {
        transform: translate(-50%, -50%) scale(1.3);
        z-index: 20;
    }

    .lung-point.active {
        transform: translate(-50%, -50%) scale(1.5);
        z-index: 30;
    }

    .lung-point-trakea {
        background: rgba(255, 0, 0, 0.7);
        border: 2px solid #ff0000;
        top: 20%;
        left: 52%;
    }

    .lung-point-bronkus {
        background: rgba(0, 128, 0, 0.7);
        border: 2px solid #008000;
        top: 35%;
        left: 45%;
    }

    .lung-point-bronkiolus {
        background: rgba(0, 0, 255, 0.7);
        border: 2px solid #0000ff;
        top: 45%;
        left: 25%;
    }

    .lung-point-alveoli {
        background: rgba(255, 165, 0, 0.7);
        border: 2px solid #ffa500;
        top: 60%;
        left: 24%;
    }

    .lung-point-pleura {
        background: rgba(128, 0, 128, 0.7);
        border: 2px solid #800080;
        top: 50%;
        left: 60%;
    }

    .lung-point-diafragma {
        background: rgba(165, 42, 42, 0.7);
        border: 2px solid #a52a2a;
        top: 70%;
        left: 50%;
    }

    .lung-point-label {
        position: absolute;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        transform: translate(-50%, -100%);
        margin-top: -10px;
        display: none;
        z-index: 40;
    }

    .lung-point:hover .lung-point-label {
        display: block;
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

    /* Gaya untuk pencarian pasien */
    .search-container {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .search-form {
        display: flex;
        gap: 10px;
        align-items: flex-end;
    }

    .form-group {
        flex: 1;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        font-size: 14px;
    }

    .form-group input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background: white;
    }

    .btn {
        padding: 8px 16px;
        background: #0077ff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn:hover {
        background: #0055cc;
    }

    .patient-data {
        background: #e7f3ff;
        border: 1px solid #b3d9ff;
        border-radius: 5px;
        padding: 10px;
        margin-top: 10px;
    }

    .patient-data h4 {
        margin: 0 0 10px 0;
        color: #0066cc;
    }

    .patient-info {
        display: flex;
        gap: 20px;
    }

    .patient-info div {
        font-size: 14px;
    }

    /* Gaya untuk input anatomi paru */
    .lung-input {
        width: 100%;
        padding: 8px;
        margin: 5px 0;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 12px;
    }

    .lung-save-btn {
        background: #28a745;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        margin-top: 5px;
    }

    .lung-save-btn:hover {
        background: #218838;
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

        .submit-btn {
            display: none;
        }

        .search-container {
            display: none;
        }

    }
    </style>
</head>

<body>

    <div class="sheet">
        <!-- Pesan sukses/error -->
        <?php if(isset($_SESSION['success_message'])): ?>
        <div id="notification"
            style="background:#d4edda;padding:10px;border-radius:5px;color:#155724;margin-bottom:10px;">
            <?= $_SESSION['success_message']; ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <script>
        setTimeout(function() {
            let notif = document.getElementById('notification');
            if (notif) {
                notif.style.opacity = "0";
                setTimeout(() => notif.remove(), 500);
            }
        }, 3000); // 3 detik hilang
        </script>

        <form method="POST" action="" id="pengkajianForm">
            <!-- Modal untuk gambar paru-paru yang diperbesar -->
            <div class="lung-modal" id="lungModal">
                <div class="lung-modal-content">
                    <<button type="button" class="lung-modal-close" id="lungModalClose">&times;</button>
                        <h3 style="margin-top: 0;">Anatomi Paru-Paru</h3>

                        <div class="lung-detail-container">
                            <div class="lung-detail-image">
                                <img src="paru.png" alt="Ilustrasi Paru-Paru Detail" id="lungDetailImage">

                                <!-- Titik-titik anatomi paru -->
                                <div class="lung-point lung-point-trakea" data-part="trakea">
                                    <div class="lung-point-label">Trakea</div>
                                </div>
                                <div class="lung-point lung-point-bronkus" data-part="bronkus">
                                    <div class="lung-point-label">Bronkus</div>
                                </div>
                                <div class="lung-point lung-point-bronkiolus" data-part="bronkiolus">
                                    <div class="lung-point-label">Bronkiolus</div>
                                </div>
                                <div class="lung-point lung-point-alveoli" data-part="alveoli">
                                    <div class="lung-point-label">Alveoli</div>
                                </div>
                                <div class="lung-point lung-point-pleura" data-part="pleura">
                                    <div class="lung-point-label">Pleura</div>
                                </div>
                                <div class="lung-point lung-point-diafragma" data-part="diafragma">
                                    <div class="lung-point-label">Diafragma</div>
                                </div>
                            </div>

                            <div class="lung-detail-info">
                                <h4 id="lungPartTitle">Pilih Bagian Paru-Paru</h4>
                                <p id="lungPartDescription">Klik pada salah satu titik di gambar atau bagian paru-paru
                                    di bawah untuk melihat penjelasan detail.</p>
                                <div class="lung-parts">
                                    <div class="lung-part" data-part="trakea">
                                        <h4>Trakea</h4>
                                        <p>Saluran udara utama yang menghubungkan laring dengan bronkus.</p>
                                        <input type="text" class="lung-input" name="paru_trakea"
                                            placeholder="Masukkan keterangan Trakea..." style="width: 90%;">
                                    </div>
                                    <div class="lung-part" data-part="bronkus">
                                        <h4>Bronkus</h4>
                                        <p>Cabang trakea yang menuju ke paru-paru kiri dan kanan.</p>
                                        <input type="text" class="lung-input" name="paru_bronkus"
                                            placeholder="Masukkan keterangan Bronkus..." style="width: 90%;">
                                    </div>
                                    <div class="lung-part" data-part="bronkiolus">
                                        <h4>Bronkiolus</h4>
                                        <p>Cabang kecil dari bronkus yang menuju ke alveoli.</p>
                                        <input type="text" class="lung-input" name="paru_bronkiolus"
                                            placeholder="Masukkan keterangan Bronkiolus..." style="width: 90%;">
                                    </div>
                                    <div class="lung-part" data-part="alveoli">
                                        <h4>Alveoli</h4>
                                        <p>Kantung udara kecil tempat pertukaran oksigen dan karbon dioksida.</p>
                                        <input type="text" class="lung-input" name="paru_alveoli"
                                            placeholder="Masukkan keterangan Alveoli..." style="width: 90%;">
                                    </div>
                                    <div class="lung-part" data-part="pleura">
                                        <h4>Pleura</h4>
                                        <p>Membran tipis yang melapisi paru-paru dan rongga dada.</p>
                                        <input type="text" class="lung-input" name="paru_pleura"
                                            placeholder="Masukkan keterangan Pleura..." style="width: 90%;">
                                    </div>
                                    <div class="lung-part" data-part="diafragma">
                                        <h4>Diafragma</h4>
                                        <p>Otot utama yang digunakan dalam proses pernapasan.</p>
                                        <input type="text" class="lung-input" name="paru_diafragma"
                                            placeholder="Masukkan keterangan Diafragma..." style="width: 90%;">
                                    </div>
                                </div>
                            </div>

                        </div>
                </div>
            </div>

            <!-- Kontainer untuk gambar paru-paru -->
            <div class="lung-image-container" id="lungImageContainer" style="margin-top: 25%;">
                <img src="paru.png" alt="Ilustrasi Paru-Paru" class="lung-image">
                <div class="lung-label">Klik untuk memperbesar</div>
                <div style="margin-top: 10%; margin-left: 15%; font-size: 15px;">
                    <p>Gambar Paru</p>
                </div>
            </div>

            <div class="kop">
                <div class="right">BLUD RSUD SANJIWANI GIANYAR</div>
                <div class="right">RM. 01.06.A/2015</div>
            </div>
            <div class="title">PENGKAJIAN MEDIS & KEPERAWATAN HEMODIALISA</div>
            <div class="info">
                <div class="item">Nama: <input type="text" name="nama" value="<?= $nama ?>" <?= $data_pasien ? : '' ?>
                        style="width: 80px;"></div>
                <div class="item">Tgl. Lahir: <input type="date" name="tgl_lahir" value="<?= $tgl_lahir ?>"
                        <?= $data_pasien ? : '' ?> style="width: 100px;"></div>
                <div class="item">No. RM: <input type="text" name="no_rm" value="<?= $no_rm ?>"
                        <?= $data_pasien ? : '' ?> style="width: 50px;"></div>
                <div class="item">Reg. No: <input type="text" name="reg_no" value="<?= $reg_no ?>" style="width: 50px;">
                </div>
            </div>

            <div class="section-body" style="padding: 0%;">
                <p style="font-size:13px; margin:6px 0;">
                    <strong style="margin-left:10px">Penilaian Nyeri</strong>
                    &nbsp;&nbsp; Lokasi: <input type="text" name="lokasi" style="width: 60px;">
                    &nbsp;&nbsp; Intensitas (0–10): <strong><input type="number" name="intensitas" min="0" max="10"></strong>
                </p>
            </div>

            <div class="section-title">TANDA-TANDA VITAL</div>
            <div class="section-body">
                <div style="margin-bottom:6px;">
                    <strong style="margin-bottom: 10px;">Keadaan Umum: </strong>
                    <label><input type="checkbox" name="keadaan_umum[]" value="Baik">Baik</label>
                    <label><input type="checkbox" name="keadaan_umum[]" value="Sedang">Sedang</label>
                    <label><input type="checkbox" name="keadaan_umum[]" value="Lemah">Lemah</label>
                    <label><input type="checkbox" name="keadaan_umum[]" value="Buruk">Buruk</label>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; GCS: <strong>E: <input type="text" name="E"
                            style="width: 30px;"> V: <input type="text" name="V" style="width: 30px"> M: <input
                            type="text" name="M" style="width: 30px"></strong>
                </div>
                <div style="margin-top:6px;">
                    Tensi: <strong><input type="text" name="tensi" style="width: 40px;"></strong>
                    &nbsp;&nbsp;&nbsp;
                    Nadi: <strong><input type="text" name="nadi" style="width: 40px"></strong>
                    &nbsp;&nbsp;&nbsp;
                    Respirasi: <strong><input type="text" name="respirasi" style="width: 40px;"></strong>
                    &nbsp;&nbsp;&nbsp;
                    Suhu: <strong><input type="text" name="suhu" style="width: 40px">°C</strong>
                </div>
            </div>

            <div class="section-title">PEMERIKSAAN FISIK</div>
            <div class="section-body">
                <div style="margin-bottom:6px;"><strong>Mata:</strong>
                    <label><input type="checkbox" name="anemi" value="1"> Anemi</label>
                    <label><input type="checkbox" name="ikterus" value="1"> Ikterus</label>
                    <label><input type="checkbox" name="reflex_pupil" value="1"> Reflex Pupil</label>
                    <label><input type="checkbox" name="oedema_palpebra" value="1"> Oedema Palpebra</label>
                </div>

                <div style="margin-bottom:6px;"><strong>THT:</strong>
                    <label><input type="checkbox" name="tonsil" value="1">Tonsil <input type="text"
                            name="tonsil_keterangan" style="width: 20px"></label>
                    <label><input type="checkbox" name="pharing" value="1">Pharing <input type="text"
                            name="pharing_keterangan" style="width: 40px"></label>
                    <label><input type="checkbox" name="lidah" value="1">Lidah <input type="text"
                            name="lidah_keterangan" style="width: 50px"></label>
                    <label><input type="checkbox" name="bibir" value="1">Bibir <input type="text"
                            name="bibir_keterangan" style="width: 80px"></label>
                </div>

                <div style="margin-bottom:6px;"><strong>Leher:</strong>
                    <label><input type="checkbox" name="jvp" value="1">JVP <input type="text" name="jvp_keterangan"
                            style="width: 20px"></label>
                    <label><input type="checkbox" name="pembesar_kelenjar" value="1">Pembesar Kelenjar <input
                            type="text" name="pembesar_kelenjar_keterangan" style="width: 20px"></label>
                    <label><input type="checkbox" name="kaku_kuduk" value="1">Kaku Kuduk+/-</label>
                </div>

                <div style="margin-bottom:6px;"><strong>Thoraks:</strong>
                    <label><input type="checkbox" name="simetris" value="1">Simetris/ Asimetris <input type="text"
                            name="simetris_keterangan"></label>
                </div>

                <div style="margin-bottom:6px;"> &nbsp;&nbsp;&nbsp; -Cardiovaskuler:
                    <label><input type="checkbox" name="cardiovaskuler" value="1">S1,S2 <input type="text"
                            name="cardiovaskuler_keterangan" style="width: 50px;"> reguler/Ireguler</label>
                    <label><input type="checkbox" name="murmur" value="1">Murmur <input type="text"
                            name="murmur_keterangan" style="width: 80px;"></label><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <label><input type="checkbox" name="c_lain-lain" value="1"> Lain-lain <input type="text"
                            name="lain-lain_keterangan_c"></label>
                </div>

                <div style="margin-bottom:6px;"> &nbsp;&nbsp;&nbsp; -Pulmo:
                    <label><input type="checkbox" name="suara_nafas" value="1">Suara Nafas <input type="text"
                            name="suara_nafas_keterangan" style="width: 50px;"></label>
                    <label><input type="checkbox" name="ronchi" value="1">Ronchi <input type="text"
                            name="ronchi_keterangan" style="width: 50px;"></label>
                    <label><input type="checkbox" name="wheezing" value="1">Wheezing <input type="text"
                            name="wheezing_keterangan" style="width: 50px;"></label> <br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><input
                            type="checkbox" name="lain-lain" value="1"> Lain-lain <input type="text"
                            name="lain-lain_keterangan"></label>
                </div>

                <div style="margin-top:6px;"><strong>Abdomen:</strong>
                    <label><input type="checkbox" name="distensi" value="1">Distensi:+/-</label>
                    <label><input type="checkbox" name="meteorismus" value="1"> Meteorismus:+/-</label>
                    <label><input type="checkbox" name="peristaltic" value="1">Peristaltic:</label>
                    <label><input type="checkbox" name="normal" value="1">Normal</label>
                    <label><input type="checkbox" name="meningkat" value="1">Meningkat</label>
                    <label><input type="checkbox" name="menurun" value="1">Menurun</label>
                    <label><input type="checkbox" name="ascites" value="1">Ascites:+/-</label> <br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><input
                            type="checkbox" name="nyeri_tekan" value="1">Nyeri tekan:+/- Lokasi <input type="text"
                            name="nyeri_tekan_lokasi"></label> <br>
                    <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - Hepar: <input type="text" name="hepar"></label> <br>
                    <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Lien: &nbsp;&nbsp;&nbsp;<input type="text"
                            name="lien"></label>
                </div>

                <div><strong>Extremitas:</strong>
                    <label><input type="checkbox" name="hangat_dingin" value="1"> Hangat/Dingin</label>
                    <label><input type="checkbox" name="edama" value="1">Edama. pada <input type="text"
                            name="edama_lokasi"></label>
                </div>
            </div>

            <div class="section-title">HASIL PEMERIKSAAN PENUNJANG</div>
            <div class="section-body">
                <label>1. Laboratorium: <input type="text" name="laboratorium"></label> <br>
                <label>2. EKG:
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input
                        type="text" name="ekg">
                </label> <br>
                <label>3. X-Ray &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input
                        type="text" name="x-ray">
                </label>
            </div>

            <div style="display:flex; gap:8px; margin-top:8px;">
                <div class="col">
                    <div class="section-title" style="margin:0;">DIAGNOSA KERJA / DIAGNOSA BANDING</div>
                    <div class="inner" style="padding:8px; min-height:100px;">
                        <textarea name="diagnosa_kerja" placeholder="Tulis diagnosa kerja atau diagnosa banding di sini..."></textarea>
                    </div>
                </div>
                <div class="col">
                    <div class="section-title" style="margin:0;">TERAPI / TINDAKAN</div>
                    <div class="inner" style="padding:8px; min-height:100px;">
                        <textarea name="terapi_tindakan" placeholder="Tulis terapi atau tindakan yang dilakukan di sini..."></textarea>
                    </div>
                </div>
            </div>

            <div class="footer">
                <div>
                    <div>Mengetahui,</div>
                    <div><strong>Perawat Pelaksana</strong></div>

                    <div style="margin-top: 10px;">( <input type="text" name="perawat"
                            style="width: 150px; text-align: center; margin-top: 90px;" required
                            placeholder="Nama Jelas & TTD"> )
                    </div>
                </div>

                <div style="text-align:right;">
                    <div>Gianyar, <input type="date" name="tgl" style="width: 60%;" value="<?= date('Y-m-d') ?>"></div>
                    <div><strong>Dokter Penanggung Jawab</strong></div>

                    <div style="margin-top: 10px;">( <input type="text" name="dokter"
                            style="width: 150px; text-align: center; margin-top: 90px;" required
                            placeholder="Nama Jelas & TTD"> )
                    </div>
                </div>
            </div>
            <div class="submit-btn no-print">
                <button type="submit">Simpan</button>
            </div>
        </form>
    </div>

    <script>
    // Data detail bagian paru-paru
    const lungPartsData = {
        'trakea': {
            title: 'Trakea',
            description: 'Saluran udara utama yang menghubungkan laring dengan bronkus.'
        },
        'bronkus': {
            title: 'Bronkus',
            description: 'Cabang trakea yang menuju ke paru-paru kiri dan kanan.'
        },
        'bronkiolus': {
            title: 'Bronkiolus',
            description: 'Cabang kecil dari bronkus yang menuju ke alveoli.'
        },
        'alveoli': {
            title: 'Alveoli',
            description: 'Kantung udara kecil tempat pertukaran oksigen dan karbon dioksida.'
        },
        'pleura': {
            title: 'Pleura',
            description: 'Membran tipis yang melapisi paru-paru dan rongga dada.'
        },
        'diafragma': {
            title: 'Diafragma',
            description: 'Otot utama yang digunakan dalam proses pernapasan.'
        }
    };

    window.onload = function() {
        setupLungModal();
    };

    function setupLungModal() {
        const lungContainer = document.getElementById('lungImageContainer');
        const lungModal = document.getElementById('lungModal');
        const lungModalClose = document.getElementById('lungModalClose');
        const lungParts = document.querySelectorAll('.lung-part');
        const lungPoints = document.querySelectorAll('.lung-point');

        lungContainer.addEventListener('click', function() {
            lungModal.classList.add('active');
        });

        lungModalClose.addEventListener('click', function() {
            lungModal.classList.remove('active');
            resetLungParts();
        });

        lungModal.addEventListener('click', function(e) {
            if (e.target === lungModal) {
                lungModal.classList.remove('active');
                resetLungParts();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && lungModal.classList.contains('active')) {
                lungModal.classList.remove('active');
                resetLungParts();
            }
        });

        lungParts.forEach(part => {
            part.addEventListener('click', function() {
                const partId = this.getAttribute('data-part');
                updateLungPartInfo(partId);

                lungParts.forEach(p => p.classList.remove('active'));
                this.classList.add('active');

                lungPoints.forEach(point => {
                    point.classList.toggle('active', point.getAttribute('data-part') ===
                        partId);
                });
            });
        });

        lungPoints.forEach(point => {
            point.addEventListener('click', function() {
                const partId = this.getAttribute('data-part');
                updateLungPartInfo(partId);

                lungPoints.forEach(p => p.classList.remove('active'));
                this.classList.add('active');

                lungParts.forEach(part => {
                    part.classList.toggle('active', part.getAttribute('data-part') === partId);
                });
            });
        });
    }

    function updateLungPartInfo(partId) {
        document.getElementById('lungPartTitle').textContent = lungPartsData[partId].title;
        document.getElementById('lungPartDescription').textContent = lungPartsData[partId].description;
    }

    function resetLungParts() {
        document.querySelectorAll('.lung-part').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.lung-point').forEach(p => p.classList.remove('active'));

        document.getElementById('lungPartTitle').textContent = 'Pilih Bagian Paru-Paru';
        document.getElementById('lungPartDescription').textContent =
            'Klik pada salah satu titik di gambar atau bagian paru-paru di bawah untuk melihat penjelasan detail.';
    }
    </script>


</body>

</html>