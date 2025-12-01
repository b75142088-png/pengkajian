<?php
session_start();

// Konfigurasi database
$host = "localhost";
$username = "root"; 
$password = "";
$database = "rumah_sakit";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tanggal_handover = $_POST['tanggal_handover'];
    $ruangan = $_POST['ruangan'];
    
    $success = 0;
    for ($i=1; $i<=6; $i++){
        if(!empty($_POST["nama_pasien$i"])){
            $sql = "INSERT INTO handover_jaga 
                    (tanggal_handover,ruangan,kamar_pasien,nama_pasien,no_rm,kondisi_pagi,
                    petugas_menyerahkan,petugas_menerima,ttd_menyerahkan,ttd_penerima)
                    VALUES ('{$tanggal_handover}','{$ruangan}',
                    '{$_POST["kamar_pasien$i"]}','{$_POST["nama_pasien$i"]}','{$_POST["no_rm$i"]}','{$_POST["kondisi_pagi$i"]}',
                    '{$_POST["petugas_menyerahkan$i"]}','{$_POST["petugas_menerima$i"]}',
                    '{$_POST["petugas_menyerahkan$i"]}','{$_POST["petugas_menerima$i"]}')";
            mysqli_query($conn,$sql);
            $success++;
        }
    }

    $_SESSION['notif'] = $success;
}

$default_tanggal = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>HANDOVER JAGA PAGI KE SORE</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- QR library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
    @media print {
        .no-print {
            display: none !important;
        }

        .qrcode {
            display: block !important;
            visibility: visible;
            opacity: 1;
            width: fit-content;
        }
    }

    .qrcode {
        display: none;
    }
    </style>
</head>

<body class="bg-light p-4">

    <?php if(isset($_SESSION['notif'])): ?>
    <script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil Disimpan!',
        text: '<?= $_SESSION["notif"] ?> data berhasil ditambahkan.',
        timer: 1500,
        showConfirmButton: false
    });
    </script>
    <?php unset($_SESSION['notif']); endif; ?>

    <div class="container bg-white p-4 rounded shadow" style="max-width:1100px">

        <h2 class="text-center fw-bold">HANDOVER JAGA PAGI → SORE</h2>
        <p class="text-center text-muted mb-4">RSUD SANJIWANI GIANYAR</p>

        <form method="POST">

            <!-- Input data -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Tanggal</label>
                    <input type="date" id="tanggal_handover" name="tanggal_handover" value="<?= $default_tanggal ?>"
                        class="form-control text-center">
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Ruangan</label>
                    <input type="text" name="ruangan" value="KELAS III" class="form-control text-center">
                </div>
            </div>

            <table class="table table-bordered text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Kamar</th>
                        <th>Nama Pasien</th>
                        <th>No RM</th>
                        <th>Kondisi Pagi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i=1;$i<=6;$i++): ?>
                    <tr>
                        <td><input class="form-control text-center" type="text" name="kamar_pasien<?= $i ?>"></td>
                        <td><input class="form-control text-center" type="text" name="nama_pasien<?= $i ?>"></td>
                        <td><input class="form-control text-center" type="text" name="no_rm<?= $i ?>"></td>
                        <td><input class="form-control text-center" type="text" name="kondisi_pagi<?= $i ?>"></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <table class="table table-bordered text-center align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Petugas Menyerahkan</th>
                        <th>Petugas Menerima</th>
                        <th>QR Menyerahkan</th>
                        <th>QR Menerima</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i=1;$i<=5;$i++): ?>
                    <tr>
                        <td><input class="form-control text-center petugas_menyerahkan" type="text"
                                name="petugas_menyerahkan<?= $i ?>"></td>
                        <td><input class="form-control text-center petugas_menerima" type="text"
                                name="petugas_menerima<?= $i ?>"></td>
                        <td>
                            <div id="qr_menyerahkan_<?= $i ?>" class="qrcode"></div>
                        </td>
                        <td>
                            <div id="qr_menerima_<?= $i ?>" class="qrcode"></div>
                        </td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <!-- Buttons -->
            <div class="text-center">
                <button class="btn btn-success btn-lg no-print" type="submit">💾 SIMPAN</button>
                <button class="btn btn-danger btn-lg no-print" type="reset">♻ RESET</button>
            </div>
        </form>
    </div>

    <script>
    window.onbeforeprint = function() {
        let tanggal = document.getElementById("tanggal_handover").value;
        let ruangan = document.querySelector("input[name='ruangan']").value;
        let base = window.location.origin + window.location.pathname.replace(/[^\/]+$/, "");

        document.querySelectorAll(".petugas_menyerahkan").forEach((input, i) => {
            let val = input.value.trim();
            let div = document.getElementById("qr_menyerahkan_" + (i + 1));
            div.innerHTML = "";

            if (val !== "") {
                let url =
                    base + "qrcode_handover.php?" +
                    "mode=menyerahkan" +
                    "&menyerahkan=" + encodeURIComponent(val) +
                    "&tanggal=" + encodeURIComponent(tanggal) +
                    "&ruangan=" + encodeURIComponent(ruangan);

                new QRCode(div, {
                    text: url,
                    width: 120,
                    height: 120
                });
            }
        });

        document.querySelectorAll(".petugas_menerima").forEach((input, i) => {
            let val = input.value.trim();
            let div = document.getElementById("qr_menerima_" + (i + 1));
            div.innerHTML = "";

            if (val !== "") {
                let url =
                    base + "qrcode_handover.php?" +
                    "mode=menerima" +
                    "&menerima=" + encodeURIComponent(val) +
                    "&tanggal=" + encodeURIComponent(tanggal) +
                    "&ruangan=" + encodeURIComponent(ruangan);

                new QRCode(div, {
                    text: url,
                    width: 120,
                    height: 120
                });
            }
        });
    };
    </script>

</body>

</html>