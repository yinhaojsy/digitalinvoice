<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl text-ink-900 dark:text-ink-50">New draft invoice</h1>
        <p class="text-sm text-ink-500 mt-1">Saved locally until you validate / post to FBR sandbox</p>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('invoices.store') }}">
            @csrf
            @include('invoices._form', ['invoice' => null, 'scenarios' => $scenarios, 'provinces' => $provinces, 'customers' => $customers])
        </form>
    </div>
</x-app-layout>
