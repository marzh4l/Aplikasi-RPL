<?php
    /**
     * Print Page - Seluruh Data Konversi Nilai RPL Mahasiswa (Fixed v4)
     * MK Wajib Ditempuh dipisah halaman, tombol kembali ke data_konversi
     */

    require_once __DIR__ . '/../config/database.php'; // Sesuaikan path koneksi Anda

    session_start();

    if (!isset($_SESSION['user_id'])) {
        die('<h3 style="text-align:center; margin-top:50px;">Akses ditolak. Silakan login terlebih dahulu.</h3>');
    }

    $id_mhs = $_GET['id'];

    // ===== AMBIL SEMUA DATA KONVERSI HEADER =====
    $q_header = $conn->prepare("SELECT * FROM konversi_header WHERE nim_tujuan = ? ORDER BY id DESC");
    $q_header->bind_param("i", $id_mhs);
    $q_header->execute();
    $result_header = $q_header->get_result();

    if ($result_header->num_rows == 0) {
        die('<h3 style="text-align:center; margin-top:50px;">Belum ada data konversi.</h3>');
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Data Konversi Nilai RPL</title>
    <link rel="stylesheet" href="../assets/css/print_konversi.css">
</head>
<body>
<div class="container">

    <!-- Print Controls (Hidden saat print) -->
    <div class="print-controls">
        <h3>&#128424;&#65039; Print Preview - Data Konversi Nilai RPL</h3>
        <div style="display: flex; gap: 8px;">
            <a href="?page=mahasiswa/data_konversi" class="btn btn-back">&#8592; Kembali</a>
            <button onclick="window.print()" class="btn btn-print">&#128424;&#65039; Print / Save PDF</button>
        </div>
    </div>

    <!-- Document Header -->
    <div class="doc-header">
        <h2>Data Konversi Nilai RPL</h2>
        <p>Seluruh Riwayat Konversi Mahasiswa</p>
    </div>

<?php
$no = 1;
while ($header = $result_header->fetch_assoc()):
    $id_konversi = $header['id'];

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
                $q_mk_belum = $conn->prepare("SELECT * FROM kurikulum WHERE id_prodi = ? AND periode_tahun = ? ORDER BY semester, kode_mk");
                $q_mk_belum->bind_param("is", $id_prodi_tujuan, $header['periode_tahun']);
            } else {
                $types = str_repeat('s', count($kode_mk_dipilih));
                $q_mk_belum = $conn->prepare("SELECT * FROM kurikulum WHERE id_prodi = ? AND periode_tahun = ? AND kode_mk NOT IN ($placeholders) ORDER BY semester, kode_mk");
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

    $total_sks_tidak_dipilih = $total_sks_ganjil + $total_sks_genap;

    $badge_class = [
        'draft' => 'badge-draft',
        'menunggu' => 'badge-menunggu',
        'diverifikasi' => 'badge-diverifikasi',
        'ditolak' => 'badge-ditolak'
    ];
    $status = $header['status'] ?? 'draft';
    $status_label = ucfirst($status);
?>

    <!-- Konversi Block -->
    <div class="konversi-block">
        <div class="konversi-header">
            <span class="left">&#127891; Data Konversi | Periode: <?= htmlspecialchars($header['periode_tahun'] ?? '-') ?></span>
            <span class="right"><span class="badge <?= $badge_class[$status] ?? 'badge-draft' ?>"><?= $status_label ?></span></span>
        </div>

        <!-- Info PT -->
        <table class="info-table-outer">
            <tr>
                <td>
                    <div class="info-card-title">&#127970; Asal Perguruan Tinggi</div>
                    <table class="info-table">
                        <tr><td>Nama</td><td>: <?= htmlspecialchars($header['nama_asal'] ?? '-') ?></td></tr>
                        <tr><td>NIM</td><td>: <?= htmlspecialchars($header['nim_asal'] ?? '-') ?></td></tr>
                        <tr><td>PT</td><td>: <?= htmlspecialchars($header['pt_asal'] ?? '-') ?></td></tr>
                        <tr><td>Prodi</td><td>: <?= htmlspecialchars($header['prodi_asal'] ?? '-') ?></td></tr>
                    </table>
                </td>
                <td>
                    <div class="info-card-title">&#127979; Tujuan Perguruan Tinggi</div>
                    <table class="info-table">
                        <tr><td>Nama</td><td>: <?= htmlspecialchars($header['nama_tujuan'] ?? '-') ?></td></tr>
                        <tr><td>NIM</td><td>: <?= htmlspecialchars($header['nim_tujuan'] ?? '-') ?></td></tr>
                        <tr><td>PT</td><td>: <?= htmlspecialchars($header['pt_tujuan'] ?? '-') ?></td></tr>
                        <tr><td>Prodi</td><td>: <?= htmlspecialchars($header['prodi_tujuan'] ?? '-') ?></td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Hasil Konversi -->
        <div class="section-title">&#128202; Hasil Konversi Nilai</div>
        <table class="main-table">
            <thead>
                <tr>
                    <th colspan="5" class="header-asal">Nilai Perguruan Tinggi Asal</th>
                    <th colspan="5" class="header-tujuan">Konversi Nilai PT Baru (Diakui)</th>
                </tr>
                <tr>
                    <th>Kode MK</th><th>Nama MK</th><th>SKS</th><th>Indeks</th><th>Nilai</th>
                    <th>Kode MK</th><th>Nama MK</th><th>SKS</th><th>Indeks</th><th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($detail_list)): ?>
                <tr><td colspan="10" style="padding: 10px;">Belum ada detail konversi</td></tr>
                <?php else: ?>
                    <?php foreach ($detail_list as $detail): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($detail['kode_mk_asal'] ?: '-') ?></td>
                        <td class="text-left"><?= htmlspecialchars($detail['nama_mk_asal'] ?: '-') ?></td>
                        <td><?= $detail['sks_asal'] ?: '-' ?></td>
                        <td class="fw-bold"><?= $detail['nilai_indek_asal'] ?? '-' ?></td>
                        <td><?= $detail['nilai_huruf_asal'] ?: '-' ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($detail['kode_mk_tujuan'] ?: '-') ?></td>
                        <td class="text-left"><?= htmlspecialchars($detail['nama_mk_tujuan'] ?: '-') ?></td>
                        <td><?= $detail['sks_tujuan'] ?: '-' ?></td>
                        <td class="fw-bold"><?= $detail['nilai_indek_tujuan'] ?? '-' ?></td>
                        <td><?= $detail['nilai_huruf_tujuan'] ?: '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="2" class="text-right">JUMLAH SKS</td>
                        <td><?= $total_sks_asal ?></td>
                        <td colspan="2"></td>
                        <td colspan="2" class="text-right">JUMLAH SKS</td>
                        <td><?= $total_sks_tujuan ?></td>
                        <td colspan="2"></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Signature -->
    <table class="signature-table">
        <tr>
            <td>
                <p>Mengetahui <br>Wakil Ketua 1</p>
                <br><br><br><br>
                <p class="fw-bold"></p>
                <p>Bdn. Anur Rohmin, S.ST, M.KM </p>
            </td>
            <td>
                <p>Palembang, <?= date('d F Y') ?> <br> Ka. Pusat Pengembangan Pembelajaran</p>
                <br><br><br><br>
                <p class="fw-bold">Ns. Lenny Astuti., S.Kep., M.Kes</p>
                <p>NIK.108749</p>
            </td>
        </tr>
    </table>

    <!-- MK Wajib Ditempuh - DIPISAH HALAMAN -->
    <div class="mk-section">
        <div class="konversi-header">
            <span class="left">&#127891; Data Konversi | Periode: <?= htmlspecialchars($header['periode_tahun'] ?? '-') ?></span>
            <span class="right"><span class="badge <?= $badge_class[$status] ?? 'badge-draft' ?>"><?= $status_label ?></span></span>
        </div>

        <div class="section-title">
            &#128221; Mata Kuliah Wajib Ditempuh (Belum Dikonversi)
        </div>
        <table class="mk-table">
            <thead>
                <tr>
                    <th width="35">No</th>
                    <th>Kode MK</th>
                    <th>Mata Kuliah</th>
                    <th width="50">Semester</th>
                    <th width="40">SKS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($mk_ganjil) && empty($mk_genap)): ?>
                <tr><td colspan="5" style="padding: 12px;">Semua mata kuliah sudah dikonversi!</td></tr>
                <?php else: ?>
                    <?php $n = 1; ?>

                    <?php if (!empty($mk_ganjil)): ?>
                    <tr class="group-header">
                        <td colspan="5">SEMESTER GANJI &mdash; <?= count($mk_ganjil) ?> MK | <?= $total_sks_ganjil ?> SKS</td>
                    </tr>
                    <?php foreach ($mk_ganjil as $mk): ?>
                    <tr>
                        <td><?= $n++ ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($mk['kode_mk']) ?></td>
                        <td class="text-left"><?= htmlspecialchars($mk['nama_mk']) ?></td>
                        <td><?= htmlspecialchars($mk['semester']) ?></td>
                        <td><?= $mk['sks'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($mk_genap)): ?>
                    <tr class="group-header">
                        <td colspan="5">SEMESTER GENAP &mdash; <?= count($mk_genap) ?> MK | <?= $total_sks_genap ?> SKS</td>
                    </tr>
                    <?php foreach ($mk_genap as $mk): ?>
                    <tr>
                        <td><?= $n++ ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($mk['kode_mk']) ?></td>
                        <td class="text-left"><?= htmlspecialchars($mk['nama_mk']) ?></td>
                        <td><?= htmlspecialchars($mk['semester']) ?></td>
                        <td><?= $mk['sks'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($mk_ganjil) || !empty($mk_genap)): ?>
            <tfoot>
                <tr class="tfoot-total">
                    <th colspan="4" class="text-right">TOTAL KESELURUHAN</th>
                    <th><?= $total_sks_tidak_dipilih ?> SKS</th>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>

<?php $no++; endwhile; ?>

    <!-- Signature -->
    <table class="signature-table">
        <tr>
            <td>
                <p>Mengetahui <br>Wakil Ketua 1,</p>
                <br><br><br><br>
                <p class="fw-bold"></p>
                <p>Bdn. Anur Rohmin, S.ST, M.KM </p>
            </td>
            <td>
                <p>Palembang, <?= date('d F Y') ?> <br> Ka. Pusat Pengembangan Pembelajaran</p>
                <br><br><br><br>
                <p class="fw-bold">Ns. Lenny Astuti., S.Kep., M.Kes</p>
                <p>NIK.108749</p>
            </td>
        </tr>
    </table>

    <div class="doc-footer">
        Dokumen ini dicetak secara otomatis dari Sistem Konversi Nilai RPL.<br>
        Tanggal Cetak: <?= date('d F Y H:i:s') ?>
    </div>

</div>
</body>
</html>
