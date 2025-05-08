<?php

namespace App\Models\PurchaseOrder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InputOrder extends Model
{
    protected $table = 'input_order';

    protected $fillable = [
        'ID_purchase_order',
        'ID_input',
        'InitialQuantity',
        'UnitMeasurement',
        'PriceQuantity',
        'UnityPrice'
    ];

    protected $attributes = [
        'InitialQuantity' => 0,
        'UnitMeasurement' => 'Kg',
        'UnityPrice' => 0
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class,'ID_purchase_order');
    }

    public function input():BelongsTo
    {
        return $this->belongsTo(Input::class,'ID_input');
    }
}
