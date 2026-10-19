// Interaksi halaman beranda: navbar, menu mobile, animasi muncul saat scroll.

export default function beranda() {
    const inner = document.getElementById('navbar-inner');
    const btn = document.getElementById('menu-btn');
    const menu = document.getElementById('menu-mobile');

    const aturNavbar = () => {
        const aktif = window.scrollY > 24;
        inner.classList.toggle('bg-white/80', aktif);
        inner.classList.toggle('backdrop-blur-xl', aktif);
        inner.classList.toggle('border-ink/5', aktif);
        inner.classList.toggle('shadow-card', aktif);
    };
    aturNavbar();
    window.addEventListener('scroll', aturNavbar, { passive: true });

    btn?.addEventListener('click', () => {
        const terbuka = menu.classList.toggle('hidden') === false;
        btn.setAttribute('aria-expanded', String(terbuka));
    });
    menu?.querySelectorAll('a').forEach((a) =>
        a.addEventListener('click', () => {
            menu.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
        }),
    );

    const elemen = document.querySelectorAll('[data-reveal]');
    if (!('IntersectionObserver' in window)) {
        elemen.forEach((el) => el.classList.add('is-visible'));
        return;
    }
    const pengamat = new IntersectionObserver(
        (entri) =>
            entri.forEach((e) => {
                if (e.isIntersecting) {
                    e.target.classList.add('is-visible');
                    pengamat.unobserve(e.target);
                }
            }),
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
    );
    elemen.forEach((el) => pengamat.observe(el));
}
