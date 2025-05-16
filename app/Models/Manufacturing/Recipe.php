<?php

namespace App\Models\Manufacturing;

use App\Models\PurchaseOrder\Input;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipe extends Model
{
    protected $table = 'recipe';

    protected $fillable = [
        'ID_manufacturing',
        'ID_inputs',
        'AmountSpent',
        'UnitMeasurement',
        'PriceQuantitySpent'
    ];

    protected $attributes = [
        'PriceQuantitySpent' => 0,
        'UnitMeasurement' => 'g'
    ];

    public function manufacturing(): BelongsTo
    {
        return $this->belongsTo(Manufacturing::class, 'ID_manufacturing');
    }

    public function input(): BelongsTo
    {
        return $this->belongsTo(Input::class, 'ID_inputs');
    }

    public function priceQuantitySpent()
    {
        $input = $this->input;

        if (!$input) {
            throw new \Exception("El insumo asociado no exsite");
        }

        $inputOrder = $this->input->inputOrders()->with(['purchaseOrder' => function($query) {
            $query->orderBy('PurchaseOrderDate', 'desc');
        }])
        ->first();

        if (!$inputOrder) {
            throw new \Exception("No se encontro el insumo asociado con su orden de compra");
        }

        $amountInOrderUnit = $this->AmountSpent;

        if ($inputOrder->UnitMeasurement === 'Kg') {
            $amountInOrderUnit = $this->AmountSpent / 1000;
        } elseif ($inputOrder->UnitMeasurement === 'lb') {
            $amountInOrderUnit = $this->AmountSpent / 453.592;
        }

        return round($amountInOrderUnit * $inputOrder->UnityPrice, 2);

    }

    public function RestoreStockInputs()
    {
        $input = $this->Input;

        if ($input) {
            $input->increment('CurrentStock', $this->AmountSpent);
        }
    }
}

