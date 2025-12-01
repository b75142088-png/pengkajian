<?php
// Koneksi ke database rsud_sanjiwani
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

// Inisialisasi variabel
$success_message = '';
$error_message = '';
$on_hd_id = null;

// Ambil parameter dari URL
$nama = $_GET['nama'] ?? '';
$tgl_lahir = $_GET['tgl_lahir'] ?? '';
$no_rm = $_GET['no_rm'] ?? '';
$reg_no = $_GET['reg_no'] ?? '';

// Jumlah baris dinamis untuk DATA ON-HD
$jumlah_baris = isset($_POST['jumlah_baris']) ? intval($_POST['jumlah_baris']) : 1;

// Tambah baris jika tombol enter/tambah ditekan
if (isset($_POST['tambah_baris'])) {
    $jumlah_baris = min($jumlah_baris + 1, 5); // Maksimal 5 baris
}

// Fungsi validasi data tidak kosong
function validateDataOnHD($data, $jumlah_baris) {
    // Cek data pasien
    if (empty($data['nama_pasien']) || empty($data['no_rm_pasien']) || empty($data['reg_no'])) {
        return "Data pasien (Nama, No. RM, Reg. No) harus diisi";
    }
    
    // Cek minimal 1 baris observasi terisi
    $has_data = false;
    for ($i = 1; $i <= $jumlah_baris; $i++) {
        if (!empty($data["jam$i"]) || !empty($data["td$i"]) || !empty($data["nadi$i"])) {
            $has_data = true;
            break;
        }
    }
    
    if (!$has_data) {
        return "Minimal 1 baris observasi harus diisi";
    }
    
    return true;
}

function validateDataPostHD($data) {
    // Cek data wajib
    if (empty($data['keadaan_umum']) || empty($data['tekanan_darah']) || empty($data['bb_post'])) {
        return "Data keadaan umum, tekanan darah, dan BB Post harus diisi";
    }
    
    return true;
}

