// Dasbor: daftar donasi, klaim, buat donasi, statistik admin, dan pembaruan realtime (Reverb).

import { api, sesi, wajibLogin, GalatApi } from '../lib/api';
import { ikon } from '../lib/ikon';
import { esc, toast, bukaModal, tutupModal, pasangModal, setMemuat, tampilkanGalatField, formatTanggal, sisaWaktu, inisial } from '../lib/ui';

// Kelas Tailwind ditulis utuh supaya terdeteksi saat build.
const WARNA_STAT = {
    brand: 'bg-brand-50 text-brand-600',
    sun: 'bg-sun-100 text-sun-600',
    ink: 'bg-ink/5 text-ink/70',
};

const BADGE_KLAIM = {
    diajukan: 'bg-sun-100 text-sun-600',
    disetujui: 'bg-brand-100 text-brand-700',
    ditolak: 'bg-rose-50 text-rose-700',
    dibatalkan: 'bg-rose-50 text-rose-700',
    ditugaskan: 'bg-sun-100 text-sun-600',
    menuju_lokasi: 'bg-sky-100 text-sky-700',
    diambil: 'bg-sky-100 text-sky-700',
    diambil_relawan: 'bg-sky-100 text-sky-700',
    diantar: 'bg-sky-100 text-sky-700',
    selesai: 'bg-brand-600 text-white',
};

const SAPAAN_PERAN = {
    donatur: 'Ada makanan berlebih? Bagikan sekarang dan bantu sesama.',
    penerima: 'Temukan donasi makanan yang tersedia dan klaim sebelum kehabisan.',
    relawan: 'Pantau donasi yang tersedia dan siap membantu pengantaran.',
    admin: 'Pantau aktivitas dan dampak platform FoodRescue.',
};

// ---------- State ----------
const state = {
    user: null,
    tab: 'donasi',
    halaman: 1,
    meta: { total: 0, last_page: 1 },
    donasi: [],
    kategori: [],
    titik: [],
    klaim: [],
    tersedia: [],
    tugas: [],
    stat: null,
    q: '',
    kategoriId: '',
    live: 'off',
};

const $ = (id) => document.getElementById(id);
const adalah = (...peran) => peran.includes(state.user.role);

// ---------- Header & sapaan ----------
function renderHeader() {
    const u = state.user;
    $('user-nama').textContent = u.name;
    $('user-role').textContent = u.role;
    $('user-avatar').textContent = inisial(u.name);

    const jam = new Date().getHours();
    const waktu = jam < 11 ? 'pagi' : jam < 15 ? 'siang' : jam < 18 ? 'sore' : 'malam';
    $('salam-kecil').textContent = `Selamat ${waktu}`;
    $('salam').textContent = `Halo, ${u.name.split(' ')[0]} 👋`;
    $('salam-ket').textContent = SAPAAN_PERAN[u.role] ?? '';

    $('btn-buat').classList.toggle('hidden', !adalah('donatur', 'admin'));
    $('link-admin').classList.toggle('hidden', !adalah('admin'));
    document.querySelector('[data-tab="klaim"]').classList.toggle('hidden', !adalah('penerima', 'admin'));
    document.querySelector('[data-tab="tugas"]').classList.toggle('hidden', !adalah('relawan'));
    document.querySelector('[data-tab="statistik"]').classList.toggle('hidden', !adalah('admin'));
    $('label-klaim').textContent = adalah('admin') ? 'Semua klaim' : 'Klaim saya';
}

// ---------- Ringkasan ----------
function kartuStat({ label, nilai, ikonNama, warna = 'brand' }) {
    return `
        <div class="card flex items-center gap-4 p-5 animate-pop">
            <span class="grid size-12 shrink-0 place-items-center rounded-xl ${WARNA_STAT[warna]}">${ikon(ikonNama, 'size-6')}</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-medium text-ink/55">${esc(label)}</p>
                <p class="mt-0.5 text-2xl font-extrabold tracking-tight">${esc(nilai)}</p>
            </div>
        </div>`;
}

