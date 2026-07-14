@php
    $isEdit = isset($invoice) && $invoice;
    $items = old('items');
    if ($items === null) {
        if ($isEdit && $invoice->items->isNotEmpty()) {
            $items = $invoice->items->map(fn ($item) => [
                'hs_code' => $item->hs_code,
                'product_description' => $item->product_description,
                'rate' => $item->rate,
                'uom' => $item->uom,
                'quantity' => $item->quantity,
                'total_values' => $item->total_values,
                'value_sales_excluding_st' => $item->value_sales_excluding_st,
                'fixed_notified_value_or_retail_price' => $item->fixed_notified_value_or_retail_price,
                'sales_tax_applicable' => $item->sales_tax_applicable,
                'sales_tax_withheld_at_source' => $item->sales_tax_withheld_at_source,
                'extra_tax' => $item->extra_tax,
                'further_tax' => $item->further_tax,
                'sro_schedule_no' => $item->sro_schedule_no,
                'fed_payable' => $item->fed_payable,
                'discount' => $item->discount,
                'sale_type' => $item->sale_type,
                'sro_item_serial_no' => $item->sro_item_serial_no,
            ])->all();
        } else {
            $items = [[
                'hs_code' => '0101.2100',
                'product_description' => '',
                'rate' => '18%',
                'uom' => 'Numbers, pieces, units',
                'quantity' => '1',
                'total_values' => '0',
                'value_sales_excluding_st' => '1000',
                'fixed_notified_value_or_retail_price' => '0',
                'sales_tax_applicable' => '180',
                'sales_tax_withheld_at_source' => '0',
                'extra_tax' => '0',
                'further_tax' => '0',
                'sro_schedule_no' => '',
                'fed_payable' => '0',
                'discount' => '0',
                'sale_type' => 'Goods at standard rate (default)',
                'sro_item_serial_no' => '',
            ]];
        }
    }
@endphp

<div
    x-data="{
        items: {{ Js::from(array_values($items)) }},
        addItem() {
            this.items.push({
                hs_code: '', product_description: '', rate: '18%', uom: 'Numbers, pieces, units',
                quantity: '1', total_values: '0', value_sales_excluding_st: '0',
                fixed_notified_value_or_retail_price: '0', sales_tax_applicable: '0',
                sales_tax_withheld_at_source: '0', extra_tax: '0', further_tax: '0',
                sro_schedule_no: '', fed_payable: '0', discount: '0',
                sale_type: 'Goods at standard rate (default)', sro_item_serial_no: ''
            });
        },
        removeItem(index) {
            if (this.items.length > 1) this.items.splice(index, 1);
        }
    }"
    class="space-y-6"
