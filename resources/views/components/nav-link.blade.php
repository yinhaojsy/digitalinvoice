@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-sun-500 text-sm font-medium leading-5 text-ink-900 dark:text-ink-50 focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-ink-500 dark:text-ink-400 hover:text-ink-800 dark:hover:text-ink-100 hover:border-ink-300 dark:hover:border-ink-600 focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
