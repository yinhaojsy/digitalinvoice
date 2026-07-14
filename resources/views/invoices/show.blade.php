<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="font-display text-2xl text-ink-900 dark:text-ink-50">{{ $invoice->buyer_business_name }}</h1>
                    <x-invoice-status :status="$invoice->status" />
                </div>
                <p class="text-sm text-ink-500 mt-1">
                    {{ $invoice->invoice_type }} · {{ $invoice->invoice_date->format('Y-m-d') }} · {{ $invoice->scenario_id }}
                </p>
                @if ($invoice->fbr_invoice_number)
                    <p class="mt-2 font-mono text-sm text-emerald-700 dark:text-emerald-300">FBR # {{ $invoice->fbr_invoice_number }}</p>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($invoice->isEditable())
                    <a href="{{ route('invoices.edit', $invoice) }}" class="px-3 py-2 text-sm rounded-lg border border-ink-300 dark:border-ink-600">Edit</a>
                    <form method="POST" action="{{ route('invoices.validate', $invoice) }}">
                        @csrf
                        <button class="px-3 py-2 text-sm rounded-lg border border-sky-500/50 text-sky-700 dark:text-sky-300 bg-sky-500/10">Validate (sandbox)</button>
                    </form>
                    <form method="POST" action="{{ route('invoices.post', $invoice) }}" onsubmit="return confirm('Post this invoice to FBR sandbox?')">
                        @csrf
                        <button class="px-3 py-2 text-sm rounded-lg bg-ink-800 dark:bg-sun-500 text-white dark:text-ink-950 font-semibold">Post to FBR sandbox</button>
                    </form>
                    <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" onsubmit="return confirm('Delete this draft?')">
                        @csrf
                        @method('DELETE')
                        <button class="px-3 py-2 text-sm rounded-lg text-rose-600 dark:text-rose-400">Delete</button>
                    </form>
                @endif
                <a href="{{ route('invoices.index') }}" class="px-3 py-2 text-sm text-ink-500">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200 px-4 py-3 text-sm">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-lg border border-rose-500/30 bg-rose-500/10 text-rose-800 dark:text-rose-200 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif
        @if ($invoice->last_error)
            <div class="rounded-lg border border-rose-500/30 bg-rose-500/5 px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                <span class="font-semibold">Last FBR error:</span> {{ $invoice->last_error }}
            </div>
        @endif

        <div class="grid sm:grid-cols-2 gap-4 rounded-xl border border-ink-200 dark:border-ink-800 bg-white dark:bg-ink-900 p-5 text-sm">
            <div><span class="text-ink-500">Buyer NTN/CNIC</span><div class="font-medium">{{ $invoice->buyer_ntn_cnic ?: '—' }}</div></div>
            <div><span class="text-ink-500">Registration</span><div class="font-medium">{{ $invoice->buyer_registration_type }}</div></div>
            <div><span class="text-ink-500">Province</span><div class="font-medium">{{ $invoice->buyer_province }}</div></div>
            <div><span class="text-ink-500">Address</span><div class="font-medium">{{ $invoice->buyer_address }}</div></div>
        </div>

        <div class="rounded-xl border border-ink-200 dark:border-ink-800 overflow-hidden bg-white dark:bg-ink-900">
            <div class="px-5 py-3 border-b border-ink-100 dark:border-ink-800 font-display">Items</div>
            <table class="min-w-full text-sm">
                <thead class="text-left text-ink-500 bg-ink-50 dark:bg-ink-950/40">
                    <tr>
                        <th class="px-4 py-2">HS</th>
                        <th class="px-4 py-2">Description</th>
                        <th class="px-4 py-2">Qty</th>
                        <th class="px-4 py-2">Excl. ST</th>
                        <th class="px-4 py-2">Tax</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">{{ $item->hs_code }}</td>
                            <td class="px-4 py-3">{{ $item->product_description }}<div class="text-xs text-ink-500">{{ $item->rate }} · {{ $item->uom }}</div></td>
                            <td class="px-4 py-3">{{ $item->quantity }}</td>
                            <td class="px-4 py-3">{{ number_format((float) $item->value_sales_excluding_st, 2) }}</td>
                            <td class="px-4 py-3">{{ number_format((float) $item->sales_tax_applicable, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="rounded-xl border border-ink-200 dark:border-ink-800 bg-white dark:bg-ink-900 overflow-hidden">
            <div class="px-5 py-3 border-b border-ink-100 dark:border-ink-800 font-display">FBR sandbox submissions</div>
            @if ($invoice->submissions->isEmpty())
                <p class="p-5 text-sm text-ink-500">No validate/post attempts yet.</p>
            @else
                <ul class="divide-y divide-ink-100 dark:divide-ink-800 text-sm">
                    @foreach ($invoice->submissions as $submission)
                        <li class="px-5 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <span class="capitalize font-medium">{{ $submission->action }}</span>
                                <span class="text-ink-500">· HTTP {{ $submission->http_status ?? '—' }} · {{ $submission->created_at->diffForHumans() }}</span>
                                @if ($submission->error_message)
                                    <div class="text-rose-600 dark:text-rose-400 mt-1">{{ $submission->error_message }}</div>
                                @endif
                                @if ($submission->fbr_invoice_number)
                                    <div class="font-mono text-xs text-emerald-700 dark:text-emerald-300 mt-1">{{ $submission->fbr_invoice_number }}</div>
                                @endif
                            </div>
                            <span class="{{ $submission->success ? 'text-emerald-600' : 'text-rose-600' }} text-xs font-semibold uppercase">{{ $submission->success ? 'OK' : 'Failed' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-app-layout>
