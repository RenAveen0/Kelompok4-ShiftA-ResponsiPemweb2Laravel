{{-- Pemakaian: @include('partials.icon', ['name' => 'bolt', 'class' => 'size-5']) --}}
@php
    $icons = [
        'bolt'     => '<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z"/>',
        'shield'   => '<path d="M12 3 4 6v6c0 4.5 3.2 8 8 9 4.8-1 8-4.5 8-9V6l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
        'map'      => '<path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
        'camera'   => '<path d="M4 8h3l2-2h6l2 2h3v11H4V8Z"/><circle cx="12" cy="13" r="3.5"/>',
        'star'     => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9L12 3Z"/>',
        'flag'     => '<path d="M5 21V4m0 0h11l-2 4 2 4H5"/>',
        'users'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.5 2.9-6 6.5-6s6.5 2.5 6.5 6"/><path d="M16 5a3.5 3.5 0 0 1 0 6.5M18 14c2 .6 3.5 2.6 3.5 6"/>',
        'gift'     => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M5 12v8h14v-8M12 8v12M12 8c-2.5 0-4-1-4-2.5S9.5 3 11 4c.8.5 1 2 1 4Zm0 0c2.5 0 4-1 4-2.5S14.5 3 13 4c-.8.5-1 2-1 4Z"/>',
        'hand'     => '<path d="M8 12V6a1.5 1.5 0 0 1 3 0v5m0-6a1.5 1.5 0 0 1 3 0v6m0-4a1.5 1.5 0 0 1 3 0v8c0 3-2.5 5-5.5 5S7 19 5.5 16.5L3.5 13a1.5 1.5 0 0 1 2.5-1.5L8 14"/>',
        'truck'    => '<path d="M3 6h11v10H3V6Zm11 4h4l3 3v3h-7"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
        'check'    => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
        'arrow'    => '<path d="M5 12h14m-5-5 5 5-5 5"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'x'        => '<path d="M6 6l12 12M18 6 6 18"/>',
        'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'leaf'     => '<path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15"/><path d="M5 19c3-5 6-8 10-10"/>',
        'search'   => '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4-4"/>',
        'logout'   => '<path d="M10 4H5v16h5M15 8l4 4-4 4M19 12H9"/>',
        'chart'    => '<path d="M4 20V10m6 10V4m6 16v-7m4 7H2"/>',
        'eye'      => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'inbox'    => '<path d="M3 13l3-8h12l3 8v6H3v-6Zm0 0h5l1 2h6l1-2h5"/>',
    ];
@endphp
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="{{ $class ?? 'size-5' }}" aria-hidden="true">{!! $icons[$name] ?? '' !!}</svg>
