<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
<body class="min-h-screen font-sans antialiased bg-ink-50 text-ink-900 dark:bg-ink-950 dark:text-ink-100">
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(212,160,23,0.18),_transparent_55%)] dark:bg-[radial-gradient(ellipse_at_top,_rgba(232,184,74,0.12),_transparent_50%)]"></div>
        <div class="absolute inset-0 opacity-[0.035] dark:opacity-[0.06]" style="background-image:url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M0 0h60v60H0z\" fill=\"none\"/%3E%3Cpath d=\"M30 0v60M0 30h60\" stroke=\"%23000\" stroke-width=\"1\"/%3E%3C/svg%3E');"></div>

        <header class="relative max-w-6xl mx-auto px-6 pt-8 flex items-center justify-between">
            <div class="font-display text-xl tracking-tight">Digital<span class="text-sun-600 dark:text-sun-400">Invoicing</span></div>
            <nav class="flex items-center gap-3 text-sm">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg bg-ink-800 dark:bg-sun-500 text-white dark:text-ink-950 font-semibold">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 text-ink-600 dark:text-ink-300 hover:underline">Log in</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-ink-800 dark:bg-sun-500 text-white dark:text-ink-950 font-semibold">Start free</a>
                @endauth
            </nav>
        </header>

        <main class="relative max-w-6xl mx-auto px-6 py-20 lg:py-28">
            <p class="text-xs uppercase tracking-[0.2em] text-sun-600 dark:text-sun-400 mb-4">Multi-tenant · FBR sandbox</p>
            <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl max-w-3xl leading-[1.1] text-ink-900 dark:text-ink-50">
                Draft invoices. Validate. Post to FBR sandbox.
            </h1>
            <p class="mt-6 max-w-xl text-lg text-ink-600 dark:text-ink-300">
                A light SaaS for your clients: create digital invoices, review drafts, and submit only through FBR’s sandbox APIs until you’re ready for production.
            </p>
            <div class="mt-10 flex flex-wrap gap-4">
                <a href="{{ route('register') }}" class="inline-flex px-5 py-3 rounded-lg bg-ink-800 dark:bg-sun-500 text-white dark:text-ink-950 font-semibold">Create your organization</a>
                <a href="{{ route('login') }}" class="inline-flex px-5 py-3 rounded-lg border border-ink-300 dark:border-ink-700 text-ink-700 dark:text-ink-200">Log in</a>
            </div>

            <ul class="mt-16 grid sm:grid-cols-3 gap-6 text-sm">
                <li class="rounded-xl border border-ink-200 dark:border-ink-800 bg-white/70 dark:bg-ink-900/50 p-5">
                    <div class="font-display text-lg mb-2">Day &amp; night</div>
                    <p class="text-ink-500 dark:text-ink-400">Switch themes anytime — prefs sync with your account.</p>
                </li>
                <li class="rounded-xl border border-ink-200 dark:border-ink-800 bg-white/70 dark:bg-ink-900/50 p-5">
                    <div class="font-display text-lg mb-2">Org isolation</div>
                    <p class="text-ink-500 dark:text-ink-400">Each client org keeps its own seller profile, token, and invoices.</p>
                </li>
                <li class="rounded-xl border border-ink-200 dark:border-ink-800 bg-white/70 dark:bg-ink-900/50 p-5">
                    <div class="font-display text-lg mb-2">Sandbox locked</div>
                    <p class="text-ink-500 dark:text-ink-400">Validate &amp; post hit <code class="text-xs">*_sb</code> endpoints only.</p>
                </li>
            </ul>
        </main>
    </div>
</body>
</html>
