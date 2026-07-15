<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl text-ink-900 dark:text-ink-50">Add customer</h1>
        <p class="text-sm text-ink-500 mt-1">Registration / STATL status is checked live when you pick them on an invoice</p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('customers.store') }}">
            @csrf
            @include('customers._form', ['customer' => null, 'provinces' => $provinces])
        </form>
    </div>
</x-app-layout>
