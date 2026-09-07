<?php
    /**
     * Lihat Detail Konversi - Tampilan Petugas (Read Only)
     * Menampilkan data konversi mahasiswa seperti data_konversi.php
     */

    require_once __DIR__ . '/../../config/database.php'; // Sesuaikan path

    if (!isset($_SESSION['user_id'])) {
        die('<h3 style="text-align:center; margin-top:50px;">Akses ditolak. Silakan login terlebih dahulu.</h3>');
    }

    // Ambil ID konversi dari URL
    $id_konversi = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id_konversi <= 0) {
        die('<h3 style="text-align:center; margin-top:50px;">ID Konversi tidak valid.</h3>');
    }

    // ===== AMBIL DATA HEADER =====
    $q_header = $conn->prepare("SELECT * FROM konversi_header WHERE id = ?");
    $q_header->bind_param("i", $id_konversi);
    $q_header->execute();
    $result_header = $q_header->get_result();
    $header = $result_header->fetch_assoc();

    if (!$header) {
        die('<h3 style="text-align:center; margin-top:50px;">Data konversi tidak ditemukan.</h3>');
    }

    // ===== AMBIL DATA DETAIL =====
    $q_detail = $conn->prepare("SELECT * FROM konversi_detail WHERE id_konversi = ? ORDER BY id");
    $q_detail->bind_param("i", $id_konversi);
    $q_detail->execute();
    $result_detail = $q_detail->get_result();

    $detail_list = [];
    $kode_mk_dipilih = [];
    $total_sks_asal = 0;
    $total_sks_tujuan = 0;

    while ($d = $result_detail->fetch_assoc()) {
        $detail_list[] = $d;
        $kode_mk_dipilih[] = $d['kode_mk_tujuan'];
        $total_sks_asal += intval($d['sks_asal'] ?? 0);
        $total_sks_tujuan += intval($d['sks_tujuan'] ?? 0);
    }

    // ===== AMBIL MATA KULIAH TIDAK DIPILIH =====
    $mk_ganjil = [];
    $mk_genap = [];
    $total_sks_ganjil = 0;
    $total_sks_genap = 0;

    if (!empty($header['prodi_tujuan']) && !empty($header['periode_tahun'])) {
        $q_prodi_id = $conn->prepare("SELECT id FROM program_studi WHERE nama_prodi = ?");
        $q_prodi_id->bind_param("s", $header['prodi_tujuan']);
        $q_prodi_id->execute();
        $r_prodi_id = $q_prodi_id->get_result();

        if ($r_prodi_id->num_rows > 0) {
            $id_prodi_tujuan = $r_prodi_id->fetch_assoc()['id'];

            $placeholders = implode(',', array_fill(0, count($kode_mk_dipilih), '?'));
            if (empty($kode_mk_dipilih)) {
                $q_mk_belum = $conn->prepare("SELECT * FROM kurikulum WHERE id_prodi = ? AND periode_tahun = ? ORDER BY id ASC");
                $q_mk_belum->bind_param("is", $id_prodi_tujuan, $header['periode_tahun']);
            } else {
                $types = str_repeat('s', count($kode_mk_dipilih));
                $q_mk_belum = $conn->prepare("SELECT * FROM kurikulum WHERE id_prodi = ? AND periode_tahun = ? AND kode_mk NOT IN ($placeholders) ORDER BY id ASC");
                $params = array_merge([$id_prodi_tujuan, $header['periode_tahun']], $kode_mk_dipilih);
                $q_mk_belum->bind_param("is" . $types, ...$params);
            }
            $q_mk_belum->execute();
            $r_mk_belum = $q_mk_belum->get_result();

            while ($mk = $r_mk_belum->fetch_assoc()) {
                $semester = strtoupper(trim($mk['semester'] ?? ''));
                $ganjil_list = ['I', 'III', 'V', 'VII', 'PILIHAN'];
                $genap_list = ['II', 'IV', 'VI', 'VIII'];

                if (in_array($semester, $ganjil_list)) {
                    $mk_ganjil[] = $mk;
                    $total_sks_ganjil += intval($mk['sks']);
                } elseif (in_array($semester, $genap_list)) {
                    $mk_genap[] = $mk;
                    $total_sks_genap += intval($mk['sks']);
                } else {
                    $mk_ganjil[] = $mk;
                    $total_sks_ganjil += intval($mk['sks']);
                }
            }
        }
    }

    $mk_tidak_dipilih = array_merge($mk_ganjil, $mk_genap);
    $total_sks_tidak_dipilih = $total_sks_ganjil + $total_sks_genap;

    // Status badge
    $badge_class = [
        'draft' => 'bg-secondary',
        'menunggu' => 'bg-warning text-dark',
        'diverifikasi' => 'bg-success',
        'ditolak' => 'bg-danger'
    ];
    $status = $header['status'] ?? 'draft';
