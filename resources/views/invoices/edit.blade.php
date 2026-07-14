<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl text-ink-900 dark:text-ink-50">Edit draft</h1>
        <p class="text-sm text-ink-500 mt-1">{{ $invoice->buyer_business_name }}</p>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('invoices.update', $invoice) }}">
            @csrf
            @method('PUT')
            @include('invoices._form', ['invoice' => $invoice, 'scenarios' => $scenarios])
        </form>
    </div>
</x-app-layout>
