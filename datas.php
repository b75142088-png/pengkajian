<?php
// --- KONFIGURASI DATABASE ---
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_rsud_sedasi";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) { die("Koneksi gagal: " . mysqli_connect_error()); }

// --- LOGIKA PENCARIAN VIA URL (MULTI-FILTER) ---
$where_conditions = [];

// 1. Cek Filter REGNO
if (isset($_GET['regno']) && !empty($_GET['regno'])) {
    $regno = mysqli_real_escape_string($conn, $_GET['regno']);
    $where_conditions[] = "regno LIKE '%$regno%'";
}

// 2. Cek Filter NAMA
if (isset($_GET['nama_pasien']) && !empty($_GET['nama_pasien'])) {
    $nama = mysqli_real_escape_string($conn, $_GET['nama_pasien']);
    $where_conditions[] = "nama_pasien LIKE '%$nama%'";
}

// 3. Cek Filter TGL LAHIR
if (isset($_GET['tgl_lahir']) && !empty($_GET['tgl_lahir'])) {
    $tgl_lahir = mysqli_real_escape_string($conn, $_GET['tgl_lahir']);
    $where_conditions[] = "tgl_lahir LIKE '%$tgl_lahir%'";
}

// 4. Cek Filter NO RM
if (isset($_GET['no_rm']) && !empty($_GET['no_rm'])) {
    $no_rm = mysqli_real_escape_string($conn, $_GET['no_rm']);
    $where_conditions[] = "no_rm LIKE '%$no_rm%'";
}

// 5. Cek Filter Umum (Keyword 'q')
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $q = mysqli_real_escape_string($conn, $_GET['q']);
    $where_conditions[] = "(regno LIKE '%$q%' OR nama_pasien LIKE '%$q%' OR tgl_lahir LIKE '%$q%' OR no_rm LIKE '%$q%')";
}

// --- MENYUSUN QUERY ---
$where_clause = "";
if (count($where_conditions) > 0) {
    $where_clause = " WHERE " . implode(" AND ", $where_conditions);
}

$query = "SELECT * FROM status_sedasi $where_clause ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Status Sedasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .card-header { 
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); /* Hijau Modern */
            color: white; 
            border-radius: 12px 12px 0 0 !important;
            padding: 15px 20px;
        }
        .table thead th { 
            background-color: #f1f3f5; 
            vertical-align: middle; 
            font-size: 0.85rem; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            color: #555;
        }
        .badge-reg { font-size: 0.8rem; background-color: #34495e !important; }
        .btn-action { border-radius: 50px; padding: 5px 12px; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list-ul me-2"></i> Data Status Sedasi</h5>
            <!-- Tombol Tambah diarahkan ke setatuss.php (kosong) -->
            <a href="setatuss.php" class="btn btn-light btn-sm fw-bold text-success shadow-sm">
                <i class="fas fa-plus-circle"></i> Tambah Baru
            </a>
        </div>
        
        <div class="card-body p-4">
            
            <!-- Fitur Pencarian Sederhana -->
            <form action="" method="get" class="mb-4">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Cari Nama, No RM, atau Regno..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <button class="btn btn-success" type="submit"><i class="fas fa-search"></i> Cari</button>
                    <?php if(isset($_GET['q'])): ?>
                        <a href="data.php" class="btn btn-outline-secondary" title="Reset"><i class="fas fa-sync-alt"></i></a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-center">
                            <th width="5%">No</th>
                            <th width="15%">Reg No</th>
                            <th class="text-start">Nama Pasien</th>
                            <th width="15%">Tgl Lahir</th>
                            <th width="15%">No RM</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) { 
                        ?>
                        <tr>
                            <td class="text-center text-muted"><?php echo $no++; ?></td>
                            <td class="text-center"><span class="badge badge-reg"><?php echo htmlspecialchars($row['regno']); ?></span></td>
                            <td class="fw-semibold">
                                <?php echo htmlspecialchars($row['nama_pasien']); ?>
                                <br><small class="text-muted" style="font-size: 0.75rem;">
                                    <?php echo ($row['jenis_kelamin'] == 'L') ? '<i class="fas fa-mars text-primary"></i> Laki-laki' : '<i class="fas fa-venus text-danger"></i> Perempuan'; ?>
                                </small>
                            </td>
                            <td class="text-center text-muted"><?php echo htmlspecialchars($row['tgl_lahir']); ?></td>
                            <td class="text-center fw-bold text-dark"><?php echo htmlspecialchars($row['no_rm']); ?></td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <!-- Tombol Edit: Mengirim Parameter ?regno=... ke setatuss.php -->
                                    <a href="setatus.php?regno=<?php echo $row['regno']; ?>" class="btn btn-sm btn-primary btn-action text-white shadow-sm me-1" title="Lihat/Edit Detail">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <!-- Tombol Hapus (Opsional jika ingin difungsikan nanti) -->
                                    <button class="btn btn-sm btn-danger btn-action btn-hapus shadow-sm" data-id="<?php echo $row['id']; ?>" data-nama="<?php echo htmlspecialchars($row['nama_pasien']); ?>" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php } } else { ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted bg-light">
                                <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i><br>
                                Data belum tersedia atau tidak ditemukan.
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 text-muted small">
                <i class="fas fa-info-circle me-1"></i> Total Data: <strong><?php echo mysqli_num_rows($result); ?></strong> pasien.
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // SweetAlert untuk Hapus (Demo Only - Logic hapus.php belum ada di script ini)
    const deleteButtons = document.querySelectorAll('.btn-hapus');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            
            Swal.fire({
                title: 'Hapus Data?',
                text: "Pasien " + nama + " akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect ke script hapus (misal hapus.php)
                    // window.location.href = 'hapus.php?id=' + id;
                    Swal.fire('Terhapus!', 'Data berhasil dihapus (Simulasi).', 'success');
                }
            });
        });
    });
</script>

</body>
</html>
