<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl text-ink-900 dark:text-ink-50">Organization &amp; FBR sandbox</h1>
        <p class="text-sm text-ink-500 mt-1">Seller profile and sandbox Bearer token · production is disabled</p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200 px-4 py-3 text-sm">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('settings.organization.update') }}" class="space-y-6 rounded-xl border border-ink-200 dark:border-ink-800 bg-white dark:bg-ink-900 p-6">
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="name" value="Organization name" />
                <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $organization->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="seller_ntn_cnic" value="Seller NTN / CNIC" />
                    <x-text-input id="seller_ntn_cnic" name="seller_ntn_cnic" class="mt-1 block w-full" :value="old('seller_ntn_cnic', $organization->seller_ntn_cnic)" placeholder="7 or 13 digits" />
                    <x-input-error :messages="$errors->get('seller_ntn_cnic')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="seller_business_name" value="Seller business name" />
                    <x-text-input id="seller_business_name" name="seller_business_name" class="mt-1 block w-full" :value="old('seller_business_name', $organization->seller_business_name)" />
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="seller_province" value="Seller province" />
                    <x-province-select
                        name="seller_province"
                        :provinces="$provinces"
                        :value="old('seller_province', $organization->seller_province)"
                    />
                    <p class="mt-1 text-xs text-ink-500">Values from FBR provinces API (or fallback list).</p>
                    <x-input-error :messages="$errors->get('seller_province')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="seller_address" value="Seller address" />
                    <x-text-input id="seller_address" name="seller_address" class="mt-1 block w-full" :value="old('seller_address', $organization->seller_address)" />
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="business_activity" value="Business activity" />
                    <x-text-input id="business_activity" name="business_activity" class="mt-1 block w-full" :value="old('business_activity', $organization->business_activity)" placeholder="Manufacturer" />
                </div>
                <div>
                    <x-input-label for="sector" value="Sector" />
                    <x-text-input id="sector" name="sector" class="mt-1 block w-full" :value="old('sector', $organization->sector)" placeholder="All Other Sectors" />
                </div>
            </div>

            <div>
                <x-input-label for="fbr_sandbox_token" value="FBR sandbox Bearer token" />
                <textarea id="fbr_sandbox_token" name="fbr_sandbox_token" rows="3" class="mt-1 block w-full rounded-md border-ink-300 dark:border-ink-700 dark:bg-ink-950 dark:text-ink-100 shadow-sm focus:border-sun-500 focus:ring-sun-500" placeholder="{{ $organization->hasSandboxToken() ? 'Token saved — paste a new token only to replace it' : 'Paste sandbox token from PRAL' }}"></textarea>
                <p class="mt-1 text-xs text-ink-500">Stored encrypted. Sent only as Authorization: Bearer … to sandbox URLs.</p>
                <x-input-error :messages="$errors->get('fbr_sandbox_token')" class="mt-2" />
            </div>

            <div class="flex items-center gap-3">
                <x-primary-button>Save settings</x-primary-button>
                <span class="text-xs text-ink-500">Validate URL: postinvoicedata_sb / validateinvoicedata_sb</span>
            </div>
        </form>
    </div>
</x-app-layout>
