<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$alert = ['show' => false, 'type' => 'success', 'title' => '', 'message' => ''];

// ===== PROSES IMPORT EXCEL =====
if (isset($_POST['import_excel']) && isset($_FILES['file_excel'])) {
    require 'vendor/autoload.php'; // PhpSpreadsheet
    
    $file = $_FILES['file_excel']['tmp_name'];
    $id_prodi_import = intval($_POST['id_prodi_import'] ?? 0);
    $periode_import = $_POST['periode_import'] ?? '';
    
    if ($id_prodi_import == 0 || empty($periode_import)) {
        $alert = ['show' => true, 'type' => 'warning', 'title' => 'Peringatan!', 'message' => 'Pilih Program Studi dan Periode untuk import!'];
    } else {
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            $sukses = 0;
            $gagal = 0;
            
            // Skip baris header (baris 0)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $kode = trim($row[0] ?? '');
                $nama = trim($row[1] ?? '');
                $sks = intval($row[2] ?? 0);
                $semester = trim($row[3] ?? '');
                
                if (empty($kode) || empty($nama) || $sks <= 0 ) continue;
                
                $stmt = $conn->prepare("INSERT INTO kurikulum (id_prodi, periode_tahun, kode_mk, nama_mk, sks, semester) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE nama_mk=VALUES(nama_mk), sks=VALUES(sks), semester=VALUES(semester)");
                $stmt->bind_param("isssis", $id_prodi_import, $periode_import, $kode, $nama, $sks, $semester);
                
                if ($stmt->execute()) $sukses++;
                else $gagal++;
            }
            
            $alert = ['show' => true, 'type' => 'success', 'title' => 'Import Selesai!', 'message' => "Berhasil: $sukses, Gagal: $gagal"];
            
        } catch (Exception $e) {
            $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal Import!', 'message' => $e->getMessage()];
        }
    }
}

// ===== PROSES SIMPAN =====
if (isset($_POST['simpan'])) {
    $id_prodi = intval($_POST['id_prodi']);
    $periode = trim($_POST['periode_tahun']);
    $kode = trim($_POST['kode_mk']);
    $nama = trim($_POST['nama_mk']);
    $sks = intval($_POST['sks']);
    $semester = trim($_POST['semester']);
    
    if (empty($periode) || empty($kode) || empty($nama) || $sks <= 0) {
        $alert = ['show' => true, 'type' => 'warning', 'title' => 'Peringatan!', 'message' => 'Semua field wajib diisi dengan benar!'];
    } else {
        $cek = $conn->prepare("SELECT id FROM kurikulum WHERE id_prodi = ? AND periode_tahun = ? AND kode_mk = ?");
        $cek->bind_param("iss", $id_prodi, $periode, $kode);
        $cek->execute();
        
        if ($cek->get_result()->num_rows > 0) {
            $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'Kode MK sudah ada!'];
        } else {
            $stmt = $conn->prepare("INSERT INTO kurikulum (id_prodi, periode_tahun, kode_mk, nama_mk, sks, semester) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("isssis", $id_prodi, $periode, $kode, $nama, $sks, $semester);
            
            if ($stmt->execute()) {
                $alert = ['show' => true, 'type' => 'success', 'title' => 'Berhasil!', 'message' => 'Mata Kuliah berhasil disimpan!'];
            } else {
                $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'Error: ' . $stmt->error];
            }
        }
    }
}

// ===== PROSES UPDATE =====
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $id_prodi = intval($_POST['id_prodi']);
    $periode = trim($_POST['periode_tahun']);
    $kode = trim($_POST['kode_mk']);
    $nama = trim($_POST['nama_mk']);
    $sks = intval($_POST['sks']);
    $semester = trim($_POST['semester']);
    
    $cek = $conn->prepare("SELECT id FROM kurikulum WHERE id_prodi = ? AND periode_tahun = ? AND kode_mk = ? AND id != ?");
    $cek->bind_param("issi", $id_prodi, $periode, $kode, $id);
    $cek->execute();
    
    if ($cek->get_result()->num_rows > 0) {
        $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'Kode MK sudah digunakan!'];
    } else {
        $stmt = $conn->prepare("UPDATE kurikulum SET id_prodi=?, periode_tahun=?, kode_mk=?, nama_mk=?, sks=?, semester=? WHERE id=?");
        $stmt->bind_param("isssisi", $id_prodi, $periode, $kode, $nama, $sks, $semester, $id);
        
        if ($stmt->execute()) {
            $alert = ['show' => true, 'type' => 'success', 'title' => 'Berhasil!', 'message' => 'Mata Kuliah berhasil diperbarui!'];
        } else {
            $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'Error: ' . $stmt->error];
        }
    }
}

