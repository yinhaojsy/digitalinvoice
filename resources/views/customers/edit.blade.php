<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl text-ink-900 dark:text-ink-50">Edit customer</h1>
        <p class="text-sm text-ink-500 mt-1">{{ $customer->name }}</p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf
            @method('PUT')
            @include('customers._form', ['customer' => $customer, 'provinces' => $provinces])
        </form>
    </div>
</x-app-layout>
