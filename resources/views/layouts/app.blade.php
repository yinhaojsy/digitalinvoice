<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{
        theme: @js(auth()->user()?->theme_preference ?? 'system'),
        init() {
            this.apply();
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (this.theme === 'system') this.apply();
            });
        },
        apply() {
            const night = this.theme === 'night' || (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', night);
            localStorage.setItem('theme', this.theme);
        },
        setTheme(value) {
            this.theme = value;
            this.apply();
            fetch('{{ route('theme.update') }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ theme_preference: value }),
            });
        }
    }"
    x-cloak
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Digital Invoicing') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|source-sans-3:400,500,600,700&display=swap" rel="stylesheet" />

        <script>
            (function () {
                const saved = localStorage.getItem('theme') || @json(auth()->user()?->theme_preference ?? 'system');
                const night = saved === 'night' || (saved === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                if (night) document.documentElement.classList.add('dark');
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none!important}</style>
    </head>
    <body class="font-sans antialiased bg-ink-50 text-ink-900 dark:bg-ink-950 dark:text-ink-100 transition-colors duration-300">
        <div class="min-h-screen">
            @include('layouts.navigation')

            @isset($header)
                <header class="border-b border-ink-200/80 dark:border-ink-800 bg-white/70 dark:bg-ink-900/60 backdrop-blur">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="py-6">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
