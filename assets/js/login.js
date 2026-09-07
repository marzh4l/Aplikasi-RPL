// Fungsi untuk menampilkan semester yang dipilih
function showSemester(prodiId, semester, element) {
    // Update active state pada pagination
    const paginationContainer = document.getElementById('semester-pills-' + prodiId);
    const links = paginationContainer.querySelectorAll('.page-link-semester');
    links.forEach(link => link.classList.remove('active'));
    element.classList.add('active')
    // Sembunyikan semua semester content untuk prodi ini
    const contents = document.querySelectorAll('[id^="semester-' + prodiId + '-"]');
    contents.forEach(content => content.classList.remove('active'))
    // Tampilkan semester yang dipilih
    const safeSem = semester.replace(/[ /&]/g, '_');
    const targetContent = document.getElementById('semester-' + prodiId + '-' + safeSem);
    if (targetContent) {
        targetContent.classList.add('active');
    }
};

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