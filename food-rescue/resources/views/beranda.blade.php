@extends('layouts.app')

@section('title', 'Selamatkan Makanan, Bagikan Kebaikan')
@section('page', 'beranda')

@section('content')

{{-- ============ NAVBAR ============ --}}
<header id="navbar" class="fixed inset-x-0 top-0 z-50 transition duration-300">
    <div class="mx-auto mt-3 flex max-w-6xl items-center justify-between rounded-2xl border border-transparent px-4 py-2.5 transition sm:px-5" id="navbar-inner">
        @include('partials.logo')

        <nav class="hidden items-center gap-1 md:flex" aria-label="Navigasi utama">
            <a href="#fitur" class="btn btn-ghost">Fitur</a>
            <a href="#cara-kerja" class="btn btn-ghost">Cara Kerja</a>
            <a href="#peran" class="btn btn-ghost">Peran</a>
        </nav>

        <div class="hidden items-center gap-2 md:flex">
            <a href="{{ route('masuk') }}" class="btn btn-ghost">Masuk</a>
            <a href="{{ route('daftar') }}" class="btn btn-primary">Daftar gratis</a>
        </div>

        <button id="menu-btn" class="btn btn-ghost !p-2 md:hidden" aria-label="Buka menu" aria-expanded="false" aria-controls="menu-mobile">
            @include('partials.icon', ['name' => 'menu', 'class' => 'size-6'])
        </button>
    </div>

    <div id="menu-mobile" class="mx-4 mt-2 hidden rounded-2xl border border-ink/5 bg-white p-3 shadow-float md:hidden">
        <a href="#fitur" class="btn btn-ghost w-full !justify-start">Fitur</a>
        <a href="#cara-kerja" class="btn btn-ghost w-full !justify-start">Cara Kerja</a>
        <a href="#peran" class="btn btn-ghost w-full !justify-start">Peran</a>
        <div class="mt-2 grid grid-cols-2 gap-2">
            <a href="{{ route('masuk') }}" class="btn btn-outline">Masuk</a>
            <a href="{{ route('daftar') }}" class="btn btn-primary">Daftar</a>
        </div>
    </div>
</header>

