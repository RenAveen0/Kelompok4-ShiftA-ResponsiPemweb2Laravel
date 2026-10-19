<a href="{{ route('beranda') }}" class="inline-flex items-center gap-2.5 {{ $class ?? '' }}">
    <span class="grid size-9 place-items-center rounded-xl bg-linear-to-br from-brand-500 to-brand-700 text-white shadow-soft">
        @include('partials.icon', ['name' => 'leaf', 'class' => 'size-5'])
    </span>
    <span class="text-lg font-bold tracking-tight {{ ($light ?? false) ? 'text-white' : 'text-ink' }}">Food<span class="{{ ($light ?? false) ? 'text-brand-300' : 'text-brand-600' }}">Rescue</span></span>
</a>
