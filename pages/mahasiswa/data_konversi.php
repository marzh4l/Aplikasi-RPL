<?php
// Ambil data mahasiswa dari session
$id_mhs = $_SESSION['user_id'] ?? 0;

// ===== AMBIL DATA KONVERSI HEADER =====
$q_header = $conn->prepare("SELECT * FROM konversi_header WHERE id_mahasiswa = ? ORDER BY id DESC");
$q_header->bind_param("i", $id_mhs);
$q_header->execute();
$result_header = $q_header->get_result();

// Cek apakah ada data
$total_konversi = $result_header->num_rows;
?>

<h4 class="text-danger mb-4"><i class="bi bi-table"></i> Data Konversi Saya</h4>

<?php if ($total_konversi == 0): ?>
<!-- BELUM ADA DATA -->
<div class="card border-0 shadow-sm text-center py-5">
    <div class="card-body">
        <i class="bi bi-inbox fs-1 text-muted opacity-50 d-block mb-3"></i>
        <h5 class="text-muted">Belum Ada Data Konversi</h5>
        <p class="text-muted">Anda belum menginput data konversi RPL.</p>
        <a href="?page=mahasiswa/input_konversi" class="btn btn-theme">
            <i class="bi bi-plus-circle"></i> Input Konversi Baru
        </a>
    </div>
</div>

<?php else: 
    $no = 1;
    while ($header = $result_header->fetch_assoc()):
        $id_konversi = $header['id'];
        
        // ===== AMBIL DATA DETAIL KONVERSI =====
        $q_detail = $conn->prepare("SELECT * FROM konversi_detail WHERE id_konversi = ? ORDER BY id");
        $q_detail->bind_param("i", $id_konversi);
        $q_detail->execute();
        $result_detail = $q_detail->get_result();
        
        // Simpan detail ke array untuk diproses
        $detail_list = [];
        $kode_mk_dipilih = [];
        $total_sks_asal = 0;      // ← TOTAL SKS ASAL
        $total_sks_tujuan = 0;    // ← TOTAL SKS TUJUAN
        while ($d = $result_detail->fetch_assoc()) {
            $detail_list[] = $d;
            $kode_mk_dipilih[] = $d['kode_mk_tujuan'];
            // Hitung total SKS
            $total_sks_asal += intval($d['sks_asal'] ?? 0);
            $total_sks_tujuan += intval($d['sks_tujuan'] ?? 0);
        }
        
        // ===== AMBIL MATA KULIAH YANG TIDAK DIPILIH =====
        // Cari kurikulum prodi tujuan & periode yang sama, exclude yang sudah dipilih
        $mk_tidak_dipilih = [];
        $mk_ganjil = [];      // Semester I, III, V, VII, PILIHAN
        $mk_genap = [];       // Semester II, IV, VI, VIII
        $total_sks_ganjil = 0;
        $total_sks_genap = 0;

        if (!empty($header['prodi_tujuan']) && !empty($header['periode_tahun'])) {
            // Cari id_prodi dari nama_prodi
            $q_prodi_id = $conn->prepare("SELECT id FROM program_studi WHERE nama_prodi = ?");
            $q_prodi_id->bind_param("s", $header['prodi_tujuan']);
            $q_prodi_id->execute();
            $r_prodi_id = $q_prodi_id->get_result();
            
            if ($r_prodi_id->num_rows > 0) {
                $id_prodi_tujuan = $r_prodi_id->fetch_assoc()['id'];
                
                // Query kurikulum yang tidak ada di detail
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
                    
                    // Kelompokkan: Ganjil (I, III, V, VII, PILIHAN) | Genap (II, IV, VI, VIII)
                    $ganjil_list = ['I', 'III', 'V', 'VII', 'PILIHAN'];
                    $genap_list = ['II', 'IV', 'VI', 'VIII'];
                    
                    if (in_array($semester, $ganjil_list)) {
                        $mk_ganjil[] = $mk;
                        $total_sks_ganjil += intval($mk['sks']);
                    } elseif (in_array($semester, $genap_list)) {
                        $mk_genap[] = $mk;
                        $total_sks_genap += intval($mk['sks']);
                    } else {
                        // Jika semester tidak terdefinisi, masukkan ke ganjil sebagai default
                        $mk_ganjil[] = $mk;
                        $total_sks_ganjil += intval($mk['sks']);
                    }
                }
            }
        }

        // Gabungkan untuk tampilan tabel: Ganjil dulu, lalu Genap
        $mk_tidak_dipilih = array_merge($mk_ganjil, $mk_genap);
        $total_sks_tidak_dipilih = $total_sks_ganjil + $total_sks_genap;
