<?php 
    require 'config/database.php';
    include 'includes/header.php';
    include 'includes/sidebar.php'; 
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-danger-subtle">
    
    <?php
    // Router untuk konten dinamis
    $page = $_GET['page'] ?? '';
    
    if ($page == ''):
        // ===== DASHBOARD DEFAULT =====
    ?>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
            <h1 class="h2 text-danger"><i class="bi bi-speedometer2"></i> Dashboard</h1>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card border-danger h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted mb-1">Selamat Datang</h6>
                                <h4 class="text-danger mb-0"><?= $_SESSION['nama']; ?></h4>
                                <small class="text-muted"><?= ucfirst($_SESSION['role']); ?></small>
                            </div>
                            <div class="fs-1 text-danger opacity-25"><i class="bi bi-person-circle"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if($_SESSION['role'] == 'admin'): ?>
            <div class="col-md-4 mb-4">
                <div class="card border-danger h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted mb-1">Total User</h6>
                                <?php
                                $q = $conn->query("SELECT COUNT(*) as total FROM users");
                                $d = $q->fetch_assoc();
                                ?>
                                <h4 class="text-danger mb-0"><?= $d['total']; ?></h4>
                            </div>
                            <div class="fs-1 text-danger opacity-25"><i class="bi bi-people"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card border-danger h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted mb-1">Total Konversi</h6>
                                <?php
                                $q = $conn->query("SELECT COUNT(*) as total FROM konversi_header");
                                $d = $q->fetch_assoc();
                                ?>
                                <h4 class="text-danger mb-0"><?= $d['total']; ?></h4>
                            </div>
                            <div class="fs-1 text-danger opacity-25"><i class="bi bi-file-earmark-text"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    
    <?php 
    else:
        // ===== LOAD HALAMAN DINAMIS =====
        $file = "pages/$page.php";
        if (file_exists($file)) {
            include $file;
        } else {
            echo '<div class="alert alert-danger">Halaman tidak ditemukan!</div>';
        }
    endif;
    ?>
    
</main>

<?php include 'includes/footer.php'; ?>