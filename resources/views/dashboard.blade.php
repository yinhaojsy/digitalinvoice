<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="font-display text-2xl text-ink-900 dark:text-ink-50">Dashboard</h1>
                <p class="text-sm text-ink-500 dark:text-ink-400 mt-1">
                    {{ $organization?->name ?? 'Your organization' }} · FBR sandbox only
                </p>
            </div>
            <a href="{{ route('invoices.create') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-ink-800 dark:bg-sun-500 text-white dark:text-ink-950 text-sm font-semibold hover:opacity-90 transition">
                New draft invoice
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        @if (session('status'))
            <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200 px-4 py-3 text-sm">{{ session('status') }}</div>
        @endif

        @if (! $organization?->sellerProfileComplete() || ! $organization?->hasSandboxToken())
            <div class="rounded-xl border border-sun-500/40 bg-sun-500/10 px-5 py-4 text-sm text-ink-800 dark:text-ink-100">
                <p class="font-semibold">Finish sandbox setup</p>
                <p class="mt-1 text-ink-600 dark:text-ink-300">Add seller NTN/details and your FBR sandbox Bearer token under Org &amp; FBR before validating or posting invoices.</p>
                @if(Auth::user()->isOwner())
                    <a href="{{ route('settings.organization') }}" class="inline-block mt-3 underline decoration-sun-500 underline-offset-2">Open organization settings</a>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([
                ['Drafts', $stats['drafts'], 'text-ink-700 dark:text-ink-200'],
                ['Posted', $stats['posted'], 'text-emerald-700 dark:text-emerald-300'],
                ['Failed', $stats['failed'], 'text-rose-700 dark:text-rose-300'],
                ['Total', $stats['total'], 'text-ink-700 dark:text-ink-200'],
            ] as [$label, $value, $color])
                <div class="rounded-xl border border-ink-200 dark:border-ink-800 bg-white dark:bg-ink-900 p-4">
                    <div class="text-xs uppercase tracking-wide text-ink-500">{{ $label }}</div>
                    <div class="mt-2 font-display text-3xl {{ $color }}">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        <section>
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-display text-lg">Recent invoices</h2>
                <a href="{{ route('invoices.index') }}" class="text-sm text-sun-600 dark:text-sun-400 hover:underline">View all</a>
            </div>

            <div class="rounded-xl border border-ink-200 dark:border-ink-800 overflow-hidden bg-white dark:bg-ink-900">
                @if ($recent->isEmpty())
                    <p class="p-6 text-sm text-ink-500">No invoices yet. Create a draft to get started.</p>
                @else
                    <table class="min-w-full text-sm">
                        <thead class="bg-ink-50 dark:bg-ink-950/50 text-left text-ink-500">
                            <tr>
                                <th class="px-4 py-3 font-medium">Buyer</th>
                                <th class="px-4 py-3 font-medium">Date</th>
                                <th class="px-4 py-3 font-medium">Scenario</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($recent as $invoice)
                                <tr class="hover:bg-ink-50/80 dark:hover:bg-ink-800/40">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-ink-800 dark:text-ink-100 hover:underline">
                                            {{ $invoice->buyer_business_name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-ink-500">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3 text-ink-500">{{ $invoice->scenario_id }}</td>
                                    <td class="px-4 py-3"><x-invoice-status :status="$invoice->status" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </section>
    </div>
</x-app-layout>
