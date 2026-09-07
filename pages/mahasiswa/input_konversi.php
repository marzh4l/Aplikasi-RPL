<?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    // global $conn; // Tambahkan ini untuk memastikan $conn bisa diakses

    $alert = ['show' => false, 'type' => 'success', 'title' => '', 'message' => ''];

    $id_mhs = $_SESSION['user_id'] ?? 0;
    $nama_mhs = $_SESSION['nama'] ?? '';
    $username_mhs = $_SESSION['username'] ?? '';

    $q_prodi = $conn->query("SELECT * FROM program_studi ORDER BY nama_prodi");
    $q_periode = $conn->query("SELECT DISTINCT periode_tahun FROM kurikulum ORDER BY periode_tahun DESC");

    // ===== PROSES SIMPAN =====
    if (isset($_POST['simpan'])) {
        // Debug: cek data yang diterima
        error_log("POST data received: " . print_r($_POST, true));
        
        // Validasi data wajib
        if (empty($_POST['nama_asal']) || empty($_POST['nama_tujuan']) || empty($_POST['prodi_tujuan']) || empty($_POST['periode_tahun'])) {
            $alert = [
                'show' => true, 
                'type' => 'warning', 
                'title' => 'Peringatan!', 
                'message' => 'Nama Asal, Nama Tujuan, Program Studi, dan Periode Tahun wajib diisi!'
            ];
        } elseif (empty($_POST['kode_mk_tujuan']) || !is_array($_POST['kode_mk_tujuan'])) {
            $alert = [
                'show' => true, 
                'type' => 'warning', 
                'title' => 'Peringatan!', 
                'message' => 'Pilih minimal 1 mata kuliah konversi!'
            ];
        } else {
            $conn->begin_transaction();
            try {
                // Simpan Header
                $stmt = $conn->prepare("INSERT INTO konversi_header 
                    (id_mahasiswa, periode_tahun, nama_asal, nim_asal, pt_asal, prodi_asal,
                    nama_tujuan, nim_tujuan, pt_tujuan, prodi_tujuan, status) 
                    VALUES (?,?,?,?,?,?,?,?,?,?,'menunggu')");
                
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $periode_tahun = $_POST['periode_tahun'];
                $nama_asal = $_POST['nama_asal'];
                $nim_asal = $_POST['nim_asal'];
                $pt_asal = $_POST['pt_asal'];
                $prodi_asal = $_POST['prodi_asal'];
                $nama_tujuan = $_POST['nama_tujuan'];
                $pt_tujuan = 'STIK SITI KHADIJAH';
                $prodi_tujuan = $_POST['prodi_tujuan'];

                $stmt->bind_param("isssssssss", 
                    $id_mhs, 
                    $periode_tahun,
                    $nama_asal, 
                    $nim_asal, 
                    $pt_asal, 
                    $prodi_asal,
                    $nama_tujuan, 
                    $username_mhs, 
                    $pt_tujuan, 
                    $prodi_tujuan
                );
                
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                
                $id_konversi = $conn->insert_id;
                error_log("Header saved with ID: " . $id_konversi);
                
                // Simpan Detail
                $sukses_count = 0;
                $jumlah_mk = count($_POST['kode_mk_asal'] ?? []);
                
                for ($i = 0; $i < $jumlah_mk; $i++) {
                    // Skip jika tidak ada kode MK tujuan atau indek asal kosong
                    if (empty($_POST['kode_mk_tujuan'][$i]) || empty($_POST['nilai_indek_asal'][$i])) {
                        continue;
                    }
                    
                    $stmt2 = $conn->prepare("INSERT INTO konversi_detail 
                        (id_konversi, kode_mk_asal, nama_mk_asal, sks_asal, nilai_indek_asal, nilai_huruf_asal,
                        kode_mk_tujuan, nama_mk_tujuan, sks_tujuan, nilai_indek_tujuan, nilai_huruf_tujuan) 
                        VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                    
                    if (!$stmt2) {
                        throw new Exception("Prepare detail failed: " . $conn->error);
                    }
                    
                    $kode_mk_asal = $_POST['kode_mk_asal'][$i] ?? '';
                    $nama_mk_asal = $_POST['nama_mk_asal'][$i] ?? '';
                    $sks_asal = intval($_POST['sks_asal'][$i] ?? 0);
                    $nilai_indek_asal = floatval($_POST['nilai_indek_asal'][$i] ?? 0);
                    $nilai_huruf_asal = $_POST['nilai_huruf_asal'][$i] ?? '';
                    $kode_mk_tujuan = $_POST['kode_mk_tujuan'][$i];
                    $nama_mk_tujuan = $_POST['nama_mk_tujuan'][$i] ?? '';
                    $sks_tujuan = intval($_POST['sks_tujuan'][$i] ?? 0);
                    $nilai_indek_tujuan = floatval($_POST['nilai_indek_tujuan'][$i] ?? 0);
                    $nilai_huruf_tujuan = $_POST['nilai_huruf_tujuan'][$i] ?? '';
                    
                    $stmt2->bind_param("issidsssids", 
                        $id_konversi, //i
                        $kode_mk_asal, //s
                        $nama_mk_asal, //s
                        $sks_asal, //i
                        $nilai_indek_asal, //d
                        $nilai_huruf_asal, //s
                        $kode_mk_tujuan, //s
                        $nama_mk_tujuan, //s
                        $sks_tujuan, //i
                        $nilai_indek_tujuan, //d
                        $nilai_huruf_tujuan //s
                    );
                    
                    if ($stmt2->execute()) {
                        $sukses_count++;
                    } else {
                        error_log("Detail insert failed: " . $stmt2->error);
                    }
                }
                
                $conn->commit();
                error_log("Transaction committed. $sukses_count details saved.");
                
                // Redirect ke Data Konversi dengan alert sukses
                $alert_data = [
                    'show' => true,
                    'type' => 'success',
                    'title' => 'Berhasil!',
                    'message' => "Konversi berhasil disimpan! ($sukses_count mata kuliah tersimpan)"
                ];
                
                $redirect_url = "?page=mahasiswa/data_konversi&alert=" . urlencode(json_encode($alert_data));
                error_log("Redirecting to: " . $redirect_url);
                
                header("Location: " . $redirect_url);
                exit;
                
            } catch (Exception $e) {
                $conn->rollback();
                error_log("Error: " . $e->getMessage());
                $alert = [
                    'show' => true, 
                    'type' => 'danger', 
                    'title' => 'Gagal!', 
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ];
            }
        }
    }

    // Cek alert dari redirect
    if (isset($_GET['alert'])) {
        $alert = json_decode(urldecode($_GET['alert']), true);
        $alert['show'] = true;
    }
?>

<!-- ALERT UTAMA -->
<?php if ($alert['show']): ?>
<div class="alert alert-<?= $alert['type'] ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
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

<h4 class="text-danger mb-4"><i class="bi bi-plus-circle"></i> Input Konversi RPL</h4>

<!-- FORM DENGAN ACTION EXPLICIT -->
<form method="POST" enctype="multipart/form-data" action="" id="formKonversi">
    <!-- Tambahkan ini di paling atas form -->
    <input type="hidden" name="simpan" value="1">
    <!-- ALERT STEP 1 -->
    <div id="alertStep1" class="custom-alert alert alert-warning d-none mb-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div id="messageStep1"></div>
        </div>
        <button type="button" class="btn-close btn-close-white" onclick="hideAlert('alertStep1')"></button>
    </div>

    <!-- ALERT STEP 1 -->
    <div id="alertStep1" class="custom-alert alert alert-warning d-none mb-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div id="messageStep1"></div>
        </div>
        <button type="button" class="btn-close btn-close-white" onclick="hideAlert('alertStep1')"></button>
    </div>

    <!-- STEP 1 -->
    <div id="step1">
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-building"></i> Asal Perguruan Tinggi
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_asal" id="namaAsal" class="form-control" value="<?= htmlspecialchars($nama_mhs) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">NIM Asal</label>
                        <input type="text" name="nim_asal" id="nimAsal" class="form-control" placeholder="Masukkan NIM asal">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Asal Perguruan Tinggi</label>
                        <input type="text" name="pt_asal" id="ptAsal" class="form-control" placeholder="Nama perguruan tinggi asal">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Asal Program Studi</label>
                        <input type="text" name="prodi_asal" id="prodiAsal" class="form-control" placeholder="Nama program studi asal">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-mortarboard-fill"></i> Tujuan Perguruan Tinggi
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_tujuan" id="namaTujuan" class="form-control" value="<?= htmlspecialchars($nama_mhs) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">NIM Tujuan</label>
                        <!-- <input type="text" name="nim_tujuan" class="form-control" value="" readonly> -->
                        <input type="text" name="nim_tujuan" class="form-control" " readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tujuan Perguruan Tinggi</label>
                        <input type="text" name="pt_tujuan" class="form-control" value="STIK SITI KHADIJAH" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tujuan Program Studi <span class="text-danger">*</span></label>
                        <select name="prodi_tujuan" id="selectProdi" class="form-select">
                            <option value="">-- Pilih Program Studi --</option>
                            <?php 
                            $q_prodi->data_seek(0);
                            while($p = $q_prodi->fetch_assoc()): 
                            ?>
                            <option value="<?= htmlspecialchars($p['nama_prodi']) ?>" data-id="<?= $p['id'] ?>">
                                <?= htmlspecialchars($p['nama_prodi']) ?> (<?= $p['jenjang'] ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Periode Tahun Kurikulum <span class="text-danger">*</span></label>
                        <select name="periode_tahun" id="selectPeriode" class="form-select">
                            <option value="">-- Pilih Periode --</option>
                            <?php 
                            $q_periode->data_seek(0);
                            while($per = $q_periode->fetch_assoc()): 
                            ?>
                            <option value="<?= htmlspecialchars($per['periode_tahun']) ?>">
                                <?= htmlspecialchars($per['periode_tahun']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- UPLOAD BERKAS -->
        <!-- <div class="card mb-4 border-warning">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-upload"></i> Upload Berkas Pendukung <span class="text-danger">*</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-file-earmark-pdf text-danger"></i> Berkas Silabus (PDF) <span class="text-danger"></span>
                        </label>
                        <input type="file" name="berkas_silabus" id="berkasSilabus" class="form-control" accept=".pdf" onchange="previewFile(this, 'previewSilabus')">
                        <div class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Format: PDF | Maks: 5 MB
                        </div>
                        <div id="previewSilabus" class="mt-2 d-none">
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> <span class="filename"></span></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-file-earmark-pdf text-danger"></i> Berkas Transkrip Nilai (PDF) <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="berkas_transkrip" id="berkasTranskrip" class="form-control" accept=".pdf" onchange="previewFile(this, 'previewTranskrip')">
                        <div class="form-text text-muted">
                            <i class="bi bi-info-circle"></i> Format: PDF | Maks: 5 MB
                        </div>
                        <div id="previewTranskrip" class="mt-2 d-none">
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> <span class="filename"></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <div class="text-end">
            <!-- TOMBOL LANJUT - PASTIKAN ID BENAR -->
            <button type="button" class="btn btn-theme btn-lg" id="btnLanjutStep1">
                Lanjut <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>

    <!-- ALERT STEP 2 -->
    <div id="alertStep2" class="custom-alert alert alert-warning d-none mb-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div id="messageStep2"></div>
        </div>
        <button type="button" class="btn-close btn-close-white" onclick="hideAlert('alertStep2')"></button>
    </div>

    <!-- STEP 2 -->
    <div id="step2" style="display:none;">
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-check2-square"></i> Pilih Mata Kuliah Konversi</span>
                <span class="badge bg-light text-danger" id="infoProdiPeriode"></span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-danger text-white">
                            <tr>
                                <th width="50" class="text-center"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                                <th>Kode MK</th>
                                <th>Mata Kuliah</th>
                                <th>Semester</th>
                                <th class="text-center">SKS</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyKurikulum">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-arrow-left-circle fs-1 d-block mb-2 opacity-50"></i>
                                    Kembali ke step 1 dan pilih Prodi serta Periode
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" onclick="nextStep(1)">
                <i class="bi bi-arrow-left"></i> Kembali
            </button>
            <button type="button" class="btn btn-theme" id="btnLanjutStep2">
                Lanjut <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>

    <!-- ALERT STEP 3 -->
    <div id="alertStep3" class="custom-alert alert alert-warning d-none mb-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div id="messageStep3"></div>
        </div>
        <button type="button" class="btn-close btn-close-white" onclick="hideAlert('alertStep3')"></button>
    </div>

    <!-- STEP 3 -->
    <div id="step3" style="display:none;">
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-input-cursor-text"></i> Input Nilai Konversi
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" id="tabelKonversi">
                        <thead>
                            <tr class="text-center">
                                <th colspan="5" class="bg-primary text-white fs-6">
                                    <i class="bi bi-building"></i> Nilai Perguruan Tinggi Asal
                                </th>
                                <th colspan="5" class="bg-danger text-white fs-6">
                                    <i class="bi bi-mortarboard-fill"></i> Konversi Nilai PT Baru (Diakui)
                                </th>
                            </tr>
                            <tr class="table-danger text-white text-center">
                                <th width="10%">Kode MK</th>
                                <th width="16%">Nama MK</th>
                                <th width="8%">Bobot MK<br><small class="fw-normal">(SKS)</small></th>
                                <th width="8%">Angka<br><small class="fw-normal">(Indeks)</small></th>
                                <th width="8%">Nilai Huruf</th>
                                <th width="10%">Kode MK</th>
                                <th width="16%">Nama MK</th>
                                <th width="8%">Bobot MK<br><small class="fw-normal">(SKS)</small></th>
                                <th width="8%">Angka<br><small class="fw-normal">(Indeks)</small></th>
                                <th width="8%">Nilai Huruf</th>
                            </tr>
                        </thead>
                        <tbody id="nilaiBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" onclick="nextStep(2)">
                <i class="bi bi-arrow-left"></i> Kembali
            </button>
            <!-- BUTTON INI MEMBUKA MODAL, BUKAN SUBMIT LANGSUNG -->
            <button type="button" class="btn btn-success btn-lg" id="btnSimpanKonversi">
                <i class="bi bi-save"></i> Simpan Konversi
            </button>
        </div>
    </div>

</form>

<!-- ========================================== -->
<!-- MODAL KONFIRMASI SIMPAN (BOOTSTRAP)        -->
<!-- ========================================== -->
<div class="modal fade" id="modalKonfirmasiSimpan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-question-circle-fill"></i> Konfirmasi Simpan</h5>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-save fs-1 text-success"></i>
                </div>
                <h5 id="pesanKonfirmasi">Yakin ingin menyimpan konversi?</h5>
                <p class="text-muted mb-0">Data yang sudah disimpan tidak dapat diubah.</p>
            </div>
            <div class="modal-footer justify-content-center bg-light">
                <button type="button" class="btn btn-secondary btn-lg px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i> Batal
                </button>
                <!-- BUTTON INI YANG AKAN SUBMIT FORM -->
                <button type="button" class="btn btn-success btn-lg px-4" id="btnConfirmSimpan">
                    <i class="bi bi-check-lg"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ==========================================
// GLOBAL VARIABLES
// ==========================================
let kurikulumData = [];
let modalKonfirmasi = null;

// ==========================================
// INIT SAAT DOM READY
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM Loaded - Initializing Input Konversi ===');
    
    // Inisialisasi modal Bootstrap
    var modalEl = document.getElementById('modalKonfirmasiSimpan');
    if (modalEl) {
        modalKonfirmasi = new bootstrap.Modal(modalEl);
        console.log('Modal initialized');
    } else {
        console.error('Modal element not found!');
    }
    
    // Attach event listeners
    var btn1 = document.getElementById('btnLanjutStep1');
    var btn2 = document.getElementById('btnLanjutStep2');
    var btnSimpan = document.getElementById('btnSimpanKonversi');
    var btnConfirm = document.getElementById('btnConfirmSimpan');
    var checkAll = document.getElementById('checkAll');
    
    if (btn1) {
        btn1.addEventListener('click', function(e) {
            e.preventDefault();
            validasiStep1();
        });
    }
    
    if (btn2) {
        btn2.addEventListener('click', function(e) {
            e.preventDefault();
            validasiStep2();
        });
    }
    
    // Tombol Simpan (buka modal)
    if (btnSimpan) {
        btnSimpan.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Simpan button clicked - opening modal');
            tampilkanModalKonfirmasi();
        });
    }
    
    // Tombol Confirm di Modal (submit form)
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Confirm button clicked - submitting form');
            
            // Tutup modal
            if (modalKonfirmasi) {
                modalKonfirmasi.hide();
            }
            
            // Submit form
            var form = document.getElementById('formKonversi');
            if (form) {
                // console.log('Submitting form...');
                // form.submit();
                // TAMBAHKAN BARIS INI:
                // Membuat input bayangan agar isset($_POST['simpan']) di PHP jadi TRUE
                let inputSimpan = document.createElement('input');
                inputSimpan.type = 'hidden';
                inputSimpan.name = 'simpan'; 
                inputSimpan.value = '1';
                form.appendChild(inputSimpan);

                console.log('Submitting form with hidden simpan field...');
                form.submit();
            } else {
                console.error('Form not found!');
            }
        });
    }
    
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            document.querySelectorAll('.check-mk').forEach(cb => cb.checked = this.checked);
        });
    }
});