// ===== PROSES HAPUS =====
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $cek = $conn->query("SELECT nama_mk FROM kurikulum WHERE id = $id");
    
    if ($cek->num_rows > 0) {
        $data = $cek->fetch_assoc();
        $conn->query("DELETE FROM kurikulum WHERE id = $id");
        $alert = ['show' => true, 'type' => 'success', 'title' => 'Terhapus!', 'message' => 'Mata Kuliah ' . htmlspecialchars($data['nama_mk']) . ' berhasil dihapus!'];
    } else {
        $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'Data tidak ditemukan!'];
    }
    
    header("Location: ?page=admin/kelola_kurikulum&alert=" . urlencode(json_encode($alert)));
    exit;
}

if (isset($_GET['alert'])) {
    $alert = json_decode(urldecode($_GET['alert']), true);
    $alert['show'] = true;
}

$data_edit = null;
if (isset($_GET['edit'])) {
    $id_edit = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM kurikulum WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $data_edit = $stmt->get_result()->fetch_assoc();
}

// Filter
$filter_prodi = $_GET['filter_prodi'] ?? '';
$filter_periode = $_GET['filter_periode'] ?? '';

$sql = "SELECT k.*, p.nama_prodi, p.jenjang FROM kurikulum k 
        JOIN program_studi p ON k.id_prodi = p.id WHERE 1=1";
if ($filter_prodi) $sql .= " AND k.id_prodi = " . intval($filter_prodi);
if ($filter_periode) $sql .= " AND k.periode_tahun = '" . $conn->real_escape_string($filter_periode) . "'";
$sql .= " ORDER BY k.periode_tahun ,id ASC, k.semester, k.kode_mk";

$kurikulum = $conn->query($sql);
?>

<?php if ($alert['show']): ?>
<div class="alert alert-<?= $alert['type'] ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-<?= $alert['type'] == 'success' ? 'check-circle-fill' : ($alert['type'] == 'danger' ? 'x-circle-fill' : 'exclamation-triangle-fill') ?> me-2 fs-5"></i>
        <div><strong><?= $alert['title'] ?></strong><br><?= $alert['message'] ?></div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
    <h4 class="text-danger m-0"><i class="bi bi-book"></i> Kelola Kurikulum</h4>
    <div>
        <!-- Tombol Import Excel (Butuh Composer) -->
        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalImport">
            <i class="bi bi-file-earmark-excel"></i> Import MK
        </button>
        <button class="btn btn-theme" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-lg"></i> Tambah MK
        </button>
    </div>
</div>

