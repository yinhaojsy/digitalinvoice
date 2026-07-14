<nav x-data="{ open: false }" class="bg-white/90 dark:bg-ink-900/90 border-b border-ink-200 dark:border-ink-800 backdrop-blur sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center gap-2">
                    <a href="{{ route('dashboard') }}" class="font-display text-lg tracking-tight text-ink-800 dark:text-ink-50">
                        Digital<span class="text-sun-600 dark:text-sun-400">Invoicing</span>
                    </a>
                    <span class="hidden sm:inline text-[10px] uppercase tracking-wider px-1.5 py-0.5 rounded bg-sun-500/15 text-sun-600 dark:text-sun-400 border border-sun-500/30">Sandbox</span>
                </div>

                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>
                    <x-nav-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')">
                        Invoices
                    </x-nav-link>
                    @if(Auth::user()->isOwner())
                        <x-nav-link :href="route('settings.organization')" :active="request()->routeIs('settings.*')">
                            Org & FBR
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                <div class="flex items-center rounded-lg border border-ink-200 dark:border-ink-700 p-0.5 text-xs">
                    <button type="button" @click="setTheme('day')" :class="theme === 'day' ? 'bg-ink-100 dark:bg-ink-800 text-ink-900 dark:text-white' : 'text-ink-500'" class="px-2 py-1 rounded-md transition">Day</button>
                    <button type="button" @click="setTheme('night')" :class="theme === 'night' ? 'bg-ink-100 dark:bg-ink-800 text-ink-900 dark:text-white' : 'text-ink-500'" class="px-2 py-1 rounded-md transition">Night</button>
                    <button type="button" @click="setTheme('system')" :class="theme === 'system' ? 'bg-ink-100 dark:bg-ink-800 text-ink-900 dark:text-white' : 'text-ink-500'" class="px-2 py-1 rounded-md transition">Auto</button>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-ink-600 dark:text-ink-300 bg-transparent hover:text-ink-900 dark:hover:text-white focus:outline-none transition">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-ink-400 hover:text-ink-600 dark:hover:text-ink-200 hover:bg-ink-100 dark:hover:bg-ink-800 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-ink-200 dark:border-ink-800">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')">Invoices</x-responsive-nav-link>
            @if(Auth::user()->isOwner())
                <x-responsive-nav-link :href="route('settings.organization')" :active="request()->routeIs('settings.*')">Org & FBR</x-responsive-nav-link>
            @endif
        </div>
        <div class="pt-4 pb-1 border-t border-ink-200 dark:border-ink-800">
            <div class="px-4 mb-3 flex gap-2 text-xs">
                <button type="button" @click="setTheme('day')" class="px-2 py-1 rounded border border-ink-300 dark:border-ink-600">Day</button>
                <button type="button" @click="setTheme('night')" class="px-2 py-1 rounded border border-ink-300 dark:border-ink-600">Night</button>
                <button type="button" @click="setTheme('system')" class="px-2 py-1 rounded border border-ink-300 dark:border-ink-600">Auto</button>
            </div>
            <div class="px-4">
                <div class="font-medium text-base text-ink-800 dark:text-ink-100">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-ink-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Profile</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
