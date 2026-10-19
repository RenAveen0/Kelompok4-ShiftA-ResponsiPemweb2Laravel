<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'FoodRescue') · FoodRescue</title>
        <meta name="description" content="@yield('description', 'FoodRescue menghubungkan donatur, penerima, dan relawan untuk menyelamatkan makanan berlebih secara realtime.')">
        <meta name="theme-color" content="#178d58">
        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E🍲%3C/text%3E%3C/svg%3E">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body data-page="@yield('page', 'beranda')" class="min-h-screen">
        <script>document.documentElement.classList.add('js');</script>

        @yield('content')

        {{-- Wadah notifikasi (toast) --}}
        <div id="toasts" class="pointer-events-none fixed inset-x-0 bottom-4 z-[100] mx-auto flex w-full max-w-sm flex-col gap-2 px-4 sm:inset-x-auto sm:right-4 sm:bottom-4 sm:mx-0"></div>
    </body>
</html>
