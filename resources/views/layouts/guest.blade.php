<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Digital Invoicing') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|source-sans-3:400,500,600,700&display=swap" rel="stylesheet" />

        <script>
            (function () {
                const saved = localStorage.getItem('theme') || 'system';
                const night = saved === 'night' || (saved === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                if (night) document.documentElement.classList.add('dark');
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink-900 dark:text-ink-100 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-b from-ink-50 via-ink-100/40 to-ink-50 dark:from-ink-950 dark:via-ink-900 dark:to-ink-950">
            <div class="text-center mb-2">
                <a href="/" class="font-display text-2xl tracking-tight text-ink-800 dark:text-ink-50">
                    Digital<span class="text-sun-600 dark:text-sun-400">Invoicing</span>
                </a>
                <p class="text-xs uppercase tracking-wider text-ink-500 mt-1">FBR sandbox SaaS</p>
            </div>

            <div class="w-full sm:max-w-md mt-4 px-6 py-6 bg-white/90 dark:bg-ink-900/90 border border-ink-200 dark:border-ink-800 overflow-hidden sm:rounded-xl shadow-sm backdrop-blur">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
