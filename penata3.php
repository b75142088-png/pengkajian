<?php
// =============================================
// Backend PHP Sederhana (Untuk menangani Submit)
// =============================================
$pesan_sukses = "";
$signature_preview = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Menangkap Data Form (Contoh)
    $nama_pasien = $_POST['nama_pasien'] ?? '';

    // 2. Menangkap Data Tanda Tangan (Base64)
    if (!empty($_POST['signature_image'])) {
        $data_uri = $_POST['signature_image'];
        // Di sini Anda bisa simpan ke database atau file server
        // $encoded_image = explode(",", $data_uri)[1];
        // file_put_contents("ttd_".$nama_pasien.".png", base64_decode($encoded_image));

        $signature_preview = $data_uri; // Untuk preview
    }

    $pesan_sukses = "Data Asesmen Pra Anestesi & Tanda Tangan berhasil disubmit.";
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
        /* ========================================================================
           CSS KHUSUS UNTUK MENIRU TAMPILAN KERTAS (HARDCOPY LOOK-ALIKE)
        ======================================================================== */
        :root {
            --header-green: #00FFCC;
            /* Warna hijau terang dari gambar */
            --border-color: #000;
            /* Batas hitam tegas */
        }

        body {
            background-color: #525659;
            /* Latar belakang abu-abu seperti PDF reader */
            font-family: "Times New Roman", Times, serif;
            /* Font Serif agar terlihat resmi */
            font-size: 11px;
            /* Ukuran font kecil agar muat */
            color: #000;
        }

        /* Kontainer Kertas A4 */
        .page-a4 {
            background: white;
            width: 210mm;
            /* Lebar A4 standar */
            min-height: 297mm;
            /* Tinggi A4 standar */
            margin: 20px auto;
            padding: 10mm;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            border: 1px solid #ccc;
            box-sizing: border-box;
            position: relative;
        }

        /* --- Utility: Input Garis Bawah (PENTING) --- */
        .input-line {
            border: none;
            border-bottom: 1px dotted #000;
            /* Garis putus-putus halus */
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

        /* Textarea tanpa border kotak */
        textarea.flat-textarea {
            width: 100%;
            border: none;
            resize: none;
            font-family: inherit;
            font-size: inherit;
            padding: 2px;
            background: transparent;
            overflow: hidden;
        }

        /* --- Utility: Header & Layout --- */
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

        /* Tabel Super Padat */
        table.dense-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.dense-table td {
            padding: 1px 2px;
            vertical-align: top;
        }

        /* Checkbox/Radio ala Formulir */
        input[type="radio"],
        input[type="checkbox"] {
            margin: 0 2px 0 5px;
            vertical-align: middle;
            transform: scale(0.85);
        }

        .yt-label {
            margin-right: 8px;
        }

        .logo-placeholder {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #999;
            text-align: center;
            margin: 0 auto;
        }

        /* --- CSS TANDA TANGAN (CANVAS) --- */
        .signature-wrapper {
            position: relative;
            width: 100%;
            height: 100px;
            border: 2px dashed #999;
            background-color: #fcfcfc;
            margin-top: 5px;
            cursor: crosshair;
        }

        .signature-wrapper canvas {
            display: block;
            width: 100%;
            height: 100%;
        }

        /* --- Print Specific CSS --- */
        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }

            html,
            body {
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
                zoom: 0.95;
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

            .signature-wrapper {
                border: none;
                background: transparent;
            }

            /* Hilangkan kotak saat print */
        }
    </style>
</head>

