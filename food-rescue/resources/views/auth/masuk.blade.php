@extends('layouts.auth')

@section('title', 'Masuk')
@section('page', 'masuk')

@section('form')
    <h1 class="text-3xl font-extrabold tracking-tight">Selamat datang kembali 👋</h1>
    <p class="mt-2 text-ink/60">Masuk untuk melanjutkan berbagi dan menyelamatkan makanan.</p>

    <div id="form-alert" class="mt-6 hidden rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700" role="alert"></div>

    <form id="form-masuk" class="mt-6 space-y-5" novalidate>
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required placeholder="nama@email.com" class="field">
            <p class="field-error hidden" data-error-for="email"></p>
        </div>

        <div>
            <label for="password" class="label">Kata sandi</label>
            <div class="relative">
                <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="Minimal 8 karakter" class="field pr-12">
                <button type="button" data-toggle-password="password" class="absolute inset-y-0 right-0 grid w-12 place-items-center text-ink/40 transition hover:text-ink" aria-label="Tampilkan kata sandi">
                    @include('partials.icon', ['name' => 'eye', 'class' => 'size-5'])
                </button>
            </div>
            <p class="field-error hidden" data-error-for="password"></p>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-full" data-submit>
            <span data-label>Masuk</span>
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-ink/60">
        Belum punya akun?
        <a href="{{ route('daftar') }}" class="font-semibold text-brand-700 hover:underline">Daftar sekarang</a>
    </p>
@endsection
