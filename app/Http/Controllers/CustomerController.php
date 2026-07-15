<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\Fbr\FbrReferenceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        private readonly FbrReferenceClient $fbrReference,
    ) {}

    public function index(Request $request): View
    {
        $organization = $request->user()->organization;
        abort_if($organization === null, 404);

        $customers = Customer::query()
            ->forOrganization($organization->id)
            ->orderBy('name')
            ->paginate(20);

        return view('customers.index', compact('customers'));
    }

    public function create(Request $request): View
    {
        $organization = $request->user()->organization;
        abort_if($organization === null, 404);

        return view('customers.create', [
            'customer' => null,
            'provinces' => $this->fbrReference->provinces($organization),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;
        abort_if($organization === null, 404);

        $data = $this->validated($request, $organization);

        Customer::create([
            ...$data,
            'organization_id' => $organization->id,
        ]);

        return redirect()
            ->route('customers.index')
            ->with('status', 'Customer saved.');
    }

    public function edit(Request $request, Customer $customer): View
    {
        $this->authorizeCustomer($request, $customer);

        return view('customers.edit', [
            'customer' => $customer,
            'provinces' => $this->fbrReference->provinces($request->user()->organization),
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorizeCustomer($request, $customer);

        $data = $this->validated($request, $request->user()->organization);

        $customer->update($data);

        return redirect()
            ->route('customers.index')
            ->with('status', 'Customer updated.');
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorizeCustomer($request, $customer);

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('status', 'Customer deleted.');
    }

    /**
     * Live FBR status for a customer / NTN (used when selecting buyer on invoice).
     */
    public function liveStatus(Request $request): JsonResponse
    {
        $organization = $request->user()->organization;
        abort_if($organization === null, 404);

        $data = $request->validate([
            'ntn' => ['required', 'string', 'max:20'],
            'customer_id' => ['nullable', 'integer'],
        ]);

        if (! empty($data['customer_id'])) {
            $customer = Customer::query()
                ->forOrganization($organization->id)
                ->whereKey($data['customer_id'])
                ->firstOrFail();
            $data['ntn'] = $customer->ntn;
        }

        $status = $this->fbrReference->buyerLiveStatus($organization, $data['ntn']);

        return response()->json($status);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, $organization): array
    {
        $provinces = $this->fbrReference->provinces($organization);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'contact_no' => ['nullable', 'string', 'max:50'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'ntn' => ['required', 'string', 'max:20'],
            'strn' => ['nullable', 'string', 'max:30'],
            'province' => ['required', 'string', 'max:100', Rule::in($provinces)],
        ]);
    }

    private function authorizeCustomer(Request $request, Customer $customer): void
    {
        abort_unless(
            $customer->organization_id === $request->user()->organization_id,
            404
        );
    }
}
