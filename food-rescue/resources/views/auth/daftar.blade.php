@extends('layouts.auth')

@section('title', 'Daftar')
@section('page', 'daftar')

@section('form')
    <h1 class="text-3xl font-extrabold tracking-tight">Buat akun baru</h1>
    <p class="mt-2 text-ink/60">Pilih peran Anda dan mulai berkontribusi hari ini.</p>

    <div id="form-alert" class="mt-6 hidden rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700" role="alert"></div>

    <form id="form-daftar" class="mt-6 space-y-5" novalidate>
        {{-- Pilihan peran --}}
        <fieldset>
            <legend class="label">Saya ingin bergabung sebagai</legend>
            @php
                $peran = [
                    ['donatur',  'Donatur',  'gift',  'Berbagi makanan'],
                    ['penerima', 'Penerima', 'hand',  'Menerima donasi'],
                    ['relawan',  'Relawan',  'truck', 'Mengantar donasi'],
                ];
            @endphp
            <div class="grid grid-cols-3 gap-2.5">
                @foreach ($peran as $i => [$nilai, $nama, $ikon, $ket])
                    <label class="group cursor-pointer">
                        <input type="radio" name="role" value="{{ $nilai }}" class="peer sr-only" {{ $i === 0 ? 'checked' : '' }}>
                        <span class="flex h-full flex-col items-center gap-1.5 rounded-xl border border-ink/10 bg-white p-3 text-center transition peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:ring-4 peer-checked:ring-brand-500/15 peer-focus-visible:ring-4 peer-focus-visible:ring-brand-500/30 hover:border-brand-300">
                            <span class="text-brand-600">@include('partials.icon', ['name' => $ikon, 'class' => 'size-6'])</span>
                            <span class="text-sm font-bold">{{ $nama }}</span>
                            <span class="text-[11px] leading-tight text-ink/50">{{ $ket }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <p class="field-error hidden" data-error-for="role"></p>
        </fieldset>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="label">Nama lengkap</label>
                <input id="name" name="name" type="text" autocomplete="name" required placeholder="Nama Anda" class="field">
                <p class="field-error hidden" data-error-for="name"></p>
            </div>

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" autocomplete="email" required placeholder="nama@email.com" class="field">
                <p class="field-error hidden" data-error-for="email"></p>
            </div>

            <div>
                <label for="no_telepon" class="label">No. telepon</label>
                <input id="no_telepon" name="no_telepon" type="tel" autocomplete="tel" required placeholder="08xxxxxxxxxx" class="field">
                <p class="field-error hidden" data-error-for="no_telepon"></p>
            </div>

            <div class="sm:col-span-2">
                <label for="alamat" class="label">Alamat <span class="font-normal text-ink/40">(opsional)</span></label>
                <textarea id="alamat" name="alamat" rows="2" placeholder="Alamat lengkap" class="field resize-none"></textarea>
                <p class="field-error hidden" data-error-for="alamat"></p>
            </div>

            <div>
                <label for="password" class="label">Kata sandi</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required placeholder="Minimal 8 karakter" class="field">
                <p class="field-error hidden" data-error-for="password"></p>
            </div>

            <div>
                <label for="password_confirmation" class="label">Ulangi kata sandi</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required placeholder="Ketik ulang" class="field">
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-full" data-submit>
            <span data-label>Buat akun</span>
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-ink/60">
        Sudah punya akun?
        <a href="{{ route('masuk') }}" class="font-semibold text-brand-700 hover:underline">Masuk</a>
    </p>
@endsection
