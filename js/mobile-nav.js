document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');
    const closeMenu = document.getElementById('closeMenu');
    const mobileOverlay = document.getElementById('mobileOverlay');
    function openMenu() { mobileMenu?.classList.add('open'); mobileOverlay?.classList.add('show'); hamburger?.classList.add('active'); document.body.style.overflow = 'hidden'; }
    function closeMenuFn() { mobileMenu?.classList.remove('open'); mobileOverlay?.classList.remove('show'); hamburger?.classList.remove('active'); document.body.style.overflow = ''; }
    hamburger?.addEventListener('click', openMenu);
    closeMenu?.addEventListener('click', closeMenuFn);
    mobileOverlay?.addEventListener('click', closeMenuFn);
    const currentPath = window.location.pathname;
    document.querySelectorAll('.bottom-nav-item').forEach(function(item) {
        const href = item.getAttribute('href') || '';
        if (href && currentPath.endsWith(href.split('/').pop())) item.classList.add('active');
    });
    if (currentPath === '/' || currentPath.endsWith('index.php')) {
        const first = document.querySelector('.bottom-nav-item');
        if (first) first.classList.add('active');
    }
});
