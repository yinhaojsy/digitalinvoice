@props([
    'name',
    'id' => null,
    'value' => null,
    'provinces' => [],
    'required' => false,
])

@php
    $id = $id ?? $name;
    $list = $provinces ?: \App\Services\Fbr\FbrReferenceClient::FALLBACK_PROVINCES;
@endphp

<select
    id="{{ $id }}"
    name="{{ $name }}"
    @required($required)
    {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 dark:text-ink-100 shadow-sm focus:border-sun-500 focus:ring-sun-500']) }}
>
    <option value="">Select province</option>
    @foreach ($list as $province)
        <option value="{{ $province }}" @selected((string) $value === (string) $province)>{{ $province }}</option>
    @endforeach
    @if (filled($value) && ! in_array($value, $list, true))
        <option value="{{ $value }}" selected>{{ $value }} (current — reselect a valid FBR value)</option>
    @endif
</select>
