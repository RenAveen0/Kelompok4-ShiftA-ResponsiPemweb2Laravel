// Panel Admin: CRUD untuk seluruh sumber daya API, digerakkan oleh konfigurasi RESOURCES di bawah.
// Untuk menambah modul baru cukup tambahkan satu entri di RESOURCES (tanpa menulis HTML baru).

import { api, sesi, wajibLogin, GalatApi } from '../lib/api';
import { ikon } from '../lib/ikon';
import { esc, toast, bukaModal, tutupModal, pasangModal, setMemuat, tampilkanGalatField, formatTanggal, inisial } from '../lib/ui';

const PER_HALAMAN = 10;

// Kelas Tailwind ditulis utuh supaya terdeteksi saat build.
const WARNA = {
    // peran
    admin: 'bg-ink text-white',
    donatur: 'bg-brand-100 text-brand-700',
    penerima: 'bg-sun-100 text-sun-600',
    relawan: 'bg-sky-100 text-sky-700',
    // status baik
    tersedia: 'bg-brand-100 text-brand-700',
    disetujui: 'bg-brand-100 text-brand-700',
    ditindaklanjuti: 'bg-brand-100 text-brand-700',
    aktif: 'bg-brand-100 text-brand-700',
    selesai: 'bg-brand-600 text-white',
    // status menunggu / berjalan
    diajukan: 'bg-sun-100 text-sun-600',
    ditugaskan: 'bg-sun-100 text-sun-600',
    menuju_lokasi: 'bg-sky-100 text-sky-700',
    diambil: 'bg-sky-100 text-sky-700',
    diantar: 'bg-sky-100 text-sky-700',
    diambil_relawan: 'bg-sky-100 text-sky-700',
    menunggu_tinjauan: 'bg-sun-100 text-sun-600',
    diproses: 'bg-sky-100 text-sky-700',
    // status buruk
    ditolak: 'bg-rose-50 text-rose-700',
    dibatalkan: 'bg-rose-50 text-rose-700',
    habis: 'bg-rose-50 text-rose-700',
    nonaktif: 'bg-rose-50 text-rose-700',
};

const lencana = (nilai) => `<span class="badge capitalize ${WARNA[nilai] ?? 'bg-ink/5 text-ink/60'}">${esc(String(nilai ?? '-').replaceAll('_', ' '))}</span>`;
const potong = (teks, n = 70) => {
    const t = String(teks ?? '');
    return t.length > n ? `${t.slice(0, n)}…` : t;
};
const opsiStatus = (...daftar) => daftar.map((v) => ({ v, l: v.replaceAll('_', ' ') }));

