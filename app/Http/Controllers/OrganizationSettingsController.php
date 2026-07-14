<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->isOwner(), 403);

        return view('settings.organization', [
            'organization' => $user->organization,
            'scenarios' => config('fbr.scenarios'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isOwner(), 403);

        $organization = $user->organization;
        abort_if($organization === null, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'seller_ntn_cnic' => ['nullable', 'string', 'max:20'],
            'seller_business_name' => ['nullable', 'string', 'max:255'],
            'seller_province' => ['nullable', 'string', 'max:100'],
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
        }

        $organization->save();

        return back()->with('status', 'Organization settings saved. FBR environment remains sandbox-only.');
    }
}