?>

<style>
/* Styling sama seperti data_konversi.php */
.card-header { font-weight: 600; }
[id^="tabelHasil"] th, [id^="tabelHasil"] td {
    vertical-align: middle;
    font-size: 0.85rem;
}
[id^="tabelHasil"] thead tr:first-child th {
    font-size: 0.95rem;
    padding: 10px;
}
@media (max-width: 768px) {
    [id^="tabelHasil"] { font-size: 0.75rem; }
    [id^="tabelHasil"] th, [id^="tabelHasil"] td {
        padding: 4px;
        min-width: 60px;
    }
}

/* Read-only indicator */
.readonly-badge {
    background: #e9ecef;
    border: 1px dashed #adb5bd;
    color: #6c757d;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 8pt;
}

/* Action bar */
.action-bar {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px 15px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.btn-back {
    padding: 6px 14px;
    background: #6c757d;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 9pt;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.btn-print {
    padding: 6px 14px;
    background: #0d6efd;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 9pt;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
</style>

<!-- Action Bar -->
<div class="action-bar">
    <div>
        <span class="readonly-badge"><i class="bi bi-eye"></i> MODE LIHAT (READ ONLY)</span>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="?page=rpl/konversi" class="btn-back">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
        </a>
        <a href="exports/print.php?id=<?= $header['nim_tujuan'] ?>" class="btn-print" target="_blank">
            <i class="bi bi-printer"></i> Print
        </a>
    </div>
</div>

<h4 class="text-danger mb-4">
    <i class="bi bi-eye"></i> Detail Konversi Mahasiswa
</h4>

<!-- ========================================== -->
<!-- CARD GROUP: DATA PERGURUAN TINGGI          -->
<!-- ========================================== -->
<div class="card mb-4 border border-dark-subtle shadow-sm">
    <div class="card-header text-dark d-flex justify-content-between align-items-center">
        <span><i class="bi bi-mortarboard text-primary"></i> Data Konversi</span>
        <div class="d-flex align-items-center gap-2">
            Periode: 
            <span class="badge bg-danger text-white px-2 py-1">
                <?= htmlspecialchars($header['periode_tahun']) ?>
            </span>
            <span class="badge <?= $badge_class[$status] ?? 'bg-secondary' ?> px-2 py-1">
                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> <?= ucfirst($status) ?>
            </span>
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            <!-- ASAL PERGURUAN TINGGI -->
            <div class="col-md-6 mb-3">
                <div class="card h-100 border border-dark-subtle">
                    <div class="card-header text-secondary">
                        <i class="bi bi-building"></i> Asal Perguruan Tinggi
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td width="35%" class="text-muted">Nama</td>
                                <td class="fw-bold"><?= htmlspecialchars($header['nama_asal'] ?: '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">NIM</td>
                                <td class="fw-bold"><?= htmlspecialchars($header['nim_asal'] ?: '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Perguruan Tinggi</td>
                                <td><?= htmlspecialchars($header['pt_asal'] ?: '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Program Studi</td>
                                <td><?= htmlspecialchars($header['prodi_asal'] ?: '-') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TUJUAN PERGURUAN TINGGI -->
            <div class="col-md-6 mb-3">
                <div class="card h-100 border border-primary-subtle">
                    <div class="card-header text-primary">
                        <i class="bi bi-mortarboard-fill"></i> Tujuan Perguruan Tinggi
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td width="35%" class="text-muted">Nama</td>
                                <td class="fw-bold"><?= htmlspecialchars($header['nama_tujuan'] ?: '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">NIM</td>
                                <td class="fw-bold"><?= htmlspecialchars($header['nim_tujuan'] ?: '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Perguruan Tinggi</td>
                                <td><?= htmlspecialchars($header['pt_tujuan'] ?: '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Program Studi</td>
                                <td><?= htmlspecialchars($header['prodi_tujuan'] ?: '-') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- CARD: HASIL KONVERSI (TABEL 2 KOLOM)       -->
<!-- ========================================== -->
<div class="card mb-4 border border-dark-subtle shadow-sm">
    <div class="card-header text-dark">
        <i class="bi bi-table text-primary"></i> Hasil Konversi Nilai
    </div>
    <div class="card-body p-0">
        <?php if (empty($detail_list)): ?>
        <div class="text-center py-4 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
            Belum ada detail konversi
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered mb-0" id="tabelHasil<?= $id_konversi ?>">
                <thead>
                    <tr class="text-center">
                        <th colspan="5" class="border-secondary-subtle text-secondary">
                            <i class="bi bi-building"></i> Nilai Perguruan Tinggi Asal
                        </th>
                        <th colspan="5" class="border-primary-subtle text-primary">
                            <i class="bi bi-mortarboard-fill"></i> Konversi Nilai PT Baru (Diakui)
                        </th>
                    </tr>
                    <tr class="table-secondary text-white text-center">
                        <th>Kode MK</th>
                        <th>Nama MK</th>
                        <th>Bobot MK<br><small class="fw-normal">(SKS)</small></th>
                        <th>Angka<br><small class="fw-normal">(Indeks)</small></th>
                        <th>Nilai Huruf</th>
                        <th>Kode MK</th>
                        <th>Nama MK</th>
                        <th>Bobot MK<br><small class="fw-normal">(SKS)</small></th>
                        <th>Angka<br><small class="fw-normal">(Indeks)</small></th>
                        <th>Nilai Huruf</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detail_list as $detail): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($detail['kode_mk_asal'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($detail['nama_mk_asal'] ?: '-') ?></td>
                        <td class="text-center"><?= $detail['sks_asal'] ?: '-' ?></td>
                        <td class="text-center fw-bold"><?= $detail['nilai_indek_asal'] ?? '-' ?></td>
                        <td class="text-center">
                            <span class="badge bg-secondary"><?= $detail['nilai_huruf_asal'] ?: '-' ?></span>
                        </td>
                        <td class="fw-bold text-primary"><?= htmlspecialchars($detail['kode_mk_tujuan'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($detail['nama_mk_tujuan'] ?: '-') ?></td>
                        <td class="text-center"><?= $detail['sks_tujuan'] ?: '-' ?></td>
                        <td class="text-center fw-bold text-primary"><?= $detail['nilai_indek_tujuan'] ?? '-' ?></td>
                        <td class="text-center">
                            <span class="badge bg-primary"><?= $detail['nilai_huruf_tujuan'] ?: '-' ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th class="text-center" colspan="2">JUMLAH SKS</th>
                        <th class="text-center"><?= $total_sks_asal ?></th>
                        <th class="text-center" colspan="2"></th>
                        <th class="text-center" colspan="2"></th>
                        <th class="text-center"><?= $total_sks_tujuan ?></th>
                        <th class="text-center" colspan="2"></th>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================== -->
<!-- CARD: MATA KULIAH WAJIB DITEMPUH           -->
<!-- ========================================== -->
<div class="card mb-4 border border-info-subtle shadow-sm">
    <div class="card-header text-dark">
        <i class="bi bi-clipboard-check text-dark"></i> Mata Kuliah Wajib Ditempuh
        <span class="badge bg-info text-dark ms-2"><?= count($mk_tidak_dipilih) ?> MK | <?= $total_sks_tidak_dipilih ?> SKS</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($mk_tidak_dipilih)): ?>
        <div class="text-center py-4 text-muted">
            <i class="bi bi-check-circle fs-1 d-block mb-2 text-success opacity-50"></i>
            Semua mata kuliah sudah dikonversi!
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-secondary text-center">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>Kode MK</th>
                        <th>Mata Kuliah</th>
                        <th width="80">Semester</th>
                        <th class="text-center">SKS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($mk_ganjil)): ?>
                    <tr class="table-secondary">
                        <td colspan="5" class="fw-bold text-center text-dark">
                            <i class="bi bi-calendar"></i> SEMESTER GANJIL 
                            <span class="badge bg-info text-dark ms-2"><?= count($mk_ganjil) ?> MK | <?= $total_sks_ganjil ?> SKS</span>
                        </td>
                    </tr>
                    <?php $n = 1; foreach ($mk_ganjil as $mk): ?>
                    <tr>
                        <td class="text-center"><?= $n++ ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($mk['kode_mk']) ?></td>
                        <td><?= htmlspecialchars($mk['nama_mk']) ?></td>
                        <td class="text-center"><span class="badge bg-secondary"><?= htmlspecialchars($mk['semester']) ?></span></td>
                        <td class="text-center"><span class="badge bg-info text-dark"><?= $mk['sks'] ?> SKS</span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($mk_genap)): ?>
                    <tr class="table-secondary">
                        <td colspan="5" class="fw-bold text-center text-dark">
                            <i class="bi bi-calendar"></i> SEMESTER GENAP 
                            <span class="badge bg-info text-dark ms-2"><?= count($mk_genap) ?> MK | <?= $total_sks_genap ?> SKS</span>
                        </td>
                    </tr>
                    <?php $n = 1; foreach ($mk_genap as $mk): ?>
                    <tr>
                        <td class="text-center"><?= $n++ ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($mk['kode_mk']) ?></td>
                        <td><?= htmlspecialchars($mk['nama_mk']) ?></td>
                        <td class="text-center"><span class="badge bg-secondary"><?= htmlspecialchars($mk['semester']) ?></span></td>
                        <td class="text-center"><span class="badge bg-info text-dark"><?= $mk['sks'] ?> SKS</span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-secondary text-dark">
                    <tr>
                        <th colspan="4" class="text-center">JUMLAH TOTAL</th>
                        <th class="text-center"><?= $total_sks_tidak_dipilih ?> SKS</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tombol Aksi Bawah -->
<div class="d-flex justify-content-between mb-5">
    <a href="?page=petugas/konversi_rpl" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
    <?php if ($header['status'] == 'menunggu'): ?>
    <div>
        <button type="button" class="btn btn-success me-2" onclick="verifikasiKonversi(<?= $id_konversi ?>)">
            <i class="bi bi-check-circle"></i> Verifikasi
        </button>
        <button type="button" class="btn btn-danger" onclick="tolakKonversi(<?= $id_konversi ?>)">
            <i class="bi bi-x-circle"></i> Tolak
        </button>
    </div>
    <?php endif; ?>
</div>

<script>
function verifikasiKonversi(id) {
    if (confirm('Yakin ingin memverifikasi konversi ini?')) {
        window.location.href = 'proses_verifikasi.php?id=' + id + '&status=diverifikasi';
    }
}
function tolakKonversi(id) {
    var alasan = prompt('Masukkan alasan penolakan:');
    if (alasan !== null && alasan.trim() !== '') {
        window.location.href = 'proses_verifikasi.php?id=' + id + '&status=ditolak&catatan=' + encodeURIComponent(alasan);
    }
}
</script>