// ==========================================
// MODAL KONFIRMASI
// ==========================================
function tampilkanModalKonfirmasi() {
    console.log('=== tampilkanModalKonfirmasi() called ===');
    
    // Validasi form terlebih dahulu
    var indekInputs = document.querySelectorAll('.nilai-indek-asal');
    var hurufInputs = document.querySelectorAll('input[name="nilai_huruf_asal[]"]');
    var kodeInputs = document.querySelectorAll('input[name="kode_mk_asal[]"]');
    var namaInputs = document.querySelectorAll('input[name="nama_mk_asal[]"]');
    
    var errors = [];
    var emptyIndek = 0;
    var emptyHuruf = 0;
    var emptyKode = 0;
    var emptyNama = 0;
    var emptySks = 0;
    
    for (var i = 0; i < indekInputs.length; i++) {
        if (!indekInputs[i].value || parseFloat(indekInputs[i].value) === 0) emptyIndek++;
        if (!hurufInputs[i] || !hurufInputs[i].value.trim()) emptyHuruf++;
    }
    
    for (var j = 0; j < kodeInputs.length; j++) {
        if (!kodeInputs[j].value.trim()) emptyKode++;
        if (!namaInputs[j] || !namaInputs[j].value.trim()) emptyNama++;
        var sksInput = document.querySelectorAll('input[name="sks_asal[]"]')[j];
        if (!sksInput || !sksInput.value) emptySks++;
    }
    
    if (emptyKode > 0) errors.push(emptyKode + ' baris Kode MK Asal belum diisi');
    if (emptyNama > 0) errors.push(emptyNama + ' baris Nama MK Asal belum diisi');
    if (emptySks > 0) errors.push(emptySks + ' baris SKS Asal belum diisi');
    if (emptyIndek > 0) errors.push(emptyIndek + ' baris nilai indek asal belum diisi');
    if (emptyHuruf > 0) errors.push(emptyHuruf + ' baris nilai huruf asal belum diisi');
    
    if (errors.length > 0) {
        console.log('Validation errors:', errors);
        showAlert('alertStep3', errors.join('<br>'), 'danger');
        return;
    }
    
    hideAlert('alertStep3');
    
    // Hitung jumlah MK
    var totalMK = document.querySelectorAll('input[name="kode_mk_tujuan[]"]').length;
    console.log('Total MK:', totalMK);
    
    // Update pesan modal
    var pesanEl = document.getElementById('pesanKonfirmasi');
    if (pesanEl) {
        pesanEl.innerHTML = 'Yakin ingin menyimpan konversi dengan <strong class="text-danger">' + totalMK + ' mata kuliah</strong>?';
    }
    
    // Tampilkan modal
    if (modalKonfirmasi) {
        console.log('Showing modal...');
        modalKonfirmasi.show();
    } else {
        console.error('Modal not initialized!');
        // Fallback
        if (confirm('Yakin ingin menyimpan konversi dengan ' + totalMK + ' mata kuliah?')) {
            document.getElementById('formKonversi').submit();
        }
    }
}

