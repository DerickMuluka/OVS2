// Mobile nav toggle
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('navToggle');
    const nav    = document.getElementById('siteNav');
    if (toggle && nav) {
        toggle.addEventListener('click', () => nav.classList.toggle('open'));
    }

    // Registration password match validation
    const regForm = document.getElementById('registerForm');
    if (regForm) {
        regForm.addEventListener('submit', (e) => {
            const pwd  = document.getElementById('password').value;
            const conf = document.getElementById('confirm').value;
            if (pwd !== conf) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            if (pwd.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters.');
                return false;
            }
        });
    }

    // Auto-hide alerts after 4s
    document.querySelectorAll('.alert').forEach(a => {
        setTimeout(() => { a.style.transition='opacity 0.5s'; a.style.opacity = 0; }, 4000);
    });
});