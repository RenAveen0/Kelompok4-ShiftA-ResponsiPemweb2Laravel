@extends('layouts.app')

@section('title', 'Dasbor')
@section('page', 'dasbor')

@section('content')
<div class="min-h-screen bg-linear-to-b from-brand-50/70 to-white">

    {{-- ============ TOPBAR ============ --}}
    <header class="sticky top-0 z-40 border-b border-ink/5 bg-white/80 backdrop-blur-xl">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3 sm:px-6">
            @include('partials.logo')

            <div class="flex items-center gap-3">
                <span id="live" class="badge hidden bg-ink/5 text-ink/60 sm:inline-flex" title="Status koneksi realtime">
                    <span id="live-dot" class="size-2 rounded-full bg-ink/30"></span>
                    <span id="live-teks">Offline</span>
                </span>

                <a id="link-admin" href="{{ route('admin') }}" class="btn btn-soft hidden !py-2">
                    @include('partials.icon', ['name' => 'shield', 'class' => 'size-4'])
                    Panel Admin
                </a>

                <div class="flex items-center gap-2.5 rounded-full border border-ink/5 bg-white py-1 pr-3 pl-1 shadow-card">
                    <span id="user-avatar" class="grid size-8 place-items-center rounded-full bg-linear-to-br from-brand-500 to-brand-700 text-xs font-bold text-white">?</span>
                    <div class="hidden leading-tight sm:block">
                        <p id="user-nama" class="max-w-32 truncate text-sm font-semibold">…</p>
                        <p id="user-role" class="text-[11px] font-medium text-ink/50 capitalize">…</p>
                    </div>
                </div>

                <button id="btn-logout" class="btn btn-ghost !p-2.5" aria-label="Keluar" title="Keluar">
                    @include('partials.icon', ['name' => 'logout', 'class' => 'size-5'])
                </button>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-10">

        {{-- ============ SAPAAN ============ --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-brand-700" id="salam-kecil">Dasbor</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight sm:text-4xl" id="salam">Halo 👋</h1>
                <p class="mt-2 max-w-xl text-ink/60" id="salam-ket"></p>
            </div>
            <button id="btn-buat" class="btn btn-primary btn-lg hidden">
                @include('partials.icon', ['name' => 'plus', 'class' => 'size-5'])
                Bagikan donasi
            </button>
        </div>

        {{-- ============ KARTU RINGKASAN ============ --}}
        <section id="ringkasan" class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Ringkasan">
            @for ($i = 0; $i < 4; $i++)
                <div class="skeleton h-28"></div>
            @endfor
        </section>

        {{-- ============ TAB ============ --}}
        <div class="mt-10 flex flex-wrap items-center gap-2 rounded-full border border-ink/5 bg-white p-1.5 shadow-card sm:inline-flex" role="tablist">
            <button class="tab" role="tab" data-tab="donasi" aria-selected="true">
                @include('partials.icon', ['name' => 'gift', 'class' => 'size-4'])
                Donasi tersedia
            </button>
            <button class="tab hidden" role="tab" data-tab="klaim" aria-selected="false">
                @include('partials.icon', ['name' => 'inbox', 'class' => 'size-4'])
                <span id="label-klaim">Klaim saya</span>
            </button>
            <button class="tab hidden" role="tab" data-tab="statistik" aria-selected="false">
                @include('partials.icon', ['name' => 'chart', 'class' => 'size-4'])
                Statistik
            </button>
        </div>

        {{-- ---------- Tab: Donasi ---------- --}}
        <section id="tab-donasi" class="mt-6" role="tabpanel">
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-4 grid place-items-center text-ink/40">
                        @include('partials.icon', ['name' => 'search', 'class' => 'size-5'])
                    </span>
                    <input id="cari" type="search" placeholder="Cari judul atau lokasi donasi…" class="field !pl-12" aria-label="Cari donasi">
                </div>
                <select id="filter-kategori" class="field sm:w-60" aria-label="Filter kategori">
                    <option value="">Semua kategori</option>
                </select>
            </div>

            <div id="grid-donasi" class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3"></div>

            <nav id="paging" class="mt-8 flex items-center justify-center gap-3" aria-label="Halaman"></nav>
        </section>

        {{-- ---------- Tab: Klaim ---------- --}}
        <section id="tab-klaim" class="mt-6 hidden" role="tabpanel">
            <div id="list-klaim" class="grid gap-4"></div>
        </section>

        {{-- ---------- Tab: Statistik (admin) ---------- --}}
        <section id="tab-statistik" class="mt-6 hidden" role="tabpanel">
            <div id="isi-statistik"></div>
        </section>
    </main>
</div>

{{-- ============ MODAL: BUAT DONASI ============ --}}
<div id="modal-buat" class="fixed inset-0 z-[90] hidden items-end justify-center bg-ink/50 p-0 backdrop-blur-sm sm:items-center sm:p-6" role="dialog" aria-modal="true" aria-labelledby="judul-buat">
    <div class="max-h-[92vh] w-full max-w-xl animate-pop overflow-y-auto rounded-t-3xl bg-white p-6 shadow-float sm:rounded-3xl sm:p-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="judul-buat" class="text-2xl font-extrabold tracking-tight">Bagikan donasi makanan</h2>
                <p class="mt-1 text-sm text-ink/60">Penerima akan langsung melihat donasi Anda.</p>
            </div>
            <button type="button" data-tutup class="btn btn-ghost !p-2" aria-label="Tutup">@include('partials.icon', ['name' => 'x', 'class' => 'size-5'])</button>
        </div>

        <div id="alert-buat" class="mt-5 hidden rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700" role="alert"></div>

        <form id="form-buat" class="mt-6 grid gap-4 sm:grid-cols-2" novalidate>
            <div class="sm:col-span-2">
                <label class="label" for="judul">Judul donasi</label>
                <input id="judul" name="judul" type="text" maxlength="150" required placeholder="mis. Nasi kotak ayam bakar" class="field">
                <p class="field-error hidden" data-error-for="judul"></p>
            </div>

            <div>
                <label class="label" for="kategori_id">Kategori</label>
                <select id="kategori_id" name="kategori_id" required class="field"></select>
                <p class="field-error hidden" data-error-for="kategori_id"></p>
            </div>

            <div>
                <label class="label" for="porsi">Jumlah porsi</label>
                <input id="porsi" name="porsi" type="number" min="1" required placeholder="10" class="field">
                <p class="field-error hidden" data-error-for="porsi"></p>
            </div>

            <div class="sm:col-span-2">
                <label class="label" for="lokasi">Lokasi pengambilan</label>
                <input id="lokasi" name="lokasi" type="text" required placeholder="Alamat atau patokan lokasi" class="field">
                <p class="field-error hidden" data-error-for="lokasi"></p>
            </div>

            <div>
                <label class="label" for="titik_lokasi_id">Titik penjemputan <span class="font-normal text-ink/40">(opsional)</span></label>
                <select id="titik_lokasi_id" name="titik_lokasi_id" class="field"></select>
                <p class="field-error hidden" data-error-for="titik_lokasi_id"></p>
            </div>

            <div>
                <label class="label" for="batas_waktu">Batas waktu <span class="font-normal text-ink/40">(opsional)</span></label>
                <input id="batas_waktu" name="batas_waktu" type="datetime-local" class="field">
                <p class="field-error hidden" data-error-for="batas_waktu"></p>
            </div>

            <div class="sm:col-span-2">
                <label class="label" for="deskripsi">Deskripsi <span class="font-normal text-ink/40">(opsional)</span></label>
                <textarea id="deskripsi" name="deskripsi" rows="3" placeholder="Menu, bahan, atau catatan penyimpanan" class="field resize-none"></textarea>
                <p class="field-error hidden" data-error-for="deskripsi"></p>
            </div>

            <div class="mt-2 flex justify-end gap-3 sm:col-span-2">
                <button type="button" data-tutup class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary" data-submit><span data-label>Publikasikan</span></button>
            </div>
        </form>
    </div>
</div>

{{-- ============ MODAL: KLAIM DONASI ============ --}}
<div id="modal-klaim" class="fixed inset-0 z-[90] hidden items-end justify-center bg-ink/50 p-0 backdrop-blur-sm sm:items-center sm:p-6" role="dialog" aria-modal="true" aria-labelledby="judul-klaim">
    <div class="w-full max-w-md animate-pop rounded-t-3xl bg-white p-6 shadow-float sm:rounded-3xl sm:p-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="judul-klaim" class="text-2xl font-extrabold tracking-tight">Klaim donasi</h2>
                <p id="klaim-nama" class="mt-1 text-sm text-ink/60"></p>
            </div>
            <button type="button" data-tutup class="btn btn-ghost !p-2" aria-label="Tutup">@include('partials.icon', ['name' => 'x', 'class' => 'size-5'])</button>
        </div>

        <div id="alert-klaim" class="mt-5 hidden rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700" role="alert"></div>

        <form id="form-klaim" class="mt-6 space-y-5" novalidate>
            <input type="hidden" name="donasi_id">
            <div>
                <label class="label" for="jumlah_porsi">Jumlah porsi</label>
                <div class="flex items-center gap-3">
                    <button type="button" id="porsi-kurang" class="btn btn-outline !px-4" aria-label="Kurangi">−</button>
                    <input id="jumlah_porsi" name="jumlah_porsi" type="number" min="1" value="1" required class="field text-center text-lg font-bold">
                    <button type="button" id="porsi-tambah" class="btn btn-outline !px-4" aria-label="Tambah">+</button>
                </div>
                <p id="klaim-sisa" class="mt-2 text-xs font-medium text-ink/50"></p>
                <p class="field-error hidden" data-error-for="jumlah_porsi"></p>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" data-tutup class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary" data-submit><span data-label>Ajukan klaim</span></button>
            </div>
        </form>
    </div>
</div>
@endsection