// ==========================================
// NAVIGASI STEP
// ==========================================
function nextStep(n) {
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'none';
    document.getElementById('step'+n).style.display = 'block';
    window.scrollTo(0,0);
}

// ==========================================
// ALERT FUNCTIONS
// ==========================================
function showAlert(elementId, message, type) {
    type = type || 'warning';
    
    var alertEl = document.getElementById(elementId);
    var msgEl = document.getElementById(elementId.replace('alert', 'message'));
    
    if (!alertEl || !msgEl) {
        console.error('Alert element not found:', elementId);
        window.alert(message.replace(/<br>/g, '\n'));
        return;
    }
    
    alertEl.classList.remove('d-none');
    alertEl.className = 'custom-alert alert alert-' + type + ' mb-3';
    
    var titleText = (type === 'danger') ? 'Error!' : (type === 'success' ? 'Berhasil!' : 'Perhatian!');
    msgEl.innerHTML = '<strong>' + titleText + '</strong><br>' + message;
    
    alertEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideAlert(elementId) {
    var el = document.getElementById(elementId);
    if (el) el.classList.add('d-none');
}

// ==========================================
// VALIDASI STEP 1
// ==========================================
function validasiStep1() {
    var namaAsal = document.getElementById('namaAsal');
    var nimAsal = document.getElementById('nimAsal');
    var ptAsal = document.getElementById('ptAsal');
    var prodiAsal = document.getElementById('prodiAsal');
    var namaTujuan = document.getElementById('namaTujuan');
    var prodiSelect = document.getElementById('selectProdi');
    var periodeSelect = document.getElementById('selectPeriode');
    
    if (!namaAsal || !prodiSelect || !periodeSelect) {
        console.error('Required elements not found');
        return false;
    }
    
    var namaAsalVal = namaAsal.value.trim();
    var nimAsalVal = nimAsal ? nimAsal.value.trim() : '';
    var ptAsalVal = ptAsal ? ptAsal.value.trim() : '';
    var prodiAsalVal = prodiAsal ? prodiAsal.value.trim() : '';
    var namaTujuanVal = namaTujuan ? namaTujuan.value.trim() : '';
    var prodi = prodiSelect.value;
    var periode = periodeSelect.value;
    
    var errors = [];
    
    if (!namaAsalVal) errors.push('Nama Lengkap Asal wajib diisi');
    if (!namaTujuanVal) errors.push('Nama Lengkap Tujuan wajib diisi');
    if (!prodi) errors.push('Tujuan Program Studi wajib dipilih');
    if (!periode) errors.push('Periode Tahun Kurikulum wajib dipilih');
    if (!nimAsalVal) errors.push('NIM Asal belum diisi');
    if (!ptAsalVal) errors.push('Asal Perguruan Tinggi belum diisi');
    if (!prodiAsalVal) errors.push('Asal Program Studi belum diisi');
    
    if (errors.length > 0) {
        showAlert('alertStep1', errors.join('<br>'), 'warning');
        return false;
    }
    
    hideAlert('alertStep1');
    
    var selectedOption = prodiSelect.options[prodiSelect.selectedIndex];
    var idProdi = selectedOption ? selectedOption.getAttribute('data-id') : null;
    
    if (!idProdi) {
        showAlert('alertStep1', 'ID Program Studi tidak valid', 'danger');
        return false;
    }
    
    document.getElementById('infoProdiPeriode').textContent = prodi + ' | ' + periode;
    
    fetch('ajax/get_kurikulum.php?id_prodi=' + encodeURIComponent(idProdi) + '&periode=' + encodeURIComponent(periode))
        .then(function(response) {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(function(result) {
            if (result.status === 'success') {
                kurikulumData = result.data || [];
                renderTabelKurikulum();
                nextStep(2);
            } else {
                showAlert('alertStep1', 'Gagal mengambil data kurikulum: ' + (result.message || 'Unknown error'), 'danger');
            }
        })
        .catch(function(err) {
            showAlert('alertStep1', 'Terjadi kesalahan koneksi: ' + err.message, 'danger');
        });
    
    return true;
}

// ==========================================
// VALIDASI STEP 2
// ==========================================
function validasiStep2() {
    var checkboxes = document.querySelectorAll('.check-mk:checked');
    
    if (checkboxes.length === 0) {
        showAlert('alertStep2', 'Pilih minimal 1 mata kuliah untuk dikonversi!', 'warning');
        return false;
    }
    
    hideAlert('alertStep2');
    generateStep3();
    return true;
}

// ==========================================
// KONVERSI NILAI
// ==========================================
function konversiNilai(indek) {
    indek = parseFloat(indek);
    if (isNaN(indek)) return { indek: '', huruf: '' };
    
    var hasilIndek, hasilHuruf;
    
    if (indek == 4) { hasilIndek = 4; hasilHuruf = 'A+'; }
    else if (indek >= 3.75) { hasilIndek = 3.75; hasilHuruf = 'A'; }
    else if (indek >= 3.5) { hasilIndek = 3.5; hasilHuruf = 'B+'; }
    else if (indek >= 3) { hasilIndek = 3; hasilHuruf = 'B'; }
    else if (indek >= 2.5) { hasilIndek = 2.5; hasilHuruf = 'C+'; }
    else if (indek >= 2) { hasilIndek = 2; hasilHuruf = 'C'; }
    else if (indek >= 1) { hasilIndek = 1; hasilHuruf = 'D'; }
    else { hasilIndek = 0; hasilHuruf = 'D'; }
    
    return { indek: hasilIndek, huruf: hasilHuruf };
}

// ==========================================
// RENDER TABEL KURIKULUM
// ==========================================
function renderTabelKurikulum() {
    var tbody = document.getElementById('tbodyKurikulum');
    if (!tbody) return;
    
    if (kurikulumData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>Belum ada kurikulum untuk prodi dan periode yang dipilih</td></tr>';
        return;
    }
    
    var html = '';
    for (var i = 0; i < kurikulumData.length; i++) {
        var row = kurikulumData[i];
        html += '<tr>' +
            '<td class="text-center"><input type="checkbox" class="check-mk form-check-input" value="' + row.id + '" data-kode="' + escapeHtml(row.kode_mk) + '" data-nama="' + escapeHtml(row.nama_mk) + '" data-sks="' + row.sks + '"></td>' +
            '<td class="fw-bold">' + escapeHtml(row.kode_mk) + '</td>' +
            '<td>' + escapeHtml(row.nama_mk) + '</td>' +
            '<td class="text-center"><span class="badge bg-warning text-dark">' + escapeHtml(row.semester) + ' SKS</span></td>' +
            '<td class="text-center"><span class="badge bg-info">' + row.sks + ' SKS</span></td>' +
            '</tr>';
    }
    
    tbody.innerHTML = html;
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ==========================================
// GENERATE STEP 3
// ==========================================
function generateStep3() {
    var checkboxes = document.querySelectorAll('.check-mk:checked');
    var html = '';
    
    for (var i = 0; i < checkboxes.length; i++) {
        var cb = checkboxes[i];
        var kodeTujuan = cb.dataset.kode;
        var namaTujuan = cb.dataset.nama;
        var sksTujuan = cb.dataset.sks;
        
        html += '<tr>' +
            '<td><input type="text" name="kode_mk_asal[]" class="form-control form-control-sm" placeholder="Kode MK"></td>' +
            '<td><input type="text" name="nama_mk_asal[]" class="form-control form-control-sm" placeholder="Nama MK"></td>' +
            '<td><input type="number" name="sks_asal[]" class="form-control form-control-sm text-center" min="1" max="6" placeholder="SKS"></td>' +
            '<td><input type="number" step="0.01" name="nilai_indek_asal[]" class="form-control form-control-sm text-center nilai-indek-asal" placeholder="0.00" min="0" max="4" oninput="autoKonversi(this, ' + i + ')"></td>' +
            '<td><input type="text" name="nilai_huruf_asal[]" class="form-control form-control-sm text-center" placeholder="A-E"></td>' +
            '<td class="bg-light"><input type="hidden" name="kode_mk_tujuan[]" value="' + kodeTujuan + '"><span class="fw-bold text-danger">' + kodeTujuan + '</span></td>' +
            '<td class="bg-light"><input type="hidden" name="nama_mk_tujuan[]" value="' + namaTujuan + '">' + namaTujuan + '</td>' +
            '<td class="bg-light text-center"><input type="hidden" name="sks_tujuan[]" value="' + sksTujuan + '"><span class="badge bg-info">' + sksTujuan + ' SKS</span></td>' +
            '<td class="bg-light"><input type="number" step="0.01" name="nilai_indek_tujuan[]" class="form-control form-control-sm text-center nilai-indek-tujuan-' + i + '" placeholder="0.00" readonly></td>' +
            '<td class="bg-light"><input type="text" name="nilai_huruf_tujuan[]" class="form-control form-control-sm text-center nilai-huruf-tujuan-' + i + '" placeholder="A-E" readonly></td>' +
            '</tr>';
    }
    
    document.getElementById('nilaiBody').innerHTML = html;
    nextStep(3);
}

// ==========================================
// AUTO KONVERSI
// ==========================================
function autoKonversi(input, index) {
    var indekAsal = parseFloat(input.value);
    var row = input.closest('tr');
    
    if (isNaN(indekAsal)) {
        var indekTujuan = row.querySelector('.nilai-indek-tujuan-' + index);
        var hurufTujuan = row.querySelector('.nilai-huruf-tujuan-' + index);
        if (indekTujuan) indekTujuan.value = '';
        if (hurufTujuan) hurufTujuan.value = '';
        return;
    }
    
    var konversi = konversiNilai(indekAsal);
    
    var indekTujuan = row.querySelector('.nilai-indek-tujuan-' + index);
    var hurufTujuan = row.querySelector('.nilai-huruf-tujuan-' + index);
    
    if (indekTujuan) indekTujuan.value = konversi.indek;
    if (hurufTujuan) hurufTujuan.value = konversi.huruf;
}
</script>

<style>
.custom-alert {
    position: relative;
    padding: 1rem 3rem 1rem 1rem;
    border-radius: 0.5rem;
    animation: slideIn 0.3s ease-out;
}

.custom-alert.alert-warning {
    background-color: #fff3cd;
    border: 2px solid #ffc107;
    color: #856404;
}

.custom-alert.alert-danger {
    background-color: #f8d7da;
    border: 2px solid #dc3545;
    color: #721c24;
}

.custom-alert.alert-success {
    background-color: #d1e7dd;
    border: 2px solid #198754;
    color: #0f5132;
}

.custom-alert .btn-close {
    position: absolute;
    top: 50%;
    right: 1rem;
    transform: translateY(-50%);
    opacity: 0.7;
}

.custom-alert .btn-close:hover {
    opacity: 1;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

#tabelKonversi th, #tabelKonversi td {
    vertical-align: middle;
    font-size: 0.85rem;
}

#tabelKonversi thead tr:first-child th {
    font-size: 1rem;
    padding: 12px;
}

#tabelKonversi .bg-light {
    background-color: #fff5f5 !important;
}

.nilai-indek-asal:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

@media (max-width: 768px) {
    #tabelKonversi { font-size: 0.75rem; }
    #tabelKonversi .form-control-sm { font-size: 0.75rem; padding: 2px 4px; min-width: 60px; }
    .col-6 { width: 100% !important; }
}
</style>