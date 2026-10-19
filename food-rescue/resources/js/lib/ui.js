// Utilitas tampilan: escape HTML, toast, modal, format tanggal.

/** Escape teks sebelum disisipkan ke innerHTML. */
export function esc(nilai) {
    return String(nilai ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
}

const GAYA_TOAST = {
    sukses: 'bg-brand-700 text-white',
    galat: 'bg-rose-600 text-white',
    info: 'bg-ink text-white',
};

export function toast(pesan, jenis = 'info') {
    const wadah = document.getElementById('toasts');
    if (!wadah) return;

    const el = document.createElement('div');
    el.setAttribute('role', 'status');
    el.className = `pointer-events-auto animate-pop rounded-xl px-4 py-3 text-sm font-medium shadow-float ${GAYA_TOAST[jenis] ?? GAYA_TOAST.info}`;
    el.textContent = pesan;
    wadah.appendChild(el);

    setTimeout(() => {
        el.style.transition = 'opacity .3s, transform .3s';
        el.style.opacity = '0';
        el.style.transform = 'translateY(8px)';
        setTimeout(() => el.remove(), 300);
    }, 3800);
}

export function bukaModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('hidden');
    el.classList.add('flex');
    document.body.classList.add('overflow-hidden');
    el.querySelector('input:not([type=hidden]), select, textarea')?.focus();
}

export function tutupModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.add('hidden');
    el.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

/** Pasang penutup modal: klik latar, tombol [data-tutup], dan tombol Escape. */
export function pasangModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('click', (e) => {
        if (e.target === el || e.target.closest('[data-tutup]')) tutupModal(id);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !el.classList.contains('hidden')) tutupModal(id);
    });
}

/** Tampilkan keadaan memuat pada tombol submit. */
export function setMemuat(tombol, memuat, teks) {
    if (!tombol) return;
    tombol.disabled = memuat;
    const label = tombol.querySelector('[data-label]') ?? tombol;
    if (memuat) {
        tombol.dataset.teksAsli = label.textContent;
        label.textContent = teks ?? 'Memproses…';
    } else if (tombol.dataset.teksAsli) {
        label.textContent = tombol.dataset.teksAsli;
    }
}

/** Tampilkan galat validasi per-field (objek galat dari API). */
export function tampilkanGalatField(form, galat) {
    form.querySelectorAll('[data-error-for]').forEach((p) => {
        p.classList.add('hidden');
        p.textContent = '';
    });
    form.querySelectorAll('.field').forEach((i) => i.classList.remove('!border-rose-500'));
    if (!galat) return;

    Object.entries(galat).forEach(([nama, pesan]) => {
        const p = form.querySelector(`[data-error-for="${nama}"]`);
        if (p) {
            p.textContent = Array.isArray(pesan) ? pesan[0] : pesan;
            p.classList.remove('hidden');
        }
        form.querySelector(`[name="${nama}"]`)?.classList.add('!border-rose-500');
    });
}

const fmtTanggal = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

export function formatTanggal(iso) {
    if (!iso) return '-';
    const d = new Date(iso);
    return Number.isNaN(d.getTime()) ? '-' : fmtTanggal.format(d);
}

/** Sisa waktu dari sekarang menuju batas waktu, dalam teks singkat. */
export function sisaWaktu(iso) {
    if (!iso) return { teks: 'Tanpa batas waktu', mendesak: false, lewat: false };
    const selisih = new Date(iso).getTime() - Date.now();
    if (Number.isNaN(selisih)) return { teks: 'Tanpa batas waktu', mendesak: false, lewat: false };
    if (selisih <= 0) return { teks: 'Kedaluwarsa', mendesak: true, lewat: true };

    const menit = Math.floor(selisih / 60000);
    const jam = Math.floor(menit / 60);
    const hari = Math.floor(jam / 24);

    if (hari >= 1) return { teks: `${hari} hari lagi`, mendesak: false, lewat: false };
    if (jam >= 1) return { teks: `${jam} jam lagi`, mendesak: jam < 3, lewat: false };
    return { teks: `${Math.max(menit, 1)} menit lagi`, mendesak: true, lewat: false };
}

export function inisial(nama) {
    return (
        String(nama ?? '?')
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map((k) => k[0]?.toUpperCase())
            .join('') || '?'
    );
}
