<?php
// Ambil data dari URL
$mode        = $_GET['mode'] ?? '';   // menyerahkan / menerima
$menyerahkan = $_GET['menyerahkan'] ?? '';
$menerima    = $_GET['menerima'] ?? '';
$tanggal     = $_GET['tanggal'] ?? '';
$ruangan     = $_GET['ruangan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Handover</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #eef2f7;
        }
        .card-custom {
            max-width: 600px;
            margin: auto;
            margin-top: 50px;
            border-radius: 12px;
            border: none;
            box-shadow: 0px 6px 18px rgba(0,0,0,0.1);
        }
        .title-header {
            background: #0d6efd;
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 18px;
        }
        .data-item {
            font-size: 18px;
            padding: 10px 0;
        }
        .label-text {
            font-weight: bold;
            color: #495057;
        }
        .btn-print {
            width: 100%;
            font-size: 18px;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>

<body>

<div class="card card-custom">
    <div class="title-header text-center">
        <h3 class="fw-bold m-0">DETAIL HANDOVER</h3>
    </div>

    <div class="card-body">

        <div class="data-item">
            <span class="label-text">Tanggal:</span> 
            <?= htmlspecialchars($tanggal) ?>
        </div>

        <div class="data-item">
            <span class="label-text">Ruangan:</span> 
            <?= htmlspecialchars($ruangan) ?>
        </div>

        <hr>

        <?php if ($mode === "menyerahkan"): ?>
            <div class="data-item">
                <span class="label-text">Petugas Menyerahkan:</span> 
                <?= htmlspecialchars($menyerahkan) ?>
            </div>
        <?php endif; ?>

        <?php if ($mode === "menerima"): ?>
            <div class="data-item">
                <span class="label-text">Petugas Menerima:</span> 
                <?= htmlspecialchars($menerima) ?>
            </div>
        <?php endif; ?>

        <hr>

        <button onclick="window.print()" class="btn btn-primary btn-print no-print">
            🖨️ Cetak Dokumen
        </button>
    </div>
</div>

</body>
</html>
