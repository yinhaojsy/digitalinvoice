@props(['status'])

@php
    $map = [
        'draft' => 'bg-ink-100 text-ink-700 dark:bg-ink-800 dark:text-ink-200',
        'validated' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/50 dark:text-sky-200',
        'posted' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200',
        'failed' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium capitalize '.($map[$status] ?? $map['draft'])]) }}>
    {{ $status }}
</span>
