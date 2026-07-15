<?php

namespace App\Http\Controllers;

use App\Services\Fbr\FbrReferenceClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationSettingsController extends Controller
{
    public function edit(Request $request, FbrReferenceClient $fbrReference): View
    {
        $user = $request->user();
        abort_unless($user->isOwner(), 403);

        $organization = $user->organization;

        return view('settings.organization', [
            'organization' => $organization,
            'scenarios' => config('fbr.scenarios'),
            'provinces' => $fbrReference->provinces($organization),
        ]);
    }

    public function update(Request $request, FbrReferenceClient $fbrReference): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isOwner(), 403);

        $organization = $user->organization;
        abort_if($organization === null, 404);

        $provinces = $fbrReference->provinces($organization);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'seller_ntn_cnic' => ['nullable', 'string', 'max:20'],
            'seller_business_name' => ['nullable', 'string', 'max:255'],
            'seller_province' => ['nullable', 'string', 'max:100', Rule::in($provinces)],
            'seller_address' => ['nullable', 'string', 'max:500'],
            'business_activity' => ['nullable', 'string', 'max:100'],
            'sector' => ['nullable', 'string', 'max:100'],
            'fbr_sandbox_token' => ['nullable', 'string', 'max:2000'],
        ]);

        $token = $data['fbr_sandbox_token'] ?? null;
        unset($data['fbr_sandbox_token']);

        $organization->fill($data);

        if (filled($token)) {
            $organization->fbr_sandbox_token = $token;
            Cache::forget('fbr.provinces.'.hash('sha256', $token));
        }

        $organization->save();

        return back()->with('status', 'Organization settings saved. FBR environment remains sandbox-only.');
    }
}
