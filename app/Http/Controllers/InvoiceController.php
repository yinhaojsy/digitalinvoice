<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->user()->organization;
        abort_if($organization === null, 404);

        $status = $request->string('status')->toString();

        $invoices = Invoice::query()
            ->forOrganization($organization->id)
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->withCount('items')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('invoices.index', compact('invoices', 'status'));
    }

    public function create(): View
    {
        return view('invoices.create', [
            'scenarios' => config('fbr.scenarios'),
            'invoice' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;
        abort_if($organization === null, 404);

        $data = $this->validateInvoice($request);

        $invoice = DB::transaction(function () use ($request, $organization, $data) {
            $invoice = Invoice::create([
                'organization_id' => $organization->id,
                'created_by' => $request->user()->id,
                'status' => Invoice::STATUS_DRAFT,
                'invoice_type' => $data['invoice_type'],
                'invoice_date' => $data['invoice_date'],
                'buyer_ntn_cnic' => $data['buyer_ntn_cnic'] ?? null,
                'buyer_business_name' => $data['buyer_business_name'],
                'buyer_province' => $data['buyer_province'],
                'buyer_address' => $data['buyer_address'],
                'buyer_registration_type' => $data['buyer_registration_type'],
                'invoice_ref_no' => $data['invoice_ref_no'] ?? null,
                'scenario_id' => $data['scenario_id'],
            ]);

            $this->syncItems($invoice, $data['items']);

            return $invoice;
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('status', 'Draft invoice saved.');
    }

    public function show(Request $request, Invoice $invoice): View
    {
        $this->authorizeInvoice($request, $invoice);

        $invoice->load(['items', 'submissions' => fn ($q) => $q->limit(10)]);

        return view('invoices.show', [
            'invoice' => $invoice,
            'scenarios' => config('fbr.scenarios'),
        ]);
    }

    public function edit(Request $request, Invoice $invoice): View
    {
        $this->authorizeInvoice($request, $invoice);
        abort_unless($invoice->isEditable(), 403, 'Posted invoices cannot be edited.');

        $invoice->load('items');

        return view('invoices.edit', [
            'invoice' => $invoice,
            'scenarios' => config('fbr.scenarios'),
        ]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($request, $invoice);
        abort_unless($invoice->isEditable(), 403, 'Posted invoices cannot be edited.');

        $data = $this->validateInvoice($request);

        DB::transaction(function () use ($invoice, $data) {
            $invoice->update([
                'invoice_type' => $data['invoice_type'],
                'invoice_date' => $data['invoice_date'],
                'buyer_ntn_cnic' => $data['buyer_ntn_cnic'] ?? null,
                'buyer_business_name' => $data['buyer_business_name'],
                'buyer_province' => $data['buyer_province'],
                'buyer_address' => $data['buyer_address'],
                'buyer_registration_type' => $data['buyer_registration_type'],
                'invoice_ref_no' => $data['invoice_ref_no'] ?? null,
                'scenario_id' => $data['scenario_id'],
                'status' => Invoice::STATUS_DRAFT,
                'validated_at' => null,
                'last_error' => null,
            ]);

            $invoice->items()->delete();
            $this->syncItems($invoice, $data['items']);
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('status', 'Draft updated.');
    }

    public function destroy(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($request, $invoice);
        abort_unless($invoice->isEditable(), 403, 'Posted invoices cannot be deleted.');

        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('status', 'Draft deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateInvoice(Request $request): array
    {
        return $request->validate([
            'invoice_type' => ['required', Rule::in(['Sale Invoice', 'Debit Note'])],
            'invoice_date' => ['required', 'date'],
            'buyer_ntn_cnic' => ['nullable', 'string', 'max:20'],
            'buyer_business_name' => ['required', 'string', 'max:255'],
            'buyer_province' => ['required', 'string', 'max:100'],
            'buyer_address' => ['required', 'string', 'max:500'],
            'buyer_registration_type' => ['required', Rule::in(['Registered', 'Unregistered'])],
            'invoice_ref_no' => ['nullable', 'string', 'max:40'],
            'scenario_id' => ['required', Rule::in(array_keys(config('fbr.scenarios')))],
            'items' => ['required', 'array', 'min:1'],
            'items.*.hs_code' => ['required', 'string', 'max:20'],
            'items.*.product_description' => ['required', 'string', 'max:500'],
            'items.*.rate' => ['required', 'string', 'max:100'],
            'items.*.uom' => ['required', 'string', 'max:100'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.total_values' => ['nullable', 'numeric', 'min:0'],
            'items.*.value_sales_excluding_st' => ['required', 'numeric', 'min:0'],
            'items.*.fixed_notified_value_or_retail_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.sales_tax_applicable' => ['required', 'numeric', 'min:0'],
            'items.*.sales_tax_withheld_at_source' => ['nullable', 'numeric', 'min:0'],
            'items.*.extra_tax' => ['nullable', 'numeric', 'min:0'],
            'items.*.further_tax' => ['nullable', 'numeric', 'min:0'],
            'items.*.sro_schedule_no' => ['nullable', 'string', 'max:100'],
            'items.*.fed_payable' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.sale_type' => ['required', 'string', 'max:255'],
            'items.*.sro_item_serial_no' => ['nullable', 'string', 'max:50'],
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(Invoice $invoice, array $items): void
    {
        foreach (array_values($items) as $index => $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'hs_code' => $item['hs_code'],
                'product_description' => $item['product_description'],
                'rate' => $item['rate'],
                'uom' => $item['uom'],
                'quantity' => $item['quantity'],
                'total_values' => $item['total_values'] ?? 0,
                'value_sales_excluding_st' => $item['value_sales_excluding_st'],
                'fixed_notified_value_or_retail_price' => $item['fixed_notified_value_or_retail_price'] ?? 0,
                'sales_tax_applicable' => $item['sales_tax_applicable'],
                'sales_tax_withheld_at_source' => $item['sales_tax_withheld_at_source'] ?? 0,
                'extra_tax' => $item['extra_tax'] ?? 0,
                'further_tax' => $item['further_tax'] ?? 0,
                'sro_schedule_no' => $item['sro_schedule_no'] ?? null,
                'fed_payable' => $item['fed_payable'] ?? 0,
                'discount' => $item['discount'] ?? 0,
                'sale_type' => $item['sale_type'],
                'sro_item_serial_no' => $item['sro_item_serial_no'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }

    private function authorizeInvoice(Request $request, Invoice $invoice): void
    {
        abort_unless(
            $invoice->organization_id === $request->user()->organization_id,
            404
        );
    }
}