function renderRingkasan() {
    let kartu = [];

    if (adalah('admin') && state.stat) {
        const s = state.stat;
        kartu = [
            { label: 'Porsi diselamatkan', nilai: Number(s.porsi_diselamatkan ?? 0).toLocaleString('id-ID'), ikonNama: 'leaf' },
            { label: 'Klaim selesai', nilai: Number(s.klaim_selesai ?? 0).toLocaleString('id-ID'), ikonNama: 'check' },
            { label: 'Donasi aktif', nilai: Number(s.donasi_aktif ?? 0).toLocaleString('id-ID'), ikonNama: 'gift', warna: 'sun' },
            { label: 'Rata-rata rating', nilai: Number(s.rata_rata_rating ?? 0).toFixed(1), ikonNama: 'star', warna: 'sun' },
        ];
    } else if (adalah('penerima')) {
        const milik = klaimSaya();
        kartu = [
            { label: 'Donasi tersedia', nilai: state.meta.total, ikonNama: 'gift' },
            { label: 'Klaim diajukan', nilai: milik.filter((k) => k.status === 'diajukan').length, ikonNama: 'clock', warna: 'sun' },
            { label: 'Klaim selesai', nilai: milik.filter((k) => k.status === 'selesai').length, ikonNama: 'check' },
        ];
    } else if (!adalah('admin')) {
        kartu = [
            { label: 'Donasi tersedia', nilai: state.meta.total, ikonNama: 'gift' },
            { label: 'Peran Anda', nilai: state.user.role.charAt(0).toUpperCase() + state.user.role.slice(1), ikonNama: 'users', warna: 'ink' },
            { label: 'Realtime', nilai: state.live === 'on' ? 'Aktif' : 'Offline', ikonNama: 'bolt', warna: 'sun' },
        ];
    }

    if (!kartu.length) return;
    const el = $('ringkasan');
    el.classList.toggle('lg:grid-cols-3', kartu.length === 3);
    el.classList.toggle('lg:grid-cols-4', kartu.length === 4);
    el.innerHTML = kartu.map(kartuStat).join('');
}

// ---------- Tab ----------
function pilihTab(nama) {
    state.tab = nama;
    document.querySelectorAll('[data-tab]').forEach((t) => t.setAttribute('aria-selected', String(t.dataset.tab === nama)));
        ['donasi', 'klaim', 'tugas', 'statistik'].forEach((n) => $(`tab-${n}`).classList.toggle('hidden', n !== nama));
}

// ---------- Donasi ----------
function kerangkaDonasi() {
    $('grid-donasi').innerHTML = Array.from({ length: 6 }, () => '<div class="skeleton h-64"></div>').join('');
}

function donasiTersaring() {
    const q = state.q.trim().toLowerCase();
    return state.donasi.filter((d) => {
        const cocokKategori = !state.kategoriId || String(d.kategori_id) === state.kategoriId;
        const cocokTeks = !q || [d.judul, d.lokasi, d.deskripsi].some((t) => String(t ?? '').toLowerCase().includes(q));
        return cocokKategori && cocokTeks;
    });
}

