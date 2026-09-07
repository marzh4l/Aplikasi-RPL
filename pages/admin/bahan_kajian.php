<?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $alert = ['show' => false, 'type' => 'success', 'title' => '', 'message' => ''];

    // Konfigurasi upload
    $uploadDir = 'uploads/bahan_kajian/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedExt = ['pdf'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Helper: sanitasi nama file agar aman di server
    function sanitizeFilename($string) {
        $string = strtolower(trim($string));
        $string = str_replace(' ', '_', $string);          // spasi → underscore
        $string = preg_replace('/[^a-z0-9_-]/i', '', $string); // hanya alfanumerik, underscore, dash
        return $string;
    }

    // Helper: generate nama file berdasarkan id_prodi & periode_tahun
    function generateFilename($id_prodi, $periode_tahun, $ext) {
        // Mapping id_prodi ke nama prodi
        $namaProdi = '';
        if ($id_prodi == 1) {
            $namaProdi = 'farmasi';
        } elseif ($id_prodi == 2) {
            $namaProdi = 'ilmu_keperawatan';
        } else {
            $namaProdi = 'prodi_' . $id_prodi; // fallback jika id selain 1 & 2
        }

        $safeProdi   = sanitizeFilename($namaProdi);
        $safePeriode = sanitizeFilename($periode_tahun);
        $timestamp   = time();
        $uniqid      = uniqid();

        return "{$safeProdi}_{$safePeriode}.{$ext}";
    }

    $q_prodi = $conn->query("SELECT * FROM program_studi ORDER BY nama_prodi");
    $q_periode = $conn->query("SELECT DISTINCT periode_tahun FROM kurikulum ORDER BY periode_tahun DESC");

    // ===== PROSES SIMPAN =====
    if (isset($_POST['simpan'])) {
        $id_prodi       = $_POST['id_prodi'];
        $periode_tahun  = $_POST['periode_tahun'];
        
        // Proses upload file
        $fileName = '';
        if (isset($_FILES['berkas']) && $_FILES['berkas']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['berkas']['name'], PATHINFO_EXTENSION));
            $fileSize = $_FILES['berkas']['size'];
            
            if (!in_array($ext, $allowedExt)) {
                $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'Format file tidak diizinkan. Hanya: ' . implode(', ', $allowedExt)];
            } elseif ($fileSize > $maxSize) {
                $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'Ukuran file maksimal 5MB.'];
            } else {
                // Generate nama file: [prodi]_[periode]_[time]_[uniqid].[ext]
                $fileName = generateFilename($id_prodi, $periode_tahun, $ext);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['berkas']['tmp_name'], $targetPath)) {
                    $stmt = $conn->prepare("INSERT INTO bahan_kajian (id_prodi, periode_tahun, bahan_kajian) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $id_prodi, $periode_tahun, $fileName);
                    
                    if ($stmt->execute()) {
                        $alert = ['show' => true, 'type' => 'success', 'title' => 'Berhasil!', 'message' => 'Data bahan kajian berhasil disimpan.'];
                    } else {
                        $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'Terjadi kesalahan saat menyimpan data ke database.'];
                        @unlink($targetPath);
                    }
                    $stmt->close();
                } else {
                    $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'Gagal mengupload file.'];
                }
            }
        } else {
            $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'File berkas wajib diupload.'];
        }
    }

    // ===== PROSES HAPUS =====
    if (isset($_GET['hapus'])) {
        $id = $_GET['hapus'];

        $stmt = $conn->prepare("SELECT bahan_kajian FROM bahan_kajian WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $dataHapus = $result->fetch_assoc();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM bahan_kajian WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            if (!empty($dataHapus['bahan_kajian']) && file_exists($uploadDir . $dataHapus['bahan_kajian'])) {
                @unlink($uploadDir . $dataHapus['bahan_kajian']);
            }
            $alert = ['show' => true, 'type' => 'success', 'title' => 'Berhasil!', 'message' => 'Data bahan kajian berhasil dihapus.'];
        } else {
            $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal!', 'message' => 'Terjadi kesalahan saat menghapus data.'];
        }
        $stmt->close();
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
    <h4 class="text-danger m-0"><i class="bi bi-building"></i> Kelola Bahan Kajian</h4>
    <button class="btn btn-theme mt-2 mt-sm-0" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-lg"></i> Tambah Bahan Kajian
    </button>
</div>

<!-- TABEL -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom border-danger">
        <h6 class="mb-0 text-danger"><i class="bi bi-list-ul"></i> Daftar Bahan Kajian</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-danger">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>Periode</th>
                        <th>Program Studi</th>
                        <th class="text-center">Berkas Bahan Kajian</th>
                        <th class="text-center" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $q = $conn->query("SELECT bahan_kajian.*, program_studi.nama_prodi, program_studi.jenjang FROM bahan_kajian JOIN program_studi ON bahan_kajian.id_prodi = program_studi.id");
                    if ($q->num_rows == 0):
                    ?>
                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>Belum ada data bahan kajian</td></tr>
                    <?php else: while($d = $q->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($d['periode_tahun']) ?></td>
                        <td><?= htmlspecialchars($d['nama_prodi']) ?> <small class="text-muted">(<?= $d['jenjang'] ?>)</small></td>
                        <td class="text-center">
                            <?php if (!empty($d['bahan_kajian'])): ?>
                                <a href="<?= $uploadDir . htmlspecialchars($d['bahan_kajian']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Download">
                                    <i class="bi bi-file-earmark-arrow-down"></i> <?= htmlspecialchars($d['bahan_kajian']) ?>
                                </a>
                            <?php else: ?>
                                <span class="badge bg-secondary">Tidak ada file</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="?page=admin/bahan_kajian&hapus=<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus data bahan kajian <?= htmlspecialchars($d['nama_prodi']) ?> periode <?= htmlspecialchars($d['periode_tahun']) ?>?')" title="Hapus"><i class="bi bi-trash"></i></a>
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
                <h5 class="modal-title"><i class="bi bi-building-add"></i> Tambah Bahan Kajian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Program Studi <span class="text-danger">*</span></label>
                        <select name="id_prodi" id="selectProdi" class="form-select" required>
                            <option value="">-- Pilih Program Studi --</option>
                            <?php 
                            $q_prodi->data_seek(0);
                            while($p = $q_prodi->fetch_assoc()): 
                            ?>
                            <option value="<?= $p['id'] ?>">
                                <?= htmlspecialchars($p['nama_prodi']) ?> (<?= $p['jenjang'] ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Periode Tahun Kurikulum <span class="text-danger">*</span></label>
                        <select name="periode_tahun" id="selectPeriode" class="form-select" required>
                            <option value="">-- Pilih Periode --</option>
                            <?php 
                            $q_periode->data_seek(0);
                            while($per = $q_periode->fetch_assoc()): 
                            ?>
                            <option value="<?= htmlspecialchars($per['periode_tahun']) ?>">
                                <?= htmlspecialchars($per['periode_tahun']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Berkas Bahan Kajian <span class="text-danger">*</span></label>
                        <input type="file" name="berkas" class="form-control" accept=".pdf" required>
                        <div class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Format: PDF. Maks: 5MB
                        </div>
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