<body>

    <?php if ($pesan_sukses): ?>
        <div class="alert alert-success text-center no-print m-3">
            <?php echo $pesan_sukses; ?>
            <?php if ($signature_preview): ?>
                <div class="mt-2"><small>Preview TTD:</small><br><img src="<?php echo $signature_preview; ?>" style="height:50px; border:1px solid #ccc;"></div>
            <?php endif; ?>
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
                    <img src="logo.png" alt="" style="height: 80px;">
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
                            <td>: <input type="text" name="no_rm" class="input-line" style="width: 140px;"></td>
                        </tr>
                        <tr>
                            <td>Nama</td>
                            <td>: <input type="text" name="nama_pasien" class="input-line" style="width: 140px;"></td>
                        </tr>
                        <tr>
                            <td>Tgl.Lahir</td>
                            <td>: <input type="text" name="tgl_lahir" class="input-line" style="width: 140px;"></td>
                        </tr>
                        <tr>
                            <td>Jenis Kelamin</td>
                            <td>:
                                <label><input type="radio" name="jk_header" value="L"> L</label>
                                <label class="ms-3"><input type="radio" name="jk_header" value="P"> P</label>
                            </td>
                        </tr>
                        <tr>
                            <td>Regno</td>
                            <td>: <input type="text" name="regno" class="input-line" style="width: 140px;"></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="border-box d-flex p-1 justify-content-between" style="border-top: none; font-size: 11px;">
                <div>Ruangan : <input type="text" name="ruangan" class="input-line" style="width: 150px;"></div>
                <div>
                    Tanggal : <input type="date" name="tgl_asesmen" class="input-line">
                    Jam : <input type="time" name="jam_asesmen" class="input-line"> WITA
                </div>
            </div>
            <div style="border: 1px solid #000; padding: 5px;">
                <div style="font-weight: bold; border-bottom: 1px solid black; margin-top: 5px;">
                    I. Data Diisi Oleh Penata Anestesi
                </div>

                <div class="d-flex justify-content-between mt-2 mb-2" style="font-size: 11px;">
                    <div>Umur: <input type="text" name="umur" class="input-line" style="width: 40px;"></div>
                    <div>Jenis Kelamin: <label><input type="radio" name="jk_penata" value="L"> L</label> <label class="ms-2"><input type="radio" name="jk_penata" value="P"> P</label></div>
                    <div>Menikah: <label><input type="radio" name="menikah" value="Y"> Y</label> <label class="ms-2"><input type="radio" name="menikah" value="T"> T</label></div>
                    <div>Pekerjaan: <input type="text" name="pekerjaan" class="input-line" style="width: 120px;"></div>
                </div>

                <div class="section-title">KEBIASAAN</div>
                <table class="dense-table mb-1">
                    <tr>
                        <td width="50%">Merokok: <label><input type="radio" name="rokok" value="Y"> Y</label> <label class="yt-label"><input type="radio" name="rokok" value="T"> T</label> Sebanyak: <input type="text" class="input-line" style="width: 80px;"></td>
                        <td>Kopi/teh/soda: <label><input type="radio" name="kopi" value="Y"> Y</label> <label class="yt-label"><input type="radio" name="kopi" value="T"> T</label> Sebanyak: <input type="text" class="input-line" style="width: 80px;"></td>
                    </tr>
                    <tr>
                        <td>Alkohol: <label><input type="radio" name="alkohol" value="Y"> Y</label> <label class="yt-label"><input type="radio" name="alkohol" value="T"> T</label> Sebanyak: <input type="text" class="input-line" style="width: 80px;"></td>
                        <td>Olahraga rutin: <label><input type="radio" name="olahraga" value="Y"> Y</label> <label class="yt-label"><input type="radio" name="olahraga" value="T"> T</label> Sebanyak: <input type="text" class="input-line" style="width: 80px;"></td>
                    </tr>
                </table>

                <div class="section-title" style="margin-bottom: 0;">PENGOBATAN: (Sebutkan dosis atau jumlah pil per hari)</div>
                <div class="mb-1">
                    <label class="me-4"><input type="checkbox" name="obat_resep"> Obat Resep</label>
                    <label><input type="checkbox" name="obat_bebas"> Obat bebas (vitamin, herbal): <input type="text" class="input-line" style="width: 200px;"></label>
                </div>
                <table class="dense-table mb-1">
                    <tr>
                        <td width="200">Penggunaan Aspirin rutin</td>
                        <td>: <label><input type="radio" name="aspirin" value="Y"> Y</label> <label class="yt-label"><input type="radio" name="aspirin" value="T"> T</label> Dosis dan frekuensi: <input type="text" class="input-line" style="width: 150px;"></td>
                    </tr>
                    <tr>
                        <td>Obat anti sakit</td>
                        <td>: <label><input type="radio" name="painkiller" value="Y"> Y</label> <label class="yt-label"><input type="radio" name="painkiller" value="T"> T</label> Dosis dan frekuensi: <input type="text" class="input-line" style="width: 150px;"></td>
                    </tr>
                    <tr>
                        <td>Injeksi steroid tahun-tahun terakhir</td>
                        <td>: <label><input type="radio" name="steroid" value="Y"> Y</label> <label class="yt-label"><input type="radio" name="steroid" value="T"> T</label> Tanggal dan lokasi injeksi: <input type="text" class="input-line" style="width: 150px;"></td>
                    </tr>
                    <tr>
                        <td>Alergi obat</td>
                        <td>: <label><input type="radio" name="alergi_obat" value="Y"> Y</label> <label class="yt-label"><input type="radio" name="alergi_obat" value="T"> T</label> Daftar obat dan tipe reaksi: <input type="text" class="input-line" style="width: 150px;"></td>
                    </tr>
                </table>
                <div class="mb-2">
                    Alergi lateks: <label><input type="radio" name="alt" value="Y"> Y</label> <label class="yt-label me-4"><input type="radio" name="alt" value="T"> T</label>
                    Alergi plester: <label><input type="radio" name="alp" value="Y"> Y</label> <label class="yt-label me-4"><input type="radio" name="alp" value="T"> T</label>
                    Alergi makanan: <label><input type="radio" name="alm" value="Y"> Y</label> <label class="yt-label"><input type="radio" name="alm" value="T"> T</label>
                </div>

                <div class="section-title">RIWAYAT KELUARGA (Apakah keluarga mendapat permasalahan seperti di bawah ini):</div>
                <div class="row g-0">
                    <div class="col-6">
                        <table class="dense-table">
                            <?php
                            $keluarga_kiri = ['Perdarahan yang tidak normal', 'Pembekuan darah tidak normal', 'Permasalahan dalam pembiusan', 'Operasi jantung koroner', 'Diabetes'];
                            foreach ($keluarga_kiri as $k) {
                                echo "<tr><td>$k</td><td>: <label><input type='radio' name='rk_" . str_replace(' ', '', $k) . "' value='Y'> Y</label> <label><input type='radio' name='rk_" . str_replace(' ', '', $k) . "' value='T'> T</label></td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="dense-table ps-2">
                            <?php
                            $keluarga_kanan = ['Serangan jantung', 'Hipertensi', 'Tuberkulosis', 'Penyakit berat lainnya'];
                            foreach ($keluarga_kanan as $k) {
                                echo "<tr><td width='160'>$k</td><td>: <label><input type='radio' name='rk_" . str_replace(' ', '', $k) . "' value='Y'> Y</label> <label><input type='radio' name='rk_" . str_replace(' ', '', $k) . "' value='T'> T</label></td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                </div>
                <div class="mb-1">Jelaskan penyakit keluarga apa bila dijawab "Ya": <input type="text" class="input-line" style="width: 50%;"></div>

                <div class="section-title">KOMUNIKASI</div>
                <div class="mb-1">
                    Bahasa: <label class="me-3"><input type="checkbox" name="bahasa_indo"> Indonesia</label>
                    <label><input type="checkbox" name="bahasa_lain"> Lainnya: <input type="text" class="input-line" style="width: 150px;"></label>
                </div>
                <table class="dense-table mb-2">
                    <tr>
                        <td width="180">Gangguan Penglihatan/Buta</td>
                        <td>: <label><input type="radio" name="kom_mata" value="Y"> Y</label> <label><input type="radio" name="kom_mata" value="T"> T</label></td>
                    </tr>
                    <tr>
                        <td>Gangguan Pendengaran/Tuli</td>
                        <td>: <label><input type="radio" name="kom_telinga" value="Y"> Y</label> <label><input type="radio" name="kom_telinga" value="T"> T</label></td>
                    </tr>
                    <tr>
                        <td>Gangguan Bicara</td>
                        <td>: <label><input type="radio" name="kom_bicara" value="Y"> Y</label> <label><input type="radio" name="kom_bicara" value="T"> T</label></td>
                    </tr>
                </table>

                <div class="section-title">RIWAYAT PENYAKIT PASIEN: Apakah pasien pernah menderita penyakit di bawah ini?</div>
                <div class="row g-0">
                    <div class="col-6">
                        <table class="dense-table">
                            <?php
                            $pasien_kiri = ['Perdarahan tidak normal', 'Pembekuan darah tidak normal', 'Sakit maag', 'Anemia', 'Sesak napas', 'Asma', 'Pingsan'];
                            foreach ($pasien_kiri as $p) {
                                echo "<tr><td>$p</td><td>: <label><input type='radio' name='rp_" . str_replace(' ', '', $p) . "' value='Y'> Y</label> <label><input type='radio' name='rp_" . str_replace(' ', '', $p) . "' value='T'> T</label></td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="dense-table ps-2">
                            <?php
                            $pasien_kanan = ['Serangan jantung/Nyeri dada', 'Hepatitis/sakit kuning', 'Hipertensi', 'Sumbatan jalan nafas saat Tidur/Mengorok', 'Penyakit berat lainnya', 'Diabetes'];
                            foreach ($pasien_kanan as $p) {
                                echo "<tr><td width='220'>$p</td><td>: <label><input type='radio' name='rp_" . str_replace(' ', '', $p) . "' value='Y'> Y</label> <label><input type='radio' name='rp_" . str_replace(' ', '', $p) . "' value='T'> T</label></td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                </div>
                <div class="mb-2">Jelaskan penyakit yang dijawab "Ya" : <input type="text" class="input-line" style="width: 50%;"></div>

                <table class="dense-table mb-1">
                    <tr>
                        <td>Apakah pasien pernah mendapatkan transfusi darah?</td>
                        <td><label><input type="radio" name="transfusi" value="Y"> Y</label> <label class="yt-label"><input type="radio" name="transfusi" value="T"> T</label> Bila ya, tahun berapa? <input type="text" class="input-line" style="width: 80px;"></td>
                    </tr>
                    <tr>
                        <td>Apakah pasien pernah diperiksa untuk diagnosis HIV?</td>
                        <td><label><input type="radio" name="hiv_check" value="Y"> Y</label> <label class="yt-label"><input type="radio" name="hiv_check" value="T"> T</label> Bila ya, tahun berapa? <input type="text" class="input-line" style="width: 80px;"></td>
                    </tr>
                    <tr>
                        <td>Hasil pemeriksaan HIV :</td>
                        <td><label class="me-3"><input type="radio" name="hiv_res" value="Positif"> Positif</label> <label><input type="radio" name="hiv_res" value="Negatif"> Negatif</label></td>
                    </tr>
                </table>

                <div class="mb-2">
                    Apakah pasien memakai ? <br>
                    Lensa kontak : <label><input type="radio" name="lk" value="Y"> Y</label> <label class="yt-label me-3"><input type="radio" name="lk" value="T"> T</label>
                    Kacamata : <label><input type="radio" name="km" value="Y"> Y</label> <label class="yt-label me-3"><input type="radio" name="km" value="T"> T</label>
                    Alat bantu dengar : <label><input type="radio" name="abd" value="Y"> Y</label> <label class="yt-label me-3"><input type="radio" name="abd" value="T"> T</label>
                    Gigi palsu : <label><input type="radio" name="gp" value="Y"> Y</label> <label class="yt-label"><input type="radio" name="gp" value="T"> T</label>
                </div>

                <div class="section-title">Riwayat operasi, tahun dan jenis operasi:</div>
                <div class="mb-2">
                    Jenis anestesi yang digunakan dan sebutkan komplikasi/reaksi yang dialami:
                    <table class="dense-table ps-3">
                        <tr>
                            <td width="220">Anestesia lokal-komplikasi/reaksi</td>
                            <td>: <input type="text" class="input-line" style="width: 80%;"></td>
                        </tr>
                        <tr>
                            <td>Anestesia regional-komplikasi/reaksi</td>
                            <td>: <input type="text" class="input-line" style="width: 80%;"></td>
                        </tr>
                        <tr>
                            <td>Anestesia umum-komplikasi/reaksi</td>
                            <td>: <input type="text" class="input-line" style="width: 80%;"></td>
                        </tr>
                    </table>
                    Tanggal terakhir kali periksa kesehatan ke dokter: <input type="date" class="input-line"> dimana: <input type="text" class="input-line" style="width: 200px;"><br>
                    Untuk penyakit gangguan apa: <input type="text" class="input-line" style="width: 80%;">
                </div>

                <div class="section-title">KHUSUS PASIEN PEREMPUAN :</div>
                <div class="d-flex justify-content-between">
                    <div>Jumlah kehamilan: <input type="text" class="input-line" style="width: 50px;"></div>
                    <div>Jumlah anak: <input type="text" class="input-line" style="width: 50px;"></div>
                    <div>Menstruasi: <input type="text" class="input-line" style="width: 100px;"></div>
                    <div>Menyusui: <label><input type="radio" name="menyusui" value="Y"> Y</label> <label><input type="radio" name="menyusui" value="T"> T</label></div>
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
                <div class="section-title">KAJIAN SISTEM</div>
                <div>Anamnesis Singkat:</div>
                <div class="border-box mb-2" style="height: 80px;">
                    <textarea name="anamnesis" class="flat-textarea" style="height: 100%;"></textarea>
                </div>

                <div class="row g-0 mb-1">
                    <div class="col-6">
                        <table class="dense-table">
                            <?php
                            $dok_kiri = ['Hilangnya gigi', 'Masalah mobilisasi leher', 'Leher pendek', 'Batuk', 'Sesak nafas', 'Baru saja menderita infeksi saluran nafas atas', 'Periode menstruasi tidak normal', 'Stroke'];
                            foreach ($dok_kiri as $d) {
                                echo "<tr><td width='180'>$d</td><td>: <label><input type='radio' name='dok_" . str_replace(' ', '', $d) . "' value='Y'> Y</label> <label><input type='radio' name='dok_" . str_replace(' ', '', $d) . "' value='T'> T</label></td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="dense-table ps-2">
                            <?php
                            $dok_kanan = ['Sakit dada', 'Denyut jantung tidak normal', 'Muntah', 'Susah kencing', 'Kejang', 'Sedang hamil', 'Pingsan', 'Obesitas'];
                            foreach ($dok_kanan as $d) {
                                echo "<tr><td width='160'>$d</td><td>: <label><input type='radio' name='dok_" . str_replace(' ', '', $d) . "' value='Y'> Y</label> <label><input type='radio' name='dok_" . str_replace(' ', '', $d) . "' value='T'> T</label></td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                </div>
                <div class="mb-1">Keterangan: <input type="text" class="input-line" style="width: 80%;"></div>
                <hr style="border-top: 1px solid black; opacity: 1; margin: 5px 0;">

                <div class="section-title">KEADAAN UMUM</div>
                <div class="d-flex justify-content-between mb-1">
                    <div style="width: 25%;">Kesadaran: <input type="text" class="input-line" style="width: 60%;"></div>
                    <div style="width: 25%;">Visus: <input type="text" class="input-line" style="width: 60%;"></div>
                    <div style="width: 25%;">Faring: <input type="text" class="input-line" style="width: 60%;"></div>
                    <div style="width: 25%;">Gigi palsu: <input type="text" class="input-line" style="width: 60%;"></div>
                </div>
                <div class="mb-2">Keterangan: <input type="text" class="input-line" style="width: 80%;"></div>

                <div class="section-title">PEMERIKSAAN FISIK</div>
                <div class="mb-2">
                    Tinggi: <input type="text" class="input-line" style="width: 30px;"> cm &nbsp;
                    Berat: <input type="text" class="input-line" style="width: 30px;"> kg &nbsp;
                    TD: <input type="text" class="input-line" style="width: 60px;"> mmHg &nbsp;
                    Nadi: <input type="text" class="input-line" style="width: 40px;"> x/menit &nbsp;
                    Pernafasan: <input type="text" class="input-line" style="width: 40px;"> x/menit &nbsp;
                    Suhu: <input type="text" class="input-line" style="width: 40px;"> °C
                </div>
                <table class="dense-table mb-2">
                    <tr>
                        <td width="150">Paru-paru</td>
                        <td>: <input type="text" class="input-line" style="width: 100%;"></td>
                    </tr>
                    <tr>
                        <td>Jantung</td>
                        <td>: <input type="text" class="input-line" style="width: 100%;"></td>
                    </tr>
                    <tr>
                        <td>Abdomen</td>
                        <td>: <input type="text" class="input-line" style="width: 100%;"></td>
                    </tr>
                    <tr>
                        <td>Ekstrimitas</td>
                        <td>: <input type="text" class="input-line" style="width: 100%;"></td>
                    </tr>
                    <tr>
                        <td>Neurologi (bila dapat diperiksa)</td>
                        <td>: <input type="text" class="input-line" style="width: 100%;"></td>
                    </tr>
                    <tr>
                        <td>Keterangan lainnya</td>
                        <td>: <input type="text" class="input-line" style="width: 100%;"></td>
                    </tr>
                </table>

                <div class="section-title">LABORATORIUM (bila tersedia)</div>
                <div class="row g-0">
                    <div class="col-6">
                        <table class="dense-table">
                            <tr>
                                <td width="80">Hb/Ht</td>
                                <td>: <input type="text" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>PT/APTT</td>
                                <td>: <input type="text" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>Tes kehamilan</td>
                                <td>: <input type="text" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>Kalium</td>
                                <td>: <input type="text" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>Ureum</td>
                                <td>: <input type="text" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>Keterangan</td>
                                <td>: <input type="text" class="input-line" style="width: 80%;"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="dense-table ps-3">
                            <tr>
                                <td width="80">Rontgen dada</td>
                                <td>: <input type="text" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>EKG</td>
                                <td>: <input type="text" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>Na/Cl</td>
                                <td>: <input type="text" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>CO2</td>
                                <td>: <input type="text" class="input-line" style="width: 80%;"></td>
                            </tr>
                            <tr>
                                <td>Lain-lain</td>
                                <td>: <input type="text" class="input-line" style="width: 80%;"></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="section-title mt-3">DAFTAR MASALAH :</div>
                <div class="border-box mb-2" style="height: 50px;">
                    <textarea name="masalah" class="flat-textarea" style="height: 100%;"></textarea>
                </div>

                <div class="section-title">DIAGNOSIS :</div>
                <div class="mb-2">
                    Klasifikasi berdasarkan ASA :
                    <select name="asa" style="font-family: inherit; font-size: inherit; padding: 1px;">
                        <option value="">-- Pilih ASA --</option>
                        <option value="ASA 1">ASA 1</option>
                        <option value="ASA 2">ASA 2</option>
                        <option value="ASA 3">ASA 3</option>
                        <option value="ASA 4">ASA 4</option>
                        <option value="ASA 5">ASA 5</option>
                        <option value="E">E (Emergency)</option>
                    </select>
                </div>

                <div class="section-title">SARAN :</div>
                <div class="border-box mb-2" style="height: 50px;">
                    <textarea name="saran" class="flat-textarea" style="height: 100%;"></textarea>
                </div>

                <div class="section-title">REKOMENDASI TINDAKAN ANESTESI YANG DIPILIH:</div>
                <table class="dense-table mb-3 ps-2">
                    <tr>
                        <td width="130"><label><input type="checkbox" name="ane_umum"> Anestesi Umum :</label></td>
                        <td>
                            <label class="me-3"><input type="checkbox" name="au_iv"> Intravena</label>
                            <label class="me-3"><input type="checkbox" name="au_sm"> Sungkup Muka</label>
                            <label class="me-3"><input type="checkbox" name="au_lma"> Laringeal Mask Airway</label>
                            <label><input type="checkbox" name="au_ett"> Pipa Endotrakeal Tube</label>
                        </td>
                    </tr>
                    <tr>
                        <td><label><input type="checkbox" name="ane_reg"> Regional Anestesi :</label></td>
                        <td>
                            <label class="me-3"><input type="checkbox" name="ar_sab"> Spinal Anestesi Blok</label>
                            <label class="me-3"><input type="checkbox" name="ar_epi"> Epidural</label>
                            <label class="me-3"><input type="checkbox" name="ar_cse"> Combinasi Spinal Epidural</label>
                            <label><input type="checkbox" name="ar_pnb"> Peripheral Nerve Block</label>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><label><input type="checkbox" name="ane_umum_reg"> Anestesi Umum + Regional Anestesi</label></td>
                    </tr>
                </table>

                <div class="mb-4">
                    Puasa mulai : Jam <input type="time" name="puasa_jam" class="input-line"> Wita &nbsp;&nbsp;
                    Tanggal <input type="date" name="puasa_tgl" class="input-line">
                </div>

                <div class="row no-print">
                    <div class="col-6 offset-6 text-center" style="width: 250px; margin-left: auto;">
                        <strong>Tanda Tangan Dokter</strong>

                        <div class="signature-wrapper">
                            <canvas id="signature-pad"></canvas>
                        </div>
                        <input type="hidden" name="signature_image" id="signature-image-input">

                        <button type="button" class="btn btn-outline-danger btn-sm py-0 mt-1" id="clear-signature" style="font-size: 10px;">Hapus / Ulangi</button>

                        <div class="mt-2">
                            ( <input type="text" name="nama_dokter_ttd" class="input-line text-center" placeholder="Nama Dokter" style="width: 180px;"> )
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 fst-italic" style="font-size: 10px;">
                Catatan : isilah tanda (v) sesuai dengan pilihan dan coret yang tidak perlu pada tanda (*)
            </div>
        </div>

        <div class="text-center mb-5 no-print">
            <button type="submit" class="btn btn-primary btn-lg px-5">Simpan Data Rekam Medis</button>
        </div>

    </form>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var canvas = document.getElementById('signature-pad');
            var signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)', // Transparan
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
        });
    </script>

</body>

</html>