// Proses penyimpanan DATA ON-HD
if (isset($_POST['save_on_hd'])) {
    try {
        // Data pasien dari form
        $nama_pasien = $_POST['nama_pasien'] ?? '';
        $tgl_lahir_pasien = $_POST['tgl_lahir_pasien'] ?? '';
        $no_rm_pasien = $_POST['no_rm_pasien'] ?? '';
        $reg_no = $_POST['reg_no'] ?? '';
        
        // Validasi data tidak kosong
        $validation = validateDataOnHD($_POST, $jumlah_baris);
        if ($validation !== true) {
            throw new Exception($validation);
        }
        
        // Data On-HD (dinamis berdasarkan jumlah baris)
        $data_on_hd = [];
        for ($i = 1; $i <= $jumlah_baris; $i++) {
            $data_on_hd[$i] = [
                'jam' => $_POST["jam$i"] ?? '',
                'td' => $_POST["td$i"] ?? '',
                'nadi' => $_POST["nadi$i"] ?? '',
                'qb' => $_POST["qb$i"] ?? '',
                'vena_presure' => $_POST["vena_presure$i"] ?? '',
                'uf_goal' => $_POST["uf_goal$i"] ?? '',
                'uf_reuse' => $_POST["uf_reuse$i"] ?? '',
                'rf_removed' => $_POST["rf_removed$i"] ?? '',
                'masalah' => $_POST["masalah$i"] ?? '',
                'petugas' => $_POST["petugas$i"] ?? ''
            ];
        }
        
        // Data tambahan On-HD
        $treated_blood_time = $_POST['treated_blood_time'] ?? '';
        $treated_blood_volume = $_POST['treated_blood_volume'] ?? '';
        $dialisate = $_POST['dialisate'] ?? '';
        
        // Bangun query INSERT untuk DATA ON-HD secara dinamis
        $columns = [
            'nama_pasien', 'tgl_lahir_pasien', 'no_rm_pasien', 'reg_no',
            'treated_blood_time', 'treated_blood_volume', 'dialisate'
        ];
        $values = [
            ':nama_pasien', ':tgl_lahir_pasien', ':no_rm_pasien', ':reg_no',
            ':treated_blood_time', ':treated_blood_volume', ':dialisate'
        ];
        $params = [
            ':nama_pasien' => $nama_pasien,
            ':tgl_lahir_pasien' => $tgl_lahir_pasien,
            ':no_rm_pasien' => $no_rm_pasien,
            ':reg_no' => $reg_no,
            ':treated_blood_time' => $treated_blood_time,
            ':treated_blood_volume' => $treated_blood_volume,
            ':dialisate' => $dialisate
        ];
        
        // Tambahkan kolom untuk setiap baris data
        for ($i = 1; $i <= $jumlah_baris; $i++) {
            $columns[] = "jam$i";
            $columns[] = "td$i";
            $columns[] = "nadi$i";
            $columns[] = "qb$i";
            $columns[] = "vena_presure$i";
            $columns[] = "uf_goal$i";
            $columns[] = "uf_reuse$i";
            $columns[] = "rf_removed$i";
            $columns[] = "masalah$i";
            $columns[] = "petugas$i";
            
            $values[] = ":jam$i";
            $values[] = ":td$i";
            $values[] = ":nadi$i";
            $values[] = ":qb$i";
            $values[] = ":vena_presure$i";
            $values[] = ":uf_goal$i";
            $values[] = ":uf_reuse$i";
            $values[] = ":rf_removed$i";
            $values[] = ":masalah$i";
            $values[] = ":petugas$i";
            
            $params[":jam$i"] = $data_on_hd[$i]['jam'];
            $params[":td$i"] = $data_on_hd[$i]['td'];
            $params[":nadi$i"] = $data_on_hd[$i]['nadi'];
            $params[":qb$i"] = $data_on_hd[$i]['qb'];
            $params[":vena_presure$i"] = $data_on_hd[$i]['vena_presure'];
            $params[":uf_goal$i"] = $data_on_hd[$i]['uf_goal'];
            $params[":uf_reuse$i"] = $data_on_hd[$i]['uf_reuse'];
            $params[":rf_removed$i"] = $data_on_hd[$i]['rf_removed'];
            $params[":masalah$i"] = $data_on_hd[$i]['masalah'];
            $params[":petugas$i"] = $data_on_hd[$i]['petugas'];
        }
        
        $sql_on_hd = "INSERT INTO data_on_hd (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";
        
        $stmt_on_hd = $pdo->prepare($sql_on_hd);
        $stmt_on_hd->execute($params);
        $on_hd_id = $pdo->lastInsertId();
        
        $success_message = "Data ON-HD berhasil disimpan! ID: " . $on_hd_id;
        
    } catch (Exception $e) {
        $error_message = "Error menyimpan DATA ON-HD: " . $e->getMessage();
    }
}

