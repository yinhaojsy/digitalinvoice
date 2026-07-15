<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="font-display text-2xl text-ink-900 dark:text-ink-50">Customers</h1>
                <p class="text-sm text-ink-500 mt-1">Local buyer directory — FBR status is checked when you create an invoice</p>
            </div>
            <a href="{{ route('customers.create') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-ink-800 dark:bg-sun-500 text-white dark:text-ink-950 text-sm font-semibold">Add customer</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @if (session('status'))
            <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200 px-4 py-3 text-sm">{{ session('status') }}</div>
        @endif

        <div class="rounded-xl border border-ink-200 dark:border-ink-800 overflow-hidden bg-white dark:bg-ink-900">
            @if ($customers->isEmpty())
                <p class="p-6 text-sm text-ink-500">No customers yet. Add one to reuse buyers on invoices.</p>
            @else
                <table class="min-w-full text-sm">
                    <thead class="bg-ink-50 dark:bg-ink-950/50 text-left text-ink-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Business</th>
                            <th class="px-4 py-3 font-medium">NTN</th>
                            <th class="px-4 py-3 font-medium">Province</th>
                            <th class="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($customers as $customer)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $customer->name }}</td>
                                <td class="px-4 py-3">{{ $customer->business_name }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $customer->ntn }}</td>
                                <td class="px-4 py-3 text-ink-500">{{ $customer->province }}</td>
                                <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                                    <a href="{{ route('customers.edit', $customer) }}" class="text-sun-600 dark:text-sun-400 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline" onsubmit="return confirm('Delete this customer?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 dark:text-rose-400 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4 border-t border-ink-100 dark:border-ink-800">{{ $customers->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
