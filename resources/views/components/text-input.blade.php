@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-ink-300 dark:border-ink-700 dark:bg-ink-950 dark:text-ink-100 focus:border-sun-500 focus:ring-sun-500 rounded-md shadow-sm']) }}>