// Proses penyimpanan DATA POST HD
if (isset($_POST['save_post_hd'])) {

    try {

        $on_hd_id = $_POST['on_hd_id'] ?? null;

        if (!$on_hd_id) {
            throw new Exception("ID DATA ON-HD tidak ditemukan. Simpan DATA ON-HD terlebih dahulu.");
        }

        // 🔍 VALIDASI DULU SEBELUM DIPROSES
        $validation = validateDataPostHD($_POST);
        if ($validation !== true) {
            throw new Exception($validation);
        }

        // 🧾 Ambil semua data POST-HD
        $keluhan = $_POST['keluhan'] ?? '';
        $keadaan_umum = $_POST['keadaan_umum'] ?? '';
        $tekanan_darah = $_POST['tekanan_darah'] ?? '';
        $nadi_post = $_POST['nadi_post'] ?? '';
        $respirasi = $_POST['respirasi'] ?? '';
        $bb_post = $_POST['bb_post'] ?? '';
        $lama_hd = $_POST['lama_hd'] ?? '';
        $uf_removed = $_POST['uf_removed'] ?? '';

        // 💧 Cairan HD
        $sisa_priming = $_POST['sisa_priming'] ?? 0;
        $reuse = isset($_POST['reuse']) ? 1 : 0;
        $tidak_reuse = isset($_POST['tidak_reuse']) ? 1 : 0;
        $transfusi = $_POST['transfusi'] ?? 0;
        $beku_bocor1 = isset($_POST['beku_bocor1']) ? 1 : 0;
        $wash_out = $_POST['wash_out'] ?? 0;
        $single_use = isset($_POST['single_use']) ? 1 : 0;
        $minum = $_POST['minum'] ?? 0;
        $pakai_8x = isset($_POST['pakai_8x']) ? 1 : 0;
        $penyakit_menular = isset($_POST['penyakit_menular']) ? 1 : 0;

        // ➕ Hitung jumlah cairan otomatis
        $jumlah = 
            (int)$sisa_priming + 
            (int)$transfusi + 
            (int)$wash_out + 
            (int)$minum;

        $sql_post_hd = "INSERT INTO data_post_hd (
            on_hd_id, keluhan, keadaan_umum, tekanan_darah, nadi_post, respirasi, bb_post, lama_hd, uf_removed,
            sisa_priming, reuse, tidak_reuse, transfusi, beku_bocor1, wash_out, single_use, minum, pakai_8x, jumlah, penyakit_menular
        ) VALUES (
            :on_hd_id, :keluhan, :keadaan_umum, :tekanan_darah, :nadi_post, :respirasi, :bb_post, :lama_hd, :uf_removed,
            :sisa_priming, :reuse, :tidak_reuse, :transfusi, :beku_bocor1, :wash_out, :single_use, :minum, :pakai_8x, :jumlah, :penyakit_menular
        )";

        $stmt_post_hd = $pdo->prepare($sql_post_hd);

        $params_post_hd = [
            ':on_hd_id' => $on_hd_id,
            ':keluhan' => $keluhan,
            ':keadaan_umum' => $keadaan_umum,
            ':tekanan_darah' => $tekanan_darah,
            ':nadi_post' => $nadi_post,
            ':respirasi' => $respirasi,
            ':bb_post' => $bb_post,
            ':lama_hd' => $lama_hd,
            ':uf_removed' => $uf_removed,

            ':sisa_priming' => $sisa_priming,
            ':reuse' => $reuse,
            ':tidak_reuse' => $tidak_reuse,
            ':transfusi' => $transfusi,
            ':beku_bocor1' => $beku_bocor1,
            ':wash_out' => $wash_out,
            ':single_use' => $single_use,
            ':minum' => $minum,
            ':pakai_8x' => $pakai_8x,
            ':jumlah' => $jumlah,
            ':penyakit_menular' => $penyakit_menular
        ];

        $stmt_post_hd->execute($params_post_hd);

        // 🎉 SUCCESS
        $success_message = "Data POST HD berhasil disimpan! Terkait dengan DATA ON-HD ID: " . $on_hd_id;

        // 🧹 Reset form tapi pertahankan ID ON-HD
        $temp_on_hd_id = $on_hd_id;
        $_POST = [];
        $_POST['on_hd_id'] = $temp_on_hd_id;

    } catch (Exception $e) {
        $error_message = "Error menyimpan DATA POST HD: " . $e->getMessage();
    }
}


