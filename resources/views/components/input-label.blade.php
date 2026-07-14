@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-ink-700 dark:text-ink-300']) }}>
    {{ $value ?? $slot }}
</label>