// ---------- Konfigurasi sumber daya ----------
// kolom : [{ l: judul kolom, f: (baris) => HTML aman }]
// form  : [{ k, l, tipe, wajib, nullable, penuh, opsi, min, step, ph }]
//         tipe: text | textarea | number | select | checkbox | datetime
const RESOURCES = {
    ringkasan: { judul: 'Ringkasan', ikon: 'chart', ket: 'Dampak dan aktivitas platform FoodRescue.', khusus: true },

    pengguna: {
        judul: 'Pengguna',
        ikon: 'users',
        ket: 'Daftar seluruh akun. Admin dapat menghapus akun pengguna lain.',
        daftar: '/admin/users',
        server: true,
        hapus: (id) => `/admin/users/${id}`,
        bisaHapus: (r, state) => r.id !== state.user.id,
        label: (r) => r.name,
        kolom: [
            {
                l: 'Pengguna',
                f: (r) => `<div class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-100 text-xs font-bold text-brand-700">${esc(inisial(r.name))}</span>
                    <div class="min-w-0"><p class="truncate font-semibold">${esc(r.name)}</p><p class="truncate text-xs text-ink/50">${esc(r.email)}</p></div></div>`,
            },
            { l: 'Peran', f: (r) => lencana(r.role) },
            { l: 'Telepon', f: (r) => esc(r.no_telepon ?? '-') },
            { l: 'Alamat', f: (r) => esc(potong(r.alamat ?? '-', 50)) },
        ],
    },

    kategori: {
        judul: 'Kategori Makanan',
        ikon: 'leaf',
        ket: 'Jenis makanan, panduan penyimpanan, dan batas jam simpan.',
        daftar: '/kategori-makanan',
        buat: '/admin/kategori-makanan',
        ubah: (id) => `/admin/kategori-makanan/${id}`,
        hapus: (id) => `/admin/kategori-makanan/${id}`,
        label: (r) => r.nama,
        kolom: [
            { l: 'Nama', f: (r) => `<span class="font-semibold">${esc(r.nama)}</span>` },
            { l: 'Batas simpan', f: (r) => `${esc(r.batas_simpan_jam)} jam` },
            { l: 'Deskripsi', f: (r) => esc(potong(r.deskripsi ?? '-')) },
            { l: 'Panduan simpan', f: (r) => esc(potong(r.panduan_simpan ?? '-')) },
        ],
        form: [
            { k: 'nama', l: 'Nama kategori', tipe: 'text', wajib: true, penuh: true, ph: 'mis. Makanan berat' },
            { k: 'batas_simpan_jam', l: 'Batas simpan (jam)', tipe: 'number', wajib: true, min: 1, ph: '6', penuh: true },
            { k: 'deskripsi', l: 'Deskripsi', tipe: 'textarea', nullable: true, penuh: true },
            { k: 'panduan_simpan', l: 'Panduan penyimpanan', tipe: 'textarea', nullable: true, penuh: true },
        ],
    },

    titik: {
        judul: 'Titik Lokasi',
        ikon: 'map',
        ket: 'Posko dan zona penjemputan makanan. Admin melihat semua titik, termasuk yang nonaktif.',
        daftar: '/titik-lokasi?semua=1',
        buat: '/admin/titik-lokasi',
        ubah: (id) => `/admin/titik-lokasi/${id}`,
        hapus: (id) => `/admin/titik-lokasi/${id}`,
        label: (r) => r.nama,
        kolom: [
            { l: 'Nama', f: (r) => `<span class="font-semibold">${esc(r.nama)}</span>` },
            { l: 'Alamat', f: (r) => esc(potong(r.alamat)) },
            { l: 'Koordinat', f: (r) => `<span class="text-xs text-ink/60">${esc(r.latitude)}, ${esc(r.longitude)}</span>` },
            { l: 'Status', f: (r) => lencana(r.is_aktif ? 'aktif' : 'nonaktif') },
        ],
        form: [
            { k: 'nama', l: 'Nama titik', tipe: 'text', wajib: true, penuh: true, ph: 'mis. Posko Alun-alun' },
            { k: 'alamat', l: 'Alamat', tipe: 'textarea', wajib: true, penuh: true },
            { k: 'latitude', l: 'Latitude', tipe: 'number', wajib: true, step: 'any', ph: '-7.4242' },
            { k: 'longitude', l: 'Longitude', tipe: 'number', wajib: true, step: 'any', ph: '109.2303' },
            { k: 'is_aktif', l: 'Titik aktif', tipe: 'checkbox', penuh: true, bawaan: true },
        ],
    },

    donasi: {
        judul: 'Donasi Makanan',
        ikon: 'gift',
        ket: 'Postingan donasi dari donatur. API hanya menampilkan donasi berstatus tersedia.',
        daftar: '/donasi-makanan',
        server: true,
        buat: '/donasi-makanan',
        ubah: (id) => `/donasi-makanan/${id}`,
        hapus: (id) => `/donasi-makanan/${id}`,
        label: (r) => r.judul,
        kolom: [
            {
                l: 'Donasi',
                f: (r) => `<p class="font-semibold">${esc(potong(r.judul, 40))}</p><p class="text-xs text-ink/50">${esc(r.user?.name ?? '-')}</p>`,
            },
            { l: 'Kategori', f: (r) => esc(r.kategori?.nama ?? '-') },
            { l: 'Porsi', f: (r) => `<span class="font-semibold">${esc(r.porsi_tersisa)}</span><span class="text-ink/50"> / ${esc(r.porsi)}</span>` },
            { l: 'Batas waktu', f: (r) => `<span class="text-xs">${esc(formatTanggal(r.batas_waktu))}</span>` },
            { l: 'Status', f: (r) => lencana(r.status) },
        ],
        form: [
            { k: 'judul', l: 'Judul', tipe: 'text', wajib: true, penuh: true },
            { k: 'kategori_id', l: 'Kategori', tipe: 'select', wajib: true, opsi: 'kategori' },
            { k: 'porsi', l: 'Jumlah porsi', tipe: 'number', wajib: true, min: 1 },
            { k: 'lokasi', l: 'Lokasi pengambilan', tipe: 'text', wajib: true, penuh: true },
            { k: 'titik_lokasi_id', l: 'Titik penjemputan', tipe: 'select', nullable: true, opsi: 'titik', kosong: 'Tidak ada' },
            { k: 'batas_waktu', l: 'Batas waktu', tipe: 'datetime', nullable: true },
            { k: 'status', l: 'Status', tipe: 'select', opsi: opsiStatus('tersedia', 'habis', 'dibatalkan'), sembunyiSaatBuat: true },
            { k: 'deskripsi', l: 'Deskripsi', tipe: 'textarea', nullable: true, penuh: true },
        ],
    },

       klaim: {
        judul: 'Klaim Donasi',
        ikon: 'inbox',
        ket: 'Setujui atau tolak klaim penerima, lalu tugaskan relawan untuk klaim yang sudah disetujui.',
        daftar: '/klaim-donasi',
        ubah: (id) => `/klaim-donasi/${id}`,
        hapus: (id) => `/klaim-donasi/${id}`,
        label: (r) => `klaim #${r.id}`,
        // Tombol cepat sesuai status klaim
        aksiTambahan: (r) => {
            if (r.status === 'diajukan') {
                return `<button class="btn btn-outline !px-3 !py-1.5 text-xs" data-aksi="status" data-status="disetujui" data-id="${r.id}">Setujui</button>
                    <button class="btn btn-ghost !px-3 !py-1.5 text-xs hover:!bg-rose-50 hover:!text-rose-600" data-aksi="status" data-status="ditolak" data-id="${r.id}">Tolak</button>`;
            }
            if (r.status === 'disetujui' && !r.penugasan) {
                return `<button class="btn btn-primary !px-3 !py-1.5 text-xs" data-aksi="tugaskan" data-id="${r.id}">Tugaskan relawan</button>`;
            }
            return '';
        },
        kolom: [
            { l: 'Donasi', f: (r) => `<span class="font-semibold">${esc(potong(r.donasi?.judul ?? `Donasi #${r.donasi_id}`, 40))}</span>` },
            { l: 'Penerima', f: (r) => esc(r.penerima?.name ?? `#${r.penerima_id}`) },
            { l: 'Porsi', f: (r) => esc(r.jumlah_porsi) },
            { l: 'Relawan', f: (r) => esc(r.relawan?.name ?? '-') },
            { l: 'Status', f: (r) => lencana(r.status) },
            { l: 'Diajukan', f: (r) => `<span class="text-xs">${esc(formatTanggal(r.created_at))}</span>` },
        ],
        form: [
            {
                k: 'status',
                l: 'Status klaim',
                tipe: 'select',
                wajib: true,
                penuh: true,
                opsi: opsiStatus('diajukan', 'disetujui', 'ditolak', 'diambil_relawan', 'diantar', 'selesai', 'dibatalkan'),
            },
        ],
    },

    penugasan: {
        judul: 'Penugasan Relawan',
        ikon: 'truck',
        ket: 'Tugaskan relawan ke klaim yang sudah disetujui, dan pantau tahap pengantarannya.',
        daftar: '/penugasan-relawan',
        buat: '/penugasan-relawan',
        ubah: (id) => `/penugasan-relawan/${id}`,
        hapus: (id) => `/penugasan-relawan/${id}`,
        label: (r) => `penugasan #${r.id}`,
        kolom: [
            {
                l: 'Donasi',
                f: (r) => `<p class="font-semibold">${esc(potong(r.klaim?.donasi?.judul ?? `Klaim #${r.klaim_id}`, 40))}</p>
                    <p class="text-xs text-ink/50">Klaim #${esc(r.klaim_id)} · ${esc(r.klaim?.penerima?.name ?? '-')}</p>`,
            },
            { l: 'Relawan', f: (r) => esc(r.relawan?.name ?? `#${r.relawan_id}`) },
            { l: 'Status', f: (r) => lencana(r.status) },
            { l: 'Catatan', f: (r) => esc(potong(r.catatan ?? '-', 50)) },
        ],
        form: [
            { k: 'klaim_id', l: 'Klaim (yang sudah disetujui)', tipe: 'select', wajib: true, penuh: true, opsi: 'klaimSiap', kosong: 'Pilih klaim…', sembunyiSaatUbah: true },
            { k: 'relawan_id', l: 'Relawan', tipe: 'select', wajib: true, penuh: true, opsi: 'relawan', kosong: 'Pilih relawan…' },
            { k: 'status', l: 'Status penugasan', tipe: 'select', wajib: true, penuh: true, opsi: opsiStatus('ditugaskan', 'menuju_lokasi', 'diambil', 'diantar', 'selesai'), sembunyiSaatBuat: true },
            { k: 'catatan', l: 'Catatan', tipe: 'textarea', nullable: true, penuh: true },
        ],
    },

    ulasan: {
        judul: 'Ulasan & Rating',
        ikon: 'star',
        ket: 'Penilaian kepuasan dari penerima.',
        daftar: '/ulasan',
        hapus: (id) => `/ulasan/${id}`,
        label: (r) => `ulasan #${r.id}`,
        kolom: [
            { l: 'Klaim', f: (r) => `<span class="font-semibold">Klaim #${esc(r.klaim_id)}</span>` },
            {
                l: 'Rating',
                f: (r) => `<span class="text-sun-500" title="${esc(r.rating)} dari 5">${'★'.repeat(Number(r.rating) || 0)}</span><span class="text-ink/15">${'★'.repeat(5 - (Number(r.rating) || 0))}</span>`,
            },
            { l: 'Komentar', f: (r) => esc(potong(r.komentar ?? '-', 80)) },
        ],
    },

    laporan: {
        judul: 'Laporan Kelayakan',
        ikon: 'flag',
        ket: 'Laporan makanan tidak layak atau kemasan rusak.',
        daftar: '/laporan-kelayakan',
        ubah: (id) => `/laporan-kelayakan/${id}`,
        hapus: (id) => `/laporan-kelayakan/${id}`,
        label: (r) => `laporan #${r.id}`,
        kolom: [
            { l: 'Donasi', f: (r) => `<span class="font-semibold">Donasi #${esc(r.donasi_id)}</span>` },
            { l: 'Alasan', f: (r) => esc(potong(r.alasan, 80)) },
            { l: 'Status', f: (r) => lencana(r.status) },
            { l: 'Dilaporkan', f: (r) => `<span class="text-xs">${esc(formatTanggal(r.created_at))}</span>` },
        ],
        form: [{ k: 'status', l: 'Status laporan', tipe: 'select', wajib: true, penuh: true, opsi: opsiStatus('menunggu_tinjauan', 'diproses', 'ditindaklanjuti', 'ditolak') }],
    },

    bukti: {
        judul: 'Bukti Serah Terima',
        ikon: 'camera',
        ket: 'Bukti penyerahan donasi yang diunggah relawan.',
        daftar: '/bukti-serah-terima',
        hapus: (id) => `/bukti-serah-terima/${id}`,
        label: (r) => `bukti #${r.id}`,
        kolom: [
            { l: 'Klaim', f: (r) => `<span class="font-semibold">Klaim #${esc(r.klaim_id)}</span>` },
            { l: 'Catatan', f: (r) => esc(potong(r.catatan ?? '-', 60)) },
            { l: 'Waktu serah terima', f: (r) => `<span class="text-xs">${esc(formatTanggal(r.waktu_serah_terima))}</span>` },
            {
                l: 'Foto',
                f: (r) =>
                    /^https?:\/\//.test(r.foto_bukti ?? '')
                        ? `<a href="${esc(r.foto_bukti)}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 font-semibold text-brand-700 hover:underline">${ikon('link', 'size-4')}Buka</a>`
                        : `<span class="text-xs text-ink/55">${esc(potong(r.foto_bukti ?? '-', 30))}</span>`,
            },
        ],
    },
};

// ---------- State ----------
const state = {
    user: null,
    kunci: 'ringkasan',
    baris: [],
    meta: { current_page: 1, last_page: 1 },
    halaman: 1,
    q: '',
    opsi: { kategori: [], titik: [], klaimSiap: [], relawan: [] },
    awalForm: null,
    opsiDimuat: false,
    sedangUbah: null,
    akanHapus: null,
};

const $ = (id) => document.getElementById(id);
const res = () => RESOURCES[state.kunci];

// ---------- Navigasi ----------
function renderNav() {
    const item = Object.entries(RESOURCES);

    $('nav-desktop').innerHTML = item
        .map(
            ([k, r]) => `
        <a href="#${k}" data-nav="${k}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/65 transition hover:bg-white/5 hover:text-white aria-[current=page]:bg-white/10 aria-[current=page]:text-white">
            <span class="text-brand-300">${ikon(r.ikon, 'size-5')}</span>${esc(r.judul)}
        </a>`,
        )
        .join('');

    $('nav-mobile').innerHTML = item
        .map(([k, r]) => `<a href="#${k}" data-nav="${k}" class="tab shrink-0 whitespace-nowrap" aria-selected="false">${ikon(r.ikon, 'size-4')}${esc(r.judul)}</a>`)
        .join('');
}

function tandaiNav() {
    document.querySelectorAll('[data-nav]').forEach((a) => {
        const aktif = a.dataset.nav === state.kunci;
        if (aktif) a.setAttribute('aria-current', 'page');
        else a.removeAttribute('aria-current');
        a.setAttribute('aria-selected', String(aktif));
    });
}

// ---------- Tampilan bagian ----------
function skeletonTabel() {
    $('isi').innerHTML = `<div class="card divide-y divide-ink/5">${Array.from({ length: 5 }, () => '<div class="flex gap-4 p-5"><div class="skeleton h-5 w-1/4"></div><div class="skeleton h-5 w-1/3"></div><div class="skeleton h-5 w-1/5"></div></div>').join('')}</div>`;
}

async function tampil() {
    const kunci = (location.hash || '#ringkasan').slice(1);
    state.kunci = RESOURCES[kunci] ? kunci : 'ringkasan';
    state.halaman = 1;
    state.q = '';
    $('cari').value = '';

    const r = res();
    $('bagian-judul').textContent = r.judul;
    $('bagian-ket').textContent = r.ket ?? '';
    $('btn-tambah').classList.toggle('hidden', !r.buat);
    $('bar-cari').classList.toggle('hidden', Boolean(r.khusus));
    tandaiNav();
    window.scrollTo({ top: 0 });

    if (r.khusus) {
        $('paging').innerHTML = '';
        await muatRingkasan();
    } else {
        await muatData();
    }
    
    // Datang dari tombol "Tugaskan relawan" di tabel klaim: buka form dengan klaim terpilih
    if (state.awalForm && state.kunci === 'penugasan') {
        const awal = state.awalForm;
        state.awalForm = null;
        bukaForm(null, awal);
    }
}

// ---------- Ringkasan ----------
async function muatRingkasan() {
    $('isi').innerHTML = '<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">' + Array.from({ length: 4 }, () => '<div class="skeleton h-28"></div>').join('') + '</div>';
    let s;
    try {
        s = (await api('/admin/dasbor')).data;
    } catch (err) {
        $('isi').innerHTML = `<div class="card p-10 text-center font-medium text-rose-600">${esc(err.message)}</div>`;
        return;
    }

    const kartu = [
        { l: 'Porsi diselamatkan', n: Number(s.porsi_diselamatkan ?? 0).toLocaleString('id-ID'), i: 'leaf', w: 'bg-brand-50 text-brand-600' },
        { l: 'Klaim selesai', n: Number(s.klaim_selesai ?? 0).toLocaleString('id-ID'), i: 'check', w: 'bg-brand-50 text-brand-600' },
        { l: 'Donasi aktif', n: Number(s.donasi_aktif ?? 0).toLocaleString('id-ID'), i: 'gift', w: 'bg-sun-100 text-sun-600' },
        { l: 'Rata-rata rating', n: Number(s.rata_rata_rating ?? 0).toFixed(1), i: 'star', w: 'bg-sun-100 text-sun-600' },
    ]
        .map(
            (k) => `
        <div class="card flex items-center gap-4 p-5 animate-pop">
            <span class="grid size-12 shrink-0 place-items-center rounded-xl ${k.w}">${ikon(k.i, 'size-6')}</span>
            <div><p class="text-sm font-medium text-ink/55">${k.l}</p><p class="mt-0.5 text-2xl font-extrabold tracking-tight">${k.n}</p></div>
        </div>`,
        )
        .join('');

    const data = s.grafik_bulanan ?? [];
    const maks = Math.max(1, ...data.map((b) => Number(b.total_porsi)));
    const batang = data.length
        ? data
              .map((b) => {
                  const tinggi = Math.max(6, Math.round((Number(b.total_porsi) / maks) * 100));
                  const bulan = new Date(`${b.bulan}-01`).toLocaleDateString('id-ID', { month: 'short', year: '2-digit' });
                  return `<div class="flex flex-1 flex-col items-center justify-end gap-2" title="${esc(b.total_porsi)} porsi">
                    <span class="text-xs font-bold">${esc(b.total_porsi)}</span>
                    <div class="w-full max-w-14 rounded-t-xl bg-linear-to-t from-brand-600 to-brand-400" style="height:${tinggi}%"></div>
                    <span class="text-xs font-medium text-ink/55">${esc(bulan)}</span></div>`;
              })
              .join('')
        : '<p class="m-auto text-sm text-ink/50">Belum ada data klaim selesai.</p>';

    const pintasan = Object.entries(RESOURCES)
        .filter(([, r]) => !r.khusus)
        .map(
            ([k, r]) => `
        <a href="#${k}" class="card group flex items-center gap-4 p-4 transition hover:-translate-y-0.5 hover:border-brand-200">
            <span class="grid size-11 place-items-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">${ikon(r.ikon, 'size-5')}</span>
            <span class="font-semibold">${esc(r.judul)}</span>
        </a>`,
        )
        .join('');

    $('isi').innerHTML = `
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">${kartu}</div>
        <div class="card mt-6 p-6 sm:p-8">
            <h3 class="text-lg font-bold">Porsi diselamatkan per bulan</h3>
            <p class="mt-1 text-sm text-ink/55">Total porsi dari klaim berstatus selesai.</p>
            <div class="mt-8 flex h-60 items-end gap-3 sm:gap-5">${batang}</div>
        </div>
        <h3 class="mt-10 mb-4 text-lg font-bold">Kelola data</h3>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">${pintasan}</div>`;
}

// ---------- Tabel ----------
async function muatData() {
    const r = res();
    skeletonTabel();
    $('paging').innerHTML = '';
    try {
        const url = r.server ? `${r.daftar}?page=${state.halaman}` : r.daftar;
        const hasil = (await api(url)).data;

        if (r.server) {
            state.baris = hasil.data ?? [];
            state.meta = { current_page: hasil.current_page ?? 1, last_page: hasil.last_page ?? 1 };
        } else {
            state.baris = Array.isArray(hasil) ? hasil : (hasil?.data ?? []);
            state.meta = { current_page: state.halaman, last_page: 1 };
        }
        renderTabel();
    } catch (err) {
        $('isi').innerHTML = `<div class="card p-10 text-center font-medium text-rose-600">${esc(err.message)}</div>`;
    }
}

function barisTersaring() {
    const q = state.q.trim().toLowerCase();
    return q ? state.baris.filter((b) => JSON.stringify(b).toLowerCase().includes(q)) : state.baris;
}

function renderTabel() {
    const r = res();
    const semua = barisTersaring();

    // Halaman klien untuk daftar non-server; halaman server memakai meta dari API
    let tampilan = semua;
    let halaman = state.meta.current_page;
    let terakhir = state.meta.last_page;
    if (!r.server) {
        terakhir = Math.max(1, Math.ceil(semua.length / PER_HALAMAN));
        halaman = Math.min(state.halaman, terakhir);
        tampilan = semua.slice((halaman - 1) * PER_HALAMAN, halaman * PER_HALAMAN);
    }

    if (!tampilan.length) {
        $('isi').innerHTML = `
            <div class="card grid place-items-center gap-3 px-6 py-16 text-center">
                <span class="grid size-14 place-items-center rounded-2xl bg-brand-50 text-brand-600">${ikon(r.ikon, 'size-7')}</span>
                <p class="text-lg font-bold">${state.q ? 'Tidak ada data yang cocok' : 'Belum ada data'}</p>
                <p class="max-w-sm text-sm text-ink/55">${state.q ? 'Coba kata kunci lain.' : r.buat ? 'Klik “Tambah data” untuk membuat yang pertama.' : 'Data akan tampil di sini saat tersedia.'}</p>
            </div>`;
        renderPaging(1, 1);
        return;
    }

    const adaAksi = Boolean(r.ubah || r.hapus || r.aksiTambahan);
    const kepala = r.kolom.map((k) => `<th scope="col" class="px-5 py-3.5 font-semibold whitespace-nowrap">${esc(k.l)}</th>`).join('') + (adaAksi ? '<th scope="col" class="px-5 py-3.5 text-right font-semibold">Aksi</th>' : '');

    const isi = tampilan
        .map((b) => {
            const sel = r.kolom.map((k) => `<td class="px-5 py-4 align-middle">${k.f(b)}</td>`).join('');
                        let aksi = '';
            if (adaAksi) {
                const tambahan = r.aksiTambahan ? r.aksiTambahan(b) : '';
                const ubah = r.ubah ? `<button class="btn btn-ghost !p-2" data-aksi="ubah" data-id="${b.id}" aria-label="Ubah" title="Ubah">${ikon('pencil', 'size-4.5')}</button>` : '';
                const hapus =
                    r.hapus && (!r.bisaHapus || r.bisaHapus(b, state))
                        ? `<button class="btn btn-ghost !p-2 hover:!bg-rose-50 hover:!text-rose-600" data-aksi="hapus" data-id="${b.id}" aria-label="Hapus" title="Hapus">${ikon('trash', 'size-4.5')}</button>`
                        : '';
                aksi = `<td class="px-5 py-4 text-right whitespace-nowrap">${tambahan}${ubah}${hapus}</td>`;
            }
            return `<tr class="transition hover:bg-brand-50/50">${sel}${aksi}</tr>`;
        })
        .join('');

    $('isi').innerHTML = `
        <div class="card animate-pop overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-ink/[0.03] text-xs tracking-wide text-ink/50 uppercase"><tr>${kepala}</tr></thead>
                    <tbody class="divide-y divide-ink/5">${isi}</tbody>
                </table>
            </div>
        </div>
        <p class="mt-3 text-xs text-ink/45">Menampilkan ${tampilan.length} data${r.server ? ' pada halaman ini' : ` dari ${semua.length}`}.</p>`;

    renderPaging(halaman, terakhir);
}

function renderPaging(halaman, terakhir) {
    const el = $('paging');
    if (terakhir <= 1) {
        el.innerHTML = '';
        return;
    }
    el.innerHTML = `
        <button class="btn btn-outline" data-hal="${halaman - 1}" ${halaman <= 1 ? 'disabled' : ''}>← Sebelumnya</button>
        <span class="text-sm font-medium text-ink/60">Halaman ${halaman} dari ${terakhir}</span>
        <button class="btn btn-outline" data-hal="${halaman + 1}" ${halaman >= terakhir ? 'disabled' : ''}>Berikutnya →</button>`;
}

// ---------- Form tambah / ubah ----------
async function pastikanOpsi() {
    if (state.opsiDimuat) return;
    try {
        const [kat, titik] = await Promise.all([api('/kategori-makanan'), api('/titik-lokasi')]);
        state.opsi.kategori = (kat.data ?? []).map((k) => ({ v: k.id, l: k.nama }));
        state.opsi.titik = (titik.data?.data ?? titik.data ?? []).map((t) => ({ v: t.id, l: t.nama }));
        state.opsiDimuat = true;
    } catch {
        /* pilihan kosong; galat akan terlihat saat menyimpan */
    }
}

// Opsi klaim dan relawan selalu dimuat ulang karena datanya berubah-ubah
async function muatOpsiPenugasan() {
    try {
        const [klaim, relawan] = await Promise.all([api('/klaim-donasi'), api('/admin/relawan')]);
        state.opsi.klaimSiap = (klaim.data ?? [])
            .filter((k) => k.status === 'disetujui' && !k.penugasan)
            .map((k) => ({ v: k.id, l: `#${k.id} · ${k.donasi?.judul ?? 'Donasi'} → ${k.penerima?.name ?? 'penerima'} (${k.jumlah_porsi} porsi)` }));
        state.opsi.relawan = (relawan.data ?? []).map((u) => ({ v: u.id, l: u.name }));
    } catch {
        /* pilihan kosong; galat akan terlihat saat menyimpan */
    }
}

const keInputLokal = (iso) => {
    if (!iso) return '';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '';
    const p = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
};

function htmlField(f, nilai, ubah) {
    if (f.sembunyiSaatBuat && !ubah) return '';
    if (f.sembunyiSaatUbah && ubah) return '';
    const id = `f-${f.k}`;
    const lebar = f.penuh ? 'sm:col-span-2' : '';
    const wajib = f.wajib ? ' <span class="text-rose-500">*</span>' : '';
    const galat = `<p class="field-error hidden" data-error-for="${f.k}"></p>`;

    if (f.tipe === 'checkbox') {
        const centang = ubah ? Boolean(nilai) : (f.bawaan ?? false);
        return `<div class="${lebar}"><label class="flex cursor-pointer items-center gap-3 rounded-xl border border-ink/10 px-4 py-3 text-sm font-medium transition hover:border-brand-300">
            <input type="checkbox" id="${id}" name="${f.k}" class="size-4 accent-brand-600" ${centang ? 'checked' : ''}>${esc(f.l)}</label>${galat}</div>`;
    }

    let input;
    if (f.tipe === 'textarea') {
        input = `<textarea id="${id}" name="${f.k}" rows="3" class="field resize-none" placeholder="${esc(f.ph ?? '')}">${esc(nilai ?? '')}</textarea>`;
    } else if (f.tipe === 'select') {
        const daftar = Array.isArray(f.opsi) ? f.opsi : (state.opsi[f.opsi] ?? []);
        const placeholder = f.wajib && ubah && Array.isArray(f.opsi) ? '' : `<option value="">${esc(f.kosong ?? 'Pilih…')}</option>`;
        input = `<select id="${id}" name="${f.k}" class="field">${placeholder}${daftar
            .map((o) => `<option value="${esc(o.v)}" ${String(o.v) === String(nilai ?? '') ? 'selected' : ''} class="capitalize">${esc(o.l)}</option>`)
            .join('')}</select>`;
    } else {
        const tipe = f.tipe === 'datetime' ? 'datetime-local' : f.tipe;
        const nilaiAman = f.tipe === 'datetime' ? keInputLokal(nilai) : (nilai ?? '');
        input = `<input id="${id}" name="${f.k}" type="${tipe}" value="${esc(nilaiAman)}" class="field" placeholder="${esc(f.ph ?? '')}" ${f.min !== undefined ? `min="${f.min}"` : ''} ${f.step ? `step="${f.step}"` : ''}>`;
    }

    return `<div class="${lebar}"><label for="${id}" class="label">${esc(f.l)}${wajib}</label>${input}${galat}</div>`;
}

async function bukaForm(baris = null, awal = {}) {
    const r = res();
    state.sedangUbah = baris;
    await pastikanOpsi();
    if (state.kunci === 'penugasan') await muatOpsiPenugasan();

    $('form-judul').textContent = `${baris ? 'Ubah' : 'Tambah'} ${r.judul.toLowerCase()}`;
    $('form-alert').classList.add('hidden');
    $('form-isi').innerHTML = r.form.map((f) => htmlField(f, baris ? baris[f.k] : awal[f.k], Boolean(baris))).join('');
    bukaModal('modal-form');
}

function ambilPayload(form) {
    const payload = {};
    for (const f of res().form) {
        const el = form.elements[f.k];
        if (!el) continue; // bidang disembunyikan saat tambah
        if (f.tipe === 'checkbox') {
            payload[f.k] = el.checked;
            continue;
        }
        let v = el.value.trim();
        if (v === '') {
            if (f.nullable) payload[f.k] = null;
            continue; // bidang wajib kosong dibiarkan agar divalidasi server
        }
        if (f.tipe === 'number') v = Number(v);
        if (f.tipe === 'datetime') v = new Date(v).toISOString();
        payload[f.k] = v;
    }
    return payload;
}

function pasangForm() {
    pasangModal('modal-form');
    $('btn-tambah').addEventListener('click', () => bukaForm());

    const form = $('form-crud');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const r = res();
        const tombol = form.querySelector('[data-submit]');
        const alertEl = $('form-alert');
        alertEl.classList.add('hidden');
        tampilkanGalatField(form, null);
        setMemuat(tombol, true, 'Menyimpan…');

        try {
            const ubah = state.sedangUbah;
            await api(ubah ? r.ubah(ubah.id) : r.buat, { method: ubah ? 'PUT' : 'POST', body: ambilPayload(form) });
            tutupModal('modal-form');
            toast(ubah ? 'Perubahan berhasil disimpan' : 'Data berhasil ditambahkan', 'sukses');
            await muatData();
        } catch (err) {
            alertEl.textContent = err.message;
            alertEl.classList.remove('hidden');
            if (err instanceof GalatApi && err.galat) tampilkanGalatField(form, err.galat);
        } finally {
            setMemuat(tombol, false);
        }
    });
}

