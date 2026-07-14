<?php

namespace App\Services\Fbr;

use App\Models\Invoice;
use App\Models\Organization;
use RuntimeException;

class InvoicePayloadBuilder
{
    /**
     * Build FBR DI API JSON payload (sandbox includes scenarioId).
     *
     * @return array<string, mixed>
     */
    public function build(Invoice $invoice, Organization $organization): array
    {
        if (! $organization->sellerProfileComplete()) {
            throw new RuntimeException('Complete seller profile (NTN, name, province, address) before calling FBR.');
        }

        $invoice->loadMissing('items');

        if ($invoice->items->isEmpty()) {
            throw new RuntimeException('Add at least one line item before calling FBR.');
        }

        $payload = [
            'invoiceType' => $invoice->invoice_type,
            'invoiceDate' => $invoice->invoice_date->format('Y-m-d'),
            'sellerNTNCNIC' => $organization->seller_ntn_cnic,
            'sellerBusinessName' => $organization->seller_business_name,
            'sellerProvince' => $organization->seller_province,
            'sellerAddress' => $organization->seller_address,
            'buyerNTNCNIC' => $invoice->buyer_ntn_cnic ?? '',
            'buyerBusinessName' => $invoice->buyer_business_name,
            'buyerProvince' => $invoice->buyer_province,
            'buyerAddress' => $invoice->buyer_address,
            'buyerRegistrationType' => $invoice->buyer_registration_type,
            'invoiceRefNo' => $invoice->invoice_ref_no ?? '',
            'scenarioId' => $invoice->scenario_id,
            'items' => $invoice->items->map(fn ($item) => [
                'hsCode' => $item->hs_code,
                'productDescription' => $item->product_description,
                'rate' => $item->rate,
                'uoM' => $item->uom,
                'quantity' => (float) $item->quantity,
                'totalValues' => (float) $item->total_values,
                'valueSalesExcludingST' => (float) $item->value_sales_excluding_st,
                'fixedNotifiedValueOrRetailPrice' => (float) $item->fixed_notified_value_or_retail_price,
                'salesTaxApplicable' => (float) $item->sales_tax_applicable,
                'salesTaxWithheldAtSource' => (float) $item->sales_tax_withheld_at_source,
                'extraTax' => (float) $item->extra_tax,
                'furtherTax' => (float) $item->further_tax,
                'sroScheduleNo' => $item->sro_schedule_no ?? '',
                'fedPayable' => (float) $item->fed_payable,
                'discount' => (float) $item->discount,
                'saleType' => $item->sale_type,
                'sroItemSerialNo' => $item->sro_item_serial_no ?? '',
            ])->values()->all(),
        ];

        return $payload;
    }
}