function h($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hasil Observasi Program Dialisis</title>
  <style>
    @page {
      size: A4;
      margin: 15mm;
    }
    
    @media print {
      button, .no-print {
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
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
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
      margin-right: 50%;
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 25px;
      font-size: 12px;
    }

    th,
    td {
      border: 1px solid #000;
      padding: 6px;
      text-align: center;
    }

    th {
      background-color: #e9ecef;
      font-weight: bold;
    }

    .section-title {
      font-weight: bold;
      margin: 20px 0 10px 0;
      font-size: 14px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      white-space: nowrap;
    }

    button {
      margin: 20px auto;
      display: block;
      padding: 10px 25px;
      border: none;
      border-radius: 6px;
      background-color: #007bff;
      color: white;
      font-size: 16px;
      cursor: pointer;
    }

    input[type="text"],
    input[type="number"],
    input[type="time"] {
      background: transparent;
      border: none;
      border-bottom: 1px solid #000;
      padding: 2px 4px;
      text-align: center;
      margin: 0 2px;
      width: 50px;
    }

    .data-post-section {
      margin: 15px 0;
    }

    .cairan-section {
      margin: 15px 0;
    }

    .cairan-line {
      margin: 6px 0;
      display: flex;
      align-items: center;
    }

    .checkbox-group {
      margin-left: 40px;
    }

    .checkbox-item {
      display: inline-block;
      margin-right: 20px;
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

    .cairan-input {
      width: 60px;
      text-align: center;
    }

    .form-line {
      margin: 8px 0;
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

    .btn-group {
      display: flex;
      justify-content: space-between;
      gap: 10px;
    }

    .btn-on-hd {
      background-color: #28a745;
    }

    .btn-post-hd {
      background-color: #ffc107;
      color: #000;
    }

    /* Style khusus untuk input dalam tabel */
    table input[type="text"] {
      width: 40px;
      text-align: center;
    }

    table input[name^="masalah"] {
      width: 120px;
    }

    table input[name^="petugas"] {
      width: 80px;
    }

    .cairan-section {
            font-family: Arial, serif;
            font-size: 14px;
            margin-top: 10px;
            margin-left: 5%;
        }

        .grid-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            /* Dua kolom */
            gap: 40px;
            /* Jarak antar kolom */
        }

        .line {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .line label {
            width: 120px;
            /* agar teks rata */
            display: inline-block;
        }

        .line input {
            width: 80px;
            border: none;
            border-bottom: 1px solid #000;
            text-align: center;
            margin-right: 10px;
        }

        hr {
            margin: 10px 0;
            width: 200px;
            border: 0;
            border-top: 1px solid #000;
        }

        .checkbox-line {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

    /* Tambahan untuk tombol tambah baris */
    .tambah-baris-container {
        text-align: center;
        margin-bottom: 10px;
    }
    
    .tambah-baris-btn {
        background-color: #17a2b8;
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }
    
    .tambah-baris-btn:hover {
        background-color: #138496;
    }
    
    .info input[type="text"],
    .info input[type="date"] {
        border: none;
        border-bottom: 1px solid #000;
        padding: 2px 4px;
        background: transparent;
        width: 120px;
    }
  </style>
</head>

<body>
  <div class="sheet">
    <!-- Pesan sukses/error -->
    <?php if ($success_message): ?>
      <div class="message success"><?= h($success_message) ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
      <div class="message error"><?= h($error_message) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <!-- Hidden field untuk menyimpan ID DATA ON-HD dan jumlah baris -->
      <input type="hidden" name="on_hd_id" value="<?= h($on_hd_id) ?>">
      <input type="hidden" name="jumlah_baris" value="<?= h($jumlah_baris) ?>">
      
      <div class="kop">
        <div class="left">BLUD RSUD SANJIWANI GIANYAR</div>
        <div class="right">RM. 01.06.B/ 2015</div>
      </div>
      
      <!-- Info Pasien dengan input fields yang bisa diisi -->
      <div class="info">
        <div class="item">Nama: 
            <input type="text" name="nama_pasien" value="<?= h($_POST['nama_pasien'] ?? $nama) ?>">
        </div>
        <div class="item">Tgl. Lahir: 
            <input type="date" name="tgl_lahir_pasien" value="<?= h($_POST['tgl_lahir_pasien'] ?? $tgl_lahir) ?>">
        </div>
        <div class="item">No. RM: 
            <input type="text" name="no_rm_pasien" value="<?= h($_POST['no_rm_pasien'] ?? $no_rm) ?>">
        </div>
        <div class="item">Reg. No: 
            <input type="text" name="reg_no" value="<?= h($_POST['reg_no'] ?? $reg_no) ?>">
        </div>
      </div>
      
      <h2>Hasil Observasi Program Dialisis</h2>
      <h3 style="text-decoration: underline;">DATA ON-HD</h3>

      <!-- Tombol Tambah Baris -->
      <div class="tambah-baris-container">
        <button type="submit" name="tambah_baris" class="tambah-baris-btn no-print" 
                <?= $jumlah_baris >= 5 ? 'disabled' : '' ?>>
            + Tambah Baris (Enter)
        </button>
        <span style="font-size: 12px; margin-left: 10px;">Jumlah baris: <?= h($jumlah_baris) ?></span>
      </div>

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
          <?php for ($i = 1; $i <= $jumlah_baris; $i++): ?>
          <tr>
            <td><input type="time" name="jam<?= $i ?>" style="width: 80px;" value="<?= h($_POST["jam$i"] ?? '') ?>"></td>
            <td><input type="text" name="td<?= $i ?>" value="<?= h($_POST["td$i"] ?? '') ?>"></td>
            <td><input type="text" name="nadi<?= $i ?>" value="<?= h($_POST["nadi$i"] ?? '') ?>"></td>
            <td><input type="text" name="qb<?= $i ?>" value="<?= h($_POST["qb$i"] ?? '') ?>"></td>
            <td><input type="text" name="vena_presure<?= $i ?>" value="<?= h($_POST["vena_presure$i"] ?? '') ?>"></td>
            <td><input type="text" name="uf_goal<?= $i ?>" value="<?= h($_POST["uf_goal$i"] ?? '') ?>"></td>
            <td><input type="text" name="uf_reuse<?= $i ?>" value="<?= h($_POST["uf_reuse$i"] ?? '') ?>"></td>
            <td><input type="text" name="rf_removed<?= $i ?>" value="<?= h($_POST["rf_removed$i"] ?? '') ?>"></td>
            <td><input type="text" name="masalah<?= $i ?>" style="width: 120px;" value="<?= h($_POST["masalah$i"] ?? '') ?>"></td>
            <td><input type="text" name="petugas<?= $i ?>" style="width: 80px;" value="<?= h($_POST["petugas$i"] ?? '') ?>"></td>
          </tr>
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
              <label style="margin-left: -9%;">TBT (M) : <input type="text" name="treated_blood_time" style="width: 30%;" value="<?= h($_POST['treated_blood_time'] ?? '') ?>"></label>
              <label style="margin-left: -9%;">TBV (L) : <input type="text" name="treated_blood_volume" style="width: 30%; " value="<?= h($_POST['treated_blood_volume'] ?? '') ?>"></label>
              <label>Dialisate (L) : <input type="text" name="dialisate" style="width: 30%;" value="<?= h($_POST['dialisate'] ?? '') ?>"></label>
            </td>
          </tr>
        </tbody>
      </table>
      <div>
        <p>Note : TBT (Treated Blood Time)</p>
        <p style="margin-left: 5%;"> TBV (Treated Blood Volume)</p>
      </div>

      <!-- Tombol Simpan DATA ON-HD -->
      <button type="submit" name="save_on_hd" class="no-print btn-on-hd">Simpan DATA ON-HD</button>
      
      <div class="divider"></div>

      <div class="section-title" style="text-decoration: underline;">DATA POST HD</div>
      
      <div class="data-post-section">
        <div class="form-line">
          <label>Keluhan : <input type="text" name="keluhan" style="width: 300px;" value="<?= h($_POST['keluhan'] ?? '') ?>"></label>
        </div>
        
        <div class="form-line">
          <div class="inline-group">
            <label>Keadaan umum : <input type="text" name="keadaan_umum" style="width: 150px;" value="<?= h($_POST['keadaan_umum'] ?? '') ?>"></label>
            <label>Tekanan darah : <input type="text" name="tekanan_darah" style="width: 60px;" value="<?= h($_POST['tekanan_darah'] ?? '') ?>"> mmHg</label>
            <label>Nadi : <input type="text" name="nadi_post" style="width: 60px;" value="<?= h($_POST['nadi_post'] ?? '') ?>"> X/mmt</label>
          </div>
        </div>
        
        <div class="form-line">
          <div class="inline-group">
            <label>Respirasi : <input type="text" name="respirasi" style="width: 80px;" value="<?= h($_POST['respirasi'] ?? '') ?>"> X/mmt</label>
            <label>BB-Post : <input type="text" name="bb_post" style="width: 60px;" value="<?= h($_POST['bb_post'] ?? '') ?>"> kg</label>
          </div>
        </div>
        
        <div class="form-line">
          <div class="inline-group">
            <label>Lama HD : <input type="text" name="lama_hd" style="width: 80px;" value="<?= h($_POST['lama_hd'] ?? '') ?>"> jam</label>
            <label>UF removed : <input type="text" name="uf_removed" style="width: 80px;" value="<?= h($_POST['uf_removed'] ?? '') ?>"></label>
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
                    <input type="text" name="sisa_priming" class="cairan-input" value="<?= h($_POST['sisa_priming'] ?? '') ?>"> mL
                </div>

                <div class="line">
                    <label>Transfusi :</label>
                    <input type="text" name="transfusi" class="cairan-input" value="<?= h($_POST['transfusi'] ?? '') ?>"> mL
                </div>

                <div class="line">
                    <label>Wash out :</label>
                    <input type="text" name="wash_out" class="cairan-input" value="<?= h($_POST['wash_out'] ?? '') ?>"> mL
                </div>

                <div class="line">
                    <label>Minum :</label>
                    <input type="text" name="minum" class="cairan-input" value="<?= h($_POST['minum'] ?? '') ?>"> mL
                </div>

                <hr style="width: 67%;">

                <div class="line total">
                    <label>Jumlah :</label>
                    <input type="text" name="jumlah" class="cairan-input" value="<?= h($_POST['jumlah'] ?? '') ?>" readonly> mL
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="right-column">
                <div class="checkbox-line">
                    <label>Dializer :</label>
                    <input type="checkbox" name="reuse" <?= isset($_POST['reuse']) ? 'checked' : '' ?>> Rause
                    <input type="checkbox" name="tidak_reuse" style="margin-left: 10px;" <?= isset($_POST['tidak_reuse']) ? 'checked' : '' ?>> Tidak reuse
                </div>

                <div class="checkbox-line" style="margin-left: 40%;">
                    <input type="checkbox" name="beku_bocor1" <?= isset($_POST['beku_bocor1']) ? 'checked' : '' ?>> beku/bocor
                </div>

                <div class="checkbox-line" style="margin-left: 40%;">
                    <input type="checkbox" name="single_use" <?= isset($_POST['single_use']) ? 'checked' : '' ?>> single use
                </div>

                <div class="checkbox-line" style="margin-left: 40%;">
                    <input type="checkbox" name="pakai_8x" <?= isset($_POST['pakai_8x']) ? 'checked' : '' ?>> >8 X pakai
                </div>

                <div class="checkbox-line" style="margin-left: 40%;">
                    <input type="checkbox" name="penyakit_menular" <?= isset($_POST['penyakit_menular']) ? 'checked' : '' ?>> Penyakit menular
                </div>
            </div>
        </div>
    </div>
      <button type="submit" name="save_post_hd" class="no-print btn-post-hd" 
              <?= !$on_hd_id ? 'disabled' : '' ?>>Simpan DATA POST HD</button>
    </form>
  </div>

  <script>
    function hitungJumlah() {
      const sisaPriming = parseFloat(document.querySelector('input[name="sisa_priming"]').value) || 0;
      const transfusi = parseFloat(document.querySelector('input[name="transfusi"]').value) || 0;
      const washOut = parseFloat(document.querySelector('input[name="wash_out"]').value) || 0;
      const minum = parseFloat(document.querySelector('input[name="minum"]').value) || 0;
      
      const jumlah = sisaPriming + transfusi + washOut + minum;
      document.querySelector('input[name="jumlah"]').value = jumlah;
    }

    document.querySelectorAll('input[name="sisa_priming"], input[name="transfusi"], input[name="wash_out"], input[name="minum"], input[name="treated_blood_time"], input[name="treated_blood_volume"], input[name="dialisate"], input[name="nadi_post"], input[name="bb_post"], input[name="lama_hd"]').forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            hitungJumlah();
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.ctrlKey) {
            e.preventDefault();
            document.querySelector('button[name="tambah_baris"]').click();
        }
    });

    document.addEventListener('DOMContentLoaded', hitungJumlah);
  </script>
</body>
</html>