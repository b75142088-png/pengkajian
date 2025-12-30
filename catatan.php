<?php
// --- KONEKSI DATABASE ---
$host = "localhost";
$user = "root";
$pass = ""; 
$db   = "db_rsud_sedasi";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("Koneksi gagal: " . $conn->connect_error); }

// --- 1. INISIALISASI VARIABEL (Agar Form Tidak Error Kosong) ---
$regno = $jam_masuk = $catatan_bawah = $keputusan = "";
$vital_sign_grid_arr = []; // Array untuk grafik
$vas_score = "";
// Skor Aldrette
$a1 = $a2 = $a3 = $a4 = $a5 = "";
// Skor Steward
$s1 = $s2 = $s3 = "";
// Tanda Tangan
$nama_dokter = $ttd_dokter = "";
$nama_anestesi = $ttd_anestesi = "";

// Mode Default
$mode_form = "simpan";

// --- 2. LOGIKA GET (LOAD DATA JIKA ADA) ---
if (isset($_GET['regno']) && !empty($_GET['regno'])) {
    $regno_get = $conn->real_escape_string($_GET['regno']);
    
    // Cek apakah data PASCA SEDASI sudah ada untuk regno ini?
    $sql_load = "SELECT * FROM pasca_sedasi WHERE regno = '$regno_get'";
    $res_load = $conn->query($sql_load);

    if ($res_load->num_rows > 0) {
        // DATA DITEMUKAN -> MODE EDIT
        $row = $res_load->fetch_assoc();
        $mode_form = "update";

        $regno = $row['regno'];
        $jam_masuk = $row['jam_masuk'];
        
        // Decode JSON Grid Chart
        $vital_sign_grid_arr = json_decode($row['vital_sign_grid'], true) ?? [];
        
        $vas_score = $row['vas_score_pasca'];

        // Aldrette
        $a1 = $row['skor_aldrette_aktifitas'];
        $a2 = $row['skor_aldrette_sirkulasi'];
        $a3 = $row['skor_aldrette_pernapasan'];
        $a4 = $row['skor_aldrette_kesadaran'];
        $a5 = $row['skor_aldrette_warna_kulit'];

        // Steward
        $s1 = $row['skor_steward_kesadaran'];
        $s2 = $row['skor_steward_pernapasan'];
        $s3 = $row['skor_steward_aktivitas'];

        $catatan_bawah = $row['catatan_bawah'];
        $keputusan = $row['keputusan']; // "Boleh Pulang", dll

        // TTD
        $nama_dokter = $row['nama_dokter'];
        $ttd_dokter = $row['ttd_dokter'];
        $nama_anestesi = $row['nama_anestesi'];
        $ttd_anestesi = $row['ttd_anestesi'];
    } else {
        // DATA BELUM ADA -> MODE INSERT
        // Kita isi regno dari URL agar user tidak perlu ketik
        $regno = $regno_get;
    }
}

