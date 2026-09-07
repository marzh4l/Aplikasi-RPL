document.addEventListener('DOMContentLoaded', function() {
    
    // ===== AUTO DISMISS ALERT =====
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });
    
    // ===== SIDEBAR ACTIVE STATE =====
    const currentPage = new URLSearchParams(window.location.search).get('page');
    const currentRole = new URLSearchParams(window.location.search).get('role');
    
    if (currentPage) {
        const submenuLinks = document.querySelectorAll('#submenuAkun a');
        submenuLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href.includes(currentPage) && href.includes(currentRole)) {
                link.classList.add('text-white');
                link.classList.remove('text-white-50');
                const submenu = document.getElementById('submenuAkun');
                if (submenu) {
                    submenu.classList.add('show');
                    const toggleBtn = document.querySelector('[href="#submenuAkun"]');
                    if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
                }
            }
        });
    }
    
    // ===== FOCUS INPUT PERTAMA SAAT MODAL DIBUKA =====
    const modalTambah = document.getElementById('modalTambah');
    if (modalTambah) {
        modalTambah.addEventListener('shown.bs.modal', function() {
            this.querySelector('input[name="username"]').focus();
        });
    }
});