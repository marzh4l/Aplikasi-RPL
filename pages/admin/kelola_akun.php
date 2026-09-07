<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Variabel alert
$alert = [
    'show' => false,
    'type' => 'success',
    'title' => '',
    'message' => ''
];

$role = $_GET['role'] ?? 'mahasiswa';
$title = $role == 'rpl' ? 'Petugas RPL' : 'Mahasiswa';

// ===== PROSES IMPORT EXCEL =====
if (isset($_POST['import_excel']) && isset($_FILES['file_excel'])) {
    require 'vendor/autoload.php'; // PhpSpreadsheet
    
    $file = $_FILES['file_excel']['tmp_name'];
    $role = $_POST['role_import'] ?? '';
   
    try {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        
        $sukses = 0;
        $gagal = 0;
        
        // Skip baris header (baris 0)
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $username = trim($row[0] ?? '');
            $nama = trim($row[1] ?? '');
            $hash_pass = password_hash($username, PASSWORD_DEFAULT);
            
            if (empty($username) || empty($nama)) continue;
            
            $stmt = $conn->prepare("INSERT INTO users (username, password, role, nama_lengkap) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE username=VALUES(username), nama_lengkap=VALUES(nama_lengkap)");
            $stmt->bind_param("ssss", $username, $hash_pass, $role, $nama);
            
            if ($stmt->execute()) $sukses++;
            else $gagal++;
        }
        
        $alert = ['show' => true, 'type' => 'success', 'title' => 'Import Selesai!', 'message' => "Berhasil: $sukses, Gagal: $gagal"];
        
    } catch (Exception $e) {
        $alert = ['show' => true, 'type' => 'danger', 'title' => 'Gagal Import!', 'message' => $e->getMessage()];
    }
}

// ===== PROSES SIMPAN (TAMBAH) =====
if (isset($_POST['simpan'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $nama = trim($_POST['nama_lengkap']);
    
    if (empty($username) || empty($password) || empty($nama)) {
        $alert = [
            'show' => true,
            'type' => 'warning',
            'title' => 'Peringatan!',
            'message' => 'Semua field wajib diisi!'
        ];
    } else {
        $cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $cek->bind_param("s", $username);
        $cek->execute();
        $hasil = $cek->get_result();
        
        if ($hasil->num_rows > 0) {
            $alert = [
                'show' => true,
                'type' => 'danger',
                'title' => 'Gagal!',
                'message' => 'Username <strong>' . htmlspecialchars($username) . '</strong> sudah digunakan!'
            ];
        } else {
            $hash_pass = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role, nama_lengkap) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $username, $hash_pass, $role, $nama);
            
            if ($stmt->execute()) {
                $alert = [
                    'show' => true,
                    'type' => 'success',
                    'title' => 'Berhasil!',
                    'message' => 'Data <strong>' . htmlspecialchars($nama) . '</strong> berhasil disimpan!'
                ];
            } else {
                $alert = [
                    'show' => true,
                    'type' => 'danger',
                    'title' => 'Gagal!',
                    'message' => 'Terjadi kesalahan: ' . $stmt->error
                ];
            }
        }
    }
}

// ===== PROSES UPDATE (EDIT) =====
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $username = trim($_POST['username']);
    $nama = trim($_POST['nama_lengkap']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($nama)) {
        $alert = [
            'show' => true,
            'type' => 'warning',
            'title' => 'Peringatan!',
            'message' => 'Username dan Nama Lengkap wajib diisi!'
        ];
    } else {
        // Cek username duplikat (kecuali untuk user yang sedang diedit)
        $cek = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $cek->bind_param("si", $username, $id);
        $cek->execute();
        $hasil = $cek->get_result();
        
        if ($hasil->num_rows > 0) {
            $alert = [
                'show' => true,
                'type' => 'danger',
                'title' => 'Gagal!',
                'message' => 'Username <strong>' . htmlspecialchars($username) . '</strong> sudah digunakan oleh user lain!'
            ];
        } else {
            // Jika password diisi, update juga password
            if (!empty($password)) {
                $hash_pass = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, nama_lengkap = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username, $hash_pass, $nama, $id);
            } else {
                // Jika password kosong, jangan update password
                $stmt = $conn->prepare("UPDATE users SET username = ?, nama_lengkap = ? WHERE id = ?");
                $stmt->bind_param("ssi", $username, $nama, $id);
            }
            
            if ($stmt->execute()) {
                $alert = [
                    'show' => true,
                    'type' => 'success',
                    'title' => 'Berhasil!',
                    'message' => 'Data <strong>' . htmlspecialchars($nama) . '</strong> berhasil diperbarui!'
                ];
            } else {
                $alert = [
                    'show' => true,
                    'type' => 'danger',
                    'title' => 'Gagal!',
                    'message' => 'Terjadi kesalahan: ' . $stmt->error
                ];
            }
        }
    }
}

// ===== PROSES HAPUS =====
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    $cek = $conn->query("SELECT nama_lengkap FROM users WHERE id = $id");
    if ($cek->num_rows > 0) {
        $data_hapus = $cek->fetch_assoc();
        $conn->query("DELETE FROM users WHERE id = $id");
        
        $alert = [
            'show' => true,
            'type' => 'success',
            'title' => 'Terhapus!',
            'message' => 'Data <strong>' . htmlspecialchars($data_hapus['nama_lengkap']) . '</strong> berhasil dihapus!'
        ];
    } else {
        $alert = [
            'show' => true,
            'type' => 'danger',
            'title' => 'Gagal!',
            'message' => 'Data tidak ditemukan atau sudah dihapus!'
        ];
    }
    
    // header("Location: ?page=admin/kelola_akun&role=$role&alert=" . urlencode(json_encode($alert)));
    // exit;
}