function kartuDonasi(d) {
    const persen = d.porsi ? Math.round((d.porsi_tersisa / d.porsi) * 100) : 0;
    const waktu = sisaWaktu(d.batas_waktu);
    const milikSendiri = d.user_id === state.user.id;
    const bisaKlaim = adalah('penerima') && !waktu.lewat && d.porsi_tersisa > 0;
    const warnaWaktu = waktu.mendesak ? 'text-rose-600' : 'text-ink/55';

    let aksi = '';
    if (bisaKlaim) {
        aksi = `<button class="btn btn-primary !py-2" data-klaim="${d.id}">Klaim</button>`;
    } else if (milikSendiri) {
        aksi = '<span class="badge bg-brand-50 text-brand-700">Donasi Anda</span>';
    }

    return `
        <article class="card group flex flex-col p-5 transition duration-300 hover:-translate-y-1 hover:border-brand-200 animate-pop">
            <div class="flex items-center justify-between gap-2">
                <span class="badge bg-brand-50 text-brand-700">${esc(d.kategori?.nama ?? 'Makanan')}</span>
                <span class="inline-flex items-center gap-1 text-xs font-semibold ${warnaWaktu}">${ikon('clock', 'size-3.5')}${esc(waktu.teks)}</span>
            </div>

            <h3 class="mt-4 line-clamp-2 text-lg leading-snug font-bold">${esc(d.judul)}</h3>
            ${d.deskripsi ? `<p class="mt-1.5 line-clamp-2 text-sm text-ink/55">${esc(d.deskripsi)}</p>` : ''}

            <p class="mt-3 flex items-start gap-1.5 text-sm text-ink/65">
                <span class="mt-0.5 shrink-0 text-brand-600">${ikon('map', 'size-4')}</span>
                <span class="line-clamp-1">${esc(d.titik_lokasi?.nama ? `${d.titik_lokasi.nama} · ${d.lokasi}` : d.lokasi)}</span>
            </p>

            <div class="mt-5">
                <div class="flex items-center justify-between text-xs font-medium text-ink/55">
                    <span>Sisa porsi</span>
                    <span class="font-bold text-ink">${d.porsi_tersisa} / ${d.porsi}</span>
                </div>
                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-ink/5">
                    <div class="h-full rounded-full bg-linear-to-r from-brand-500 to-brand-400 transition-all duration-700" style="width:${persen}%"></div>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-between gap-3 border-t border-ink/5 pt-4">
                <div class="flex min-w-0 items-center gap-2.5">
                    <span class="grid size-8 shrink-0 place-items-center rounded-full bg-brand-100 text-xs font-bold text-brand-700">${esc(inisial(d.user?.name))}</span>
                    <span class="truncate text-sm font-medium text-ink/70">${esc(d.user?.name ?? 'Donatur')}</span>
                </div>
                ${aksi}
            </div>
        </article>`;
}

function renderDonasi() {
    const daftar = donasiTersaring();
    const grid = $('grid-donasi');

    if (!daftar.length) {
        grid.innerHTML = `
            <div class="card col-span-full grid place-items-center gap-3 px-6 py-16 text-center">
                <span class="grid size-14 place-items-center rounded-2xl bg-brand-50 text-brand-600">${ikon('gift', 'size-7')}</span>
                <p class="text-lg font-bold">Belum ada donasi yang cocok</p>
                <p class="max-w-sm text-sm text-ink/55">${state.q || state.kategoriId ? 'Coba ubah kata kunci atau filter kategori.' : 'Donasi baru akan muncul di sini secara realtime.'}</p>
            </div>`;
    } else {
        grid.innerHTML = daftar.map(kartuDonasi).join('');
    }

    renderPaging();
}

function renderPaging() {
    const { current_page = 1, last_page = 1 } = state.meta;
    const el = $('paging');
    if (last_page <= 1) {
        el.innerHTML = '';
        return;
    }
    el.innerHTML = `
        <button class="btn btn-outline" data-hal="${current_page - 1}" ${current_page <= 1 ? 'disabled' : ''}>← Sebelumnya</button>
        <span class="text-sm font-medium text-ink/60">Halaman ${current_page} dari ${last_page}</span>
        <button class="btn btn-outline" data-hal="${current_page + 1}" ${current_page >= last_page ? 'disabled' : ''}>Berikutnya →</button>`;
}

async function muatDonasi(halaman = state.halaman) {
    state.halaman = halaman;
    kerangkaDonasi();
    try {
        const res = await api(`/donasi-makanan?page=${halaman}`);
        const p = res.data;
        state.donasi = p.data ?? [];
        state.meta = { total: p.total ?? state.donasi.length, current_page: p.current_page ?? 1, last_page: p.last_page ?? 1 };
        renderDonasi();
        renderRingkasan();
    } catch (err) {
        $('grid-donasi').innerHTML = `<div class="card col-span-full p-10 text-center font-medium text-rose-600">${esc(err.message)}</div>`;
    }
}

