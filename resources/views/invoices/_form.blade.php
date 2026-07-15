@php
    $isEdit = isset($invoice) && $invoice;
    $customerList = collect($customers ?? [])->values()->all();
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
        customers: {{ Js::from($customerList) }},
        customerId: {{ Js::from((string) old('customer_id', $isEdit ? ($invoice->customer_id ?? '') : '')) }},
        buyerNtn: {{ Js::from(old('buyer_ntn_cnic', $isEdit ? ($invoice->buyer_ntn_cnic ?? '') : '')) }},
        buyerBusinessName: {{ Js::from(old('buyer_business_name', $isEdit ? $invoice->buyer_business_name : '')) }},
        buyerProvince: {{ Js::from(old('buyer_province', $isEdit ? $invoice->buyer_province : 'SINDH')) }},
        buyerAddress: {{ Js::from(old('buyer_address', $isEdit ? $invoice->buyer_address : '')) }},
        buyerRegistrationType: {{ Js::from(old('buyer_registration_type', $isEdit ? $invoice->buyer_registration_type : 'Registered')) }},
        scenarioId: {{ Js::from(old('scenario_id', $isEdit ? $invoice->scenario_id : 'SN001')) }},
        liveLoading: false,
        liveStatus: null,
        liveUrl: {{ Js::from(route('customers.live-status')) }},
        csrf: {{ Js::from(csrf_token()) }},
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
        },
        async onCustomerChange() {
            this.liveStatus = null;
            if (! this.customerId) return;
            const customer = this.customers.find(c => String(c.id) === String(this.customerId));
            if (! customer) return;
            this.buyerNtn = customer.ntn || '';
            this.buyerBusinessName = customer.business_name || '';
            this.buyerProvince = customer.province || this.buyerProvince;
            this.buyerAddress = customer.business_address || this.buyerAddress;
            await this.fetchLiveStatus();
        },
        async fetchLiveStatus() {
            if (! this.buyerNtn) return;
            this.liveLoading = true;
            this.liveStatus = null;
            try {
                const response = await fetch(this.liveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        ntn: this.buyerNtn,
                        customer_id: this.customerId || null,
                    }),
                });
                const data = await response.json();
                if (! response.ok) {
                    this.liveStatus = { ok: false, messages: [], errors: [data.message || 'Live status check failed.'] };
                    return;
                }
                this.liveStatus = data;
                if (data.registration_type) {
                    this.buyerRegistrationType = data.registration_type;
                }
                if (data.suggested_scenario) {
                    this.scenarioId = data.suggested_scenario;
                }
            } catch (e) {
                this.liveStatus = { ok: false, messages: [], errors: ['Could not reach FBR live status. You can still continue.'] };
            } finally {
                this.liveLoading = false;
            }
        },
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
                <select id="scenario_id" name="scenario_id" x-model="scenarioId" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 dark:text-ink-100 shadow-sm focus:border-sun-500 focus:ring-sun-500" required>
                    @foreach ($scenarios as $id => $label)
                        <option value="{{ $id }}">{{ $id }} — {{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="buyer_registration_type" value="Buyer registration type" />
                <select id="buyer_registration_type" name="buyer_registration_type" x-model="buyerRegistrationType" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 dark:text-ink-100 shadow-sm focus:border-sun-500 focus:ring-sun-500">
                    @foreach (['Registered', 'Unregistered'] as $reg)
                        <option value="{{ $reg }}">{{ $reg }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <div class="flex items-end justify-between gap-3">
                    <div class="flex-1">
                        <x-input-label for="customer_id" value="Customer" />
                        <select
                            id="customer_id"
                            name="customer_id"
                            x-model="customerId"
                            @change="onCustomerChange()"
                            class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 dark:text-ink-100 shadow-sm focus:border-sun-500 focus:ring-sun-500"
                        >
                            <option value="">Manual buyer entry</option>
                            @foreach ($customerList as $customer)
                                <option value="{{ $customer['id'] }}">{{ $customer['name'] }} — {{ $customer['business_name'] }} ({{ $customer['ntn'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <a href="{{ route('customers.create') }}" class="mb-0.5 text-sm text-sun-700 dark:text-sun-400 whitespace-nowrap">Add customer</a>
                </div>
                <p class="mt-1 text-xs text-ink-500">Selecting a customer fills buyer fields and checks FBR Get_Reg_Type + STATL (soft warnings only).</p>
            </div>
            <div class="sm:col-span-2" x-show="liveLoading || liveStatus" x-cloak>
                <div x-show="liveLoading" class="rounded-lg border border-ink-200 dark:border-ink-700 bg-ink-50 dark:bg-ink-950 px-3 py-2 text-sm text-ink-600 dark:text-ink-300">
                    Checking buyer status with FBR…
                </div>
                <div
                    x-show="!liveLoading && liveStatus"
                    class="rounded-lg border px-3 py-2 text-sm space-y-1"
                    :class="(liveStatus?.errors?.length || (liveStatus?.statl_status && liveStatus.statl_status.toLowerCase().includes('in-active'))) ? 'border-amber-400 bg-amber-50 text-amber-950 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-100' : 'border-emerald-400 bg-emerald-50 text-emerald-950 dark:border-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-100'"
                >
                    <p class="font-medium">
                        FBR live status
                        <span x-show="liveStatus?.registration_type" x-text="'· Reg: ' + liveStatus.registration_type"></span>
                        <span x-show="liveStatus?.statl_status" x-text="'· STATL: ' + liveStatus.statl_status"></span>
                    </p>
                    <template x-for="(message, i) in (liveStatus?.messages || [])" :key="'m'+i">
                        <p x-text="message"></p>
                    </template>
                    <template x-for="(error, i) in (liveStatus?.errors || [])" :key="'e'+i">
                        <p class="text-rose-700 dark:text-rose-300" x-text="error"></p>
                    </template>
                    <p class="text-xs opacity-80">These are guidance only — you can still save and post the invoice.</p>
                </div>
            </div>
            <div>
                <x-input-label for="buyer_ntn_cnic" value="Buyer NTN / CNIC" />
                <div class="mt-1 flex gap-2">
                    <x-text-input id="buyer_ntn_cnic" name="buyer_ntn_cnic" class="block w-full" x-model="buyerNtn" />
                    <button
                        type="button"
                        @click="fetchLiveStatus()"
                        class="shrink-0 rounded-md border border-ink-300 dark:border-ink-600 px-3 text-sm hover:bg-ink-50 dark:hover:bg-ink-800"
                        :disabled="liveLoading || !buyerNtn"
                    >
                        Check FBR
                    </button>
                </div>
            </div>
            <div>
                <x-input-label for="buyer_business_name" value="Buyer business name" />
                <x-text-input id="buyer_business_name" name="buyer_business_name" class="mt-1 block w-full" x-model="buyerBusinessName" required />
            </div>
            <div>
                <x-input-label for="buyer_province" value="Buyer province" />
                <x-province-select
                    name="buyer_province"
                    :provinces="$provinces ?? []"
                    :value="old('buyer_province', $isEdit ? $invoice->buyer_province : 'SINDH')"
                    required
                    x-model="buyerProvince"
                />
                <x-input-error :messages="$errors->get('buyer_province')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="buyer_address" value="Buyer address" />
                <x-text-input id="buyer_address" name="buyer_address" class="mt-1 block w-full" x-model="buyerAddress" required />
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