// --- 3. LOGIKA SIMPAN / UPDATE (POST) ---
$status_simpan = "";
$pesan_simpan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $regno = $_POST['regno'] ?? ''; 
    $jam_masuk = $_POST['jam_masuk'] ?? '';

    // Cek Status Sedasi (Parent) Harus Ada
    $cek_parent = "SELECT id FROM status_sedasi WHERE regno = '$regno'";
    $res_parent = $conn->query($cek_parent);

    if ($res_parent->num_rows == 0) {
        $status_simpan = "not_found"; // Parent tidak ada
    } else {
        
        // Siapkan Data
        $vital_sign_grid = json_encode($_POST['grid_chart'] ?? []);
        $vas_score = intval($_POST['skala_vas_pasca'] ?? 0);

        $a1 = intval($_POST['aldrette_aktifitas'] ?? 0);
        $a2 = intval($_POST['aldrette_sirkulasi'] ?? 0);
        $a3 = intval($_POST['aldrette_pernapasan'] ?? 0);
        $a4 = intval($_POST['aldrette_kesadaran'] ?? 0);
        $a5 = intval($_POST['aldrette_warna_kulit'] ?? 0);
        $total_aldrette = $a1 + $a2 + $a3 + $a4 + $a5;

        $s1 = intval($_POST['steward_kesadaran'] ?? 0);
        $s2 = intval($_POST['steward_pernapasan'] ?? 0);
        $s3 = intval($_POST['steward_aktivitas'] ?? 0);
        $total_steward = $s1 + $s2 + $s3;

        $catatan = $_POST['catatan_bawah'] ?? '';
        
        // Logika Radio Keputusan
        $keputusan_raw = $_POST['keputusan'] ?? '';
        $keputusan_val = "";
        if ($keputusan_raw == 'pulang' || $keputusan_raw == 'Boleh Pulang') $keputusan_val = "Boleh Pulang";
        elseif ($keputusan_raw == 'ruangan' || $keputusan_raw == 'Kembali ke Ruangan') $keputusan_val = "Kembali ke Ruangan";
        elseif ($keputusan_raw == 'mrs' || $keputusan_raw == 'MRS') $keputusan_val = "MRS";

        $nama_dokter = $_POST['nama_dokter'] ?? '';
        $ttd_dokter  = $_POST['ttd_dokter_base64'] ?? '';
        $nama_anestesi = $_POST['nama_anestesi'] ?? '';
        $ttd_anestesi  = $_POST['ttd_anestesi_base64'] ?? '';

        // Cek apakah UPDATE atau INSERT (Cek tabel pasca_sedasi)
        $cek_self = "SELECT id FROM pasca_sedasi WHERE regno = '$regno'";
        $res_self = $conn->query($cek_self);

        if ($res_self->num_rows > 0) {
            // --- UPDATE DATA ---
            $sql = "UPDATE pasca_sedasi SET 
                jam_masuk=?, vital_sign_grid=?, vas_score_pasca=?,
                skor_aldrette_aktifitas=?, skor_aldrette_sirkulasi=?, skor_aldrette_pernapasan=?, skor_aldrette_kesadaran=?, skor_aldrette_warna_kulit=?, total_aldrette=?,
                skor_steward_kesadaran=?, skor_steward_pernapasan=?, skor_steward_aktivitas=?, total_steward=?,
                catatan_bawah=?, keputusan=?,
                nama_dokter=?, ttd_dokter=?, nama_anestesi=?, ttd_anestesi=?
                WHERE regno=?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiiiiiiiiiiisssssss", 
                $jam_masuk, $vital_sign_grid, $vas_score,
                $a1, $a2, $a3, $a4, $a5, $total_aldrette,
                $s1, $s2, $s3, $total_steward,
                $catatan, $keputusan_val,
                $nama_dokter, $ttd_dokter, $nama_anestesi, $ttd_anestesi,
                $regno
            );
            $pesan_sukses = "Data Berhasil Diperbarui!";

        } else {
            // --- INSERT DATA ---
            $sql = "INSERT INTO pasca_sedasi (
                regno, jam_masuk, vital_sign_grid, vas_score_pasca,
                skor_aldrette_aktifitas, skor_aldrette_sirkulasi, skor_aldrette_pernapasan, skor_aldrette_kesadaran, skor_aldrette_warna_kulit, total_aldrette,
                skor_steward_kesadaran, skor_steward_pernapasan, skor_steward_aktivitas, total_steward,
                catatan_bawah, keputusan,
                nama_dokter, ttd_dokter, nama_anestesi, ttd_anestesi
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssiiiiiiiiiiissssss", 
                $regno, $jam_masuk, $vital_sign_grid, $vas_score,
                $a1, $a2, $a3, $a4, $a5, $total_aldrette,
                $s1, $s2, $s3, $total_steward,
                $catatan, $keputusan_val,
                $nama_dokter, $ttd_dokter, $nama_anestesi, $ttd_anestesi
            );
            $pesan_sukses = "Data Berhasil Disimpan!";
        }

        if ($stmt->execute()) {
            $status_simpan = "success";
            $pesan_simpan = $pesan_sukses;
        } else {
            $status_simpan = "error";
            $pesan_simpan = $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian Pasca Sedasi - RSUD Sanjiwani</title>
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10px; margin: 20px; background-color: #f4f6f9; }
        .paper { 
            width: 210mm; 
            min-height: 297mm; 
            background-color: white; 
            margin: 0 auto; 
            padding: 15px; 
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        table { width: 100%; border-collapse: collapse; border-spacing: 0; }
        th, td { border: 1px solid black; padding: 0 2px; vertical-align: middle; height: 18px; box-sizing: border-box; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        
        .bg-green { background: linear-gradient(90deg, #92D050, #7db342); color: black; }
        .bg-black { background: #2c3e50; color: white; border-radius: 4px 4px 0 0; }
        .bg-grey { background-color: #f1f3f5; }
        
        .col-symbol { width: 35px; text-align: center; font-weight: bold; }
        .col-val { width: 30px; text-align: center; font-size: 9px; }
        .col-grid { width: 1.8%; border: 1px solid #ddd; cursor: pointer; transition: background 0.1s; } 
        .col-grid:hover { background-color: #e3f2fd; }
        .col-vas { width: 70px; text-align: center; font-weight: bold; font-size: 11px; padding: 0 !important; }
        
        .vas-label { display: block; width: 100%; height: 100%; cursor: pointer; padding-top: 2px; }
        .vas-label input[type="radio"] { display: none; }
        .vas-label span { display: block; width: 100%; height: 100%; }
        .vas-label input[type="radio"]:checked + span { background-color: #92D050; color: black; font-weight: bold; }
        .face-icon { font-size: 14px; margin-left: 3px; }
        
        .col-score-label { width: 90px; padding-left: 4px; font-size: 9px; line-height: 1.1; }
        .col-score-input { width: 35px; }
        
        input[type="text"], input[type="number"], textarea { width: 100%; border: none; outline: none; background: transparent; font-family: inherit; font-size: inherit; margin: 0; padding: 0; }
        input:focus { background-color: #f0f8ff; }
        .input-dotted { border-bottom: 1px dashed black; width: auto; min-width: 200px; display: inline-block; }
        
        .header-top { display: flex; justify-content: space-between; align-items: center; padding: 2px 5px; border: 1px solid black; font-weight: bold; }
        
        /* Tanda Tangan */
        .signature-pad { border: 1px dashed #aaa; background-color: #fdfdfd; cursor: crosshair; display: block; margin: 5px auto; border-radius: 4px; }
        .btn-clear { background-color: #ffcccc; border: 1px solid #e74c3c; color: #c0392b; font-size: 9px; padding: 2px 8px; cursor: pointer; margin-top: 2px; border-radius: 3px; }
        .input-name { border-bottom: 1px dotted black !important; text-align: center; width: 90% !important; margin-top: 5px; }

        /* BUTTON STYLES */
        .btn-container { text-align: center; margin-top: 20px; display: flex; justify-content: center; gap: 15px; }
        .btn-modern { padding: 12px 30px; font-size: 14px; font-weight: bold; border: none; border-radius: 50px; cursor: pointer; transition: all 0.3s ease; color: white; display: flex; align-items: center; gap: 8px; }
        .btn-save { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); box-shadow: 0 4px 10px rgba(52, 152, 219, 0.4); }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(52, 152, 219, 0.6); }
        .btn-print { background: linear-gradient(135deg, #8e44ad 0%, #732d91 100%); box-shadow: 0 4px 10px rgba(142, 68, 173, 0.4); }
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(142, 68, 173, 0.6); }

        @media print {
            @page { size: A4; margin: 5mm; }
            body { background-color: white; margin: 0; padding: 0; transform: scale(0.95); transform-origin: top left; width: 105%; }
            .paper { width: 100%; box-shadow: none; margin: 0; padding: 0; border: none; min-height: auto; }
            .btn-container, .btn-clear, .swal2-container, ::-webkit-scrollbar { display: none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            td, th { padding: 1px 3px !important; font-size: 10px !important; }
            .col-score-input input { font-size: 10px !important; }
            .header-black { padding: 2px !important; }
            .header-green { padding: 4px !important; }
            input[type="text"], input[type="number"] { border: none !important; }
            .input-dotted { border-bottom: 1px solid black !important; }
            .signature-pad { border: none !important; } 
        }
    </style>
</head>
<body>

<form action="" method="post" id="formPenilaian">
<div class="paper">
    
    <div style="text-align: right; margin-bottom: 5px; padding: 5px; border-bottom: 1px dashed #ccc;">
        <strong style="color: #333;">Regno Pasien:</strong> 
        <!-- Jika Edit, Regno Readonly tapi tetap dikirim -->
        <input type="text" name="regno" value="<?php echo htmlspecialchars($regno); ?>" style="width: 120px; font-weight: bold; text-align: center; border-bottom: 1px solid #333;" placeholder="Contoh: 12345" required>
    </div>

    <div style="border: 1px solid black; border-bottom: none; padding: 3px;">
        <strong>Catatan :</strong>
    </div>
    <div class="header-top bg-green">
        <span>RSUD SANJIWANI GIANYAR</span>
        <span style="padding: 1px 4px; font-size: 10px; background: rgba(255,255,255,0.4); border-radius: 3px;">RM. 05.113/2025</span>
    </div>
    
    <div class="bg-black text-center text-bold" style="padding: 2px; border: 1px solid black; border-top: none;">
        PENILAIAN PASCA SEDASI
    </div>
    
    <div style="border: 1px solid black; border-top: none; padding: 5px; margin-bottom: -1px;">
        Jam masuk ruang pulih : <input type="text" name="jam_masuk" value="<?php echo htmlspecialchars($jam_masuk); ?>" class="input-dotted">
    </div>

    <table>
        <thead>
            <tr class="bg-grey" style="height: 25px;">
                <th class="col-symbol"></th>
                <th class="col-val">R</th>
                <th class="col-val">N</th>
                <th class="col-val">TD</th>
                <?php for($i=0; $i<35; $i++): ?>
                    <th class="col-grid"></th>
                <?php endfor; ?>
                <th class="col-vas">VAS/FLACC<br>/ CRIES</th>
                <th class="col-score-label" style="text-align: center;">Skor<br>Aldrette</th>
                <th class="col-score-input"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $rows = [
                ['', '60', '', '220', '10', '😭', 'Aktifitas', false, false, 'aldrette_aktifitas', $a1],
                ['', '55', '', '200', '9', '😭', 'Sirkulasi', false, false, 'aldrette_sirkulasi', $a2],
                ['', '50', '', '180', '8', '😣', 'Pernapasan', false, false, 'aldrette_pernapasan', $a3],
                ['N ●', '45', '', '160', '7', '😣', 'Kesadaran', false, false, 'aldrette_kesadaran', $a4],
                ['Sis ▼', '40', '180', '140', '6', '☹️', 'Warna<br>kulit', false, false, 'aldrette_warna_kulit', $a5],
                ['Dis ▲', '35', '160', '120', '5', '☹️', 'TOTAL', true, false, '', ''], 
                ['R +', '30', '140', '100', '4', '😐', '<strong>Skor<br>Steward</strong>', false, true, '', ''],
                ['', '25', '120', '80', '3', '😐', 'Kesadaran', false, false, 'steward_kesadaran', $s1],
                ['', '20', '100', '60', '2', '🙂', 'Pernapasan', false, false, 'steward_pernapasan', $s2],
                ['', '15', '80', '40', '1', '🙂', 'Aktivitas', false, false, 'steward_aktivitas', $s3],
                ['', '10', '60', '20', '0', '😊', 'TOTAL', true, false, '', ''],
                ['', '5', '', '0', '', '', '', false, false, '', '']
            ];

            foreach ($rows as $rIndex => $row): 
                $symbolLabel = $row[0];
                $jsSymbol = '•'; 
                if(strpos($symbolLabel, '●') !== false) $jsSymbol = '●';
                if(strpos($symbolLabel, '▼') !== false) $jsSymbol = '▼';
                if(strpos($symbolLabel, '▲') !== false) $jsSymbol = '▲';
                if(strpos($symbolLabel, '+') !== false) $jsSymbol = '+';
                
                $isTotal = $row[7];
                $isHeader = $row[8];
                $inputName = $row[9];
                $savedValue = $row[10]; // Nilai DB
                $vasValue = $row[4];
            ?>
            <tr>
                <td class="col-symbol"><?php echo $row[0]; ?></td>
                <td class="col-val bg-grey"><?php echo $row[1]; ?></td>
                <td class="col-val bg-grey"><?php echo $row[2]; ?></td>
                <td class="col-val bg-grey"><?php echo $row[3]; ?></td>
                
                <?php for($k=0; $k<35; $k++): 
                    // Cek simbol tersimpan di grid
                    $gridSymbol = $vital_sign_grid_arr[$rIndex][$k] ?? '';
                ?>
                    <td class="col-grid" onclick="toggleGrid(this, '<?php echo $jsSymbol; ?>')" style="<?php echo $gridSymbol ? 'color:blue; font-weight:bold;' : ''; ?>">
                         <?php echo $gridSymbol; ?>
                         <input type="hidden" name="grid_chart[<?php echo $rIndex; ?>][<?php echo $k; ?>]" value="<?php echo $gridSymbol; ?>">
                    </td>
                <?php endfor; ?>
                
                <?php if ($rIndex < count($rows) - 1): ?>
                    <td class="col-vas">
                        <?php if($vasValue !== ''): ?>
                            <label class="vas-label" title="Pilih Skala Nyeri <?php echo $vasValue; ?>">
                                <input type="radio" name="skala_vas_pasca" value="<?php echo $vasValue; ?>" <?php echo ($vas_score == $vasValue && $vasValue !== '') ? 'checked' : ''; ?>>
                                <span>
                                    <?php echo $vasValue; ?> 
                                    <?php if($row[5]): ?>
                                        <span class="face-icon"><?php echo $row[5]; ?></span>
                                    <?php endif; ?>
                                </span>
                            </label>
                        <?php endif; ?>
                    </td>
                    <td class="col-score-label" style="<?php echo ($isHeader || $isTotal) ? 'text-align:center; font-weight:bold;' : ''; ?>">
                        <?php echo $row[6]; ?>
                    </td>
                    <td class="col-score-input">
                        <?php if(!$isHeader && $inputName != ''): ?>
                            <input type="number" name="<?php echo $inputName; ?>" value="<?php echo ($savedValue !== "") ? $savedValue : ''; ?>" style="text-align: center; height: 100%;">
                        <?php elseif($isTotal): ?>
                             <span style="font-size: 10px; color: #555;">(Auto)</span>
                        <?php endif; ?>
                    </td>
                <?php else: ?>
                    <td colspan="3" style="border:none; border-left: 1px solid black;"></td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <script>
        function toggleGrid(cell, symbol) {
            var input = cell.querySelector('input');
            if (cell.innerHTML.includes(symbol)) {
                cell.innerHTML = '<input type="hidden" name="' + input.name + '" value="">';
                cell.style.color = '';
            } else {
                cell.innerHTML = symbol + '<input type="hidden" name="' + input.name + '" value="' + symbol + '">';
                cell.style.fontWeight = 'bold';
                cell.style.color = 'blue';
            }
        }
    </script>

<br>
    <div style="border: 1px solid black; padding: 5px; height: 30px;">
        <strong>Catatan:</strong> 
        <input type="text" name="catatan_bawah" value="<?php echo htmlspecialchars($catatan_bawah); ?>" style="width: 90%;">
    </div>

    <table style="margin-top: 10px; border: 1px solid black;">
        <tr>
            <td width="35%" style="vertical-align: top; padding: 0;">
                <div style="background: #e9ecef; padding: 5px; font-weight: bold; border-bottom: 1px solid black;">
                    Aldrette Score / Steward Score
                </div>
                <div style="padding: 10px;">
                    <label style="display:block; margin-bottom: 3px;">
                        <input type="radio" name="keputusan" value="pulang" <?php echo ($keputusan == "Boleh Pulang") ? 'checked' : ''; ?>> Boleh pulang
                    </label>
                    <label style="display:block; margin-bottom: 3px;">
                        <input type="radio" name="keputusan" value="ruangan" <?php echo ($keputusan == "Kembali ke Ruangan") ? 'checked' : ''; ?>> Kembali ke Ruangan
                    </label>
                    <label style="display:block;">
                        <input type="radio" name="keputusan" value="mrs" <?php echo ($keputusan == "MRS") ? 'checked' : ''; ?>> MRS
                    </label>
                </div>
            </td>
            
            <!-- TANDA TANGAN DOKTER -->
            <td width="32%" style="text-align: center; vertical-align: top; border-left: 1px solid black; padding: 0;">
                <div style="background: #e9ecef; padding: 5px; font-weight: bold; border-bottom: 1px solid black;">
                    Tanda Tangan Dokter
                </div>
                <div style="padding: 5px;">
                    <!-- Jika ada TTD, gambar ulang di canvas via JS (sedikit kompleks), 
                         Solusi sederhana: Tampilkan gambar jika ada, jika mau edit hapus dulu -->
                    <?php if(!empty($ttd_dokter)): ?>
                        <img src="<?php echo $ttd_dokter; ?>" id="imgDokter" style="height: 70px; display: block; margin: 0 auto;">
                        <canvas id="canvasDokter" class="signature-pad" width="200" height="70" style="display: none;"></canvas>
                        <button type="button" class="btn-clear" onclick="enableDraw('canvasDokter', 'imgDokter')">Ubah Tanda Tangan</button>
                    <?php else: ?>
                        <canvas id="canvasDokter" class="signature-pad" width="200" height="70"></canvas>
                        <button type="button" class="btn-clear" onclick="clearCanvas('canvasDokter')">Hapus</button>
                    <?php endif; ?>
                    
                    <input type="text" name="nama_dokter" value="<?php echo htmlspecialchars($nama_dokter); ?>" class="input-name" placeholder="( Nama Dokter )">
                    <input type="hidden" name="ttd_dokter_base64" id="ttdDokterBase64" value="<?php echo $ttd_dokter; ?>">
                </div>
            </td>
            
            <!-- TANDA TANGAN PERAWAT -->
            <td width="33%" style="text-align: center; vertical-align: top; border-left: 1px solid black; padding: 0;">
                <div style="background: #e9ecef; padding: 5px; font-weight: bold; border-bottom: 1px solid black;">
                    Tanda Tangan Penata Anestesi
                </div>
                <div style="padding: 5px;">
                    <?php if(!empty($ttd_anestesi)): ?>
                        <img src="<?php echo $ttd_anestesi; ?>" id="imgAnestesi" style="height: 70px; display: block; margin: 0 auto;">
                        <canvas id="canvasAnestesi" class="signature-pad" width="200" height="70" style="display: none;"></canvas>
                        <button type="button" class="btn-clear" onclick="enableDraw('canvasAnestesi', 'imgAnestesi')">Ubah Tanda Tangan</button>
                    <?php else: ?>
                        <canvas id="canvasAnestesi" class="signature-pad" width="200" height="70"></canvas>
                        <button type="button" class="btn-clear" onclick="clearCanvas('canvasAnestesi')">Hapus</button>
                    <?php endif; ?>
                    
                    <input type="text" name="nama_anestesi" value="<?php echo htmlspecialchars($nama_anestesi); ?>" class="input-name" placeholder="( Nama Penata )">
                    <input type="hidden" name="ttd_anestesi_base64" id="ttdAnestesiBase64" value="<?php echo $ttd_anestesi; ?>">
                </div>
            </td>
        </tr>
    </table>
    
    <!-- BUTTONS -->
    <div class="btn-container">
        <button type="button" class="btn-modern btn-print" onclick="window.print()">
            <i class="fa fa-print"></i> PRINT
        </button>
        <button type="submit" id="btnSimpan" class="btn-modern btn-save">
            <i class="fa fa-save"></i> SIMPAN PERUBAHAN
        </button>
    </div>

</div>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Logic Drawpad
    function initSignaturePad(canvasId) {
        var canvas = document.getElementById(canvasId);
        if(!canvas) return; // Jika canvas di-hide (karena ada gambar), skip init
        var ctx = canvas.getContext('2d');
        var isDrawing = false;
        ctx.strokeStyle = '#000000'; ctx.lineWidth = 2;

        function startDraw(e) { isDrawing = true; ctx.beginPath(); var pos = getPos(canvas, e); ctx.moveTo(pos.x, pos.y); e.preventDefault(); }
        function draw(e) { if (!isDrawing) return; var pos = getPos(canvas, e); ctx.lineTo(pos.x, pos.y); ctx.stroke(); e.preventDefault(); }
        function endDraw() { isDrawing = false; updateHiddenInput(canvasId); }
        function getPos(canvas, e) {
            var rect = canvas.getBoundingClientRect();
            if (e.touches) { return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top }; } 
            else { return { x: e.clientX - rect.left, y: e.clientY - rect.top }; }
        }
        canvas.addEventListener('mousedown', startDraw); canvas.addEventListener('mousemove', draw); canvas.addEventListener('mouseup', endDraw); canvas.addEventListener('mouseout', endDraw);
        canvas.addEventListener('touchstart', startDraw); canvas.addEventListener('touchmove', draw); canvas.addEventListener('touchend', endDraw);
    }

    function updateHiddenInput(canvasId) {
        var canvas = document.getElementById(canvasId);
        var data = canvas.toDataURL('image/png');
        if (canvasId === 'canvasDokter') document.getElementById('ttdDokterBase64').value = data;
        if (canvasId === 'canvasAnestesi') document.getElementById('ttdAnestesiBase64').value = data;
    }

    function clearCanvas(canvasId) {
        var canvas = document.getElementById(canvasId);
        var ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        updateHiddenInput(canvasId); // Update jadi kosong/transparan
    }

    // Fungsi untuk mengganti Gambar Statis menjadi Canvas Interaktif
    function enableDraw(canvasId, imgId) {
        document.getElementById(imgId).style.display = 'none';
        var canvas = document.getElementById(canvasId);
        canvas.style.display = 'block';
        initSignaturePad(canvasId); // Init ulang canvas yang baru muncul
        clearCanvas(canvasId); // Bersihkan agar user menggambar ulang
    }

    // Init awal (hanya jika canvas terlihat)
    if(document.getElementById('canvasDokter').style.display !== 'none') initSignaturePad('canvasDokter');
    if(document.getElementById('canvasAnestesi').style.display !== 'none') initSignaturePad('canvasAnestesi');

    // SweetAlert Handling
    var statusSimpan = "<?php echo $status_simpan; ?>";
    var pesanSimpan = "<?php echo $pesan_simpan; ?>";

    if (statusSimpan === 'success') {
        Swal.fire({
            title: 'Berhasil!',
            text: pesanSimpan,
            icon: 'success',
            showConfirmButton: false,
            timer: 2000
        }).then((result) => {
            // Reload page dengan parameter regno yang sama
            var regno = document.querySelector('input[name="regno"]').value;
            window.location.href = 'catatan.php?regno=' + regno; 
        });
    } else if (statusSimpan === 'not_found') {
        Swal.fire({
            title: 'Regno Tidak Ditemukan!',
            text: 'Harap isi Formulir Status Sedasi terlebih dahulu.',
            icon: 'warning',
            confirmButtonText: 'Kembali',
            confirmButtonColor: '#d33'
        });
    } else if (statusSimpan === 'error') {
        Swal.fire('Error!', pesanSimpan, 'error');
    }
</script>

</body>
</html>
