@extends('layouts.app')

@section('title', 'Panel Admin')
@section('page', 'admin')

@section('content')
<div class="min-h-screen bg-brand-50/40 lg:grid lg:grid-cols-[17rem_1fr]">

    {{-- ============ SIDEBAR (desktop) ============ --}}
    <aside class="relative hidden overflow-hidden bg-brand-950 text-white lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col lg:p-5">
        <div class="pointer-events-none absolute -top-20 -left-20 size-64 rounded-full bg-brand-500/25 blur-3xl"></div>

        <div class="relative px-2 pt-1">@include('partials.logo', ['light' => true])</div>
        <p class="relative mt-1 px-2 text-xs font-medium tracking-wide text-brand-300/80 uppercase">Panel Admin</p>

        <nav id="nav-desktop" class="relative mt-6 flex-1 space-y-1 overflow-y-auto pr-1" aria-label="Menu admin"></nav>

        <div class="relative mt-4 flex items-center gap-3 rounded-2xl bg-white/5 p-3">
            <span id="admin-avatar" class="grid size-10 shrink-0 place-items-center rounded-full bg-linear-to-br from-brand-400 to-brand-600 text-sm font-bold">A</span>
            <div class="min-w-0 flex-1 leading-tight">
                <p id="admin-nama" class="truncate text-sm font-semibold">…</p>
                <p class="text-xs text-white/50">Administrator</p>
            </div>
            <button id="btn-logout" class="grid size-9 place-items-center rounded-lg text-white/60 transition hover:bg-white/10 hover:text-white" aria-label="Keluar" title="Keluar">
                @include('partials.icon', ['name' => 'logout', 'class' => 'size-5'])
            </button>
        </div>
    </aside>

    <div class="min-w-0">
        {{-- ============ TOPBAR (mobile) ============ --}}
        <header class="sticky top-0 z-30 border-b border-ink/5 bg-white/85 backdrop-blur-xl lg:hidden">
            <div class="flex items-center justify-between px-4 py-3">
                @include('partials.logo')
                <div class="flex items-center gap-1">
                    <a href="{{ route('dasbor') }}" class="btn btn-ghost !px-3 !py-2 text-xs">Dasbor</a>
                    <button id="btn-logout-m" class="btn btn-ghost !p-2.5" aria-label="Keluar">@include('partials.icon', ['name' => 'logout', 'class' => 'size-5'])</button>
                </div>
            </div>
            <nav id="nav-mobile" class="flex gap-2 overflow-x-auto px-4 pb-3" aria-label="Menu admin"></nav>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:py-10">
            {{-- Judul bagian --}}
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-brand-700" id="bagian-kecil">Panel Admin</p>
                    <h1 class="mt-1 text-3xl font-extrabold tracking-tight" id="bagian-judul">Ringkasan</h1>
                    <p class="mt-1.5 max-w-xl text-sm text-ink/60" id="bagian-ket"></p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('dasbor') }}" class="btn btn-outline hidden lg:inline-flex">Lihat sebagai pengguna</a>
                    <button id="btn-tambah" class="btn btn-primary hidden">
                        @include('partials.icon', ['name' => 'plus', 'class' => 'size-5'])
                        Tambah data
                    </button>
                </div>
            </div>

            {{-- Pencarian (untuk bagian berupa tabel) --}}
            <div id="bar-cari" class="relative mt-6 hidden max-w-md">
                <span class="pointer-events-none absolute inset-y-0 left-4 grid place-items-center text-ink/40">
                    @include('partials.icon', ['name' => 'search', 'class' => 'size-5'])
                </span>
                <input id="cari" type="search" placeholder="Cari di tabel ini…" class="field !pl-12" aria-label="Cari data">
            </div>

            <div id="isi" class="mt-6"></div>
            <nav id="paging" class="mt-6 flex items-center justify-center gap-3" aria-label="Halaman"></nav>
        </main>
    </div>
</div>

{{-- ============ MODAL: FORM TAMBAH / UBAH ============ --}}
<div id="modal-form" class="fixed inset-0 z-[90] hidden items-end justify-center bg-ink/50 p-0 backdrop-blur-sm sm:items-center sm:p-6" role="dialog" aria-modal="true" aria-labelledby="form-judul">
    <div class="max-h-[92vh] w-full max-w-xl animate-pop overflow-y-auto rounded-t-3xl bg-white p-6 shadow-float sm:rounded-3xl sm:p-8">
        <div class="flex items-start justify-between gap-4">
            <h2 id="form-judul" class="text-2xl font-extrabold tracking-tight"></h2>
            <button type="button" data-tutup class="btn btn-ghost !p-2" aria-label="Tutup">@include('partials.icon', ['name' => 'x', 'class' => 'size-5'])</button>
        </div>

        <div id="form-alert" class="mt-5 hidden rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700" role="alert"></div>

        <form id="form-crud" class="mt-6 grid gap-4 sm:grid-cols-2" novalidate>
            <div id="form-isi" class="contents"></div>
            <div class="mt-2 flex justify-end gap-3 sm:col-span-2">
                <button type="button" data-tutup class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary" data-submit><span data-label>Simpan</span></button>
            </div>
        </form>
    </div>
</div>

{{-- ============ MODAL: KONFIRMASI HAPUS ============ --}}
<div id="modal-hapus" class="fixed inset-0 z-[90] hidden items-end justify-center bg-ink/50 p-0 backdrop-blur-sm sm:items-center sm:p-6" role="alertdialog" aria-modal="true" aria-labelledby="hapus-judul">
    <div class="w-full max-w-md animate-pop rounded-t-3xl bg-white p-6 shadow-float sm:rounded-3xl sm:p-8">
        <span class="grid size-12 place-items-center rounded-2xl bg-rose-50 text-rose-600" id="hapus-ikon"></span>
        <h2 id="hapus-judul" class="mt-4 text-xl font-extrabold tracking-tight">Hapus data ini?</h2>
        <p id="hapus-ket" class="mt-2 text-sm text-ink/60"></p>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" data-tutup class="btn btn-outline">Batal</button>
            <button type="button" id="hapus-ya" class="btn bg-rose-600 text-white hover:bg-rose-700"><span data-label>Ya, hapus</span></button>
        </div>
    </div>
</div>
@endsection
