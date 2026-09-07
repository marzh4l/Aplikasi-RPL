<?php
require '../config/database.php';
header('Content-Type: application/json');

$id_prodi = intval($_GET['id_prodi'] ?? 0);
$periode = isset($_GET['periode']) ? $conn->real_escape_string($_GET['periode']) : '';

if ($id_prodi > 0 && !empty($periode)) {
    $stmt = $conn->prepare("SELECT * FROM kurikulum WHERE id_prodi = ? AND periode_tahun = ? ORDER BY id ASC");
    $stmt->bind_param("is", $id_prodi, $periode);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Prodi dan periode wajib dipilih']);
}
?>