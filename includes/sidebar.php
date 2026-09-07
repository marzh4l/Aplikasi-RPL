<!-- Navbar Mobile -->
<nav class="navbar navbar-dark bg-danger d-md-none">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h5"><i class="bi bi-mortarboard-fill"></i> SI-RPL</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<!-- Sidebar -->
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="text-center text-white mb-4 px-3 d-none d-md-block">
            <h5><i class="bi bi-mortarboard-fill"></i> SI-RPL</h5>
            <span class="badge bg-light text-danger"><?= strtoupper($_SESSION['role']); ?></span>
            <div class="mt-2 small"><?= $_SESSION['nama']; ?></div>
        </div>
        
        <ul class="nav flex-column" id="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link text-white <?= !isset($_GET['page']) ? 'active' : '' ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            
            <?php if ($_SESSION['role'] == 'admin'): ?>
            <!-- Menu Kelola Akun dengan Submenu -->
            <li class="nav-item">
                <a class="nav-link text-white d-flex justify-content-between align-items-center" 
                   data-bs-toggle="collapse" href="#submenuAkun" role="button" 
                   aria-expanded="false" aria-controls="submenuAkun">
                    <span><i class="bi bi-people me-2"></i> Kelola Akun</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse ps-3" id="submenuAkun" data-bs-parent="#sidebar-nav">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white-50 py-1" href="?page=admin/kelola_akun&role=rpl">
                                <i class="bi bi-circle-fill me-2" style="font-size:6px"></i> Petugas RPL
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white-50 py-1" href="?page=admin/kelola_akun&role=mahasiswa">
                                <i class="bi bi-circle-fill me-2" style="font-size:6px"></i> Mahasiswa
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white <?= ($_GET['page'] ?? '') == 'admin/kelola_prodi' ? 'active' : '' ?>" 
                   href="?page=admin/kelola_prodi">
                    <i class="bi bi-building me-2"></i> Program Studi
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white <?= ($_GET['page'] ?? '') == 'admin/kelola_kurikulum' ? 'active' : '' ?>" 
                   href="?page=admin/kelola_kurikulum">
                    <i class="bi bi-book me-2"></i> Kurikulum
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white <?= ($_GET['page'] ?? '') == 'admin/bahan_kajian' ? 'active' : '' ?>" 
                   href="?page=admin/bahan_kajian">
                    <i class="bi bi-book me-2"></i> Bahan Kajian
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white <?= ($_GET['page'] ?? '') == 'rpl/konversi' ? 'active' : '' ?>" 
                   href="?page=rpl/konversi">
                    <i class="bi bi-check-circle me-2"></i> Konversi RPL
                </a>
            </li>
            
            <?php elseif ($_SESSION['role'] == 'rpl'): ?>
            <li class="nav-item">
                <a class="nav-link text-white <?= ($_GET['page'] ?? '') == 'rpl/konversi' ? 'active' : '' ?>" 
                   href="?page=rpl/konversi">
                    <i class="bi bi-arrow-left-right me-2"></i> Konversi RPL
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= ($_GET['page'] ?? '') == 'profil' ? 'active' : '' ?>" 
                   href="?page=profil">
                    <i class="bi bi-person me-2"></i> Kelola Akun
                </a>
            </li>
            
            <?php else: ?>
            <li class="nav-item">
                <a class="nav-link text-white <?= ($_GET['page'] ?? '') == 'mahasiswa/input_konversi' ? 'active' : '' ?>" 
                   href="?page=mahasiswa/input_konversi">
                    <i class="bi bi-plus-circle me-2"></i> Input Konversi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= ($_GET['page'] ?? '') == 'mahasiswa/data_konversi' ? 'active' : '' ?>" 
                   href="?page=mahasiswa/data_konversi">
                    <i class="bi bi-table me-2"></i> Data Konversi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= ($_GET['page'] ?? '') == 'profil' ? 'active' : '' ?>" 
                   href="?page=profil">
                    <i class="bi bi-person me-2"></i> Kelola Akun
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item mt-4">
                <a class="nav-link text-warning" href="logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>