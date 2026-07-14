<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-ink-800 dark:bg-sun-500 border border-transparent rounded-md font-semibold text-xs text-white dark:text-ink-950 uppercase tracking-widest hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-sun-500 focus:ring-offset-2 dark:focus:ring-offset-ink-950 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
