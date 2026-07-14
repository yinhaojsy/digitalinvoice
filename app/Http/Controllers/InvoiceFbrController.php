<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\Fbr\FbrSandboxClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class InvoiceFbrController extends Controller
{
    public function validateInvoice(Request $request, Invoice $invoice, FbrSandboxClient $client): RedirectResponse
    {
        $this->authorizeInvoice($request, $invoice);
        abort_unless($invoice->isEditable(), 403);

        $organization = $request->user()->organization;

        try {
            $result = $client->validate($invoice, $organization);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($result['success']) {
            $invoice->update([
                'status' => Invoice::STATUS_VALIDATED,
                'validated_at' => now(),
                'last_error' => null,
            ]);

            return back()->with('status', 'FBR sandbox validation passed. You can post the invoice.');
        }

        $invoice->update([
            'status' => Invoice::STATUS_FAILED,
            'last_error' => $result['submission']->error_message,
        ]);

        return back()->with('error', $result['submission']->error_message ?? 'FBR validation failed.');
    }

    public function postInvoice(Request $request, Invoice $invoice, FbrSandboxClient $client): RedirectResponse
    {
        $this->authorizeInvoice($request, $invoice);
        abort_unless($invoice->isEditable(), 403);

        $organization = $request->user()->organization;

        try {
            $result = $client->post($invoice, $organization);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($result['success']) {
            $fbrNumber = $result['submission']->fbr_invoice_number
                ?? data_get($result['response'], 'invoiceNumber');

            $invoice->update([
                'status' => Invoice::STATUS_POSTED,
                'posted_at' => now(),
                'validated_at' => $invoice->validated_at ?? now(),
                'fbr_invoice_number' => $fbrNumber,
                'last_error' => null,
            ]);

            return back()->with('status', 'Posted to FBR sandbox. Invoice number: '.($fbrNumber ?? 'n/a'));
        }

        $invoice->update([
            'status' => Invoice::STATUS_FAILED,
            'last_error' => $result['submission']->error_message,
        ]);

        return back()->with('error', $result['submission']->error_message ?? 'FBR post failed.');
    }

    private function authorizeInvoice(Request $request, Invoice $invoice): void
    {
        abort_unless(
            $invoice->organization_id === $request->user()->organization_id,
            404
        );
    }
}