<!-- FILTER -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="page" value="admin/kelola_kurikulum">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Program Studi</label>
                <select name="filter_prodi" class="form-select">
                    <option value="">-- Semua Prodi --</option>
                    <?php 
                    $prodi_list = $conn->query("SELECT * FROM program_studi ORDER BY nama_prodi");
                    while($p = $prodi_list->fetch_assoc()): 
                    ?>
                    <option value="<?= $p['id'] ?>" <?= $filter_prodi == $p['id'] ? 'selected' : '' ?>><?= $p['nama_prodi'] ?> (<?= $p['jenjang'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Periode</label>
                <input type="text" name="filter_periode" class="form-control" placeholder="2024/2025" value="<?= htmlspecialchars($filter_periode) ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-danger w-100"><i class="bi bi-funnel"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="?page=admin/kelola_kurikulum" class="btn btn-outline-secondary w-100"><i class="bi bi-x-circle"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- TABEL -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom border-danger">
        <h6 class="mb-0 text-danger"><i class="bi bi-list-ul"></i> Daftar Kurikulum</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle" id="tabelKurikulum">
                <thead class="table-danger">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>Periode</th>
                        <th>Program Studi</th>
                        <th>Kode MK</th>
                        <th>Nama Mata Kuliah</th>
                        <th class="text-center">SKS</th>
                        <th class="text-center">Semester</th>
                        <th class="text-center" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if ($kurikulum->num_rows == 0):
                    ?>
                    <tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>Belum ada data Kurikulum</td></tr>
                    <?php else: while($d = $kurikulum->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><span class="badge bg-secondary"><?= $d['periode_tahun'] ?></span></td>
                        <td><?= htmlspecialchars($d['nama_prodi']) ?> <small class="text-muted">(<?= $d['jenjang'] ?>)</small></td>
                        <td class="fw-bold"><?= htmlspecialchars($d['kode_mk']) ?></td>
                        <td><?= htmlspecialchars($d['nama_mk']) ?></td>
                        <td class="text-center"><span class="badge bg-info"><?= $d['sks'] ?> SKS</span></td>
                        <td class="text-center"><span class="badge bg-warning text-dark"><?= $d['semester'] ?></span></td>
                        <td class="text-center">
                            <a href="?page=admin/kelola_kurikulum&edit=<?= $d['id'] ?>&filter_prodi=<?= $filter_prodi ?>&filter_periode=<?= $filter_periode ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit"><i class="bi bi-pencil-square"></i></a>
                            <a href="?page=admin/kelola_kurikulum&hapus=<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus <?= htmlspecialchars($d['nama_mk']) ?>?')" title="Hapus"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL IMPORT EXCEL -->
<div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-file-earmark-excel"></i> Import Kurikulum dari Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Program Studi <span class="text-danger">*</span></label>
                        <select name="id_prodi_import" class="form-select" required>
                            <option value="">-- Pilih Prodi --</option>
                            <?php 
                            $prodi_list = $conn->query("SELECT * FROM program_studi ORDER BY nama_prodi");
                            while($p = $prodi_list->fetch_assoc()): 
                            ?>
                            <option value="<?= $p['id'] ?>"><?= $p['nama_prodi'] ?> (<?= $p['jenjang'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Periode Tahun <span class="text-danger">*</span></label>
                        <input type="text" name="periode_import" class="form-control" placeholder="2024/2025" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">File Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file_excel" class="form-control" accept=".xlsx, .xls" required>
                        <small class="text-muted">Format kolom: A=Kode MK, B=Nama MK, C=SKS, D=Semester</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="import_excel" class="btn btn-success"><i class="bi bi-upload"></i> Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-journal-plus"></i> Tambah Mata Kuliah</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Program Studi <span class="text-danger">*</span></label>
                        <select name="id_prodi" class="form-select" required>
                            <option value="">-- Pilih Prodi --</option>
                            <?php 
                            $prodi_list = $conn->query("SELECT * FROM program_studi ORDER BY nama_prodi");
                            while($p = $prodi_list->fetch_assoc()): 
                            ?>
                            <option value="<?= $p['id'] ?>"><?= $p['nama_prodi'] ?> (<?= $p['jenjang'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Periode Tahun <span class="text-danger">*</span></label>
                        <input type="text" name="periode_tahun" class="form-control" placeholder="Contoh: 2024/2025" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kode MK <span class="text-danger">*</span></label>
                            <input type="text" name="kode_mk" class="form-control" placeholder="TI101" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">SKS <span class="text-danger">*</span></label>
                            <input type="number" name="sks" class="form-control" min="1" max="6" placeholder="1-6" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                        <select name="semester" class="form-select" required>
                            <option value="I">Semester 1</option>
                            <option value="II">Semester 2</option>
                            <option value="III">Semester 3</option>
                            <option value="IV">Semester 4</option>
                            <option value="V">Semester 5</option>
                            <option value="VI">Semester 6</option>
                            <option value="VII">Semester 7</option>
                            <option value="VIII">Semester 8</option>
                            <option value="PILIHAN">PILIHAN</option>
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-bold">Nama Mata Kuliah <span class="text-danger">*</span></label>
                        <input type="text" name="nama_mk" class="form-control" placeholder="Pemrograman Web" required>
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
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Mata Kuliah</h5>
                <a href="?page=admin/kelola_kurikulum<?= $filter_prodi ? '&filter_prodi='.$filter_prodi : '' ?><?= $filter_periode ? '&filter_periode='.$filter_periode : '' ?>" class="btn-close btn-close-white"></a>
            </div>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $data_edit['id'] ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Program Studi <span class="text-danger">*</span></label>
                        <select name="id_prodi" class="form-select" required>
                            <?php 
                            $prodi_list = $conn->query("SELECT * FROM program_studi ORDER BY nama_prodi");
                            while($p = $prodi_list->fetch_assoc()): 
                            ?>
                            <option value="<?= $p['id'] ?>" <?= $data_edit['id_prodi'] == $p['id'] ? 'selected' : '' ?>><?= $p['nama_prodi'] ?> (<?= $p['jenjang'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Periode Tahun <span class="text-danger">*</span></label>
                        <input type="text" name="periode_tahun" class="form-control" value="<?= htmlspecialchars($data_edit['periode_tahun']) ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kode MK <span class="text-danger">*</span></label>
                            <input type="text" name="kode_mk" class="form-control" value="<?= htmlspecialchars($data_edit['kode_mk']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">SKS <span class="text-danger">*</span></label>
                            <input type="number" name="sks" class="form-control" min="1" max="6" value="<?= $data_edit['sks'] ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                        <select name="semester" class="form-select" required>                            
                            <option value="I">Semester 1</option>
                            <option value="II">Semester 2</option>
                            <option value="III">Semester 3</option>
                            <option value="IV">Semester 4</option>
                            <option value="V">Semester 5</option>
                            <option value="VI">Semester 6</option>
                            <option value="VII">Semester 7</option>
                            <option value="VIII">Semester 8</option>
                            <option value="PILIHAN">PILIHAN</option>
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-bold">Nama Mata Kuliah <span class="text-danger">*</span></label>
                        <input type="text" name="nama_mk" class="form-control" value="<?= htmlspecialchars($data_edit['nama_mk']) ?>" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <a href="?page=admin/kelola_kurikulum<?= $filter_prodi ? '&filter_prodi='.$filter_prodi : '' ?><?= $filter_periode ? '&filter_periode='.$filter_periode : '' ?>" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Batal</a>
                    <button type="submit" name="update" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal-backdrop fade show"></div>
<?php endif; ?>