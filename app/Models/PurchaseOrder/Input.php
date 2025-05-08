<?php

namespace App\Models\PurchaseOrder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Input extends Model
{
    protected $table = 'inputs';

    protected $fillable = [
        'InputName',
        'CurrentStock',
        'UnitMeasurementGrams',
    ];

    protected $attributes = [
        'CurrentStock' => 0,
        'UnitMeasurementGrams' => 'g',
    ];

    public function inputOrders(): HasMany
    {
        return $this->hasMany(InputOrder::class, 'ID_input');
    }

    public function convertUnit($quantity, $unitMeasurement)
    {
        switch ($unitMeasurement) {
            case 'Kg':
                return $quantity * 1000;
            case 'lb':
                return $quantity * 453.593;
            default:
                return $quantity;
        }
    }
}