>
    <div class="rounded-xl border border-ink-200 dark:border-ink-800 bg-white dark:bg-ink-900 p-6 space-y-4">
        <h2 class="font-display text-lg">Invoice header</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="invoice_type" value="Invoice type" />
                <select id="invoice_type" name="invoice_type" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 dark:text-ink-100 shadow-sm focus:border-sun-500 focus:ring-sun-500">
                    @foreach (['Sale Invoice', 'Debit Note'] as $type)
                        <option value="{{ $type }}" @selected(old('invoice_type', $isEdit ? $invoice->invoice_type : 'Sale Invoice') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="invoice_date" value="Invoice date" />
                <x-text-input id="invoice_date" name="invoice_date" type="date" class="mt-1 block w-full" :value="old('invoice_date', $isEdit ? $invoice->invoice_date->format('Y-m-d') : now()->format('Y-m-d'))" required />
            </div>
            <div>
                <x-input-label for="scenario_id" value="Sandbox scenario ID" />
                <select id="scenario_id" name="scenario_id" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 dark:text-ink-100 shadow-sm focus:border-sun-500 focus:ring-sun-500" required>
                    @foreach ($scenarios as $id => $label)
                        <option value="{{ $id }}" @selected(old('scenario_id', $isEdit ? $invoice->scenario_id : 'SN001') === $id)>{{ $id }} — {{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="buyer_registration_type" value="Buyer registration type" />
                <select id="buyer_registration_type" name="buyer_registration_type" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 dark:text-ink-100 shadow-sm focus:border-sun-500 focus:ring-sun-500">
                    @foreach (['Registered', 'Unregistered'] as $reg)
                        <option value="{{ $reg }}" @selected(old('buyer_registration_type', $isEdit ? $invoice->buyer_registration_type : 'Registered') === $reg)>{{ $reg }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="buyer_ntn_cnic" value="Buyer NTN / CNIC" />
                <x-text-input id="buyer_ntn_cnic" name="buyer_ntn_cnic" class="mt-1 block w-full" :value="old('buyer_ntn_cnic', $isEdit ? $invoice->buyer_ntn_cnic : '')" />
            </div>
            <div>
                <x-input-label for="buyer_business_name" value="Buyer business name" />
                <x-text-input id="buyer_business_name" name="buyer_business_name" class="mt-1 block w-full" :value="old('buyer_business_name', $isEdit ? $invoice->buyer_business_name : '')" required />
            </div>
            <div>
                <x-input-label for="buyer_province" value="Buyer province" />
                <x-text-input id="buyer_province" name="buyer_province" class="mt-1 block w-full" :value="old('buyer_province', $isEdit ? $invoice->buyer_province : 'Sindh')" required />
            </div>
            <div>
                <x-input-label for="buyer_address" value="Buyer address" />
                <x-text-input id="buyer_address" name="buyer_address" class="mt-1 block w-full" :value="old('buyer_address', $isEdit ? $invoice->buyer_address : '')" required />
            </div>
            <div class="sm:col-span-2">
                <x-input-label for="invoice_ref_no" value="Invoice ref no (debit notes)" />
                <x-text-input id="invoice_ref_no" name="invoice_ref_no" class="mt-1 block w-full" :value="old('invoice_ref_no', $isEdit ? $invoice->invoice_ref_no : '')" />
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-ink-200 dark:border-ink-800 bg-white dark:bg-ink-900 p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-lg">Line items</h2>
            <button type="button" @click="addItem()" class="text-sm px-3 py-1.5 rounded-lg border border-ink-300 dark:border-ink-600 hover:bg-ink-50 dark:hover:bg-ink-800">Add item</button>
        </div>

        <template x-for="(item, index) in items" :key="index">
            <div class="rounded-lg border border-ink-200 dark:border-ink-700 p-4 space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-xs uppercase tracking-wide text-ink-500" x-text="'Item ' + (index + 1)"></span>
                    <button type="button" @click="removeItem(index)" class="text-xs text-rose-600 dark:text-rose-400" x-show="items.length > 1">Remove</button>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs text-ink-500">HS code</label>
                        <input type="text" :name="`items[${index}][hs_code]`" x-model="item.hs_code" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs text-ink-500">Description</label>
                        <input type="text" :name="`items[${index}][product_description]`" x-model="item.product_description" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm" required>
                    </div>
                    <div>
                        <label class="text-xs text-ink-500">Rate</label>
                        <input type="text" :name="`items[${index}][rate]`" x-model="item.rate" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm" required>
                    </div>
                    <div>
                        <label class="text-xs text-ink-500">UoM</label>
                        <input type="text" :name="`items[${index}][uom]`" x-model="item.uom" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm" required>
                    </div>
                    <div>
                        <label class="text-xs text-ink-500">Sale type</label>
                        <input type="text" :name="`items[${index}][sale_type]`" x-model="item.sale_type" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm" required>
                    </div>
                    <div>
                        <label class="text-xs text-ink-500">Quantity</label>
                        <input type="number" step="0.0001" :name="`items[${index}][quantity]`" x-model="item.quantity" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm" required>
                    </div>
                    <div>
                        <label class="text-xs text-ink-500">Value excl. ST</label>
                        <input type="number" step="0.01" :name="`items[${index}][value_sales_excluding_st]`" x-model="item.value_sales_excluding_st" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm" required>
                    </div>
                    <div>
                        <label class="text-xs text-ink-500">Sales tax</label>
                        <input type="number" step="0.01" :name="`items[${index}][sales_tax_applicable]`" x-model="item.sales_tax_applicable" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm" required>
                    </div>
                    <div>
                        <label class="text-xs text-ink-500">Further tax</label>
                        <input type="number" step="0.01" :name="`items[${index}][further_tax]`" x-model="item.further_tax" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-ink-500">Extra tax</label>
                        <input type="number" step="0.01" :name="`items[${index}][extra_tax]`" x-model="item.extra_tax" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-ink-500">ST withheld</label>
                        <input type="number" step="0.01" :name="`items[${index}][sales_tax_withheld_at_source]`" x-model="item.sales_tax_withheld_at_source" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-ink-500">Discount</label>
                        <input type="number" step="0.01" :name="`items[${index}][discount]`" x-model="item.discount" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-ink-500">FED payable</label>
                        <input type="number" step="0.01" :name="`items[${index}][fed_payable]`" x-model="item.fed_payable" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-ink-500">Total values</label>
                        <input type="number" step="0.01" :name="`items[${index}][total_values]`" x-model="item.total_values" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-ink-500">Fixed / retail price</label>
                        <input type="number" step="0.01" :name="`items[${index}][fixed_notified_value_or_retail_price]`" x-model="item.fixed_notified_value_or_retail_price" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-ink-500">SRO schedule</label>
                        <input type="text" :name="`items[${index}][sro_schedule_no]`" x-model="item.sro_schedule_no" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-ink-500">SRO item serial</label>
                        <input type="text" :name="`items[${index}][sro_item_serial_no]`" x-model="item.sro_item_serial_no" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 text-sm">
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div class="flex gap-3">
        <x-primary-button>{{ $isEdit ? 'Update draft' : 'Save draft' }}</x-primary-button>
        <a href="{{ $isEdit ? route('invoices.show', $invoice) : route('invoices.index') }}" class="inline-flex items-center px-4 py-2 text-sm text-ink-600 dark:text-ink-300">Cancel</a>
    </div>
</div>
