<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Offline - {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-slate-100  text-slate-900 ">
        <main class="min-h-screen flex items-center justify-center p-6">
            <div class="glass  rounded-2xl p-10 text-center max-w-sm w-full border border-slate-200 ">
                <div class="mx-auto mb-4 w-16 h-16 rounded-full gradient-brand flex items-center justify-center">
                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" class="w-8 h-8">
                        <path d="M12 3v10m0 0l-4-4m4 4l4-4" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M5 17a3 3 0 0 1 0-6 5 5 0 0 1 9.6 1.5A3.5 3.5 0 0 1 18 19H6" />
                    </svg>
                </div>
                <h1 class="font-display font-bold text-xl gradient-brand-text mb-2">Anda Sedang Offline</h1>
                <p class="text-sm text-slate-500 ">
                    Periksa koneksi internet Anda lalu muat ulang halaman.
                </p>
                <a
                    href="/"
                    class="mt-6 inline-block px-5 py-2 text-sm font-medium text-white gradient-brand rounded-xl shadow-lg shadow-indigo-500/25"
                >
                    Muat Ulang
                </a>
            </div>
        </main>
    </body>
</html>