// ---------- Hapus ----------
function pasangHapus() {
    pasangModal('modal-hapus');
    $('hapus-ikon').innerHTML = ikon('trash', 'size-6');

    $('hapus-ya').addEventListener('click', async () => {
        const tombol = $('hapus-ya');
        const { r, baris } = state.akanHapus;
        setMemuat(tombol, true, 'Menghapus…');
        try {
            await api(r.hapus(baris.id), { method: 'DELETE' });
            tutupModal('modal-hapus');
            toast('Data berhasil dihapus', 'sukses');
            await muatData();
        } catch (err) {
            tutupModal('modal-hapus');
            toast(err.message, 'galat');
        } finally {
            setMemuat(tombol, false);
        }
    });
}

// Setujui / tolak klaim dengan satu klik
async function ubahStatusCepat(r, baris, tombol) {
    const status = tombol.dataset.status;
    if (status === 'ditolak' && !window.confirm(`Tolak klaim #${baris.id}? Porsi akan dikembalikan ke donasi.`)) return;

    tombol.disabled = true;
    try {
        await api(r.ubah(baris.id), { method: 'PUT', body: { status } });
        toast(`Klaim #${baris.id} ${status}`, 'sukses');
        await muatData();
    } catch (err) {
        toast(err.message, 'galat');
        tombol.disabled = false;
    }
}

