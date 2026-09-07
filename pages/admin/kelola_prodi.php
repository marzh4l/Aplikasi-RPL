<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$alert = ['show' => false, 'type' => 'success', 'title' => '', 'message' => ''];

// ===== PROSES SIMPAN =====
if (isset($_POST['simpan'])) {
    $kode = trim($_POST['kode_prodi']);
    $nama = trim($_POST['nama_prodi']);
    $jenjang = $_POST['jenjang'];
    
    if (empty($kode) || empty($nama)) {
        $alert = ['show' => true, 'type' => 'warning', 'title' => 'Peringatan!', 'message' => 'Kode dan Nama Prodi wajib diisi!'];
    } else {
        $cek = $conn->prepare("SELECT id FROM program_studi WHERE kode_prodi = ?");
        $cek->bind_param("s", $kode);
        $cek->execute();
        
        if ($cek->get_result()->num_rows > 0) {
            $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'Kode Prodi <strong>' . htmlspecialchars($kode) . '</strong> sudah ada!'];
        } else {
            $stmt = $conn->prepare("INSERT INTO program_studi (kode_prodi, nama_prodi, jenjang) VALUES (?,?,?)");
            $stmt->bind_param("sss", $kode, $nama, $jenjang);
            
            if ($stmt->execute()) {
                $alert = ['show' => true, 'type' => 'success', 'title' => 'Berhasil!', 'message' => 'Program Studi <strong>' . htmlspecialchars($nama) . '</strong> berhasil disimpan!'];
            } else {
                $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'Error: ' . $stmt->error];
            }
        }
    }
}

// ===== PROSES UPDATE =====
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $kode = trim($_POST['kode_prodi']);
    $nama = trim($_POST['nama_prodi']);
    $jenjang = $_POST['jenjang'];
    
    $cek = $conn->prepare("SELECT id FROM program_studi WHERE kode_prodi = ? AND id != ?");
    $cek->bind_param("si", $kode, $id);
    $cek->execute();
    
    if ($cek->get_result()->num_rows > 0) {
        $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'Kode Prodi sudah digunakan oleh prodi lain!'];
    } else {
        $stmt = $conn->prepare("UPDATE program_studi SET kode_prodi = ?, nama_prodi = ?, jenjang = ? WHERE id = ?");
        $stmt->bind_param("sssi", $kode, $nama, $jenjang, $id);
        
        if ($stmt->execute()) {
            $alert = ['show' => true, 'type' => 'success', 'title' => 'Berhasil!', 'message' => 'Program Studi <strong>' . htmlspecialchars($nama) . '</strong> berhasil diperbarui!'];
        } else {
            $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'Error: ' . $stmt->error];
        }
    }
}

// ===== PROSES HAPUS =====
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $cek = $conn->query("SELECT nama_prodi FROM program_studi WHERE id = $id");
    
    if ($cek->num_rows > 0) {
        $data = $cek->fetch_assoc();
        $conn->query("DELETE FROM program_studi WHERE id = $id");
        $alert = ['show' => true, 'type' => 'success', 'title' => 'Terhapus!', 'message' => 'Program Studi <strong>' . htmlspecialchars($data['nama_prodi']) . '</strong> berhasil dihapus!'];
    } else {
        $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'Data tidak ditemukan!'];
    }
    
    // header("Location: ?page=admin/kelola_prodi&alert=" . urlencode(json_encode($alert)));
    // exit;
}

if (isset($_GET['alert'])) {
    $alert = json_decode(urldecode($_GET['alert']), true);
    $alert['show'] = true;
}

// ===== DATA EDIT =====
$data_edit = null;
if (isset($_GET['edit'])) {
    $id_edit = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM program_studi WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $data_edit = $stmt->get_result()->fetch_assoc();
}
?>

<!-- ALERT -->
<?php if ($alert['show']): ?>
<div class="alert alert-<?= $alert['type'] ?> alert-dismissible fade show shadow-sm" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-<?= $alert['type'] == 'success' ? 'check-circle-fill' : ($alert['type'] == 'danger' ? 'x-circle-fill' : 'exclamation-triangle-fill') ?> me-2 fs-5"></i>
        <div><strong><?= $alert['title'] ?></strong><br><?= $alert['message'] ?></div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
    <h4 class="text-danger m-0"><i class="bi bi-building"></i> Kelola Program Studi</h4>
    <button class="btn btn-theme mt-2 mt-sm-0" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-lg"></i> Tambah Prodi
    </button>
</div>

<!-- TABEL -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom border-danger">
        <h6 class="mb-0 text-danger"><i class="bi bi-list-ul"></i> Daftar Program Studi</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-danger">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>Kode Prodi</th>
                        <th>Nama Program Studi</th>
                        <th class="text-center">Jenjang</th>
                        <th class="text-center" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $q = $conn->query("SELECT * FROM program_studi ORDER BY id DESC");
                    if ($q->num_rows == 0):
                    ?>
                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>Belum ada data Program Studi</td></tr>
                    <?php else: while($d = $q->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($d['kode_prodi']) ?></td>
                        <td><?= htmlspecialchars($d['nama_prodi']) ?></td>
                        <td class="text-center"><span class="badge bg-primary"><?= $d['jenjang'] ?></span></td>
                        <td class="text-center">
                            <a href="?page=admin/kelola_prodi&edit=<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit"><i class="bi bi-pencil-square"></i></a>
                            <a href="?page=admin/kelola_prodi&hapus=<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus <?= htmlspecialchars($d['nama_prodi']) ?>?')" title="Hapus"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-building-add"></i> Tambah Program Studi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kode Prodi <span class="text-danger">*</span></label>
                        <input type="text" name="kode_prodi" class="form-control" placeholder="Contoh: TI-001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Program Studi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_prodi" class="form-control" placeholder="Contoh: Teknik Informatika" required>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-bold">Jenjang <span class="text-danger">*</span></label>
                        <select name="jenjang" class="form-select">
                            <option value="D3">D3 (Diploma)</option>
                            <option value="S1">S1 (Sarjana)</option>
                            <option value="S2">S2 (Magister)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Batal</button>
                    <button type="submit" name="simpan" class="btn btn-danger"><i class="bi bi-check-lg"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT -->
<?php if ($data_edit): ?>
<div class="modal fade show" id="modalEdit" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="display: block;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Program Studi</h5>
                <a href="?page=admin/kelola_prodi" class="btn-close btn-close-white"></a>
            </div>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $data_edit['id'] ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kode Prodi <span class="text-danger">*</span></label>
                        <input type="text" name="kode_prodi" class="form-control" value="<?= htmlspecialchars($data_edit['kode_prodi']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Program Studi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_prodi" class="form-control" value="<?= htmlspecialchars($data_edit['nama_prodi']) ?>" required>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-bold">Jenjang <span class="text-danger">*</span></label>
                        <select name="jenjang" class="form-select">
                            <option value="D3" <?= $data_edit['jenjang'] == 'D3' ? 'selected' : '' ?>>D3 (Diploma)</option>
                            <option value="S1" <?= $data_edit['jenjang'] == 'S1' ? 'selected' : '' ?>>S1 (Sarjana)</option>
                            <option value="S2" <?= $data_edit['jenjang'] == 'S2' ? 'selected' : '' ?>>S2 (Magister)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <a href="?page=admin/kelola_prodi" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Batal</a>
                    <button type="submit" name="update" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal-backdrop fade show"></div>
<?php endif; ?>