<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'hs_code',
        'product_description',
        'rate',
        'uom',
        'quantity',
        'total_values',
        'value_sales_excluding_st',
        'fixed_notified_value_or_retail_price',
        'sales_tax_applicable',
        'sales_tax_withheld_at_source',
        'extra_tax',
        'further_tax',
        'sro_schedule_no',
        'fed_payable',
        'discount',
        'sale_type',
        'sro_item_serial_no',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'total_values' => 'decimal:2',
            'value_sales_excluding_st' => 'decimal:2',
            'fixed_notified_value_or_retail_price' => 'decimal:2',
            'sales_tax_applicable' => 'decimal:2',
            'sales_tax_withheld_at_source' => 'decimal:2',
            'extra_tax' => 'decimal:2',
            'further_tax' => 'decimal:2',
            'fed_payable' => 'decimal:2',
            'discount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
