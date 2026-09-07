<?php
session_start();
require 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Ambil data semua program studi
$prodi_list = [];
$prodi_query = $conn->query("SELECT * FROM program_studi ORDER BY id ASC");
if ($prodi_query) {
    while ($row = $prodi_query->fetch_assoc()) {
        $prodi_list[] = $row;
    }
}

// Ambil periode unik per prodi dari tabel kurikulum
$kurikulum_data = [];
$semester_list = [];
foreach ($prodi_list as $prodi) {
    $id_prodi = $prodi['id'];

    // Ambil periode yang tersedia untuk prodi ini
    $periode_stmt = $conn->prepare("SELECT DISTINCT periode_tahun FROM kurikulum WHERE id_prodi = ? ORDER BY periode_tahun DESC");
    $periode_stmt->bind_param("i", $id_prodi);
    $periode_stmt->execute();
    $periode_result = $periode_stmt->get_result();
    $periodes = [];
    while ($p = $periode_result->fetch_assoc()) {
        $periodes[] = $p['periode_tahun'];
    }
    $periode_stmt->close();

    // Gunakan periode pertama (terbaru) sebagai default
    $default_periode = $periodes[0] ?? '2024/2025';

    // Ambil semua kurikulum untuk prodi ini, group by semester
    $kurikulum_data[$id_prodi] = [];
    $semester_list[$id_prodi] = [];

    $stmt = $conn->prepare("SELECT kode_mk, nama_mk, sks, semester FROM kurikulum WHERE id_prodi = ? AND periode_tahun = ? ORDER BY id ASC");
    $stmt->bind_param("is", $id_prodi, $default_periode);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $sem = $row['semester'];
        if (!isset($kurikulum_data[$id_prodi][$sem])) {
            $kurikulum_data[$id_prodi][$sem] = [];
            $semester_list[$id_prodi][] = $sem;
        }
        $kurikulum_data[$id_prodi][$sem][] = $row;
    }
    $stmt->close();

    // Simpan info periode
    $prodi_list[array_search($prodi, $prodi_list)]['periode'] = $default_periode;
    $prodi_list[array_search($prodi, $prodi_list)]['periodes'] = $periodes;
}

