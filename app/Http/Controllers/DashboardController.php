<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $organization = $request->user()->organization;

        $stats = [
            'drafts' => 0,
            'posted' => 0,
            'failed' => 0,
            'total' => 0,
        ];

        if ($organization) {
            $base = Invoice::query()->forOrganization($organization->id);
            $stats = [
                'drafts' => (clone $base)->whereIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_VALIDATED])->count(),
                'posted' => (clone $base)->where('status', Invoice::STATUS_POSTED)->count(),
                'failed' => (clone $base)->where('status', Invoice::STATUS_FAILED)->count(),
                'total' => (clone $base)->count(),
            ];
        }

        $recent = $organization
            ? Invoice::query()
                ->forOrganization($organization->id)
                ->latest()
                ->limit(8)
                ->get()
            : collect();

        return view('dashboard', [
            'organization' => $organization,
            'stats' => $stats,
            'recent' => $recent,
        ]);
    }
}
