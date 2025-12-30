<?php
// MULAI SESSION
session_start();

// Masukkan file koneksi
require_once 'db_connection.php';

// --- LOGIKA HAPUS DATA ---
if (isset($_GET['hapus_id'])) {
    $id_hapus = $_GET['hapus_id'];
    try {
        $stmt_hapus = $pdo->prepare("DELETE FROM asesmen_pra_anestesi WHERE id = :id");
        $stmt_hapus->execute([':id' => $id_hapus]);
        
        $_SESSION['notif_sukses'] = "Data berhasil dihapus dari database.";
        
        $query_string = $_SERVER['QUERY_STRING'];
        $query_string = preg_replace('/(&?)hapus_id=[^&]*(&?)/', '$2', $query_string);
        
        if (!empty($query_string)) {
            header("Location: data.php?" . $query_string);
        } else {
            header("Location: data.php");
        }
        exit;
    } catch (PDOException $e) {
        $_SESSION['notif_error'] = "Gagal menghapus data: " . $e->getMessage();
    }
}

// --- LOGIKA PENCARIAN (FILTER) ---
$conditions = [];
$params = [];

$regno = $_GET['regno'] ?? '';
$nama  = $_GET['nama'] ?? '';
$no_rm = $_GET['no_rm'] ?? '';
$tgl_lahir = $_GET['tgl_lahir'] ?? '';

if (!empty($regno)) {
    $conditions[] = "regno LIKE :regno";
    $params[':regno'] = "%$regno%";
}
if (!empty($nama)) {
    $conditions[] = "nama_pasien LIKE :nama";
    $params[':nama'] = "%$nama%";
}
if (!empty($no_rm)) {
    $conditions[] = "no_rm LIKE :no_rm";
    $params[':no_rm'] = "%$no_rm%";
}
if (!empty($tgl_lahir)) {
    // Karena VARCHAR, kita coba cari sebagai string.
    // Jika input dari datepicker (Y-m-d), kita cari yang mengandung string tersebut
    $conditions[] = "tgl_lahir LIKE :tgl_lahir";
    $params[':tgl_lahir'] = "%$tgl_lahir%";
}

try {
    $sql = "SELECT * FROM asesmen_pra_anestesi";
    if (count($conditions) > 0) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    $sql .= " ORDER BY id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data_pasien = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Error mengambil data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pasien - Asesmen Pra Anestesi</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 10px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .table th { background-color: #0d6efd; color: white; vertical-align: middle; }
        .btn-action { margin: 0 2px; }
        .search-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="bi bi-hospital"></i> Data Asesmen Pra Anestesi</h2>
        <a href="penata3.php" class="btn btn-success btn-lg shadow-sm">
            <i class="bi bi-plus-circle"></i> Tambah Data Baru
        </a>
    </div>

    <?php if (isset($_SESSION['notif_sukses'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= $_SESSION['notif_sukses']; ?>',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
        <?php unset($_SESSION['notif_sukses']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['notif_error'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '<?= $_SESSION['notif_error']; ?>'
            });
        </script>
        <?php unset($_SESSION['notif_error']); ?>
    <?php endif; ?>

    <div class="card p-4">
        <div class="table-responsive">
            <table class="table table-hover table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Reg No</th>
                        <th width="25%">Nama Pasien</th>
                        <th width="15%">Tgl Lahir</th>
                        <th width="15%">No RM</th>
                        <th width="25%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($data_pasien) > 0): ?>
                        <?php $no = 1; foreach ($data_pasien as $row): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['regno']); ?></td>
                                <td class="text-start ps-3"><?= htmlspecialchars($row['nama_pasien']); ?></td>
                                <td>
                                    <?= htmlspecialchars($row['tgl_lahir']); ?>
                                </td>
                                <td><?= htmlspecialchars($row['no_rm']); ?></td>
                                <td>
                                    <a href="penata3.php?regno=<?= urlencode($row['regno']); ?>" class="btn btn-warning btn-sm btn-action text-white" title="Edit Data">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    
                                    <a href="data.php?hapus_id=<?= $row['id']; ?>&regno=<?= urlencode($regno) ?>&nama=<?= urlencode($nama) ?>&no_rm=<?= urlencode($no_rm) ?>" 
                                       class="btn btn-danger btn-sm btn-action btn-delete" 
                                       data-nama="<?= htmlspecialchars($row['nama_pasien']); ?>"
                                       title="Hapus Data">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-muted py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-search fs-1 mb-2 text-secondary"></i>
                                    <h5 class="fw-bold text-secondary">Data tidak ditemukan</h5>
                                    <p class="text-muted">Coba ubah kata kunci pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const deleteButtons = document.querySelectorAll('.btn-delete');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); 
                
                const deleteUrl = this.getAttribute('href'); 
                const namaPasien = this.getAttribute('data-nama'); 

                Swal.fire({
                    title: 'Apakah Anda Yakin?',
                    text: "Data pasien " + namaPasien + " akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });
        });
    });
</script>

</body>
</html>