// Ambil data bahan kajian
$bahan_kajian = [];
$bahan_query = $conn->query("SELECT bk.*, ps.nama_prodi FROM bahan_kajian bk JOIN program_studi ps ON bk.id_prodi = ps.id");
if ($bahan_query) {
    while ($row = $bahan_query->fetch_assoc()) {
        $bahan_kajian[$row['id_prodi']] = $row;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama'] = $user['nama_lengkap'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Konversi RPL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="container py-4">
        <div class="row align-items-center justify-content-center g-4">

            <!-- BAGIAN KIRI: Informasi Kurikulum & Bahan Kajian -->
            <div class="col-lg-7 col-md-12">
                <div class="info-panel p-4">
                    <div class="text-center mb-4">
                        <h4 class="logo-text"><i class="bi bi-book-half"></i> INFORMASI KURIKULUM & BAHAN KAJIAN</h4>
                        <p class="text-muted mb-0">Sistem Konversi RPL - STIK Siti Khadijah</p>
                    </div>

                    <!-- NAVIGASI TABS PILLS: Pilih Program Studi -->
                    <ul class="nav nav-pills nav-prodi mb-3 justify-content-center" id="prodiTab" role="tablist">
                        <?php foreach ($prodi_list as $idx => $prodi): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $idx === 0 ? 'active' : '' ?>" 
                                    id="prodi-tab-<?= $prodi['id'] ?>" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#prodi-content-<?= $prodi['id'] ?>" 
                                    type="button" role="tab">
                                <i class="bi bi-mortarboard-fill"></i> <?= htmlspecialchars($prodi['nama_prodi']) ?>
                            </button>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- TAB CONTENT: Detail per Prodi -->
                    <div class="tab-content" id="prodiTabContent">
                        <?php foreach ($prodi_list as $idx => $prodi): 
                            $id_prodi = $prodi['id'];
                            $semesters = $semester_list[$id_prodi] ?? [];
                            $default_sem = $semesters[0] ?? '';
                        ?>
                        <div class="tab-pane fade <?= $idx === 0 ? 'show active' : '' ?>" 
                             id="prodi-content-<?= $id_prodi ?>" role="tabpanel">

                            <!-- Header Prodi -->
                            <div class="prodi-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5><i class="bi bi-building"></i> <?= htmlspecialchars($prodi['nama_prodi']) ?></h5>
                                    <small class="text-muted"><?= htmlspecialchars($prodi['jenjang']) ?> - Kode Prodi: <?= htmlspecialchars($prodi['kode_prodi']) ?></small>
                                </div>
                                <span class="badge-periode"><i class="bi bi-calendar3"></i> Periode <?= htmlspecialchars($prodi['periode']) ?></span>
                            </div>

                            <?php if (empty($semesters)): ?>
                                <div class="alert alert-warning text-center">
                                    <i class="bi bi-exclamation-triangle"></i> Belum ada data kurikulum untuk periode ini.
                                </div>
                            <?php else: ?>

                            <!-- PAGINATION SEMESTER -->
                            <div class="semester-pagination" id="semester-pills-<?= $id_prodi ?>">
                                <?php foreach ($semesters as $s_idx => $sem): 
                                    $sem_label = $sem;
                                    if (is_numeric($sem)) {
                                        $sem_label = 'Semester ' . $sem;
                                    } elseif ($sem === 'PILIHAN') {
                                        $sem_label = 'MK Pilihan';
                                    }
                                ?>
                                <a class="page-link-semester <?= $s_idx === 0 ? 'active' : '' ?>" 
                                   href="javascript:void(0)" 
                                   onclick="showSemester(<?= $id_prodi ?>, '<?= htmlspecialchars($sem) ?>', this)">
                                    <?= htmlspecialchars($sem_label) ?>
                                </a>
                                <?php endforeach; ?>
                            </div>

                            <!-- KURIKULUM PER SEMESTER -->
                            <?php foreach ($semesters as $s_idx => $sem): 
                                $mk_list = $kurikulum_data[$id_prodi][$sem] ?? [];
                            ?>
                            <div class="semester-content <?= $s_idx === 0 ? 'active' : '' ?>" 
                                 id="semester-<?= $id_prodi ?>-<?= htmlspecialchars(str_replace([' ', '/', '&'], ['_', '_', '_'], $sem)) ?>">
                                <div class="table-responsive">
                                    <table class="table table-bordered kurikulum-table">
                                        <thead>
                                            <tr>
                                                <th style="width:15%">Kode MK</th>
                                                <th>Nama Mata Kuliah</th>
                                                <th style="width:10%" class="text-center">SKS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($mk_list as $mk): ?>
                                            <tr>
                                                <td><code class="text-danger fw-bold"><?= htmlspecialchars($mk['kode_mk']) ?></code></td>
                                                <td><?= htmlspecialchars($mk['nama_mk']) ?></td>
                                                <td class="text-center"><span class="badge bg-danger"><?= $mk['sks'] ?></span></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-danger">
                                                <td colspan="2" class="text-end fw-bold">Total SKS:</td>
                                                <td class="text-center fw-bold">
                                                    <?= array_sum(array_column($mk_list, 'sks')) ?>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <?php endif; ?>

                            <!-- BAHAN KAJIAN -->
                            <?php if (isset($bahan_kajian[$id_prodi])): 
                                $bahan = $bahan_kajian[$id_prodi];
                            ?>
                            <!-- <div class="bahan-kajian-box">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-pdf-fill me-2"></i>
                                    <div>
                                        <strong>Bahan Kajian:</strong><br>
                                        <a href="uploads/" target="_blank">
                                            
                                            <i class="bi bi-box-arrow-up-right" style="font-size:0.7rem;"></i>
                                        </a>
                                        <span class="text-muted" style="font-size:0.8rem;">(Periode )</span>
                                    </div>
                                </div>
                            </div> -->
                            <?php else: ?>
                            <div class="alert alert-light border mt-3 text-center text-muted">
                                <i class="bi bi-info-circle"></i> Belum ada bahan kajian untuk prodi ini.
                            </div>
                            <?php endif; ?>

                        </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>

            <!-- BAGIAN KANAN: Form Login -->
            <div class="col-lg-5 col-md-8 col-sm-10 col-12">
                <div class="login-box p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h3 class="logo-text"><i class="bi bi-mortarboard-fill"></i> RPL SYSTEM</h3>
                        <p class="text-muted">Rekognisi Pembelajaran Lampau</p>
                    </div>

                    <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="bi bi-person-fill"></i> Username</label>
                            <input type="text" name="username" class="form-control" required autofocus placeholder="Masukkan username">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="bi bi-lock-fill"></i> Password</label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" id="myPassword" required placeholder="Masukkan password">
                                <button class="btn btn-outline-danger" type="button" id="togglePassword">
                                    <i class="bi bi-eye-slash" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-login w-100 py-2 fw-bold">
                            <i class="bi bi-box-arrow-in-right"></i> MASUK
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <small class="text-muted">STIK Siti Khadijah &copy; 2026</small>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/login.js"></script>
</body>
</html>