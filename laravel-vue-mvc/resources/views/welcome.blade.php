<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>

        <meta name="theme-color" content="#f1f5f9">
        <meta name="description" content="Sistem informasi pengelolaan data agenda dan dokumen.">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Data Agenda">

        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="icon" href="/icons/icon-192.png" sizes="192x192" type="image/png">
        <link rel="apple-touch-icon" href="/icons/icon-192.png">

        <script>
            window.Laravel = { user: @json(auth()->user()?->only('name', 'email') + ['role_slug' => auth()->user()?->role_slug]) };
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>