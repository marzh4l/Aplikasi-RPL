<?php
    /**
     * Export PDF - Seluruh Data Konversi Nilai RPL Mahasiswa
     * Requires: dompdf/dompdf via Composer
     */

    require_once __DIR__ . '/../config/database.php'; // Sesuaikan path koneksi Anda
    require_once __DIR__ . '/../vendor/autoload.php';   // Path autoload Composer

    use Dompdf\Dompdf;
    use Dompdf\Options;

    session_start();

    if (!isset($_SESSION['user_id'])) {
        die('<h3>Akses ditolak. Silakan login terlebih dahulu.</h3>');
    }

    $id_mhs = $_SESSION['user_id'];

    // ===== AMBIL SEMUA DATA KONVERSI HEADER =====
    $q_header = $conn->prepare("SELECT * FROM konversi_header WHERE id_mahasiswa = ? ORDER BY id DESC");
    $q_header->bind_param("i", $id_mhs);
    $q_header->execute();
    $result_header = $q_header->get_result();

    if ($result_header->num_rows == 0) {
        die('<h3>Belum ada data konversi.</h3>');
    }

    // ===== BUILD HTML FOR PDF =====
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Data Konversi Nilai RPL</title>
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: Arial, sans-serif; font-size: 9pt; color: #333; line-height: 1.3; }
            .container { padding: 15px; }

            .doc-header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 8px; }
            .doc-header h2 { font-size: 13pt; margin-bottom: 3px; text-transform: uppercase; }
            .doc-header p { font-size: 9pt; color: #555; }

            /* Konversi Block */
            .konversi-block { margin-bottom: 25px; border: 1px solid #999; padding: 10px; page-break-inside: avoid; }
            .konversi-title { font-size: 11pt; font-weight: bold; margin-bottom: 8px; padding: 6px; background: #f5f5f5; border-bottom: 1px solid #ccc; }

            /* Info Grid */
            .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
            .info-grid td { width: 50%; vertical-align: top; padding: 4px; }
            .info-box { border: 1px solid #bbb; padding: 8px; }
            .info-box-title { font-weight: bold; font-size: 9pt; border-bottom: 1px solid #bbb; padding-bottom: 4px; margin-bottom: 6px; background: #f0f0f0; padding: 5px; margin: -8px -8px 6px -8px; }
            .info-table { width: 100%; font-size: 8pt; }
            .info-table td { padding: 2px 0; border: none; }
            .info-table td:first-child { width: 35%; color: #555; }
            .label { font-weight: bold; }

            .status-box { text-align: right; margin: 5px 0; font-size: 9pt; font-weight: bold; color: #333; }

            /* Main Table */
            .main-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8pt; }
            .main-table th, .main-table td { border: 1px solid #333; padding: 4px; text-align: center; vertical-align: middle; }
            .main-table th { background: #f0f0f0; font-weight: bold; }
            .header-asal { background: #d9e2f3 !important; color: #000; }
            .header-tujuan { background: #f4cccc !important; color: #000; }
            .text-left { text-align: left; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .fw-bold { font-weight: bold; }
            .total-row { background: #e0e0e0; font-weight: bold; }

            /* MK Table */
            .mk-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 8pt; }
            .mk-table th, .mk-table td { border: 1px solid #333; padding: 4px; vertical-align: middle; }
            .mk-table th { background: #f0f0f0; text-align: center; }
            .group-header { background: #d9d9d9 !important; font-weight: bold; text-align: center; }
            .tfoot-total { background: #333 !important; color: #fff; font-weight: bold; }

            .doc-footer { margin-top: 20px; font-size: 8pt; color: #666; text-align: center; border-top: 1px solid #ccc; padding-top: 8px; }

            .page-break { page-break-after: always; }
        </style>
    </head>
    <body>
    <div class="container">

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
        $status_label = ucfirst($header['status'] ?? 'draft');
    ?>

        <div class="konversi-block">
            <div class="konversi-title">
                &#127891; Data Konversi #<?= $no ?> | Periode: <?= htmlspecialchars($header['periode_tahun'] ?? '-') ?> | Status: <?= $status_label ?>
            </div>

            <!-- Info PT -->
            <table class="info-grid">
                <tr>
                    <td>
                        <div class="info-box">
                            <div class="info-box-title">&#127970; Asal Perguruan Tinggi</div>
                            <table class="info-table">
                                <tr><td>Nama</td><td class="label">: <?= htmlspecialchars($header['nama_asal'] ?? '-') ?></td></tr>
                                <tr><td>NIM</td><td class="label">: <?= htmlspecialchars($header['nim_asal'] ?? '-') ?></td></tr>
                                <tr><td>PT</td><td>: <?= htmlspecialchars($header['pt_asal'] ?? '-') ?></td></tr>
                                <tr><td>Prodi</td><td>: <?= htmlspecialchars($header['prodi_asal'] ?? '-') ?></td></tr>
                            </table>
                        </div>
                    </td>
                    <td>
                        <div class="info-box">
                            <div class="info-box-title">&#127979; Tujuan Perguruan Tinggi</div>
                            <table class="info-table">
                                <tr><td>Nama</td><td class="label">: <?= htmlspecialchars($header['nama_tujuan'] ?? '-') ?></td></tr>
                                <tr><td>NIM</td><td class="label">: <?= htmlspecialchars($header['nim_tujuan'] ?? '-') ?></td></tr>
                                <tr><td>PT</td><td>: <?= htmlspecialchars($header['pt_tujuan'] ?? '-') ?></td></tr>
                                <tr><td>Prodi</td><td>: <?= htmlspecialchars($header['prodi_tujuan'] ?? '-') ?></td></tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Hasil Konversi -->
            <p style="font-size: 9pt; font-weight: bold; margin: 8px 0 4px 0;">&#128202; Hasil Konversi Nilai</p>
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

            <!-- MK Wajib Ditempuh -->
            <p style="font-size: 9pt; font-weight: bold; margin: 8px 0 4px 0;">&#128221; Mata Kuliah Wajib Ditempuh (Belum Dikonversi) &mdash; <?= count($mk_ganjil) + count($mk_genap) ?> MK | <?= $total_sks_tidak_dipilih ?> SKS</p>
            <table class="mk-table">
                <thead>
                    <tr><th width="30">No</th><th>Kode MK</th><th>Mata Kuliah</th><th width="50">Semester</th><th width="40">SKS</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($mk_ganjil) && empty($mk_genap)): ?>
                    <tr><td colspan="5" align="center">Semua mata kuliah sudah dikonversi!</td></tr>
                    <?php else: ?>
                        <?php $n = 1; ?>
                        <?php if (!empty($mk_ganjil)): ?>
                        <tr class="group-header"><td colspan="5">SEMESTER GANJIL &mdash; <?= count($mk_ganjil) ?> MK | <?= $total_sks_ganjil ?> SKS</td></tr>
                        <?php foreach ($mk_ganjil as $mk): ?>
                        <tr>
                            <td class="text-center"><?= $n++ ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($mk['kode_mk']) ?></td>
                            <td><?= htmlspecialchars($mk['nama_mk']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($mk['semester']) ?></td>
                            <td class="text-center"><?= $mk['sks'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($mk_genap)): ?>
                        <tr class="group-header"><td colspan="5">SEMESTER GENAP &mdash; <?= count($mk_genap) ?> MK | <?= $total_sks_genap ?> SKS</td></tr>
                        <?php foreach ($mk_genap as $mk): ?>
                        <tr>
                            <td class="text-center"><?= $n++ ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($mk['kode_mk']) ?></td>
                            <td><?= htmlspecialchars($mk['nama_mk']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($mk['semester']) ?></td>
                            <td class="text-center"><?= $mk['sks'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($mk_ganjil) || !empty($mk_genap)): ?>
                <tfoot>
                    <tr class="tfoot-total">
                        <th colspan="4" class="text-right">TOTAL KESELURUHAN</th>
                        <th class="text-center"><?= $total_sks_tidak_dipilih ?> SKS</th>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>

    <?php $no++; endwhile; ?>

        <div class="doc-footer">
            Dokumen ini dicetak secara otomatis dari Sistem Konversi Nilai RPL.<br>
            Tanggal Cetak: <?= date('d F Y H:i:s') ?>
        </div>

    </div>
    </body>
    </html>
    <?php
    $html = ob_get_clean();

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', false);
    $options->set('defaultFont', 'Arial');
    $options->set('dpi', 96);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    $filename = 'Data_Konversi_RPL_' . date('Ymd_His') . '.pdf';
    $dompdf->stream($filename, ['Attachment' => true]);
    exit;
?>