// ---------- Inisialisasi ----------
export default async function admin() {
    const user = wajibLogin();
    if (!user) return;
    if (user.role !== 'admin') {
        window.location.replace('/dasbor');
        return;
    }
    state.user = user;

    $('admin-nama').textContent = user.name;
    $('admin-avatar').textContent = inisial(user.name);

    renderNav();
    pasangForm();
    pasangHapus();

        // Aksi pada tabel (delegasi event)
    $('isi').addEventListener('click', (e) => {
        const tombol = e.target.closest('[data-aksi]');
        if (!tombol) return;
        const r = res();
        const baris = state.baris.find((b) => String(b.id) === tombol.dataset.id);
        if (!baris) return;

        const aksi = tombol.dataset.aksi;
        if (aksi === 'ubah') {
            bukaForm(baris);
        } else if (aksi === 'hapus') {
            state.akanHapus = { r, baris };
            $('hapus-ket').textContent = `“${r.label(baris)}” akan dihapus permanen dan tidak dapat dikembalikan.`;
            bukaModal('modal-hapus');
        } else if (aksi === 'status') {
            ubahStatusCepat(r, baris, tombol);
        } else if (aksi === 'tugaskan') {
            state.awalForm = { klaim_id: baris.id };
            location.hash = '#penugasan';
        }
    });

    $('cari').addEventListener('input', (e) => {
        state.q = e.target.value;
        state.halaman = 1;
        renderTabel();
    });

    $('paging').addEventListener('click', (e) => {
        const tombol = e.target.closest('[data-hal]');
        if (!tombol || tombol.disabled) return;
        state.halaman = Number(tombol.dataset.hal);
        if (res().server) muatData();
        else renderTabel();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    const keluar = async () => {
        try {
            await api('/auth/logout', { method: 'POST' });
        } catch {
            /* tetap keluar walau token sudah tidak valid */
        }
        sesi.hapus();
        window.location.href = '/masuk';
    };
    $('btn-logout').addEventListener('click', keluar);
    $('btn-logout-m').addEventListener('click', keluar);

    window.addEventListener('hashchange', tampil);
    tampil();
}