<main>
    {{-- ============ HERO ============ --}}
    <section class="relative overflow-hidden bg-linear-to-b from-brand-50 via-white to-white pt-32 pb-20 sm:pt-40 sm:pb-28">
        <div class="bg-grid pointer-events-none absolute inset-0"></div>
        <div class="pointer-events-none absolute -top-24 -right-24 size-96 rounded-full bg-brand-300/40 blur-3xl"></div>
        <div class="pointer-events-none absolute top-40 -left-24 size-80 rounded-full bg-sun-300/40 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-6xl items-center gap-14 px-4 sm:px-6 lg:grid-cols-2">
            <div>
                <span class="badge bg-white text-brand-800 shadow-card ring-1 ring-brand-100" data-reveal>
                    <span class="relative flex size-2">
                        <span class="absolute inline-flex size-full animate-ping rounded-full bg-brand-400 opacity-75"></span>
                        <span class="relative inline-flex size-2 rounded-full bg-brand-500"></span>
                    </span>
                    Notifikasi donasi realtime
                </span>

                <h1 class="mt-6 text-4xl leading-[1.08] font-extrabold tracking-tight text-balance sm:text-6xl" data-reveal style="--reveal-delay:80ms">
                    Selamatkan makanan,<br>
                    <span class="text-gradient">bagikan kebaikan.</span>
                </h1>

                <p class="mt-6 max-w-xl text-lg leading-relaxed text-ink/65" data-reveal style="--reveal-delay:160ms">
                    FoodRescue mempertemukan donatur makanan berlebih, penerima yang membutuhkan, dan relawan pengantar
                    dalam satu platform yang cepat, transparan, dan terverifikasi.
                </p>

                <div class="mt-9 flex flex-wrap items-center gap-3" data-reveal style="--reveal-delay:240ms">
                    <a href="{{ route('daftar') }}" class="btn btn-primary btn-lg">
                        Mulai berdonasi
                        @include('partials.icon', ['name' => 'arrow', 'class' => 'size-5'])
                    </a>
                    <a href="#cara-kerja" class="btn btn-outline btn-lg">Lihat cara kerja</a>
                </div>

                <dl class="mt-12 grid max-w-md grid-cols-3 gap-6 border-t border-ink/10 pt-8" data-reveal style="--reveal-delay:320ms">
                    <div>
                        <dt class="text-xs font-medium text-ink/50">Peran pengguna</dt>
                        <dd class="mt-1 text-2xl font-extrabold">4</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-ink/50">Kuota porsi</dt>
                        <dd class="mt-1 text-2xl font-extrabold">Terkunci</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-ink/50">Pembaruan</dt>
                        <dd class="mt-1 text-2xl font-extrabold">Realtime</dd>
                    </div>
                </dl>
            </div>

            {{-- Ilustrasi kartu donasi --}}
            <div class="relative mx-auto w-full max-w-md" aria-hidden="true" data-reveal style="--reveal-delay:200ms">
                <div class="absolute -inset-6 rounded-[2.5rem] bg-linear-to-br from-brand-200/60 to-sun-300/50 blur-2xl"></div>

                <div class="card relative animate-float-slow p-5 shadow-float">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="grid size-11 place-items-center rounded-xl bg-brand-100 text-xl">🍱</span>
                            <div>
                                <p class="font-bold">Nasi Kotak Ayam Bakar</p>
                                <p class="text-xs text-ink/50">Warung Bu Sari · Purwokerto</p>
                            </div>
                        </div>
                        <span class="badge bg-brand-50 text-brand-700">Tersedia</span>
                    </div>

                    <div class="mt-5">
                        <div class="flex items-center justify-between text-xs font-medium text-ink/60">
                            <span>Sisa porsi</span><span class="font-bold text-ink">18 / 24</span>
                        </div>
                        <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-ink/5">
                            <div class="h-full w-3/4 rounded-full bg-linear-to-r from-brand-500 to-brand-400"></div>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-sun-600">
                            @include('partials.icon', ['name' => 'clock', 'class' => 'size-4'])
                            2 jam lagi
                        </span>
                        <span class="btn btn-primary !py-2">Klaim sekarang</span>
                    </div>
                </div>

                <div class="card absolute -bottom-8 -left-4 flex animate-float items-center gap-3 p-3.5 pr-5 shadow-float sm:-left-10">
                    <span class="grid size-10 place-items-center rounded-full bg-sun-100 text-sun-600">
                        @include('partials.icon', ['name' => 'bolt', 'class' => 'size-5'])
                    </span>
                    <div>
                        <p class="text-sm font-bold">Donasi baru!</p>
                        <p class="text-xs text-ink/50">Roti manis · 12 porsi</p>
                    </div>
                </div>

                <div class="card absolute -top-6 -right-3 flex animate-float-slow items-center gap-2 p-3 shadow-float sm:-right-8">
                    <span class="grid size-9 place-items-center rounded-full bg-brand-100 text-brand-700">
                        @include('partials.icon', ['name' => 'check', 'class' => 'size-5'])
                    </span>
                    <p class="text-xs font-bold">Terkirim ke<br>penerima</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ FITUR ============ --}}
    <section id="fitur" class="mx-auto max-w-6xl scroll-mt-24 px-4 py-20 sm:px-6 sm:py-28">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-bold tracking-wide text-brand-600 uppercase" data-reveal>Fitur utama</p>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-balance sm:text-4xl" data-reveal>
                Semua yang dibutuhkan untuk menyalurkan makanan dengan aman
            </h2>
            <p class="mt-4 text-ink/60" data-reveal>Dari posting donasi sampai bukti serah terima, setiap tahap tercatat rapi.</p>
        </div>

        @php
            $fitur = [
                ['bolt',   'Realtime dengan Reverb',     'Donasi baru langsung muncul di layar penerima tanpa perlu memuat ulang halaman.'],
                ['shield', 'Klaim anti-rebutan',          'Kuota porsi dikunci dalam transaksi database sehingga tidak ada klaim ganda.'],
                ['map',    'Titik penjemputan',           'Posko dan zona penjemputan lengkap dengan alamat, koordinat, dan status aktif.'],
                ['camera', 'Bukti serah terima',          'Relawan dan penerima mengunggah foto serta catatan sebagai verifikasi penyerahan.'],
                ['star',   'Ulasan & rating',             'Penerima memberi nilai 1–5 dan masukan agar kualitas donasi terus meningkat.'],
                ['flag',   'Laporan kelayakan',           'Laporkan makanan yang tidak layak atau kemasan rusak agar cepat ditindaklanjuti.'],
            ];
        @endphp

        <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($fitur as $i => [$ikon, $judul, $isi])
                <article class="card group p-6 transition duration-300 hover:-translate-y-1 hover:border-brand-200" data-reveal style="--reveal-delay:{{ ($i % 3) * 80 }}ms">
                    <span class="grid size-12 place-items-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                        @include('partials.icon', ['name' => $ikon, 'class' => 'size-6'])
                    </span>
                    <h3 class="mt-5 text-lg font-bold">{{ $judul }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-ink/60">{{ $isi }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- ============ CARA KERJA ============ --}}
    <section id="cara-kerja" class="scroll-mt-24 bg-brand-950 py-20 text-white sm:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-bold tracking-wide text-brand-300 uppercase" data-reveal>Cara kerja</p>
                <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-balance sm:text-4xl" data-reveal>Empat langkah dari dapur sampai ke meja penerima</h2>
            </div>

            @php
                $langkah = [
                    ['gift',  'Donatur memposting', 'Isi judul, jumlah porsi, lokasi, dan batas waktu. Penerima langsung diberi tahu.'],
                    ['hand',  'Penerima mengklaim', 'Pilih jumlah porsi yang dibutuhkan. Kuota otomatis berkurang secara aman.'],
                    ['truck', 'Relawan mengantar',  'Relawan ditugaskan untuk menjemput dan mengantarkan donasi ke penerima.'],
                    ['check', 'Terverifikasi',      'Bukti serah terima diunggah, lalu penerima dapat memberi ulasan.'],
                ];
            @endphp

            <ol class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($langkah as $i => [$ikon, $judul, $isi])
                    <li class="relative rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur transition hover:bg-white/10" data-reveal style="--reveal-delay:{{ $i * 90 }}ms">
                        <span class="absolute top-5 right-5 text-5xl font-extrabold text-white/10">{{ $i + 1 }}</span>
                        <span class="grid size-12 place-items-center rounded-xl bg-brand-500/20 text-brand-300">
                            @include('partials.icon', ['name' => $ikon, 'class' => 'size-6'])
                        </span>
                        <h3 class="mt-5 text-lg font-bold">{{ $judul }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-white/65">{{ $isi }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ============ PERAN ============ --}}
    <section id="peran" class="mx-auto max-w-6xl scroll-mt-24 px-4 py-20 sm:px-6 sm:py-28">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-bold tracking-wide text-brand-600 uppercase" data-reveal>Untuk siapa?</p>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-balance sm:text-4xl" data-reveal>Satu platform, peran yang jelas</h2>
        </div>

        @php
            $peran = [
                ['gift',  'Donatur',  'Restoran, toko roti, hotel, atau individu yang punya makanan berlebih.',  ['Posting donasi makanan', 'Atur batas waktu & lokasi', 'Pantau status donasi']],
                ['hand',  'Penerima', 'Individu, panti, atau komunitas yang membutuhkan makanan layak.',         ['Lihat donasi tersedia', 'Klaim porsi sesuai kebutuhan', 'Beri ulasan & laporan']],
                ['truck', 'Relawan',  'Pengantar yang menjembatani donatur dengan penerima.',                    ['Terima penugasan', 'Jemput & antar donasi', 'Unggah bukti serah terima']],
            ];
        @endphp

        <div class="mt-14 grid gap-5 lg:grid-cols-3">
            @foreach ($peran as $i => [$ikon, $nama, $deskripsi, $daftar])
                <article class="card flex flex-col p-7" data-reveal style="--reveal-delay:{{ $i * 90 }}ms">
                    <span class="grid size-12 place-items-center rounded-xl bg-linear-to-br from-brand-500 to-brand-700 text-white shadow-soft">
                        @include('partials.icon', ['name' => $ikon, 'class' => 'size-6'])
                    </span>
                    <h3 class="mt-5 text-xl font-bold">{{ $nama }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-ink/60">{{ $deskripsi }}</p>
                    <ul class="mt-5 space-y-2.5 text-sm">
                        @foreach ($daftar as $item)
                            <li class="flex items-center gap-2.5 font-medium text-ink/80">
                                <span class="grid size-5 place-items-center rounded-full bg-brand-100 text-brand-700">
                                    @include('partials.icon', ['name' => 'check', 'class' => 'size-3.5'])
                                </span>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
    </section>

    {{-- ============ CTA ============ --}}
    <section class="px-4 pb-20 sm:px-6 sm:pb-28">
        <div class="relative mx-auto max-w-6xl overflow-hidden rounded-[2rem] bg-linear-to-br from-brand-600 to-brand-800 px-6 py-16 text-center text-white shadow-float sm:px-12" data-reveal>
            <div class="pointer-events-none absolute -top-20 -left-20 size-72 rounded-full bg-brand-400/30 blur-3xl"></div>
            <div class="pointer-events-none absolute -right-16 -bottom-24 size-80 rounded-full bg-sun-400/30 blur-3xl"></div>
            <h2 class="relative text-3xl font-extrabold tracking-tight text-balance sm:text-4xl">Setiap porsi yang diselamatkan berarti satu perut yang terisi.</h2>
            <p class="relative mx-auto mt-4 max-w-xl text-white/75">Bergabung sekarang sebagai donatur, penerima, atau relawan. Gratis dan hanya butuh satu menit.</p>
            <div class="relative mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('daftar') }}" class="btn btn-lg bg-white text-brand-800 hover:bg-brand-50">Buat akun</a>
                <a href="{{ route('masuk') }}" class="btn btn-lg border border-white/30 text-white hover:bg-white/10">Saya sudah punya akun</a>
            </div>
        </div>
    </section>
</main>

{{-- ============ FOOTER ============ --}}
<footer class="border-t border-ink/5 bg-white">
    <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 py-8 text-sm text-ink/55 sm:flex-row sm:px-6">
        @include('partials.logo')
        <p>© {{ date('Y') }} FoodRescue · Kelompok 04 Shift A · Responsi Pemrograman Web II</p>
    </div>
</footer>

@endsection
