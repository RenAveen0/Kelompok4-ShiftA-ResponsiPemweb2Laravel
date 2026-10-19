@extends('layouts.app')

@section('content')
<div class="grid min-h-screen lg:grid-cols-[1.05fr_1fr]">
    {{-- Panel merek (disembunyikan di layar kecil) --}}
    <aside class="relative hidden overflow-hidden bg-brand-950 p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="pointer-events-none absolute -top-24 -left-24 size-96 rounded-full bg-brand-500/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 -bottom-24 size-96 rounded-full bg-sun-500/20 blur-3xl"></div>
        <div class="bg-grid pointer-events-none absolute inset-0 opacity-20"></div>

        <div class="relative">@include('partials.logo', ['light' => true])</div>

        <div class="relative max-w-md">
            <p class="text-4xl leading-tight font-extrabold tracking-tight text-balance">
                Dari makanan berlebih menjadi <span class="text-brand-300">harapan</span> bagi sesama.
            </p>
            <ul class="mt-8 space-y-4 text-white/75">
                @foreach (['Donasi tampil realtime untuk semua penerima', 'Klaim aman, tanpa rebutan porsi', 'Setiap penyerahan terverifikasi dengan bukti foto'] as $poin)
                    <li class="flex items-center gap-3">
                        <span class="grid size-6 shrink-0 place-items-center rounded-full bg-brand-500/25 text-brand-300">
                            @include('partials.icon', ['name' => 'check', 'class' => 'size-4'])
                        </span>
                        {{ $poin }}
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="relative text-sm text-white/40">© {{ date('Y') }} FoodRescue · Kelompok 04 Shift A</p>
    </aside>

    {{-- Panel formulir --}}
    <main class="flex items-center justify-center px-5 py-10 sm:px-10">
        <div class="w-full max-w-md animate-pop">
            <div class="mb-8 lg:hidden">@include('partials.logo')</div>
            @yield('form')
        </div>
    </main>
</div>
@endsection