?>

<!-- ========================================== -->
<!-- CARD GROUP: DATA PERGURUAN TINGGI          -->
<!-- ========================================== -->
<div class="card mb-4 border border-dark-subtle shadow-sm">
    <div class="card-header text-dark d-flex justify-content-between align-items-center">
        <!-- BLOK KIRI -->
        <span><i class="bi bi-mortarboard text-primary"></i> Data Konversi</span>

        <!-- BLOK KANAN -->
        <div class="d-flex align-items-center gap-2">
            <!-- Badge Periode -->
             Periode: 
            <span class="badge bg-danger text-white px-2 py-1">
                <?= htmlspecialchars($header['periode_tahun']) ?>
            </span>

            <?php 
            $badge_class = [
                'draft' => 'bg-secondary',
                'menunggu' => 'bg-warning text-dark',
                'diverifikasi' => 'bg-success',
                'ditolak' => 'bg-danger'
            ];
            $status = $header['status'];
            ?>
            
            <!-- Badge Status (Hapus fs-6, tambah padding manual agar seragam) -->
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
        
         <?php if ($header['status'] == 'diverifikasi'): ?>
        <div class="text-end mt-2">
            <a href="<?= BASE_URL ?>exports/print.php?id=<?= $header['nim_tujuan'] ?>" class="btn btn-secondary me-2" target="_blank">
                <i class="bi bi-printer"></i> Cetak
            </a>
            <!-- <a href="<?= BASE_URL ?>exports/export_pdf.php?id=<?= $id_konversi ?>" class="btn btn-danger me-2" target="_blank">
                <i class="bi bi-file-pdf"></i> Cetak PDF
            </a> -->
            <a href="<?= BASE_URL ?>exports/export_excel.php?id=<?= $id_konversi ?>" class="btn btn-success" target="_blank">
                <i class="bi bi-file-excel"></i> Export Excel
            </a>
        </div>
    <?php endif; ?>
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
                        <!-- KOLOM KIRI: ASAL -->
                        <td class="fw-bold"><?= htmlspecialchars($detail['kode_mk_asal'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($detail['nama_mk_asal'] ?: '-') ?></td>
                        <td class="text-center"><?= $detail['sks_asal'] ?: '-' ?></td>
                        <td class="text-center fw-bold"><?= $detail['nilai_indek_asal'] ?? '-' ?></td>
                        <td class="text-center">
                            <span class="badge bg-secondary"><?= $detail['nilai_huruf_asal'] ?: '-' ?></span>
                        </td>
                        <!-- KOLOM KANAN: TUJUAN -->
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
<!-- CARD: MATA KULIAH YANG AKAN DIPILIH            -->
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
                    <!-- SEMESTER GANJIL -->
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
                    
                    <!-- SEMESTER GENAP -->
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

<?php 
    $no++;
    endwhile; 
endif; 
?>

<style>
/* Styling card */
.card-header {
    font-weight: 600;
}

/* Tabel hasil konversi */
[id^="tabelHasil"] th,
[id^="tabelHasil"] td {
    vertical-align: middle;
    font-size: 0.85rem;
}

[id^="tabelHasil"] thead tr:first-child th {
    font-size: 0.95rem;
    padding: 10px;
}

/* Responsive mobile */
@media (max-width: 768px) {
    [id^="tabelHasil"] {
        font-size: 0.75rem;
    }
    [id^="tabelHasil"] th,
    [id^="tabelHasil"] td {
        padding: 4px;
        min-width: 60px;
    }
}
</style>