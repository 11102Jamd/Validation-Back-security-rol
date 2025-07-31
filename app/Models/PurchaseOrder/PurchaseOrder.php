<?php

namespace App\Models\PurchaseOrder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_order';

    protected $fillable = [
        'ID_supplier',
        'PurchaseOrderDate',
        'PurchaseTotal'
    ];

    protected $attributes = [
        'PurchaseTotal' => 0,
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'ID_supplier', 'id');
    }

    public function inputOrders(): HasMany
    {
        return $this->hasMany(InputOrder::class, 'ID_purchase_order');
    }

    // public function addInputs(array $inputsData)
    // {
    //     $total = 0;
    //     $createdInputOrders = [];

    //     foreach ($inputsData as $inputData) {
    //         $input = Input::findOrFail($inputData['ID_input']);

    //         $grams = $input->convertUnit(
    //             $inputData['InitialQuantity'],
    //             $inputData['UnitMeasurement']
    //         );

    //         $subtotal = $inputData['InitialQuantity'] * $inputData['UnityPrice'];

    //         $input->increment('CurrentStock', $grams);

    //         $inputOrder = $this->inputOrders()->create([
    //             'ID_input' => $input->id,
    //             'PriceQuantity' => $subtotal,
    //             'InitialQuantity' => $inputData['InitialQuantity'],
    //             'UnitMeasurement' => $inputData['UnitMeasurement'],
    //             'UnityPrice' => $inputData['UnityPrice']
    //         ]);
    //         $total += $subtotal;
    //         $createdInputOrders[] = $inputOrder;
    //     }

    //     $this->PurchaseTotal = $total;
    //     $this->save();

    //     return [
    //         'order' =>  $this->fresh()->load('inputOrders.input'),
    //         'input_orders' => $createdInputOrders
    //     ];
    // }
}