// Cek alert dari redirect
if (isset($_GET['alert'])) {
    $alert = json_decode(urldecode($_GET['alert']), true);
    $alert['show'] = true;
}

// ===== AMBIL DATA UNTUK EDIT =====
$data_edit = null;
if (isset($_GET['edit'])) {
    $id_edit = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    $data_edit = $result->fetch_assoc();
}
?>

<!-- ===== ALERT DINAMIS ===== -->
<?php if ($alert['show']): ?>
<div class="alert alert-<?= $alert['type'] ?> alert-dismissible fade show shadow-sm" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-<?= 
            $alert['type'] == 'success' ? 'check-circle-fill' : 
            ($alert['type'] == 'danger' ? 'x-circle-fill' : 
            ($alert['type'] == 'warning' ? 'exclamation-triangle-fill' : 'info-circle-fill')) 
        ?> me-2 fs-5"></i>
        <div>
            <strong><?= $alert['title'] ?></strong><br>
            <?= $alert['message'] ?>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- ===== HEADER HALAMAN ===== -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
    <h4 class="text-danger m-0">
        <i class="bi bi-people"></i> Kelola Akun <?= $title; ?>
    </h4>
    <div>    
        <!-- Tombol Import Excel (Butuh Composer) -->
        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalImportAkun">
            <i class="bi bi-file-earmark-excel"></i> Import Akun
        </button>
        <button class="btn btn-theme mt-2 mt-sm-0" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-lg"></i> Tambah <?= $title; ?>
        </button>
    </div>
</div>

<!-- ===== TABEL DATA ===== -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom border-danger">
        <h6 class="mb-0 text-danger"><i class="bi bi-list-ul"></i> Daftar <?= $title; ?></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle" id="tabelAkun">
                <thead class="table-danger">
                    <tr>
                        <th class="text-center" width="50">No</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th class="text-center">Role</th>
                        <th class="text-center" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $q = $conn->prepare("SELECT * FROM users WHERE role = ? ORDER BY id DESC");
                    $q->bind_param("s", $role);
                    $q->execute();
                    $result = $q->get_result();
                    
                    if ($result->num_rows == 0):
                    ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                            Belum ada data <?= strtolower($title); ?>
                        </td>
                    </tr>
                    <?php 
                    else:
                        while($d = $result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($d['username']); ?></td>
                        <td><?= htmlspecialchars($d['nama_lengkap']); ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $d['role'] == 'admin' ? 'dark' : ($d['role'] == 'rpl' ? 'warning text-dark' : 'info') ?>">
                                <?= strtoupper($d['role']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="?page=admin/kelola_akun&role=<?= $role; ?>&edit=<?= $d['id']; ?>" 
                               class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a href="?page=admin/kelola_akun&role=<?= $role; ?>&hapus=<?= $d['id']; ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Yakin ingin menghapus <?= htmlspecialchars($d['nama_lengkap']); ?>?\n\nData yang dihapus tidak dapat dikembalikan!')"
                               title="Hapus">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    endif; 
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- MODAL IMPORT EXCEL -->
<div class="modal fade" id="modalImportAkun" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-file-earmark-excel"></i> Import Akun</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">File Excel <span class="text-danger">*</span></label>
                        <input type="hidden" name="role_import" class="form-control" placeholder="2024/2025" value="<?= $title; ?>">
                        <input type="file" name="file_excel" class="form-control" accept=".xlsx, .xls" required>
                        <small class="text-muted">Format kolom: A=username, B=nama_lengkap</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="import_excel" class="btn btn-success"><i class="bi bi-upload"></i> Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL TAMBAH ===== -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Tambah <?= $title; ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autocomplete="off">
                        </div>
                        <small class="text-muted">Username digunakan untuk login</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                        </div>
                    </div>
                    
                    <div class="mb-1">
                        <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                            <input type="text" name="nama_lengkap" class="form-control" placeholder="Masukkan nama lengkap" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Batal
                    </button>
                    <button type="submit" name="simpan" class="btn btn-danger">
                        <i class="bi bi-check-lg"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL EDIT ===== -->
<?php if ($data_edit): ?>
<div class="modal fade show" id="modalEdit" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="display: block;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit <?= $title; ?></h5>
                <a href="?page=admin/kelola_akun&role=<?= $role; ?>" class="btn-close btn-close-white" aria-label="Tutup"></a>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="id" value="<?= $data_edit['id']; ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($data_edit['username']); ?>" required autocomplete="off">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" id="myPassword" placeholder="Kosongkan jika tidak ingin mengubah password">
                            <button class="btn btn-outline-danger" type="button" id="togglePassword"><i class="bi bi-eye-slash" id="eyeIcon"></i></button>
                        </div>
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                    </div>
                    
                    <div class="mb-1">
                        <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                            <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($data_edit['nama_lengkap']); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <a href="?page=admin/kelola_akun&role=<?= $role; ?>" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i> Batal
                    </a>
                    <button type="submit" name="update" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Backdrop manual untuk modal edit -->
<div class="modal-backdrop fade show"></div>
<?php endif; ?>
<script>
    // icon mata password
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#myPassword');
    const eyeIcon = document.querySelector('#eyeIcon')
    togglePassword.addEventListener('click', function () {
        // Alihkan tipe input antara password dan text
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // Alihkan ikon antara mata terbuka dan mata dicoret
        if (type === 'text') {
            eyeIcon.classList.remove('bi-eye-slash');
            eyeIcon.classList.add('bi-eye');
        } else {
            eyeIcon.classList.remove('bi-eye');
            eyeIcon.classList.add('bi-eye-slash');
        }
    });
</script>