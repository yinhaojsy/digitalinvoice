<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="font-display text-2xl text-ink-900 dark:text-ink-50">Invoices</h1>
                <p class="text-sm text-ink-500 mt-1">Drafts, validation, and sandbox posts</p>
            </div>
            <a href="{{ route('invoices.create') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-ink-800 dark:bg-sun-500 text-white dark:text-ink-950 text-sm font-semibold">New draft</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @if (session('status'))
            <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200 px-4 py-3 text-sm">{{ session('status') }}</div>
        @endif

        <div class="flex flex-wrap gap-2 text-sm">
            <a href="{{ route('invoices.index') }}" class="px-3 py-1 rounded-full border {{ $status === '' ? 'border-sun-500 bg-sun-500/10' : 'border-ink-300 dark:border-ink-700' }}">All</a>
            @foreach (['draft', 'validated', 'posted', 'failed'] as $s)
                <a href="{{ route('invoices.index', ['status' => $s]) }}" class="px-3 py-1 rounded-full border capitalize {{ $status === $s ? 'border-sun-500 bg-sun-500/10' : 'border-ink-300 dark:border-ink-700' }}">{{ $s }}</a>
            @endforeach
        </div>

        <div class="rounded-xl border border-ink-200 dark:border-ink-800 overflow-hidden bg-white dark:bg-ink-900">
            @if ($invoices->isEmpty())
                <p class="p-6 text-sm text-ink-500">No invoices match this filter.</p>
            @else
                <table class="min-w-full text-sm">
                    <thead class="bg-ink-50 dark:bg-ink-950/50 text-left text-ink-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Buyer</th>
                            <th class="px-4 py-3 font-medium">Date</th>
                            <th class="px-4 py-3 font-medium">Items</th>
                            <th class="px-4 py-3 font-medium">FBR #</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($invoices as $invoice)
                            <tr>
                                <td class="px-4 py-3">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="font-medium hover:underline">{{ $invoice->buyer_business_name }}</a>
                                </td>
                                <td class="px-4 py-3 text-ink-500">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-ink-500">{{ $invoice->items_count }}</td>
                                <td class="px-4 py-3 text-ink-500 font-mono text-xs">{{ $invoice->fbr_invoice_number ?? '—' }}</td>
                                <td class="px-4 py-3"><x-invoice-status :status="$invoice->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4 border-t border-ink-100 dark:border-ink-800">{{ $invoices->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
