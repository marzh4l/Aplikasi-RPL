<?php
    /**
     * Export Excel - Seluruh Data Konversi Nilai RPL Mahasiswa
     * Output: HTML Table dengan header Excel-compatible
     */

    require_once __DIR__ . '/../config/database.php'; // Sesuaikan path koneksi Anda

    session_start();

    if (!isset($_SESSION['user_id'])) {
        die('Akses ditolak. Silakan login terlebih dahulu.');
    }

    $id_mhs = $_SESSION['user_id'];

    // ===== AMBIL SEMUA DATA KONVERSI HEADER =====
    $q_header = $conn->prepare("SELECT * FROM konversi_header WHERE id_mahasiswa = ? ORDER BY id DESC");
    $q_header->bind_param("i", $id_mhs);
    $q_header->execute();
    $result_header = $q_header->get_result();

    if ($result_header->num_rows == 0) {
        die('Belum ada data konversi.');
    }

    // ===== EXCEL HEADERS =====
    $filename = 'Data_Konversi_RPL_' . date('Ymd_His') . '.xls';
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Expires: 0");

    // BOM for UTF-8 Excel compatibility
    echo "ï»¿";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <style>
            table { border-collapse: collapse; }
            td, th { border: 1px solid #000000; padding: 4px; font-family: Arial, sans-serif; font-size: 9pt; vertical-align: middle; }
            .title { font-size: 13pt; font-weight: bold; text-align: center; }
            .subtitle { font-size: 9pt; text-align: center; }
            .konversi-title { font-size: 10pt; font-weight: bold; background-color: #E7E6E6; padding: 6px; }
            .header-asal { background-color: #B4C7E7; font-weight: bold; text-align: center; }
            .header-tujuan { background-color: #F4B084; font-weight: bold; text-align: center; }
            .group-header { background-color: #D9D9D9; font-weight: bold; text-align: center; }
            .total-row { background-color: #E7E6E6; font-weight: bold; }
            .tfoot-total { background-color: #404040; color: #FFFFFF; font-weight: bold; }
            .label-bold { font-weight: bold; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .no-border td { border: none; }
        </style>
    </head>
    <body>

    <!-- Title -->
    <table width="100%" border="0">
        <tr><td class="title" colspan="10">DATA KONVERSI NILAI RPL</td></tr>
        <tr><td class="subtitle" colspan="10">Seluruh Riwayat Konversi Mahasiswa</td></tr>
        <tr><td colspan="10">&nbsp;</td></tr>
    </table>

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

    <!-- Konversi Block -->
    <table width="100%" border="1">
        <tr>
            <td class="konversi-title" colspan="10">
                &#127891; Data Konversi #<?= $no ?> | Periode: <?= htmlspecialchars($header['periode_tahun'] ?? '-') ?> | Status: <?= $status_label ?>
            </td>
        </tr>
    </table>

    <!-- Info PT -->
    <table width="100%" border="1">
        <tr>
            <td colspan="5" valign="top">
                <strong>&#127970; ASAL PERGURUAN TINGGI</strong><br><br>
                <table width="100%" border="0" class="no-border">
                    <tr><td width="25%">Nama</td><td width="75%" class="label-bold">: <?= htmlspecialchars($header['nama_asal'] ?? '-') ?></td></tr>
                    <tr><td>NIM</td><td class="label-bold">: <?= htmlspecialchars($header['nim_asal'] ?? '-') ?></td></tr>
                    <tr><td>PT</td><td>: <?= htmlspecialchars($header['pt_asal'] ?? '-') ?></td></tr>
                    <tr><td>Prodi</td><td>: <?= htmlspecialchars($header['prodi_asal'] ?? '-') ?></td></tr>
                </table>
            </td>
            <td colspan="5" valign="top">
                <strong>&#127979; TUJUAN PERGURUAN TINGGI</strong><br><br>
                <table width="100%" border="0" class="no-border">
                    <tr><td width="25%">Nama</td><td width="75%" class="label-bold">: <?= htmlspecialchars($header['nama_tujuan'] ?? '-') ?></td></tr>
                    <tr><td>NIM</td><td class="label-bold">: <?= htmlspecialchars($header['nim_tujuan'] ?? '-') ?></td></tr>
                    <tr><td>PT</td><td>: <?= htmlspecialchars($header['pt_tujuan'] ?? '-') ?></td></tr>
                    <tr><td>Prodi</td><td>: <?= htmlspecialchars($header['prodi_tujuan'] ?? '-') ?></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Hasil Konversi -->
    <table width="100%" border="1">
        <tr>
            <td colspan="10" style="font-size: 10pt; font-weight: bold;">&#128202; HASIL KONVERSI NILAI</td>
        </tr>
        <tr>
            <th colspan="5" class="header-asal">Nilai Perguruan Tinggi Asal</th>
            <th colspan="5" class="header-tujuan">Konversi Nilai PT Baru (Diakui)</th>
        </tr>
        <tr>
            <th>Kode MK</th><th>Nama MK</th><th>SKS</th><th>Indeks</th><th>Nilai</th>
            <th>Kode MK</th><th>Nama MK</th><th>SKS</th><th>Indeks</th><th>Nilai</th>
        </tr>
        <?php if (empty($detail_list)): ?>
        <tr><td colspan="10" align="center">Belum ada detail konversi</td></tr>
        <?php else: ?>
            <?php foreach ($detail_list as $detail): ?>
            <tr>
                <td><?= htmlspecialchars($detail['kode_mk_asal'] ?: '-') ?></td>
                <td><?= htmlspecialchars($detail['nama_mk_asal'] ?: '-') ?></td>
                <td class="text-center"><?= $detail['sks_asal'] ?: '-' ?></td>
                <td class="text-center"><?= $detail['nilai_indek_asal'] ?? '-' ?></td>
                <td class="text-center"><?= $detail['nilai_huruf_asal'] ?: '-' ?></td>
                <td><?= htmlspecialchars($detail['kode_mk_tujuan'] ?: '-') ?></td>
                <td><?= htmlspecialchars($detail['nama_mk_tujuan'] ?: '-') ?></td>
                <td class="text-center"><?= $detail['sks_tujuan'] ?: '-' ?></td>
                <td class="text-center"><?= $detail['nilai_indek_tujuan'] ?? '-' ?></td>
                <td class="text-center"><?= $detail['nilai_huruf_tujuan'] ?: '-' ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="2" class="text-right">JUMLAH SKS</td>
                <td class="text-center"><?= $total_sks_asal ?></td>
                <td colspan="2"></td>
                <td colspan="2" class="text-right">JUMLAH SKS</td>
                <td class="text-center"><?= $total_sks_tujuan ?></td>
                <td colspan="2"></td>
            </tr>
        <?php endif; ?>
    </table>

    <!-- MK Wajib Ditempuh -->
    <table width="100%" border="1">
        <tr>
            <td colspan="5" style="font-size: 10pt; font-weight: bold;">
                &#128221; MATA KULIAH WAJIB DITEMPUH (BELUM DIKONVERSI) &mdash; <?= count($mk_ganjil) + count($mk_genap) ?> MK | <?= $total_sks_tidak_dipilih ?> SKS
            </td>
        </tr>
        <tr>
            <th width="30">No</th><th>Kode MK</th><th>Mata Kuliah</th><th width="50">Semester</th><th width="40">SKS</th>
        </tr>
        <?php if (empty($mk_ganjil) && empty($mk_genap)): ?>
        <tr><td colspan="5" align="center">Semua mata kuliah sudah dikonversi!</td></tr>
        <?php else: ?>
            <?php $n = 1; ?>
            <?php if (!empty($mk_ganjil)): ?>
            <tr class="group-header">
                <td colspan="5">SEMESTER GANJIL (I, III, V, VII, PILIHAN) &mdash; <?= count($mk_ganjil) ?> MK | <?= $total_sks_ganjil ?> SKS</td>
            </tr>
            <?php foreach ($mk_ganjil as $mk): ?>
            <tr>
                <td class="text-center"><?= $n++ ?></td>
                <td><?= htmlspecialchars($mk['kode_mk']) ?></td>
                <td><?= htmlspecialchars($mk['nama_mk']) ?></td>
                <td class="text-center"><?= htmlspecialchars($mk['semester']) ?></td>
                <td class="text-center"><?= $mk['sks'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($mk_genap)): ?>
            <tr class="group-header">
                <td colspan="5">SEMESTER GENAP (II, IV, VI, VIII) &mdash; <?= count($mk_genap) ?> MK | <?= $total_sks_genap ?> SKS</td>
            </tr>
            <?php foreach ($mk_genap as $mk): ?>
            <tr>
                <td class="text-center"><?= $n++ ?></td>
                <td><?= htmlspecialchars($mk['kode_mk']) ?></td>
                <td><?= htmlspecialchars($mk['nama_mk']) ?></td>
                <td class="text-center"><?= htmlspecialchars($mk['semester']) ?></td>
                <td class="text-center"><?= $mk['sks'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>

            <tr class="tfoot-total">
                <th colspan="4" class="text-right">TOTAL KESELURUHAN</th>
                <th class="text-center"><?= $total_sks_tidak_dipilih ?> SKS</th>
            </tr>
        <?php endif; ?>
    </table>

    <br><br>

    <?php $no++; endwhile; ?>

    <table width="100%" border="0">
        <tr>
            <td width="50%" align="center">
                <p>Mahasiswa,</p>
                <br><br><br>
                <p><strong><?= htmlspecialchars($header['nama_tujuan'] ?? '') ?></strong></p>
                <p>NIM. <?= htmlspecialchars($header['nim_tujuan'] ?? '') ?></p>
            </td>
            <td width="50%" align="center">
                <p>Petugas Verifikasi,</p>
                <br><br><br>
                <p><strong>..................................</strong></p>
                <p>NIP. ..................................</p>
            </td>
        </tr>
    </table>

    <br>
    <p style="font-size: 8pt; color: #666; text-align: center;">
        Dokumen ini di-export secara otomatis dari Sistem Konversi Nilai RPL | Tanggal: <?= date('d/m/Y H:i:s') ?>
    </p>

    </body>
</html>