// ---------- Klaim ----------
function klaimSaya() {
    return adalah('admin') ? state.klaim : state.klaim.filter((k) => k.penerima_id === state.user.id);
}

function renderKlaim() {
    const daftar = klaimSaya();
    const el = $('list-klaim');

    if (!daftar.length) {
        el.innerHTML = `
            <div class="card grid place-items-center gap-3 px-6 py-16 text-center">
                <span class="grid size-14 place-items-center rounded-2xl bg-brand-50 text-brand-600">${ikon('inbox', 'size-7')}</span>
                <p class="text-lg font-bold">Belum ada klaim</p>
                <p class="max-w-sm text-sm text-ink/55">Klaim donasi dari tab “Donasi tersedia” akan tercatat di sini.</p>
            </div>`;
        return;
    }

    el.innerHTML = daftar
        .map(
            (k) => `
        <article class="card flex flex-wrap items-center justify-between gap-4 p-5 animate-pop">
            <div class="flex min-w-0 items-center gap-4">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600">${ikon('gift', 'size-6')}</span>
                <div class="min-w-0">
                    <p class="truncate font-bold">${esc(k.donasi?.judul ?? `Donasi #${k.donasi_id}`)}</p>
                    <p class="mt-0.5 text-sm text-ink/55">${esc(k.jumlah_porsi)} porsi · ${esc(formatTanggal(k.created_at))}${adalah('admin') && k.penerima?.name ? ` · ${esc(k.penerima.name)}` : ''}</p>
                </div>
            </div>
            <span class="badge capitalize ${BADGE_KLAIM[k.status] ?? 'bg-ink/5 text-ink/60'}">${esc(k.status)}</span>
        </article>`,
        )
        .join('');
}

async function muatKlaim() {
    if (!adalah('penerima', 'admin')) return;
    try {
        const res = await api('/klaim-donasi');
        state.klaim = res.data ?? [];
        renderKlaim();
        renderRingkasan();
    } catch (err) {
        $('list-klaim').innerHTML = `<div class="card p-10 text-center font-medium text-rose-600">${esc(err.message)}</div>`;
    }
}

// ---------- Tugas relawan ----------
const LANJUT_TUGAS = {
    ditugaskan: { status: 'menuju_lokasi', label: 'Menuju lokasi' },
    menuju_lokasi: { status: 'diambil', label: 'Makanan sudah diambil' },
    diambil: { status: 'diantar', label: 'Sedang diantar' },
    diantar: { status: 'selesai', label: 'Selesai diantar' },
};

const kosongTugas = (teks) => `<div class="card px-6 py-10 text-center text-sm text-ink/55">${esc(teks)}</div>`;

function renderTugas() {
    $('list-tugas-tersedia').innerHTML = state.tersedia.length
        ? state.tersedia
              .map(
                  (k) => `
        <article class="card flex flex-wrap items-center justify-between gap-4 p-5 animate-pop">
            <div class="flex min-w-0 items-center gap-4">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600">${ikon('truck', 'size-6')}</span>
                <div class="min-w-0">
                    <p class="truncate font-bold">${esc(k.donasi?.judul ?? `Donasi #${k.donasi_id}`)}</p>
                    <p class="mt-0.5 text-sm text-ink/55">${esc(k.jumlah_porsi)} porsi · ambil di ${esc(k.donasi?.lokasi ?? '-')}</p>
                    <p class="text-sm text-ink/55">Antar ke ${esc(k.penerima?.name ?? '-')}${k.penerima?.alamat ? ` · ${esc(k.penerima.alamat)}` : ''}</p>
                </div>
            </div>
            <button class="btn btn-primary !py-2" data-ambil="${k.id}">Ambil tugas</button>
        </article>`,
              )
              .join('')
        : kosongTugas('Belum ada tugas yang tersedia.');

    $('list-tugas-saya').innerHTML = state.tugas.length
        ? state.tugas
              .map((p) => {
                  const d = p.klaim?.donasi;
                  const lanjut = LANJUT_TUGAS[p.status];
                  return `
        <article class="card flex flex-wrap items-center justify-between gap-4 p-5 animate-pop">
            <div class="flex min-w-0 items-center gap-4">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600">${ikon('truck', 'size-6')}</span>
                <div class="min-w-0">
                    <p class="truncate font-bold">${esc(d?.judul ?? `Klaim #${p.klaim_id}`)}</p>
                    <p class="mt-0.5 text-sm text-ink/55">Ambil di ${esc(d?.lokasi ?? '-')} · antar ke ${esc(p.klaim?.penerima?.name ?? '-')}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="badge capitalize ${BADGE_KLAIM[p.status] ?? 'bg-ink/5 text-ink/60'}">${esc(p.status.replaceAll('_', ' '))}</span>
                ${lanjut ? `<button class="btn btn-primary !py-2" data-lanjut="${p.id}" data-status="${lanjut.status}">${esc(lanjut.label)}</button>` : ''}
            </div>
        </article>`;
              })
              .join('')
        : kosongTugas('Anda belum mengambil tugas apa pun.');
}

async function muatTugas() {
    if (!adalah('relawan')) return;
    try {
        const [tersedia, saya] = await Promise.all([api('/klaim-donasi'), api('/penugasan-relawan')]);
        state.tersedia = tersedia.data ?? [];
        state.tugas = saya.data ?? [];
        renderTugas();
    } catch (err) {
        $('list-tugas-tersedia').innerHTML = `<div class="card p-10 text-center font-medium text-rose-600">${esc(err.message)}</div>`;
    }
}

function pasangTugas() {
    $('tab-tugas').addEventListener('click', async (e) => {
        const ambil = e.target.closest('[data-ambil]');
        const lanjut = e.target.closest('[data-lanjut]');
        const tombol = ambil ?? lanjut;
        if (!tombol) return;

        tombol.disabled = true;
        try {
            if (ambil) {
                await api('/penugasan-relawan', { method: 'POST', body: { klaim_id: Number(ambil.dataset.ambil) } });
                toast('Tugas berhasil diambil 🚚', 'sukses');
            } else {
                await api(`/penugasan-relawan/${lanjut.dataset.lanjut}`, { method: 'PUT', body: { status: lanjut.dataset.status } });
                toast('Status tugas diperbarui', 'sukses');
            }
        } catch (err) {
            toast(err.message, 'galat');
            tombol.disabled = false;
        }
        await muatTugas(); // selalu segarkan, mis. bila tugas sudah diambil relawan lain
    });
}

// ---------- Statistik admin ----------
function renderStatistik() {
    const s = state.stat;
    const el = $('isi-statistik');
    if (!s) return;

    const data = s.grafik_bulanan ?? [];
    const maks = Math.max(1, ...data.map((b) => Number(b.total_porsi)));

    const batang = data.length
        ? data
              .map((b) => {
                  const tinggi = Math.max(6, Math.round((Number(b.total_porsi) / maks) * 100));
                  const bulan = new Date(`${b.bulan}-01`).toLocaleDateString('id-ID', { month: 'short', year: '2-digit' });
                  return `
                    <div class="flex flex-1 flex-col items-center justify-end gap-2" title="${esc(b.total_porsi)} porsi">
                        <span class="text-xs font-bold">${esc(b.total_porsi)}</span>
                        <div class="w-full max-w-14 rounded-t-xl bg-linear-to-t from-brand-600 to-brand-400" style="height:${tinggi}%"></div>
                        <span class="text-xs font-medium text-ink/55">${esc(bulan)}</span>
                    </div>`;
              })
              .join('')
        : '<p class="m-auto text-sm text-ink/50">Belum ada data klaim selesai.</p>';

    el.innerHTML = `
        <div class="card p-6 sm:p-8">
            <h3 class="text-lg font-bold">Porsi diselamatkan per bulan</h3>
            <p class="mt-1 text-sm text-ink/55">Total porsi dari klaim berstatus selesai.</p>
            <div class="mt-8 flex h-64 items-end gap-3 sm:gap-5">${batang}</div>
        </div>`;
}

async function muatStatistik() {
    if (!adalah('admin')) return;
    try {
        const res = await api('/admin/dasbor');
        state.stat = res.data;
        renderStatistik();
        renderRingkasan();
    } catch (err) {
        toast(err.message, 'galat');
    }
}

// ---------- Data master ----------
async function muatMaster() {
    try {
        const [kat, titik] = await Promise.all([api('/kategori-makanan'), api('/titik-lokasi')]);
        state.kategori = kat.data ?? [];
        state.titik = (titik.data?.data ?? titik.data ?? []).filter((t) => t.is_aktif === undefined || Boolean(t.is_aktif));
    } catch {
        /* filter tetap berfungsi tanpa kategori */
    }

    const opsi = state.kategori.map((k) => `<option value="${k.id}">${esc(k.nama)}</option>`).join('');
    $('filter-kategori').innerHTML = `<option value="">Semua kategori</option>${opsi}`;
    $('kategori_id').innerHTML = `<option value="">Pilih kategori</option>${opsi}`;
    $('titik_lokasi_id').innerHTML =
        '<option value="">Tidak ada</option>' + state.titik.map((t) => `<option value="${t.id}">${esc(t.nama)}</option>`).join('');
}

// ---------- Modal: buat donasi ----------
function pasangFormBuat() {
    pasangModal('modal-buat');
    $('btn-buat').addEventListener('click', () => bukaModal('modal-buat'));

    const form = $('form-buat');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const tombol = form.querySelector('[data-submit]');
        const alertEl = $('alert-buat');
        alertEl.classList.add('hidden');
        tampilkanGalatField(form, null);

        const data = Object.fromEntries(new FormData(form));
        Object.keys(data).forEach((k) => data[k] === '' && delete data[k]);
        if (data.porsi) data.porsi = Number(data.porsi);
        if (data.batas_waktu) data.batas_waktu = new Date(data.batas_waktu).toISOString();

        setMemuat(tombol, true, 'Mempublikasikan…');
        try {
            await api('/donasi-makanan', { method: 'POST', body: data });
            tutupModal('modal-buat');
            form.reset();
            toast('Donasi berhasil dipublikasikan 🎉', 'sukses');
            pilihTab('donasi');
            muatDonasi(1);
        } catch (err) {
            alertEl.textContent = err.message;
            alertEl.classList.remove('hidden');
            if (err instanceof GalatApi && err.galat) tampilkanGalatField(form, err.galat);
        } finally {
            setMemuat(tombol, false);
        }
    });
}

// ---------- Modal: klaim ----------
function pasangFormKlaim() {
    pasangModal('modal-klaim');
    const form = $('form-klaim');
    const input = $('jumlah_porsi');

    const atur = (n) => {
        const maks = Number(input.max) || 1;
        input.value = Math.min(Math.max(1, n), maks);
    };
    $('porsi-kurang').addEventListener('click', () => atur(Number(input.value) - 1));
    $('porsi-tambah').addEventListener('click', () => atur(Number(input.value) + 1));
    input.addEventListener('change', () => atur(Number(input.value)));

    $('grid-donasi').addEventListener('click', (e) => {
        const tombol = e.target.closest('[data-klaim]');
        if (!tombol) return;
        const d = state.donasi.find((x) => String(x.id) === tombol.dataset.klaim);
        if (!d) return;

        form.donasi_id.value = d.id;
        input.max = d.porsi_tersisa;
        input.value = 1;
        $('klaim-nama').textContent = d.judul;
        $('klaim-sisa').textContent = `Tersisa ${d.porsi_tersisa} dari ${d.porsi} porsi`;
        $('alert-klaim').classList.add('hidden');
        tampilkanGalatField(form, null);
        bukaModal('modal-klaim');
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const tombol = form.querySelector('[data-submit]');
        const alertEl = $('alert-klaim');
        alertEl.classList.add('hidden');
        tampilkanGalatField(form, null);
        setMemuat(tombol, true, 'Mengajukan…');

        try {
            await api('/klaim-donasi', {
                method: 'POST',
                body: { donasi_id: Number(form.donasi_id.value), jumlah_porsi: Number(input.value) },
            });
            tutupModal('modal-klaim');
            toast('Klaim berhasil diajukan ✅', 'sukses');
            await Promise.all([muatDonasi(), muatKlaim()]);
        } catch (err) {
            alertEl.textContent = err.message;
            alertEl.classList.remove('hidden');
            if (err instanceof GalatApi && err.galat) tampilkanGalatField(form, err.galat);
        } finally {
            setMemuat(tombol, false);
        }
    });
}

// ---------- Realtime (Laravel Reverb) ----------
function aturLive(status) {
    state.live = status;
    const aktif = status === 'on';
    $('live').classList.remove('hidden');
    $('live').className = `badge sm:inline-flex ${aktif ? 'bg-brand-50 text-brand-700' : 'bg-ink/5 text-ink/60'}`;
    $('live-dot').className = `size-2 rounded-full ${aktif ? 'bg-brand-500 animate-pulse' : 'bg-ink/30'}`;
    $('live-teks').textContent = aktif ? 'Realtime' : 'Offline';
    if (!adalah('admin', 'penerima')) renderRingkasan();
}

async function mulaiRealtime() {
    // Tanpa konfigurasi Reverb di .env, dasbor tetap berfungsi (tanpa pembaruan otomatis).
    if (!import.meta.env.VITE_REVERB_APP_KEY) {
        aturLive('off');
        return;
    }
    try {
        await import('../echo');
        const koneksi = window.Echo.connector?.pusher?.connection;
        koneksi?.bind('connected', () => aturLive('on'));
        koneksi?.bind('disconnected', () => aturLive('off'));
        koneksi?.bind('unavailable', () => aturLive('off'));
        if (koneksi?.state === 'connected') aturLive('on');

        window.Echo.channel('donasi-baru').listen('.donasi.dibuat', (e) => {
            toast(`Donasi baru: ${e.donasi?.judul ?? 'makanan tersedia'}`, 'info');
            if (state.halaman === 1) muatDonasi(1);
        });
    } catch {
        aturLive('off');
    }
}

// ---------- Inisialisasi ----------
export default async function dasbor() {
    const user = wajibLogin();
    if (!user) return;
    state.user = user;

    renderHeader();
    pasangFormBuat();
    pasangFormKlaim();

    document.querySelectorAll('[data-tab]').forEach((t) => t.addEventListener('click', () => pilihTab(t.dataset.tab)));
    $('cari').addEventListener('input', (e) => {
        state.q = e.target.value;
        renderDonasi();
    });
    $('filter-kategori').addEventListener('change', (e) => {
        state.kategoriId = e.target.value;
        renderDonasi();
    });
    $('paging').addEventListener('click', (e) => {
        const tombol = e.target.closest('[data-hal]');
        if (tombol && !tombol.disabled) {
            muatDonasi(Number(tombol.dataset.hal));
            window.scrollTo({ top: $('tab-donasi').offsetTop - 120, behavior: 'smooth' });
        }
    });

    $('btn-logout').addEventListener('click', async () => {
        try {
            await api('/auth/logout', { method: 'POST' });
        } catch {
            /* tetap keluar walau token sudah tidak valid */
        }
        sesi.hapus();
        window.location.href = '/masuk';
    });

    // Sinkronkan profil terbaru, lalu muat data paralel
    api('/user/profil')
        .then((res) => {
            sesi.simpanUser(res.data);
            state.user = res.data;
            renderHeader();
        })
        .catch(() => {});

    mulaiRealtime();
    await muatMaster();
    await Promise.all([muatDonasi(1), muatKlaim(), muatTugas(), muatStatistik()]);
}
