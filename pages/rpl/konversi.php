<?php
    /**
     * Menu Konversi RPL - Tampilan Petugas/Admin
     * Menampilkan data mahasiswa dari konversi_header dengan filter Prodi & Periode
     */

    require_once __DIR__ . '/../../config/database.php'; // Sesuaikan path

    // Cek login dan role (sesuaikan dengan sistem Anda)
    if (!isset($_SESSION['user_id'])) {
        die('<h3>Akses ditolak. Silakan login terlebih dahulu.</h3>');
    }

    // ===== AMBIL DATA FILTER =====
    // Program Studi
    $q_prodi = $conn->query("SELECT DISTINCT nama_prodi FROM program_studi ORDER BY nama_prodi");
    $prodi_list = [];
    while ($p = $q_prodi->fetch_assoc()) {
        $prodi_list[] = $p['nama_prodi'];
    }

    // Periode Tahun
    $q_periode = $conn->query("SELECT DISTINCT periode_tahun FROM konversi_header ORDER BY periode_tahun DESC");
    $periode_list = [];
    while ($per = $q_periode->fetch_assoc()) {
        $periode_list[] = $per['periode_tahun'];
    }

    // Filter aktif
    $filter_prodi = isset($_GET['prodi']) ? $_GET['prodi'] : '';
    $filter_periode = isset($_GET['periode']) ? $_GET['periode'] : '';

    // ===== QUERY DATA KONVERSI =====
    $sql = "SELECT * FROM konversi_header WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($filter_prodi)) {
        $sql .= " AND prodi_tujuan = ?";
        $params[] = $filter_prodi;
        $types .= "s";
    }
    if (!empty($filter_periode)) {
        $sql .= " AND periode_tahun = ?";
        $params[] = $filter_periode;
        $types .= "s";
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Status badge styles
    $badge_class = [
        'draft' => 'bg-secondary',
        'menunggu' => 'bg-warning text-dark',
        'diverifikasi' => 'bg-success',
        'ditolak' => 'bg-danger'
    ];
?>

<!-- CSS Styles -->
<style>
    .filter-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px 20px;
        margin-bottom: 20px;
    }
    .filter-form {
        display: flex;
        gap: 12px;
        align-items: end;
        flex-wrap: wrap;
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .filter-group label {
        font-size: 9pt;
        font-weight: 600;
        color: #555;
    }
    .filter-group select {
        padding: 6px 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 9pt;
        min-width: 180px;
    }
    .btn-filter {
        padding: 6px 16px;
        background: #0d6efd;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 9pt;
        cursor: pointer;
    }
    .btn-reset {
        padding: 6px 16px;
        background: #6c757d;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 9pt;
        text-decoration: none;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
    }
    .data-table th, .data-table td {
        border: 1px solid #dee2e6;
        padding: 8px 10px;
        vertical-align: middle;
    }
    .data-table th {
        background: #f0f0f0;
        font-weight: 600;
        text-align: center;
    }
    .data-table tbody tr:hover {
        background: #f8f9fa;
    }
    .text-center { text-align: center; }
    .text-left { text-align: left; }

    .btn-aksi {
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 8pt;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        cursor: pointer;
        border: none;
    }
    .btn-lihat {
        background: #0d6efd;
        color: #fff;
    }
    .btn-verifikasi {
        background: #198754;
        color: #fff;
    }
    .btn-tolak {
        background: #dc3545;
        color: #fff;
    }

    .berkas-link {
        color: #0d6efd;
        text-decoration: none;
    }
    .berkas-link:hover {
        text-decoration: underline;
    }

    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }
    .modal-overlay.active {
        display: flex;
    }
    .modal-content {
        background: #fff;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    .modal-header {
        padding: 15px 20px;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-header h4 {
        margin: 0;
        font-size: 12pt;
    }
    .modal-close {
        background: none;
        border: none;
        font-size: 18pt;
        cursor: pointer;
        color: #666;
    }
    .modal-body {
        padding: 20px;
    }
    .modal-footer {
        padding: 15px 20px;
        border-top: 1px solid #dee2e6;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }
    .form-group {
        margin-bottom: 12px;
    }
    .form-group label {
        display: block;
        font-size: 9pt;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 9pt;
        resize: vertical;
        min-height: 80px;
    }
    .btn-confirm {
        padding: 6px 16px;
        border: none;
        border-radius: 4px;
        font-size: 9pt;
        cursor: pointer;
        color: #fff;
    }
    .btn-batal {
        background: #6c757d;
    }
</style>

<h4 class="text-danger mb-3">
    <i class="bi bi-table"></i> Data Konversi RPL Mahasiswa
</h4>

<!-- Filter Card -->
<div class="filter-card">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="page" value="petugas/konversi_rpl">

        <div class="filter-group">
            <label>Program Studi</label>
            <select name="prodi">
                <option value="">-- Semua Prodi --</option>
                <?php foreach ($prodi_list as $prodi): ?>
                <option value="<?= htmlspecialchars($prodi) ?>" <?= ($filter_prodi == $prodi) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($prodi) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label>Periode Tahun</label>
            <select name="periode">
                <option value="">-- Semua Periode --</option>
                <?php foreach ($periode_list as $periode): ?>
                <option value="<?= htmlspecialchars($periode) ?>" <?= ($filter_periode == $periode) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($periode) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn-filter">
            <i class="bi bi-funnel"></i> Filter
        </button>
        <a href="?page=petugas/konversi_rpl" class="btn-reset">
            <i class="bi bi-x-circle"></i> Reset
        </a>
    </form>
</div>

<!-- Data Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th>Nama Mahasiswa</th>
                        <th>NIM</th>
                        <th>Program Studi</th>
                        <th>Periode</th>
                        <th>Berkas Dokumen</th>
                        <th>Status</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows == 0): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                            Tidak ada data konversi
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($row['nama_tujuan'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['nim_tujuan'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['prodi_tujuan'] ?? '-') ?></td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark"><?= htmlspecialchars($row['periode_tahun'] ?? '-') ?></span>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($row['berkas_dokumen'])): ?>
                                <a href="<?= htmlspecialchars($row['berkas_dokumen']) ?>" target="_blank" class="berkas-link">
                                    <i class="bi bi-file-earmark-text"></i> Lihat Berkas
                                </a>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $badge_class[$row['status']] ?? 'bg-secondary' ?>">
                                    <?= ucfirst($row['status'] ?? 'draft') ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="?page=rpl/lihat_konversi&id=<?= $row['id'] ?>" class="btn-aksi btn-lihat" title="Lihat Hasil Konversi">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>

                                <?php if ($row['status'] == 'menunggu'): ?>
                                <button type="button" class="btn-aksi btn-verifikasi" 
                                        onclick="openModal('verifikasi', <?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_tujuan']) ?>')"
                                        title="Verifikasi">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                                <button type="button" class="btn-aksi btn-tolak"
                                        onclick="openModal('tolak', <?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_tujuan']) ?>')"
                                        title="Tolak">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Verifikasi -->
<div class="modal-overlay" id="modalVerifikasi">
    <div class="modal-content">
        <div class="modal-header">
            <h4><i class="bi bi-check-circle text-success"></i> Verifikasi Konversi</h4>
            <button type="button" class="modal-close" onclick="closeModal('verifikasi')">&times;</button>
        </div>
        <form method="POST" action="proses_verifikasi.php">
            <div class="modal-body">
                <input type="hidden" name="id_konversi" id="v_id">
                <input type="hidden" name="status" value="diverifikasi">
                <p>Anda akan <strong>menyetujui</strong> konversi milik:</p>
                <p class="fw-bold text-primary" id="v_nama"></p>
                <div class="form-group">
                    <label>Catatan Verifikasi (Opsional)</label>
                    <textarea name="catatan" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-confirm btn-batal" onclick="closeModal('verifikasi')">Batal</button>
                <button type="submit" class="btn-confirm" style="background:#198754;">
                    <i class="bi bi-check-circle"></i> Ya, Verifikasi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tolak -->
<div class="modal-overlay" id="modalTolak">
    <div class="modal-content">
        <div class="modal-header">
            <h4><i class="bi bi-x-circle text-danger"></i> Tolak Konversi</h4>
            <button type="button" class="modal-close" onclick="closeModal('tolak')">&times;</button>
        </div>
        <form method="POST" action="proses_verifikasi.php">
            <div class="modal-body">
                <input type="hidden" name="id_konversi" id="t_id">
                <input type="hidden" name="status" value="ditolak">
                <p>Anda akan <strong>menolak</strong> konversi milik:</p>
                <p class="fw-bold text-danger" id="t_nama"></p>
                <div class="form-group">
                    <label>Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="catatan" required placeholder="Wajib diisi. Berikan alasan penolakan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-confirm btn-batal" onclick="closeModal('tolak')">Batal</button>
                <button type="submit" class="btn-confirm" style="background:#dc3545;">
                    <i class="bi bi-x-circle"></i> Ya, Tolak
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(type, id, nama) {
    if (type === 'verifikasi') {
        document.getElementById('v_id').value = id;
        document.getElementById('v_nama').textContent = nama;
        document.getElementById('modalVerifikasi').classList.add('active');
    } else {
        document.getElementById('t_id').value = id;
        document.getElementById('t_nama').textContent = nama;
        document.getElementById('modalTolak').classList.add('active');
    }
}

function closeModal(type) {
    document.getElementById('modal' + (type === 'verifikasi' ? 'Verifikasi' : 'Tolak')).classList.remove('active');
}

// Close modal when clicking outside
window.onclick = function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
}
</script>