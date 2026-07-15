@php
    $isEdit = isset($customer) && $customer;
@endphp

<div class="space-y-4 rounded-xl border border-ink-200 dark:border-ink-800 bg-white dark:bg-ink-900 p-6">
    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="name" value="Name (your reference)" />
            <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $isEdit ? $customer->name : '')" required />
            <p class="mt-1 text-xs text-ink-500">Internal label only — not sent to FBR.</p>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="business_name" value="Business name" />
            <x-text-input id="business_name" name="business_name" class="mt-1 block w-full" :value="old('business_name', $isEdit ? $customer->business_name : '')" required />
            <x-input-error :messages="$errors->get('business_name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="ntn" value="NTN" />
            <x-text-input id="ntn" name="ntn" class="mt-1 block w-full" :value="old('ntn', $isEdit ? $customer->ntn : '')" required />
            <x-input-error :messages="$errors->get('ntn')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="strn" value="STRN (optional)" />
            <x-text-input id="strn" name="strn" class="mt-1 block w-full" :value="old('strn', $isEdit ? $customer->strn : '')" />
            <x-input-error :messages="$errors->get('strn')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="province" value="Province" />
            <x-province-select
                name="province"
                :provinces="$provinces"
                :value="old('province', $isEdit ? $customer->province : 'SINDH')"
                required
            />
            <x-input-error :messages="$errors->get('province')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="email" value="Email (optional)" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $isEdit ? $customer->email : '')" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="contact_no" value="Contact no (optional)" />
            <x-text-input id="contact_no" name="contact_no" class="mt-1 block w-full" :value="old('contact_no', $isEdit ? $customer->contact_no : '')" />
            <x-input-error :messages="$errors->get('contact_no')" class="mt-2" />
        </div>
        <div class="sm:col-span-2">
            <x-input-label for="business_address" value="Business address (optional)" />
            <x-text-input id="business_address" name="business_address" class="mt-1 block w-full" :value="old('business_address', $isEdit ? $customer->business_address : '')" />
            <x-input-error :messages="$errors->get('business_address')" class="mt-2" />
        </div>
    </div>

    <div class="flex gap-3 pt-2">
        <x-primary-button>{{ $isEdit ? 'Update customer' : 'Save customer' }}</x-primary-button>
        <a href="{{ route('customers.index') }}" class="inline-flex items-center px-4 py-2 text-sm text-ink-600 dark:text-ink-300">Cancel</a>
    </div>
</div>
