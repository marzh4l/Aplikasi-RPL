<?php
    /**
     * Proses Verifikasi/Tolak Konversi RPL
     * Update status konversi_header
     */

    require_once __DIR__ . '/../../config/database.php'; // Sesuaikan path

    session_start();

    // Cek login dan role petugas
    if (!isset($_SESSION['user_id'])) {
        die('Akses ditolak.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die('Method tidak diizinkan.');
    }

    $id_konversi = isset($_POST['id_konversi']) ? intval($_POST['id_konversi']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $catatan = isset($_POST['catatan']) ? trim($_POST['catatan']) : '';

    // Validasi status
    $allowed_status = ['diverifikasi', 'ditolak'];
    if ($id_konversi <= 0 || !in_array($status, $allowed_status)) {
        die('Data tidak valid.');
    }

    // Update status
    $q_update = $conn->prepare("UPDATE konversi_header SET status = ?, catatan_verifikasi = ?, tgl_verifikasi = NOW(), id_petugas = ? WHERE id = ?");
    $q_update->bind_param("ssii", $status, $catatan, $_SESSION['user_id'], $id_konversi);

    if ($q_update->execute()) {
        $msg = ($status == 'diverifikasi') ? 'Konversi berhasil diverifikasi.' : 'Konversi ditolak.';
        echo "<script>alert('$msg'); window.location.href='?page=rpl/konversi';</script>";
    } else {
        echo "<script>alert('Gagal memproses. Silakan coba lagi.'); window.history.back();</script>";
    }
exit;
?>